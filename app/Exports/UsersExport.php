<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Full /users directory export (admin-only — see routes/web.php, this sits
 * inside the same 'access:admin' group as the /users page itself).
 *
 * Unlike ReconExport/AuditTrailExport, this doesn't accept filters: the
 * Department/Position/Role/Supervisor dropdowns and search box on /users are
 * all client-side (Grid.js fetches the full /users/data payload once and
 * filters in the browser — there's no server-side query-param filtering to
 * mirror here), so "export" means the whole user directory, matching what
 * was actually asked for.
 */
class UsersExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Employee ID', 'First Name', 'Last Name', 'Email', 'Position',
            'Department', 'Role', 'Status', 'Supervisor', 'Supervisor Email',
            'Second Supervisor', 'Second Supervisor Email', 'Leaver Date',
            'Created At',
        ];
    }

    public function array(): array
    {
        // '||' (not CONCAT()) to match ReconExport's convention — portable
        // across Postgres (prod) and SQLite (tests), and NULL-propagates on
        // its own when a supervisor_id/second_supervisor_id doesn't resolve
        // to an actual user, so a missing supervisor comes through as a
        // clean null instead of a stray " " from a partially-null concat.
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
                DB::raw("(s.first_name || ' ' || s.last_name) as supervisor_name"),
                'u.supervisor_email',
                DB::raw("(s2.first_name || ' ' || s2.last_name) as second_supervisor_name"),
                'u.second_supervisor_email',
                'u.effectivity_date_leaver',
                'u.created_at'
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
                $u->status ? ucfirst($u->status) : '',
                $u->supervisor_name,
                $u->supervisor_email,
                $u->second_supervisor_name,
                $u->second_supervisor_email,
                $u->effectivity_date_leaver
                    ? \Carbon\Carbon::parse($u->effectivity_date_leaver)->format('Y-m-d')
                    : null,
                $u->created_at
                    ? \Carbon\Carbon::parse($u->created_at)->format('Y-m-d H:i:s')
                    : null,
            ])
            ->toArray();
    }
}
