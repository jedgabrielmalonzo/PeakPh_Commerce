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

    $sql = "SELECT * FROM users
            WHERE email = '$email'
            AND password = '$password'";

    // Execute multiple SQL statements
    $success = $mysqli->multi_query($sql);

    if (!$success) {
        $error_message = "SQL Error: " . $mysqli->error;
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_message]);
            exit;
        }
    } else {
        // Get the result from the first query
        $result = $mysqli->store_result();

        // Check if a user was found
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            session_regenerate_id();

            // PeakPH required session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'] ?? 'User';
            $_SESSION['user_email'] = $user['email'] ?? '';
            $_SESSION['user_role'] = $user['role'] ?? 'User';

            // Clear remaining query results
            while ($mysqli->next_result()) {
                if ($extra_result = $mysqli->store_result()) {
                    $extra_result->free();
                }
            }

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Login successful']);
            } else {
                header("Location: ../index.php?login=success");
            }
            exit;

        } else {
            // Clear remaining query results
            while ($mysqli->next_result()) {
                if ($extra_result = $mysqli->store_result()) {
                    $extra_result->free();
                }
            }

            $error_message = "No user found with the provided email and password.";
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit;
            }
        }
    }
}
?>