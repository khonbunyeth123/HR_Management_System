<?php

/**
 * Check if the current logged-in user has a specific permission.
 *
 * Usage:  hasPermission('dashboard', 'view')
 *
 * @param  string $module  e.g. 'dashboard', 'employee', 'report'
 * @param  string $action  e.g. 'view', 'view_daily'
 * @return bool
 */
function hasPermission(string $module, string $action = 'view'): bool
{
    if (!isset($_SESSION['permissions']) || !is_array($_SESSION['permissions'])) {
        return false;
    }
    return in_array($module . '.' . $action, $_SESSION['permissions'], true);
}