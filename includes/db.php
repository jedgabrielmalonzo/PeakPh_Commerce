<?php
// Detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_production = !in_array($hostname, ['localhost', '127.0.0.1']);

// Configure database credentials based on environment
if ($is_production) {
    // InfinityFree Production Database
    $host = "sql308.infinityfree.com";
    $user = "if0_42814827";
    $pass = "PeakPh2026";
    $dbname = "if0_42814827_peakph_db";
} else {
    // Local XAMPP Database
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "peakph_db";
}

$db_connection_error = false;
$conn = null;

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $db_connection_error = true;
    $conn = null;
}

function isDatabaseConnected() {
    global $conn, $db_connection_error;
    return !$db_connection_error && $conn !== null;
}

function executeQuery($query, $params = [], $types = null) {
    global $conn, $db_connection_error;

    if ($db_connection_error || $conn === null) {
        return false;
    }

    try {
        if (empty($params)) {
            $result = $conn->query($query);
        } else {
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            if ($types === null) {
                $types = str_repeat("s", count($params));
            }

            $stmt->bind_param($types, ...$params);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            if (
                stripos($query, 'INSERT') === 0 ||
                stripos($query, 'UPDATE') === 0 ||
                stripos($query, 'DELETE') === 0
            ) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                return $affected_rows > 0;
            } else {
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }
        }

        return $result;

    } catch (Exception $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}
?>