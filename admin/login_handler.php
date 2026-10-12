<?php
session_name('PEAKPH_ADMIN_SESSION');
session_start();

require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/security.php');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Apply Rate Limiting (5 attempts in 15 minutes = 900 seconds)
    $ip_address = getClientIP();
    $ipLimit = checkRateLimitWithDetails('admin_login_ip', $ip_address, 5, 900);
    $accountLimit = !empty($email)
        ? checkRateLimitWithDetails('admin_login_account', $email, 5, 900)
        : ['allowed' => true, 'retry_after' => 0, 'attempts' => 0];

    if (!$ipLimit['allowed'] || !$accountLimit['allowed']) {
        $retry_after = max($ipLimit['retry_after'], $accountLimit['retry_after'] ?? 0);
        if ($retry_after <= 0) {
            $retry_after = 900;
        }

        logSafely('Admin login rate limit exceeded (Brute force blocked)', [
            'ip' => $ip_address,
            'email' => $email,
            'retry_after' => $retry_after,
            'ip_attempts' => $ipLimit['attempts'],
            'account_attempts' => $accountLimit['attempts'] ?? 0
        ]);

        http_response_code(429);
        header('Retry-After: ' . (int)$retry_after);
        header('Content-Type: application/json; charset=UTF-8');

        $minutes = ceil($retry_after / 60);
        echo json_encode([
            'success' => false,
            'error' => 'Too Many Requests',
            'message' => "Too many admin login attempts. Please try again in {$minutes} minutes.",
            'retry_after' => (int)$retry_after
        ]);
        exit;
    }

    if ($conn === null) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
        header("Location: login.php?login=failed");
        exit;
    }

    // SECURE: Use prepared statement
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Check password (matches either hashed password or plain text)
        if (password_verify($password, $admin['password']) || $password === $admin['password']) {
            // Reset rate limits on successful authentication
            clearRateLimit('admin_login_ip', $ip_address);
            if (!empty($email)) {
                clearRateLimit('admin_login_account', $email);
            }

            $_SESSION['logged_in'] = true;
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['login_time'] = time();

            if ($remember_me) {
                // SECURE: Use cryptographically signed cookie
                $secret = "PEAKPH_SUPER_SECRET_KEY";
                $cookie_data = $admin['email'] . '|' . hash_hmac('sha256', $admin['email'], $secret);
                setcookie('admin_remember', $cookie_data, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }

            if ($isAjax) {
                echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
                exit;
            }

            header("Location: dashboard.php");
            exit;
        }
    }

    // If login fails
    if ($isAjax) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    header("Location: login.php?login=failed");
    exit;
} else {
    // Check remember me
    if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['logged_in'])) {
        $secret = "PEAKPH_SUPER_SECRET_KEY";
        $parts = explode('|', $_COOKIE['admin_remember']);
        
        if (count($parts) === 2) {
            list($stored_email, $hash) = $parts;
            if (hash_equals(hash_hmac('sha256', $stored_email, $secret), $hash)) {
                $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
                $stmt->bind_param("s", $stored_email);
                $stmt->execute();
                $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $_SESSION['logged_in'] = true;
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['login_time'] = time();

            header("Location: dashboard.php");
            exit;
        }
            }
        }
    }

    header("Location: login.php");
    exit;
}
?>