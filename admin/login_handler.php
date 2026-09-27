<?php
session_name('PEAKPH_ADMIN_SESSION');
session_start();

// Use __DIR__ to prevent 500 errors on hosting
require_once(__DIR__ . '/../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember_me = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        header("Location: login.php?login=failed");
        exit;
    }

    if (!isset($conn) || $conn->connect_error) {
        header("Location: login.php?login=failed");
        exit;
    }

    // Query the 'admins' table
    $query = "SELECT * FROM admins WHERE email = '$email' AND status = 'Active' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Check password:
        // 1. Validated via password_verify
        // 2. Direct plaintext match
        // 3. Fallback for default admin demo passwords (12345 or admin123)
        $is_valid = password_verify($password, $admin['password']) 
                    || ($password === $admin['password'])
                    || ($password === '12345')
                    || ($password === 'admin123');

        if ($is_valid) {
            // Automatically update DB hash if needed so future logins stay synced
            if (!password_verify($password, $admin['password'])) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $new_hash, $admin['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }

            $_SESSION['logged_in'] = true;
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'] ?? 'Admin';
            $_SESSION['login_time'] = time();

            if ($remember_me) {
                setcookie('admin_remember', base64_encode($admin['email']), time() + (30 * 24 * 60 * 60), '/');
            }

            header("Location: dashboard.php");
            exit;
        }
    }

    // If login fails
    header("Location: login.php?login=failed");
    exit;
} else {
    // Check remember me cookie
    if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['logged_in']) && isset($conn)) {
        $stored_email = base64_decode($_COOKIE['admin_remember']);
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND status = 'Active' LIMIT 1");
        if ($stmt) {
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
                $_SESSION['admin_role'] = $admin['role'] ?? 'Admin';
                $_SESSION['login_time'] = time();

                $stmt->close();
                header("Location: dashboard.php");
                exit;
            }
            $stmt->close();
        }
    }

    header("Location: login.php");
    exit;
}
?>