<?php

namespace Config\Sorf;

use CodeIgniter\Config\BaseConfig;

/**
 * What a role may do. The code checks these codes (drf_can()); which role has
 * which code is kept in tbl_role_permission and changed on the Roles page.
 * Adding a feature that needs its own permission = a constant + a line in $groups.
 */
class Permissions extends BaseConfig
{
    public const REQUEST_FILE = 'request.file';
    public const REQUEST_APPROVE = 'request.approve';
    public const HR_VIEW = 'hr.view';
    public const HR_STATUS = 'hr.status';
    public const HR_DOCUMENT = 'hr.document';
    // Accounts and who gets which SORF role are managed in the portal's User
    // Management (FODS), so this code is no longer offered on the Roles page
    public const ADMIN_USERS = 'admin.users';
    public const ADMIN_ROLES = 'admin.roles';

    // group => [code => [label, what it allows]] (the order shown on the Roles page)
    public array $groups = [
        'Requests' => [
            self::REQUEST_FILE => ['File requests', 'Fill in, sign and send requests, and follow them under My Requests.'],
            self::REQUEST_APPROVE => ['Note and approve requests', 'Can be chosen under "Noted by", then sign and approve the request or return it.'],
        ],
        'HR desk' => [
            self::HR_VIEW => ['See requests sent to HR', 'Every request its signatory has approved, and the conversation with the requester.'],
            self::HR_STATUS => ['Decide on requests', 'Change the status, HR remarks and S.O. number of a request.'],
            self::HR_DOCUMENT => ['Prepare the Special Order', 'Start, edit, release and withdraw the Special Order document.'],
        ],
        'Administration' => [
            self::ADMIN_ROLES => ['Manage roles and permissions', 'Full control: create roles and choose what each one may do.'],
        ],
    ];

    // a permission that only works together with another one
    public array $requires = [
        self::HR_STATUS => [self::HR_VIEW],
        self::HR_DOCUMENT => [self::HR_VIEW],
    ];

    /**
     * @return list<string> every permission code
     */
    public function codes(): array
    {
        return array_merge(...array_map('array_keys', array_values($this->groups)));
    }

    public function label(string $code): string
    {
        foreach ($this->groups as $perms) {
            if (isset($perms[$code])) {
                return $perms[$code][0];
            }
        }
        return $code;
    }

    /**
     * Known codes only, with what each one needs added.
     *
     * @return list<string>
     */
    public function normalize(array $codes): array
    {
        $codes = array_values(array_intersect($this->codes(), array_map('strval', $codes)));
        foreach ($codes as $code) {
            foreach ($this->requires[$code] ?? [] as $needed) {
                if (!in_array($needed, $codes, true)) {
                    $codes[] = $needed;
                }
            }
        }
        return array_values(array_intersect($this->codes(), $codes));
    }
}
