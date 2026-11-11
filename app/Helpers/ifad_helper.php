<?php

/**
 * IFAD Portal: which systems the signed-in user may open, which one a page
 * belongs to, and that system's sidebar. Systems are listed in Config\Systems.
 * Autoloaded app-wide via Config\Autoload::$helpers.
 */

if (!function_exists('ifad_systems')) {
    /**
     * Every portal system (Config\Systems), in diagram order.
     */
    function ifad_systems(): array
    {
        return config(\Config\Systems::class)->systems;
    }
}

if (!function_exists('ifad_can_open')) {
    /**
     * Whether the signed-in user may open a system:
     *   fods - any FODS access right (their FODS role grants at least one module)
     *   sorf - a SORF role (set in User Management) that opens at least one SORF page
     * Systems still coming soon open for nobody.
     */
    function ifad_can_open(string $system): bool
    {
        static $cache = [];
        if (isset($cache[$system])) {
            return $cache[$system];
        }
        if (!session('__xsys_myuserzicas_is_logged__')) {
            return false;
        }
        $open = false;
        try {
            if ($system === 'fods') {
                $open = count(fods_user_access_codes()) > 0;
            } elseif ($system === 'sorf') {
                $open = drf_current_user()['username'] !== '' && drf_home_path() !== null;
            }
        } catch (\Throwable $e) {
            // a system whose database is down simply does not open
            log_message('error', "IFAD portal ({$system}): " . $e->getMessage());
        }
        return $cache[$system] = $open;
    }
}

if (!function_exists('ifad_open_systems')) {
    /**
     * Keys of the systems the signed-in user may open.
     */
    function ifad_open_systems(): array
    {
        return array_values(array_filter(array_keys(ifad_systems()), 'ifad_can_open'));
    }
}

if (!function_exists('ifad_home')) {
    /**
     * A system's start page for the signed-in user (a path for site_url()).
     */
    function ifad_home(string $system): string
    {
        if ($system === 'fods') {
            return 'mydashboard';
        }
        if ($system === 'sorf') {
            return drf_home_path() ?? 'portal';
        }
        return 'portal';
    }
}

if (!function_exists('ifad_current_system')) {
    /**
     * The system the current page belongs to: 'sorf' for sorf/..., 'portal'
     * for the portal page, 'fods' for the FODS modules. Shared pages
     * (Messages, Helpdesk, My Account) keep the system the user came from.
     */
    function ifad_current_system(): string
    {
        $first = explode('/', uri_string())[0];
        if ($first === 'sorf') {
            $system = 'sorf';
        } elseif ($first === 'portal' || $first === '') {
            return 'portal';
        } elseif (in_array($first, config(\Config\Systems::class)->shared, true)) {
            $last = (string) session('ifad_system');
            return $last !== '' && ifad_can_open($last) ? $last : 'portal';
        } else {
            $system = 'fods';
        }
        // remembered for the shared pages
        if (session('ifad_system') !== $system) {
            session()->set('ifad_system', $system);
        }
        return $system;
    }
}

if (!function_exists('ifad_stage')) {
    /**
     * A system's stage and the words for it, the same on every page:
     *   live        - "Live"
     *   development - "In development": being built here
     *   integration - "Integration in progress": an existing system the
     *                 portal is connecting to (e.g. through its API)
     * Returns ['key', 'label', 'verb' (for sentences: "being built" /
     * "being connected"), 'class' (CSS modifier: is-live / is-dev / is-int)].
     */
    function ifad_stage(array $system): array
    {
        if (($system['status'] ?? '') === 'live') {
            return ['key' => 'live', 'label' => 'Live', 'verb' => 'live', 'class' => 'is-live'];
        }
        if (($system['stage'] ?? '') === 'integration') {
            return ['key' => 'integration', 'label' => 'Integration in progress', 'verb' => 'being connected', 'class' => 'is-int'];
        }
        return ['key' => 'development', 'label' => 'In development', 'verb' => 'being built', 'class' => 'is-dev'];
    }
}

if (!function_exists('ifad_nav')) {
    /**
     * Sidebar groups for a system, in the shape fods_nav() returns:
     * [['label', 'items' => [['label', 'icon', 'url', 'active', 'count'?]]]].
     * $sorfNav is the SORF page's own menu key (its views pass 'nav').
     */
    function ifad_nav(string $system, string $sorfNav = ''): array
    {
        if ($system === 'fods') {
            return fods_nav();
        }

        if ($system === 'sorf') {
            $groups = [];
            foreach (config(\Config\Navigation::class)->sorf as $group) {
                $items = [];
                foreach ($group['items'] as $item) {
                    if (!drf_can($item['perm'])) {
                        continue;
                    }
                    $item['url'] = sorf_url($item['path']);
                    $item['active'] = $item['key'] === $sorfNav;
                    if (($item['count'] ?? '') === 'approvals') {
                        $item['count'] = drf_approval_count();
                    }
                    $items[] = $item;
                }
                if ($items) {
                    $groups[] = ['label' => $group['label'], 'items' => $items];
                }
            }
            return $groups;
        }

        // the portal (and shared pages reached from it) has no sidebar
        return [];
    }
}
