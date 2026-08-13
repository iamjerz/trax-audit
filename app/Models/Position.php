<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The canonical list of Positions — replaces the two hardcoded dropdowns
 * that used to live in resources/views/sub/edituser.blade.php and
 * resources/views/users.blade.php (which had quietly drifted out of sync
 * with each other). Managed via the /positions admin page.
 *
 * `scope` drives the row-level "level" restriction on the four ticket list
 * pages (Monitoring, Recon, Coaching, Triad) via App\Support\PositionScope:
 *   - 'own'  — only records belonging to this user (LDA).
 *   - 'team' — only records belonging to users whose supervisor_id points
 *              back to this user (Supervisor).
 *   - 'all'  — unrestricted (Manager, Sr. Manager, VP, and everything else).
 *
 * This is a separate axis from page_access: page_access controls which
 * *pages* a Position can open; `scope` here controls what *slice of the
 * data* they see once they're on one.
 */
class Position extends Model
{
    protected $fillable = [
        'name',
        'scope',
        'sort_order',
    ];
}
