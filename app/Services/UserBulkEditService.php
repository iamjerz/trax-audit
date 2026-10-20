<?php

namespace App\Services;

use App\Http\Middleware\ForcePasswordChange;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Backs the /users "Bulk Edit (Excel)" feature: validateRows() turns one
 * uploaded UsersBulkEditExport-shaped sheet into a create/update/error plan
 * with no database writes at all (safe to call purely for a preview); commit()
 * separately applies an already-validated plan.
 *
 * Deliberately split into these two steps — rather than one
 * validate-and-save pass — so the admin always sees exactly what will happen,
 * row by row, before a single record changes. See UserBulkImportController
 * for how the plan travels between the two (a short-lived temp file, keyed
 * by a token) so preview and commit can never drift apart from re-parsing
 * the sheet twice.
 *
 * On every UPDATE row, a blank cell means "leave this field alone" (falls
 * back to the user's current value), never "reset to a default" — so
 * accidentally blanking a column in Excel can't silently reactivate,
 * demote, or re-parent a batch of existing users. Defaults (role=user,
 * status=active, login_method=microsoft) only apply to brand-new CREATE
 * rows, matching UserPageController::store()'s own new-user defaults.
 *
 * Scope, on purpose:
 *  - Never touches password. New users get the same standard default
 *    (ForcePasswordChange::DEFAULT_PASSWORD) as the single Add User form;
 *    existing users' passwords are never touched by this tool regardless of
 *    sheet contents — there's no password column in the template at all.
 *  - Never touches extension_access / the admin flag. UserPageController::
 *    store() still auto-assigns a legacy role-bundle access_type on
 *    single-user create, but AccessRoles::$roles is now empty (bundles were
 *    retired in favor of Position-based page_access — see AccessRoles.php),
 *    so that call no longer does anything meaningful. Not replicated here:
 *    'admin' is deliberately a one-by-one grant via the Edit User picker,
 *    never something bulk-assigned by Position/Role.
 *  - Supervisor / 2nd Supervisor must already exist in the database. Wiring
 *    up a brand-new reporting chain entirely within one upload (new hire A
 *    reporting to new hire B, both in the same sheet) isn't supported —
 *    that needs two passes (create the people, then a second upload to set
 *    supervisors) rather than this silently guessing an insert order.
 */
class UserBulkEditService
{
    private const STATUSES = ['active', 'inactive'];
    private const LOGIN_METHODS = ['password', 'microsoft', 'both'];

    /**
     * @param  Collection  $rows  One sheet's rows, as returned by
     *                            Excel::toCollection() with WithHeadingRow.
     * @return array{summary: array<string,int>, results: array}
     */
    public function validateRows(Collection $rows): array
    {
        $existingUsers = User::query()->get()->keyBy('employeeid');
        $emailOwner = $existingUsers->mapWithKeys(fn ($u) => [strtolower($u->email) => $u->employeeid]);
        $positionLookup = Position::pluck('name')->mapWithKeys(fn ($n) => [strtolower($n) => $n]);

        $results = [];
        $seenEmployeeIds = [];
        $seenEmails = [];
        $rowNum = 1; // header occupies row 1; data starts at row 2

        foreach ($rows as $row) {
            $rowNum++;

            $get = fn (string $key) => isset($row[$key]) && $row[$key] !== null
                ? trim((string) $row[$key])
                : '';

            $employeeId = $get('employee_id');

            // Skip fully blank trailing rows (Excel often leaves these
            // behind) rather than reporting them as errors.
            if ($employeeId === '' && $get('first_name') === '' && $get('last_name') === '' && $get('email') === '') {
                continue;
            }

            $isUpdate = $employeeId !== '';
            $existing = $isUpdate ? $existingUsers->get($employeeId) : null;

            if ($isUpdate && ! $existing) {
                $results[] = $this->errorRow(
                    $rowNum,
                    $employeeId,
                    trim($get('first_name') . ' ' . $get('last_name')),
                    ["Employee ID \"{$employeeId}\" doesn't match any existing user. Leave Employee ID blank to create a new user instead."]
                );
                continue;
            }

            // Effective value = the sheet cell if it's non-blank, otherwise
            // (update rows only) whatever the user already has on file.
            $eff = function (string $key, $existingValue) use ($get, $isUpdate): string {
                $cell = $get($key);
                if ($cell !== '') {
                    return $cell;
                }
                return $isUpdate ? (string) ($existingValue ?? '') : '';
            };

            $firstName = $eff('first_name', $existing->first_name ?? null);
            $lastName = $eff('last_name', $existing->last_name ?? null);
            $email = strtolower($eff('email', $existing->email ?? null));
            $department = $eff('department', $existing->department ?? null);
            $positionInput = $eff('position', $existing->position ?? null);
            $role = $eff('role', $existing->role ?? null) ?: 'user';
            $statusInput = strtolower($eff('status', $existing->status ?? null) ?: 'active');
            $loginMethodInput = strtolower($eff('login_method', $existing->login_method ?? null) ?: 'microsoft');
            $supervisorId = $eff('supervisor_employee_id', $existing->supervisor_id ?? null);
            $secondSupervisorId = $eff('2nd_supervisor_employee_id', $existing->second_supervisor_id ?? null);

            $errors = [];

            if ($firstName === '') $errors[] = 'First Name is required.';
            if ($lastName === '') $errors[] = 'Last Name is required.';
            if ($email === '') {
                $errors[] = 'Email is required.';
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email is not a valid email address.';
            }
            if ($department === '') $errors[] = 'Department is required.';

            $canonicalPosition = $positionLookup->get(strtolower($positionInput));
            if ($positionInput === '') {
                $errors[] = 'Position is required.';
            } elseif (! $canonicalPosition) {
                $errors[] = "Position \"{$positionInput}\" doesn't match any entry on the Positions admin page.";
            }

            if (! in_array($statusInput, self::STATUSES, true)) {
                $errors[] = 'Status must be "active" or "inactive".';
            }
            if (! in_array($loginMethodInput, self::LOGIN_METHODS, true)) {
                $errors[] = 'Login Method must be "password", "microsoft", or "both".';
            }

            if ($email !== '') {
                $ownerId = $emailOwner->get($email);
                if ($ownerId && (! $isUpdate || $ownerId !== $employeeId)) {
                    $errors[] = "Email \"{$email}\" is already used by another user ({$ownerId}).";
                }
                if (isset($seenEmails[$email]) && $seenEmails[$email] !== $rowNum) {
                    $errors[] = "Email \"{$email}\" is also used on row {$seenEmails[$email]} of this sheet.";
                }
                $seenEmails[$email] ??= $rowNum;
            }

            if ($isUpdate) {
                if (isset($seenEmployeeIds[$employeeId])) {
                    $errors[] = "Employee ID \"{$employeeId}\" is also used on row {$seenEmployeeIds[$employeeId]} of this sheet.";
                }
                $seenEmployeeIds[$employeeId] ??= $rowNum;
            }

            // No self-reference check here on purpose — the regular Edit
            // User form doesn't enforce one either (nothing stops someone
            // being their own supervisor there), and real data already has
            // at least one legitimate case of it (e.g. a role at the top of
            // its chain with no one else to report to). Rejecting that
            // would block edits to a row's OTHER fields over a value the
            // admin never touched.
            if ($supervisorId !== '' && ! $existingUsers->has($supervisorId)) {
                $errors[] = "Supervisor Employee ID \"{$supervisorId}\" doesn't match any existing user.";
            }
            if ($secondSupervisorId !== '' && ! $existingUsers->has($secondSupervisorId)) {
                $errors[] = "2nd Supervisor Employee ID \"{$secondSupervisorId}\" doesn't match any existing user.";
            }

            $leaverCell = $get('leaver_date');
            $leaverDateError = false;
            if ($leaverCell !== '') {
                [$leaverDate, $leaverDateError] = $this->normalizeDate($row['leaver_date'] ?? null);
                if ($leaverDateError) {
                    $errors[] = "Leaver Date \"{$leaverCell}\" isn't a recognizable date.";
                }
            } else {
                $leaverDate = ($isUpdate && $existing->effectivity_date_leaver)
                    ? Carbon::parse($existing->effectivity_date_leaver)->format('Y-m-d')
                    : null;
            }

            if ($errors) {
                $results[] = $this->errorRow($rowNum, $employeeId ?: null, trim($firstName . ' ' . $lastName), $errors);
                continue;
            }

            // Status/leaver-date sync — same rule as
            // UserPageController::updateUser(): going inactive stamps today
            // unless a leaver date is already on file (or was just set
            // above); going active always clears it.
            $leaverDate = $statusInput === 'inactive' ? ($leaverDate ?: now()->toDateString()) : null;

            $results[] = [
                'row' => $rowNum,
                'action' => $isUpdate ? 'update' : 'create',
                'employeeid' => $employeeId ?: null,
                'name' => trim($firstName . ' ' . $lastName),
                'plan' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'position' => $canonicalPosition,
                    'department' => $department,
                    'role' => $role,
                    'status' => $statusInput,
                    'login_method' => $loginMethodInput,
                    'supervisor_id' => $supervisorId ?: null,
                    'second_supervisor_id' => $secondSupervisorId ?: null,
                    'effectivity_date_leaver' => $leaverDate,
                ],
            ];
        }

        $summary = [
            'total' => count($results),
            'create' => count(array_filter($results, fn ($r) => $r['action'] === 'create')),
            'update' => count(array_filter($results, fn ($r) => $r['action'] === 'update')),
            'error' => count(array_filter($results, fn ($r) => $r['action'] === 'error')),
        ];

        return ['summary' => $summary, 'results' => $results];
    }

    /**
     * Applies an already-validated plan (exactly what validateRows()
     * returned, round-tripped through the preview/commit temp file — see
     * UserBulkImportController). Each row gets its own transaction/catch so
     * one mid-air conflict (say, someone else edited the same user between
     * preview and commit) can't sink an otherwise-good batch; it's reported
     * as a commit-time failure instead.
     */
    public function commit(array $plan): array
    {
        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];
        $createdIds = [];
        $updatedIds = [];

        foreach ($plan['results'] as $row) {
            if ($row['action'] === 'error') {
                continue;
            }

            try {
                DB::transaction(function () use ($row, &$created, &$updated, &$createdIds, &$updatedIds) {
                    $data = $row['plan'];
                    $data['position_id'] = Position::where('name', $data['position'])->value('id');

                    if ($row['action'] === 'create') {
                        $data['employeeid'] = $this->generateEmployeeId();
                        $data['password'] = ForcePasswordChange::DEFAULT_PASSWORD;
                        User::create($data);
                        $created++;
                        $createdIds[] = $data['employeeid'];
                    } else {
                        $user = User::where('employeeid', $row['employeeid'])->lockForUpdate()->firstOrFail();
                        $user->update($data);
                        $updated++;
                        $updatedIds[] = $row['employeeid'];
                    }
                });
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Row {$row['row']} ({$row['employeeid']}): " . $e->getMessage();
            }
        }

        return compact('created', 'updated', 'failed', 'errors', 'createdIds', 'updatedIds');
    }

    private function errorRow(int $rowNum, ?string $employeeId, string $name, array $errors): array
    {
        return ['row' => $rowNum, 'action' => 'error', 'employeeid' => $employeeId, 'name' => $name, 'errors' => $errors];
    }

    /**
     * Same incrementing EMP-###### scheme as
     * UserPageController::generateEmployeeId(). Safe to call once per row
     * inside commit()'s loop even for several new rows in the same sheet,
     * since each row's User::create() fully commits (own DB::transaction())
     * before the next row is processed — there's no concurrency within a
     * single request's loop, only (rarely) across two simultaneous requests,
     * which the unique employeeid/email constraints will catch and report
     * as a per-row commit failure rather than silently colliding.
     */
    private function generateEmployeeId(): string
    {
        $latest = User::orderBy('id', 'desc')->first();
        if (! $latest || ! $latest->employeeid) {
            return 'EMP-202601';
        }
        $number = (int) str_replace('EMP-', '', $latest->employeeid);
        return 'EMP-' . ($number + 1);
    }

    /**
     * Accepts a Y-m-d-ish string (what the export writes back), a
     * free-typed date, or an Excel date serial number (if the editor
     * reformatted the cell as a date instead of leaving it as text).
     *
     * @return array{0: ?string, 1: bool} [normalized "Y-m-d" or null, error?]
     */
    private function normalizeDate($value): array
    {
        if ($value === null || $value === '') {
            return [null, false];
        }
        if (is_numeric($value)) {
            try {
                return [\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d'), false];
            } catch (\Throwable $e) {
                return [null, true];
            }
        }
        try {
            return [Carbon::parse((string) $value)->format('Y-m-d'), false];
        } catch (\Throwable $e) {
            return [null, true];
        }
    }
}
