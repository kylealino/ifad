<?php

/**
 * SORF (Document Request System) inside the IFAD Portal: who is signed in
 * there and what they may do. Autoloaded app-wide via Config\Autoload::$helpers.
 *
 * The person is the portal account (FODS myua_user, default database): their
 * name, position and whether they can sign in. What they may do in SORF is
 * their SORF role, kept in the SORF database ('sorf' connection):
 * sorf.myua_user is the SORF member row (username => role_id, is_active,
 * SORF division/section), and tbl_roles / tbl_role_permission hold the roles
 * (codes in Config\Sorf\Permissions). The member row's full_name and position
 * are a copy of the portal account's, refreshed here, so SORF's own queries
 * (signatory lists, message senders) never have to reach into the FODS database.
 */

use Config\Sorf\Permissions;

if (!function_exists('sorf_url')) {
    /**
     * A SORF page: sorf_url('myrequest?meaction=FORM') => .../sorf/myrequest?meaction=FORM
     */
    function sorf_url(string $path = ''): string
    {
        return site_url('sorf/' . ltrim($path, '/'));
    }
}

if (!function_exists('drf_current_user')) {
    /**
     * The signed-in user as SORF sees them, loaded once per request. Every key
     * is present (blank) when nobody is signed in, the portal account is
     * switched off, or the person has no SORF access (no member row, member
     * switched off). A switched-off role grants nothing.
     */
    function drf_current_user(): array
    {
        static $user = null;

        if ($user !== null) {
            return $user;
        }

        $user = ['username' => '', 'full_name' => '', 'position' => '', 'division' => '', 'section' => '', 'role_id' => 0, 'role_name' => '', 'permissions' => []];

        $username = session('__xsys_myuserzicas__');
        if (empty($username)) {
            return $user;
        }

        // the portal account: who they are and whether they may sign in at all
        $account = \Config\Database::connect()->query("
            SELECT `username`, `full_name`, `position` FROM myua_user WHERE `username` = ? AND `is_active` = 1 LIMIT 1
        ", [$username])->getRowArray();
        if (!$account) {
            return $user;
        }

        $db = \Config\Database::connect('sorf');
        $row = $db->query("
            SELECT u.`recid`, u.`username`, u.`full_name`, u.`position`, u.`division`, u.`section`, u.`role_id`,
                COALESCE(r.`role_name`, '') role_name, COALESCE(r.`is_active`, 0) role_active
            FROM myua_user u
            LEFT JOIN tbl_roles r ON r.`recid` = u.`role_id`
            WHERE u.`username` = ? AND u.`is_active` = 1
            LIMIT 1
        ", [$account['username']])->getRowArray();
        if (!$row) {
            return $user;
        }

        // keep SORF's copy of the name and position in step with the portal account
        $name = trim((string) $account['full_name']);
        $position = trim((string) $account['position']);
        if ($name !== '' && ($row['full_name'] !== $name || $row['position'] !== $position)) {
            $db->query("UPDATE myua_user SET `full_name` = ?, `position` = ? WHERE `recid` = ?", [$name, $position, $row['recid']]);
            $row['full_name'] = $name;
            $row['position'] = $position;
        }

        $row['role_id'] = (int) $row['role_id'];
        $row['permissions'] = (int) $row['role_active'] !== 1 ? [] : array_column($db->query("
            SELECT `perm_code` FROM tbl_role_permission WHERE `role_id` = ?
        ", [$row['role_id']])->getResultArray(), 'perm_code');
        unset($row['role_active'], $row['recid']);
        $user = $row;

        return $user;
    }
}

if (!function_exists('drf_can')) {
    /**
     * Whether the signed-in user's SORF role has a permission (Config\Sorf\Permissions::*).
     */
    function drf_can(string $permission): bool
    {
        return in_array($permission, drf_current_user()['permissions'], true);
    }
}

if (!function_exists('drf_home_path')) {
    /**
     * Where a signed-in user starts in SORF: the first page their role opens
     * (a sorf/ path, null when the role opens none).
     */
    function drf_home_path(): ?string
    {
        foreach ([
            Permissions::HR_VIEW => 'myhrrequest',
            Permissions::REQUEST_FILE => 'myrequest',
            Permissions::REQUEST_APPROVE => 'myapproval',
            Permissions::ADMIN_ROLES => 'myroles',
        ] as $permission => $path) {
            if (drf_can($permission)) {
                return 'sorf/' . $path;
            }
        }
        return null;
    }
}

if (!function_exists('drf_approval_count')) {
    /**
     * Requests waiting for the signed-in user's "Noted by" signature (sidebar badge).
     */
    function drf_approval_count(): int
    {
        $username = drf_current_user()['username'];
        if ($username === '' || !drf_can(Permissions::REQUEST_APPROVE)) {
            return 0;
        }

        $row = \Config\Database::connect('sorf')->query("
            SELECT COUNT(*) total FROM tbl_request_hd WHERE `noted_by_user` = ? AND `status` = ?
        ", [$username, \Config\Sorf\RequestForms::STATUS_FOR_APPROVAL])->getRowArray();

        return (int) ($row['total'] ?? 0);
    }
}

if (!function_exists('drf_initials')) {
    /**
     * "DELA CRUZ, JUAN M." -> "JD", "HR Personnel" -> "HP"
     */
    function drf_initials(string $name): string
    {
        $parts = array_map('trim', explode(',', $name, 2));
        // staff names are stored surname first
        $words = preg_split('/\s+/', trim(count($parts) === 2 ? $parts[1] . ' ' . $parts[0] : $parts[0]), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return '?';
        }
        $first = mb_substr($words[0], 0, 1);
        $last = count($words) > 1 ? mb_substr($words[count($words) - 1], 0, 1) : '';
        return mb_strtoupper($first . $last);
    }
}

if (!function_exists('drf_status_tone')) {
    /**
     * The drf.css tone class of a request status.
     */
    function drf_status_tone(string $status): string
    {
        return [
            \Config\Sorf\RequestForms::STATUS_FOR_APPROVAL => 'tone-amber',
            \Config\Sorf\RequestForms::STATUS_RETURNED => 'tone-red',
            \Config\Sorf\RequestForms::STATUS_PENDING => 'tone-slate',
            \Config\Sorf\RequestForms::STATUS_COMPLIANCE => 'tone-indigo',
            \Config\Sorf\RequestForms::STATUS_APPROVED => 'tone-green',
            \Config\Sorf\RequestForms::STATUS_DENIED => 'tone-red',
        ][$status] ?? 'tone-slate';
    }
}

if (!function_exists('drf_money')) {
    /**
     * Blank for "no amount", otherwise 1,234.50
     */
    function drf_money($value): string
    {
        return $value === null || $value === '' ? '' : number_format((float) $value, 2);
    }
}

if (!function_exists('drf_doc_fill')) {
    /**
     * A value on a Special Order template, or - when the request did not
     * give one - a marked placeholder for HR to type over in the editor.
     */
    function drf_doc_fill($value, string $hint): string
    {
        $value = trim((string) $value);
        return $value !== '' ? nl2br(esc($value)) : '<span class="doc-fill">[' . esc($hint) . ']</span>';
    }
}

if (!function_exists('drf_doc_so_no')) {
    /**
     * "PTC-0012" once HR has entered the S.O. number, otherwise "PTC-_______".
     */
    function drf_doc_so_no(string $prefix, string $soNo): string
    {
        $soNo = trim($soNo);
        if ($soNo === '') {
            return esc($prefix) . '_______';
        }
        // HR may type the number with or without the series prefix
        return esc(stripos($soNo, rtrim($prefix, '- ')) === 0 ? $soNo : $prefix . $soNo);
    }
}
