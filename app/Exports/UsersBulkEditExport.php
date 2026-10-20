<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template for the /users "Bulk Edit (Excel)" feature — download, edit,
 * re-upload. Deliberately a different column shape than UsersExport (the
 * plain read-only directory export): every column here is either exactly
 * what UserBulkEditService::validateRows() expects back, or an explicit
 * "(reference only)" column it never reads — so this file round-trips
 * safely instead of silently losing data on the way back in.
 *
 * Conventions that must stay in sync with UserBulkEditService and
 * UsersBulkEditImport's heading row:
 *  - Employee ID blank on upload  => create a new user (ID is auto-assigned
 *    at commit time, never guessed from what's typed here).
 *  - Employee ID filled on upload => must match an existing user, or that
 *    row is rejected rather than silently creating a user under a made-up ID
 *    (avoids colliding with the auto-generator's own EMP-###### sequence).
 *  - Supervisor / 2nd Supervisor travel by Employee ID (what users.
 *    supervisor_id/second_supervisor_id actually store) and must already
 *    exist in the database — the "(reference only)" Name columns exist
 *    purely so an editor can see whose ID they're looking at; they're
 *    never parsed back on import. Wiring up a brand-new reporting chain
 *    entirely within one upload isn't supported — see UserBulkEditService.
 *  - No password column, no admin-access column, on purpose — see
 *    UserBulkEditService's class docblock for why.
 */
class UsersBulkEditExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Employee ID', 'First Name', 'Last Name', 'Email', 'Position',
            'Department', 'Role', 'Status', 'Login Method',
            'Supervisor Employee ID', 'Supervisor Name (reference only)',
            '2nd Supervisor Employee ID', '2nd Supervisor Name (reference only)',
            'Leaver Date',
        ];
    }

    public function array(): array
    {
        // '||' (not CONCAT()) to match UsersExport/ReconExport's convention —
        // portable across Postgres (prod) and SQLite (tests), and
        // NULL-propagates on its own when a supervisor doesn't resolve to an
        // actual user, so a missing supervisor comes through as a clean null
        // instead of a stray " " from a partially-null concat.
        return DB::table('users as u')
            ->leftJoin('users as s', 'u.supervisor_id', '=', 's.employeeid')
            ->leftJoin('users as s2', 'u.second_supervisor_id', '=', 's2.employeeid')
            ->select(
                'u.employeeid',
                'u.first_name',
                'u.last_name',
                'u.email',
                'u.position',
                'u.department',
                'u.role',
                'u.status',
                'u.login_method',
                'u.supervisor_id',
                DB::raw("(s.first_name || ' ' || s.last_name) as supervisor_name"),
                'u.second_supervisor_id',
                DB::raw("(s2.first_name || ' ' || s2.last_name) as second_supervisor_name"),
                'u.effectivity_date_leaver'
            )
            ->orderBy('u.first_name')
            ->orderBy('u.last_name')
            ->get()
            ->map(fn ($u) => [
                $u->employeeid,
                $u->first_name,
                $u->last_name,
                $u->email,
                $u->position,
                $u->department,
                $u->role,
                $u->status,
                $u->login_method,
                $u->supervisor_id,
                $u->supervisor_name,
                $u->second_supervisor_id,
                $u->second_supervisor_name,
                $u->effectivity_date_leaver
                    ? \Carbon\Carbon::parse($u->effectivity_date_leaver)->format('Y-m-d')
                    : null,
            ])
            ->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
