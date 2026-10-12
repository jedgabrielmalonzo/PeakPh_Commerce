<?php
// Force errors to display on InfinityFree for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
$mysqli = $conn;

// Check if this is an AJAX/Fetch request from the modal or an automated API tool (e.g. Burp Suite)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Read both JSON and form data
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = trim($input["email"] ?? "");
    $password = $input["password"] ?? "";

    // Apply Rate Limiting
    // Standard: 5 attempts allowed within a 900-second (15-minute) observation window
    $ip_address = getClientIP();

    // 1. IP-level check (blocks Burp Suite Cluster Bomb / Intruder / Hydra from attacking machine)
    $ipLimit = checkRateLimitWithDetails('login_ip', $ip_address, 5, 900);

    // 2. Account-level check (blocks distributed password spraying against a specific user)
    $accountLimit = !empty($email)
        ? checkRateLimitWithDetails('login_account', $email, 5, 900)
        : ['allowed' => true, 'retry_after' => 0, 'attempts' => 0];

    if (!$ipLimit['allowed'] || !$accountLimit['allowed']) {
        $retry_after = max($ipLimit['retry_after'], $accountLimit['retry_after'] ?? 0);
        if ($retry_after <= 0) {
            $retry_after = 900;
        }

        logSafely('Login rate limit exceeded (Brute force blocked)', [
            'ip' => $ip_address,
            'email' => $email,
            'retry_after' => $retry_after,
            'ip_attempts' => $ipLimit['attempts'],
            'account_attempts' => $accountLimit['attempts'] ?? 0
        ]);

        $minutes = ceil($retry_after / 60);
        $error_message = "Too many login attempts. Please try again in {$minutes} minutes.";

        // Return RFC-compliant 429 Too Many Requests with Retry-After header
        http_response_code(429);
        header('Retry-After: ' . (int)$retry_after);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode([
            'success' => false,
            'error' => 'Too Many Requests',
            'message' => $error_message,
            'retry_after' => (int)$retry_after
        ]);
        exit;
    }

    // Check for DB connection before proceeding
    if ($mysqli === null) {
        $error_message = "Database connection failed. Please try again later.";
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $error_message]);
            exit;
        } else {
            header("Location: ../index.php?login=failed&error=" . urlencode($error_message));
            exit;
        }
    }

    // SECURE: Use prepared statements and password_verify
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // SECURE: Verify hashed password
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // Reset rate limits on successful authentication
            clearRateLimit('login_ip', $ip_address);
            if (!empty($email)) {
                clearRateLimit('login_account', $email);
            }

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
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    } else {
        header("Location: ../index.php?login=failed&error=" . urlencode($error_message));
        exit;
    }
}
?>