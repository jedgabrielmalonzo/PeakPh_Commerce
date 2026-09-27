<?php
// Set same session configuration as auth_helper
ini_set('session.gc_maxlifetime', 8 * 60 * 60);
ini_set('session.cookie_lifetime', 8 * 60 * 60);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('PEAKPH_ADMIN_SESSION');
session_start();

require_once("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        header("Location: login.php?login=failed");
        exit;
    }

    if (!isDatabaseConnected() || !$conn) {
        error_log("Database connection unavailable during admin login");
        header("Location: login.php?login=failed");
        exit;
    }

    // Query database for admin user
    $stmt = $conn->prepare("SELECT id, username, email, password, role, status FROM users WHERE email = ? AND role = 'Admin' AND status = 'Active' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using password_verify (with plaintext match fallback)
            $password_matches = password_verify($password, $user['password']) || ($password === $user['password']);

            if ($password_matches) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['logged_in'] = true;
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['session_regenerated'] = time();

                // If remember me is requested
                if ($remember_me) {
                    $cookie_value = base64_encode($user['email'] . ':' . time());
                    setcookie('admin_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                }

                $stmt->close();
                header("Location: dashboard.php");
                exit;
            }
        }
        $stmt->close();
    }

    // Invalid credentials or not active admin
    header("Location: login.php?login=failed");
    exit;
} else {
    // Check for remember me cookie
    if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['logged_in'])) {
        $cookie_data = base64_decode($_COOKIE['admin_remember']);
        if (strpos($cookie_data, ':') !== false) {
            list($stored_email, $timestamp) = explode(':', $cookie_data);

            if (time() - $timestamp < (30 * 24 * 60 * 60) && isDatabaseConnected() && $conn) {
                $stmt = $conn->prepare("SELECT id, username, email, role, status FROM users WHERE email = ? AND role = 'Admin' AND status = 'Active' LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("s", $stored_email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        session_regenerate_id(true);

                        $_SESSION['logged_in'] = true;
                        $_SESSION['is_admin'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        $_SESSION['admin_email'] = $user['email'];
                        $_SESSION['login_time'] = time();
                        $_SESSION['last_activity'] = time();
                        $_SESSION['session_regenerated'] = time();

                        $stmt->close();
                        header("Location: dashboard.php");
                        exit;
                    }
                    $stmt->close();
                }
            }
            // Cookie expired or invalid, remove it
            setcookie('admin_remember', '', time() - 3600, '/');
        }
    }

    // Redirect to admin login page
    header("Location: login.php");
    exit;
}
?>