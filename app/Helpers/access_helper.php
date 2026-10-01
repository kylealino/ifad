<?php

/**
 * Access checks against tbl_user_access (kept in sync from each user's
 * role by MyUserManagementModel::syncUserAccess). Codes live in
 * Config\AccessCodes. Autoloaded app-wide via Config\Autoload::$helpers.
 */

if (!function_exists('fods_user_access_codes')) {
    /**
     * Active access codes for the logged-in user, loaded once per request.
     */
    function fods_user_access_codes(bool $refresh = false): array
    {
        static $cache = [];

        $username = session('__xsys_myuserzicas__');
        if (empty($username)) {
            return [];
        }

        if ($refresh || !isset($cache[$username])) {
            $rows = \Config\Database::connect()->query("
                SELECT `access_code` FROM tbl_user_access WHERE `username` = ? AND `is_active` = '1'
            ", [$username])->getResultArray();
            $cache[$username] = array_flip(array_column($rows, 'access_code'));
        }

        return $cache[$username];
    }
}

if (!function_exists('fods_has_access')) {
    function fods_has_access(string $code): bool
    {
        return isset(fods_user_access_codes()[$code]);
    }
}

if (!function_exists('fods_has_any_access')) {
    function fods_has_any_access(array $codes): bool
    {
        foreach ($codes as $code) {
            if (fods_has_access($code)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('fods_require_access')) {
    /**
     * Stops an AJAX save/approve/delete with the standard toastr error when
     * the user lacks $code. $action is the verb shown in the message
     * ("Saving", "Updating", "Deleting", "Approve", ...).
     */
    function fods_require_access(string $code, string $action = 'Access'): void
    {
        if (fods_has_access($code)) {
            return;
        }

        echo "
        <script>
        toastr.error('{$action} Access Denied! Please Contact the Administrator.', 'Oops!', {
                progressBar: true,
                closeButton: true,
                timeOut:2000,
            });
        </script>
        ";
        die();
    }
}

if (!function_exists('fods_current_user')) {
    /**
     * Signed-in user's profile + role name, loaded once per request.
     */
    function fods_current_user(): array
    {
        static $user = null;
        if ($user !== null) {
            return $user;
        }

        $username = session('__xsys_myuserzicas__');
        $row = empty($username) ? null : \Config\Database::connect()->query("
            SELECT u.`username`, u.`full_name`, u.`division`, u.`section`, u.`position`, r.`role_name`
            FROM `myua_user` u
            LEFT JOIN `tbl_roles` r ON r.`recid` = u.`role_id`
            WHERE u.`username` = ?
            LIMIT 1
        ", [$username])->getRowArray();

        return $user = array_merge([
            'username' => (string) $username, 'full_name' => (string) $username,
            'division' => '', 'section' => '', 'position' => '', 'role_name' => '',
        ], array_filter((array) $row, fn ($v) => $v !== null));
    }
}

if (!function_exists('fods_is_admin')) {
    /**
     * True for users on the Administrator role (the role the migrations
     * grant every code to). Used where a step is otherwise tied to a named
     * signatory, e.g. Disbursement Approval.
     */
    function fods_is_admin(): bool
    {
        return fods_current_user()['role_name'] === 'Administrator';
    }
}

if (!function_exists('fods_initials')) {
    /**
     * Avatar initials: drops anything after a comma (", CPA") and tokens
     * ending in "." (titles / middle initials like "ATTY." or "M.").
     */
    function fods_initials(string $name): string
    {
        $base = trim(explode(',', $name)[0]);
        $tokens = array_values(array_filter(preg_split('/\s+/', $base), fn ($t) => $t !== '' && substr($t, -1) !== '.'));
        if (empty($tokens)) {
            return strtoupper(substr($base, 0, 1));
        }
        $last = count($tokens) > 1 ? substr(end($tokens), 0, 1) : '';
        return strtoupper(substr($tokens[0], 0, 1) . $last);
    }
}

if (!function_exists('fods_nav')) {
    /**
     * Sidebar groups from Config\Navigation, keeping only the items this
     * user may open (and dropping groups left empty). Each item gets its
     * full 'url' and an 'active' flag for the current page.
     */
    function fods_nav(): array
    {
        $current = explode('/', uri_string())[0];
        $groups = [];

        foreach (config(\Config\Navigation::class)->groups as $group) {
            $items = [];
            foreach ($group['items'] as $item) {
                if ($item['access'] !== null && !fods_has_any_access((array) $item['access'])) {
                    continue;
                }
                $item['url'] = site_url($item['route']) . ($item['query'] !== '' ? '?' . $item['query'] : '');
                $item['active'] = $item['route'] === $current;
                $items[] = $item;
            }
            if ($items) {
                $groups[] = ['label' => $group['label'], 'items' => $items];
            }
        }

        return $groups;
    }
}

if (!function_exists('fods_nav_current')) {
    /**
     * Menu item for the current page, whether or not the user may open it
     * (the access-restricted page uses it to name the module).
     */
    function fods_nav_current(): ?array
    {
        $current = explode('/', uri_string())[0];
        foreach (config(\Config\Navigation::class)->groups as $group) {
            foreach ($group['items'] as $item) {
                if ($item['route'] === $current) {
                    return $item;
                }
            }
        }
        return null;
    }
}
