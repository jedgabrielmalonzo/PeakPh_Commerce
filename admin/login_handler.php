<?php
session_name('PEAKPH_ADMIN_SESSION');
session_start();

require_once(__DIR__ . '/../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // SECURE: Use prepared statement
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Check password (matches either hashed password or plain text)
        if (password_verify($password, $admin['password']) || $password === $admin['password']) {
            $_SESSION['logged_in'] = true;
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['login_time'] = time();

            if ($remember_me) {
                // SECURE: Use cryptographically signed cookie
                $secret = "PEAKPH_SUPER_SECRET_KEY";
                setcookie('admin_remember', $cookie_data, time() + (30 * 24 * 60 * 60), '/');
            }

            header("Location: dashboard.php");
            exit;
        }
    }

    // If login fails
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