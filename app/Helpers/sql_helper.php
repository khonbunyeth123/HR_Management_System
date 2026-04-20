<?php
/**
 * Build dynamic SQL WHERE clause with filters
 * $filters: array of ['property' => ..., 'value' => ...]
 * Returns: [$whereSQL, $params, $types]
 */
function buildSQLFilter(array $filters): array
{
    $sqlFilters = [];
    $params = [];
    $types = "";

    foreach ($filters as $filter) {
        if (!isset($filter['property']) || !isset($filter['value']))
            continue;

        $property = $filter['property'];
        $value = $filter['value'];
        $operator = "=";

        // --- Support operators: __gte, __lte, __gt, __lt, __ne ---
        if (strpos($property, "__") !== false) {
            [$property, $op] = explode("__", $property, 2);
            switch ($op) {
                case "gte":
                    $operator = ">=";
                    break;
                case "lte":
                    $operator = "<=";
                    break;
                case "gt":
                    $operator = ">";
                    break;
                case "lt":
                    $operator = "<";
                    break;
                case "ne":
                    $operator = "!=";
                    break;
            }
        }

        // --- Handle BETWEEN for array values ---
        if (is_array($value) && count($value) === 2) {
            $start = $value[0];
            $end = $value[1];

            // Auto convert date strings
            if (strtotime($start) !== false)
                $start = date("Y-m-d H:i:s", strtotime($start));
            if (strtotime($end) !== false)
                $end = date("Y-m-d H:i:s", strtotime($end . " 23:59:59"));

            $sqlFilters[] = "$property BETWEEN ? AND ?";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            continue;
        }

        // --- Auto type conversion ---
        if (is_string($value)) {
            if (strtotime($value) !== false) {
                $value = date("Y-m-d H:i:s", strtotime($value));
                $types .= "s";
            } elseif (is_numeric($value)) {
                $value = (int) $value;
                $types .= "i";
            } elseif (in_array(strtolower($value), ['true', 'false'])) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $types .= "i";
            } else {
                $types .= "s";
            }
        } elseif (is_bool($value)) {
            $value = $value ? 1 : 0;
            $types .= "i";
        } elseif (is_int($value)) {
            $types .= "i";
        } else {
            $types .= "s";
            $value = (string) $value;
        }

        // --- LIKE support ---
        if (is_string($value) && strpos($value, "%") !== false) {
            $sqlFilters[] = "$property LIKE ?";
        } else {
            $sqlFilters[] = "$property $operator ?";
        }

        $params[] = $value;
    }

    $whereSQL = count($sqlFilters) > 0 ? implode(" AND ", $sqlFilters) : "1";
    return [$whereSQL, $params, $types];
}

/**
 * Build dynamic ORDER BY clause
 * $sorts: array of ['property' => ..., 'direction' => ...]
 * $defaultOrderBy: string
 */
function buildSQLSort(array $sorts, string $defaultOrderBy = "created_at"): string
{
    if (empty($sorts))
        return " ORDER BY $defaultOrderBy";

    $orderClauses = [];
    foreach ($sorts as $sort) {
        if (empty($sort['property']))
            continue;

        $direction = strtoupper($sort['direction'] ?? "ASC");
        if (!in_array($direction, ["ASC", "DESC"]))
            $direction = "ASC";

        $orderClauses[] = "{$sort['property']} $direction";
    }

    return " ORDER BY " . implode(", ", $orderClauses);
}
