```php
<?php
session_name('PEAKPH_ADMIN_SESSION');
session_start();

require_once(__DIR__ . '/../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Security testing mode
    $hydra_test = isset($_POST['hydra_test']) && $_POST['hydra_test'] === '1';

    // VULNERABILITY: Direct SQL concatenation - vulnerable to SQL injection
    // No prepared statements or parameterized queries
    $query = "SELECT * FROM admins WHERE email = '$email' LIMIT 1";
    $result = $conn->query($query);

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
                setcookie(
                    'admin_remember',
                    base64_encode($admin['email']),
                    time() + (30 * 24 * 60 * 60),
                    '/'
                );
            }

            // Response specifically for authorized Hydra testing
            if ($hydra_test) {
                echo "LOGIN_SUCCESS";
                exit;
            }

            // Normal login behavior
            header("Location: dashboard.php");
            exit;
        }
    }

    // Response specifically for authorized Hydra testing
    if ($hydra_test) {
        echo "LOGIN_FAILED";
        exit;
    }

    // Normal login behavior
    header("Location: login.php?login=failed");
    exit;

} else {

    // Check remember me
    if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['logged_in'])) {

        $stored_email = base64_decode($_COOKIE['admin_remember']);

        $query = "SELECT * FROM admins WHERE email = '$stored_email' LIMIT 1";
        $result = $conn->query($query);

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

    header("Location: login.php");
    exit;
}
?>
```
