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

// VULNERABILITY: Removed trimming and cleaning
$full_name = $input['full_name'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// VULNERABILITY: No validation on inputs
// No field existence checks, no email format validation, no password length requirements
// No password confirmation matching

try {
    if (!isDatabaseConnected()) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // VULNERABILITY: Direct SQL concatenation - vulnerable to SQL injection
    // No prepared statements
    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($check_query);

    if ($result && $result->num_rows > 0) {
        $error_msg = 'Email already registered';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // VULNERABILITY: Plain text password storage - no password hashing
    // Removed password_hash() function
    $insert_query = "INSERT INTO users (username, email, password, role, status) VALUES ('$full_name', '$email', '$password', 'User', 'Active')";
    $success = $conn->query($insert_query);

    if (!$success) {
        $error_msg = 'Failed to create account. Please try again.';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Get the new user ID
    $user_id = $conn->insert_id;

    // VULNERABILITY: No logging of registration attempts
    // VULNERABILITY: No OTP verification required
    // Account is immediately active

    // Auto-login the user
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'User';

    if ($isAjax) {
        echo json_encode([
            'success' => true, 
            'message' => 'Account created successfully! Welcome to PeakPH!',
            'user' => [
                'id' => $user_id,
                'name' => $full_name,
                'email' => $email
            ]
        ]);
    } else {
        header('Location: ../index.php?signup=success');
    }

} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    $error_msg = 'Registration failed. Please try again.';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
    }
}
?>