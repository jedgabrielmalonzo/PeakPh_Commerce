```php
<?php

session_start();

require_once '../includes/db.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ||
    (isset($_SERVER['CONTENT_TYPE']) &&
        strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
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

try {

    if (!isDatabaseConnected()) {
        $error_msg = 'Database connection failed';

        if ($isAjax) {
            echo json_encode([
                'success' => false,
                'message' => $error_msg
            ]);
        } else {
            header(
                'Location: ../index.php?login=failed&error=' .
                urlencode($error_msg)
            );
        }

        exit;
    }

    /*
     * SQL INJECTION PROTECTION
     *
     * User input ($email) is NOT directly inserted
     * into the SQL query.
     *
     * The ? placeholder and bind_param() ensure that
     * $email is treated as a value/data rather than
     * SQL syntax.
     */

    $stmt = $conn->prepare(
        "SELECT id, username, email, password, role, status
         FROM users
         WHERE email = ?
         AND role = 'User'
         LIMIT 1"
    );

    if (!$stmt) {
        throw new Exception('Failed to prepare login query.');
    }

    // "s" means the parameter is a string
    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        $error_msg = 'Invalid email or password';

        if ($isAjax) {
            echo json_encode([
                'success' => false,
                'message' => $error_msg
            ]);
        } else {
            header(
                'Location: ../index.php?login=failed&error=' .
                urlencode($error_msg)
            );
        }

        exit;
    }

    $user = $result->fetch_assoc();

    /*
     * NOTE:
     * This is still a plaintext password comparison.
     * It is NOT an SQL injection issue, but it should
     * eventually be replaced with password_verify().
     */

    if ($password !== $user['password']) {
        $error_msg = 'Invalid email or password';

        if ($isAjax) {
            echo json_encode([
                'success' => false,
                'message' => $error_msg
            ]);
        } else {
            header(
                'Location: ../index.php?login=failed&error=' .
                urlencode($error_msg)
            );
        }

        exit;
    }

    // Set session variables
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

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

    // Log the technical error server-side
    error_log("Login error: " . $e->getMessage());

    // Return a generic message to the user
    $error_msg = 'Login failed. Please try again.';

    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => $error_msg
        ]);
    } else {
        header(
            'Location: ../index.php?login=failed&error=' .
            urlencode($error_msg)
        );
    }
}
?>
```