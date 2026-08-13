<?php

namespace App\Support;

/**
 * Historically: role bundles → underlying capability access types, so a user
 * could be assigned one role (e.g. web_user_sup) instead of many individual
 * capabilities.
 *
 * Retired. Web-page access moved to page_access (Position-based, see
 * PageRegistry + the `page:` middleware); the 4 Chrome extension
 * capabilities followed the same move (Position-based, checked directly by
 * menu.blade.php against page_access). With both now assigned directly per
 * Position, the bundle shorthand had nothing left to add, so $roles is empty
 * — kept only so expand() and its existing call sites (CheckAccess,
 * CheckPageAccess, the sidebar/menu composers) don't need to change. The
 * only thing still genuinely per-user is 'admin' (see $assignableAccessTypes).
 */
class AccessRoles
{
    public static array $roles = [];

    /**
     * Every access_type value that can be assigned to a user via the Edit
     * User picker. Just 'admin' — the one flag that's deliberately kept
     * per-person rather than Position-based, so a single individual can be
     * made an admin without their whole Position becoming one.
     */
    public static array $assignableAccessTypes = [
        'admin',
    ];

    /**
     * Expand a set of assigned access types to include any capabilities
     * implied by role bundles. Returns a de-duplicated list.
     *
     * @param  iterable<string>  $types
     * @return array<int, string>
     */
    public static function expand($types): array
    {
        $types = is_array($types) ? $types : iterator_to_array($types);
        $expanded = $types;

        foreach ($types as $t) {
            if (isset(self::$roles[$t])) {
                $expanded = array_merge($expanded, self::$roles[$t]);
            }
        }

        return array_values(array_unique($expanded));
    }
}
