<?php
require_once __DIR__ . '/../configs/db.php';
require_once __DIR__ . '/../configs/functions.php';
global $visitor_table;
function dbInsert(string $table, array $data): bool
{
    global $pdo;

    if (empty($data)) {
        return false;
    }

    // Columns
    $columns = array_keys($data);

    // Placeholders
    $placeholders = array_map(fn($col) => ':' . $col, $columns);

    $sql = sprintf(
        "INSERT INTO `%s` (%s) VALUES (%s)",
        $table,
        implode(', ', array_map(fn($c) => "`$c`", $columns)),
        implode(', ', $placeholders)
    );

    $stmt = $pdo->prepare($sql);

    // Bind values dynamically
    foreach ($data as $column => $value) {
        $stmt->bindValue(':' . $column, $value);
    }

    return $stmt->execute();
}

function dbSelect(
    string $table,
    array $where = [],
    string|array $columns = '*',
    string $orderBy = '',
    int|null $limit = null,
    string $groupBy = ''
): array {
    global $pdo;

    // Columns
    if (is_array($columns)) {
        $columns = implode(', ', array_map(fn($c) => "`$c`", $columns));
    }

    $sql = "SELECT $columns FROM `$table`";
    $conditions = [];
    $bindings = [];

    if (!empty($where)) {
        foreach ($where as $key => $value) {

            // 1️⃣ ARRAY → IN(...)
            if (is_array($value) && !isset($value['start'], $value['end'])) {
                $placeholders = [];
                foreach ($value as $i => $val) {
                    $ph = "{$key}_$i";
                    $placeholders[] = ":$ph";
                    $bindings[$ph] = $val;
                }
                $conditions[] = "`$key` IN (" . implode(',', $placeholders) . ")";
            }

            // 2️⃣ RANGE → BETWEEN
            elseif (is_array($value) && isset($value['start'], $value['end'])) {
                $startKey = "{$key}_start";
                $endKey   = "{$key}_end";
                $conditions[] = "`$key` BETWEEN :$startKey AND :$endKey";
                $bindings[$startKey] = $value['start'];
                $bindings[$endKey]   = $value['end'];
            }

            // 3️⃣ >= or <= operators
            elseif (str_ends_with($key, '>=' ) || str_ends_with($key, '<=')) {
                $column = rtrim($key, '>=<'); // remove operator chars
                $op = str_ends_with($key, '>=') ? '>=' : '<=';
                $conditions[] = "`$column` $op :$column";
                $bindings[$column] = $value;
            }

            // 4️⃣ Default = 
            else {
                $conditions[] = "`$key` = :$key";
                $bindings[$key] = $value;
            }
        }

        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    // GROUP BY
    if ($groupBy) {
        $sql .= " GROUP BY $groupBy";
    }

    // ORDER BY
    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }

    // LIMIT
    if ($limit !== null) {
        $sql .= " LIMIT $limit";
    }

    $stmt = $pdo->prepare($sql);

    // Bind values safely
    foreach ($bindings as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function dbCount(
    string $table,
    array $where = [],
    string $groupBy = ''
): int|array {
    global $pdo;

    $sql = "SELECT COUNT(*) as total";

    if ($groupBy) {
        $sql .= ", `$groupBy`";
    }

    $sql .= " FROM `$table`";

    if (!empty($where)) {
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = :$key";
        }
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    if ($groupBy) {
        $sql .= " GROUP BY `$groupBy`";
    }

    $stmt = $pdo->prepare($sql);

    foreach ($where as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    $stmt->execute();

    return $groupBy
        ? $stmt->fetchAll(PDO::FETCH_ASSOC)
        : (int) $stmt->fetchColumn();
}


function insertVisitorDetails(): bool {
    global $insertData;
    $cookieName = 'daily_visitor';

    // Step 1: Get or create the cookie
    $uuid = getOrCreateCookie();

    // Step 2: Prepare visitor data
    $ip = getClientIP();
    $obj = getIpApiData($ip ?? null);

    $data = [
        'uuid'         => $uuid,
        'ip_address'   => $obj['ip'] ?? $ip,
        'city'         => $obj['city'] ?? 'UNKNOWN',
        'country'      => $obj['country_name'] ?? 'UNKNOWN',
        'page_url'     => ($_SERVER['SERVER_NAME'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''),
        'device_type'  => getDeviceType(),
        'browser_type' => getBrowserName(),
        'others'       => json_encode($obj),
        'visit_time'   => date('Y-m-d H:i:s')
    ];

    // Step 3: Insert data into the database
    $inserted = $insertData($data);

    return $inserted;
}


$insertData = fn(array $data) =>
    dbInsert(
        $visitor_table,
        $data
    );

$getAllVisitors = fn(array $where = []) =>
    dbSelect(
        $visitor_table,
        $where, 
        '*', 
        'visit_time DESC'
    );

$getTodayVisitors = fn() =>
    dbSelect(
        $visitor_table,
        ['DATE(visit_time)' => date('Y-m-d')], // see note below
        '*',
        'visit_time DESC'
    );

$getTodayUniqueVisitors = fn() =>
    dbSelect(
        $visitor_table,
        [],
        '*',
        'visit_time DESC',
        null,
        'uuid'
    );



function getAllVisitorsEventsLog(
    string $uuids = '',
    string $startDate = '',
    string $endDate = ''
): array {
    $filePath = __DIR__ . '/logs/events_log.log';

    if (!file_exists($filePath)) {
        return [];
    }

    // UUID filter
    $uuidFilter = [];
    if (trim($uuids) !== '') {
        $uuidFilter = array_map('trim', explode(',', $uuids));
        $uuidFilter = array_flip($uuidFilter); // faster lookup
    }

    // Date filters
    $startTime = null;
    if (trim($startDate) !== '') {
        $startTime = strtotime($startDate);
    }

    $endTime = null;
    if (trim($endDate) !== '') {
        $endTime = strtotime($endDate);
    }

    $events = [];
    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        return [];
    }

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $data = json_decode($line, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            continue;
        }

        /* -------- AND CONDITIONS -------- */

        // UUID condition
        if (!empty($uuidFilter)) {
            if (!isset($data['uuid']) || !isset($uuidFilter[$data['uuid']])) {
                continue;
            }
        }

        // Date range condition
        if (isset($data['logged_at'])) {
            $logTime = strtotime($data['logged_at']);
            if ($logTime === false) {
                continue;
            }

            if ($startTime !== null && $logTime < $startTime) {
                continue;
            }

            if ($endTime !== null && $logTime > $endTime) {
                continue;
            }
        } else {
            continue;
        }

        $events[] = $data;
    }

    fclose($handle);
    return $events;
}


