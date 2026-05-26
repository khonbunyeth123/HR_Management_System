<?php

    /**
     * Whitelisted column names allowed in WHERE and ORDER BY clauses.
     * Add any new column names here before using them in filters or sorts.
     */
    function getAllowedColumns(): array
    {
        return [
            // common
            'id', 'uuid', 'status', 'created_at', 'updated_at',
            // employees
            'full_name', 'email', 'phone', 'username', 'position', 'department',
            // attendance
            'date', 'check_in', 'check_out', 'check_type_id', 'employee_id',
            // leave
            'leave_type_id', 'start_date', 'end_date', 'approved_at', 'reason',
            // users / roles / permissions
            'user_id', 'role_id', 'permission_id', 'tokenable_type', 'tokenable_id',
            // reports
            'total_days', 'total_hours',
        ];
    }

    /**
     * Build dynamic SQL WHERE clause with filters.
     *
     * $filters: array of ['property' => ..., 'value' => ...]
     * Returns: [$whereSQL, $params, $types]
     *
     * FIX: column names (property) are validated against a whitelist before being
     * interpolated into SQL, preventing SQL injection via column names.
     */
    function buildSQLFilter(array $filters): array
    {
        $allowed    = getAllowedColumns();
        $sqlFilters = [];
        $params     = [];
        $types      = '';

        foreach ($filters as $filter) {
            if (!isset($filter['property'], $filter['value'])) {
                continue;
            }

            $rawProperty = (string) $filter['property'];
            $value       = $filter['value'];
            $operator    = '=';

            // --- Parse operator suffix: column__gte, column__lte, etc. ---
            $property = $rawProperty;
            if (strpos($rawProperty, '__') !== false) {
                [$property, $op] = explode('__', $rawProperty, 2);
                switch ($op) {
                    case 'gte': $operator = '>='; break;
                    case 'lte': $operator = '<='; break;
                    case 'gt':  $operator = '>';  break;
                    case 'lt':  $operator = '<';  break;
                    case 'ne':  $operator = '!='; break;
                    default:    $operator = '=';  break;
                }
            }

            // FIX: reject any column name not in the whitelist
            if (!in_array($property, $allowed, true)) {
                continue;
            }

            // Backtick-quote the validated column name
            $col = '`' . $property . '`';

            // --- BETWEEN for two-element arrays ---
            if (is_array($value) && count($value) === 2) {
                $start = $value[0];
                $end   = $value[1];

                // FIX: only accept values that look like dates (YYYY-MM-DD…)
                $start = self_parseDate($start);
                $end   = self_parseDate($end, true);

                if ($start === null || $end === null) {
                    continue; // reject unparseable date pairs
                }

                $sqlFilters[] = "$col BETWEEN ? AND ?";
                $params[]     = $start;
                $params[]     = $end;
                $types       .= 'ss';
                continue;
            }

            // --- Type conversion for scalar values ---
            if (is_string($value)) {
                // FIX: only accept date-like strings (not "next year", "tomorrow", etc.)
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                    $parsed = self_parseDate($value);
                    if ($parsed === null) continue;
                    $value  = $parsed;
                    $types .= 's';
                } elseif (is_numeric($value)) {
                    $value  = (int) $value;
                    $types .= 'i';
                } elseif (in_array(strtolower($value), ['true', 'false'], true)) {
                    $value  = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                    $types .= 'i';
                } else {
                    $types .= 's';
                }
            } elseif (is_bool($value)) {
                $value  = $value ? 1 : 0;
                $types .= 'i';
            } elseif (is_int($value)) {
                $types .= 'i';
            } else {
                $value  = (string) $value;
                $types .= 's';
            }

            // --- LIKE support (only for string values containing %) ---
            if (is_string($value) && strpos($value, '%') !== false) {
                $sqlFilters[] = "$col LIKE ?";
            } else {
                $sqlFilters[] = "$col $operator ?";
            }

            $params[] = $value;
        }

        $whereSQL = !empty($sqlFilters) ? implode(' AND ', $sqlFilters) : '1';
        return [$whereSQL, $params, $types];
    }

    /**
     * Build dynamic ORDER BY clause.
     *
     * $sorts: array of ['property' => ..., 'direction' => ...]
     * $defaultOrderBy: column name — also validated against the whitelist.
     *
     * FIX: both the sort column and the default are validated against a whitelist.
     */
    function buildSQLSort(array $sorts, string $defaultOrderBy = 'created_at'): string
    {
        $allowed = getAllowedColumns();

        // FIX: validate the default column too — callers might pass user input
        if (!in_array($defaultOrderBy, $allowed, true)) {
            $defaultOrderBy = 'created_at';
        }

        if (empty($sorts)) {
            return " ORDER BY `$defaultOrderBy`";
        }

        $orderClauses = [];

        foreach ($sorts as $sort) {
            if (empty($sort['property'])) {
                continue;
            }

            $property = (string) $sort['property'];

            // FIX: reject column names not in the whitelist
            if (!in_array($property, $allowed, true)) {
                continue;
            }

            $direction = strtoupper($sort['direction'] ?? 'ASC');
            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }

            $orderClauses[] = "`$property` $direction";
        }

        return !empty($orderClauses)
            ? ' ORDER BY ' . implode(', ', $orderClauses)
            : " ORDER BY `$defaultOrderBy`";
    }

    /**
     * Internal helper — safely parse a date string.
     * Only accepts strings that start with YYYY-MM-DD to reject
     * relative expressions like "next year" or "tomorrow".
     *
     * Returns a formatted datetime string or null on failure.
     */
    function self_parseDate(string $value, bool $endOfDay = false): ?string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return null;
        }

        $suffix = $endOfDay ? ' 23:59:59' : '';
        $ts     = strtotime($value . $suffix);

        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }