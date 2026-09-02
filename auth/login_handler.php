<?php
session_start();
require_once '../includes/db.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
          isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

if ($isAjax) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    } else {
        header('Location: ../index.php?error=method_not_allowed');
    }
    exit;
}

// Handle both JSON and form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

// VULNERABILITY: No validation on inputs - allows SQL injection and weak passwords
// No email format validation, no password length checks

try {
    if (!isDatabaseConnected()) {
        $error_msg = 'Database connection failed';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // VULNERABILITY: Direct SQL concatenation - vulnerable to SQL injection
    // No prepared statements or parameterized queries
    $query = "SELECT id, username, email, password, role, status FROM users WHERE email = '$email' AND role = 'User'";
    $result = $conn->query($query);

    if (!$result || $result->num_rows === 0) {
        $error_msg = 'Invalid email or password';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    $user = $result->fetch_assoc();

    // VULNERABILITY: No rate limiting - allows brute force attacks
    // Removed checkRateLimit() function call

    // VULNERABILITY: Plain text password comparison - no hashing
    // Removed password_verify() - now doing direct comparison
    if ($password !== $user['password']) {
        $error_msg = 'Invalid email or password';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Set session variables
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // VULNERABILITY: Removed logging - no audit trail of login attempts
    // No accountability for failed or successful login attempts

    if ($isAjax) {
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        header('Location: ../index.php?login=success');
    }

} catch (Exception $e) {
    // VULNERABILITY: No secure error handling
    // Error messages may expose database structure
    error_log("Login error: " . $e->getMessage());
    $error_msg = 'Login failed. Please try again.';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
    }
}
?>