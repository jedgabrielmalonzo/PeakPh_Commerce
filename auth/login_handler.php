<?php
// Force errors to display on InfinityFree for debugging
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

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Read both JSON and form data
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = $input["email"] ?? "";
    $password = $input["password"] ?? "";

    // SECURE: Use prepared statements and password_verify
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // SECURE: Verify hashed password
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            session_regenerate_id(true);

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'] ?? 'User';
            $_SESSION['user_email'] = $user['email'] ?? '';
            $_SESSION['user_role'] = $user['role'] ?? 'User';

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Login successful']);
            } else {
                header("Location: ../index.php?login=success");
            }
            exit;
        }
    }

    // Generic error message for both non-existent user and wrong password
    $error_message = "Invalid email or password.";
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    } else {
        header("Location: ../index.php?login=failed&error=" . urlencode($error_message));
        exit;
    }
}
?>