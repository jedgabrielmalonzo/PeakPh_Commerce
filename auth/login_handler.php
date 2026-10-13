<?php
// Force errors to display for debugging and error-based SQL injection testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/db.php';
$mysqli = $conn;

// Check if this is an AJAX/Fetch request from the modal
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json');
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    } else {
        header("Location: ../index.php");
    }
    exit;
}

// Check database connection
if (!isDatabaseConnected() || !$mysqli) {
    $error_message = "Database connection failed";
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_message]);
    } else {
        header("Location: ../index.php?login=failed&error=" . urlencode($error_message));
        echo $error_message;
    }
    exit;
}

// Read both JSON and form data
// VULNERABILITY: No input sanitization or validation (allows SQL injection and weak passwords)
$raw_input = file_get_contents('php://input');
$json_input = json_decode($raw_input, true);
$input = is_array($json_input) ? $json_input : $_POST;

$email = $input["email"] ?? "";
$password = $input["password"] ?? "";

// VULNERABILITY: No rate limiting (allows brute force attacks)
// Removed checkRateLimit()

// VULNERABILITY: Direct SQL concatenation - vulnerable to SQL injection
// No prepared statements or parameterized queries
$sql = "SELECT * FROM users
        WHERE email = '$email'
        AND password = '$password'";

// Execute query (multi_query enables stacked queries testing)
$success = $mysqli->multi_query($sql);

if (!$success) {
    // VULNERABILITY: Verbose SQL error message exposed (error-based SQL injection)
    $error_message = "SQL Error: " . $mysqli->error;
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_message]);
    } else {
        header("Location: ../index.php?login=failed&error=" . urlencode($error_message));
        echo $error_message;
    }
    exit;
}

$user = null;
$result = $mysqli->store_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $result->free();
}

// Clear any remaining query results from multi_query
while ($mysqli->more_results() && $mysqli->next_result()) {
    if ($extra_result = $mysqli->store_result()) {
        $extra_result->free();
    }
}

// Fallback for existing accounts that may have bcrypt hashed passwords in database
if (!$user && !empty($email) && !empty($password)) {
    $fallback_sql = "SELECT * FROM users WHERE email = '$email'";
    $fallback_result = $mysqli->query($fallback_sql);
    if ($fallback_result && $fallback_result->num_rows > 0) {
        $potential_user = $fallback_result->fetch_assoc();
        if (password_verify($password, $potential_user['password'])) {
            $user = $potential_user;
        }
        $fallback_result->free();
    }
}

// Check if a user was found / authenticated
if ($user) {
    session_regenerate_id(true);

    // PeakPH required session variables
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'] ?? 'User';
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['user_role'] = 'User';

    // VULNERABILITY: No audit logging (audit_trail removed)

    if ($isAjax) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['username'] ?? 'User',
                'email' => $user['email'] ?? ''
            ]
        ]);
    } else {
        header("Location: ../index.php?login=success");
        echo "LOGIN_SUCCESS";
    }
    exit;
} else {
    $error_message = "Invalid email or password";
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_message]);
    } else {
        header("Location: ../index.php?login=failed&error=" . urlencode($error_message));
        echo $error_message;
    }
    exit;
}