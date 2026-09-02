<?php
session_start();

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$isPostRequest = $_SERVER['REQUEST_METHOD'] === 'POST';

// Log successful logout
if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    require_once 'includes/db.php';
    
    if (isDatabaseConnected()) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $log_query = "INSERT INTO audit_trail (table_name, record_id, action, new_values, user_id, user_email, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $log_values = json_encode(['logout_time' => date('Y-m-d H:i:s'), 'ip_address' => $ip_address]);
        executeQuery($log_query, ['users', $_SESSION['user_id'], 'LOGOUT', $log_values, $_SESSION['user_id'], $_SESSION['user_email'], $ip_address], 'sisssss');
    }
}

// Clear all session data
session_unset();
session_destroy();

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Handle AJAX request
if ($isAjax || $isPostRequest) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Successfully logged out']);
    exit();
}

// Regular redirect for non-AJAX requests
header("Location: index.php?logout=success");
exit();
?>