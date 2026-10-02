```php
<?php

// Force errors to display on InfinityFree for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mysqli = require __DIR__ . "/database.php";

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users
            WHERE email = '$email'
            AND password_hash = '$password'";

    // Execute multiple SQL statements
    $success = $mysqli->multi_query($sql);

    if (!$success) {

        $error_message = "SQL Error: " . $mysqli->error;

    } else {

        // Get the result from the first query
        $result = $mysqli->store_result();

        // Check if a user was found
        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            session_start();
            session_regenerate_id();

            $_SESSION["user_id"] = $user["id"];

            // Clear remaining query results
            while ($mysqli->next_result()) {

                if ($extra_result = $mysqli->store_result()) {
                    $extra_result->free();
                }
            }

            header("Location: home.php");
            exit;

        } else {

            // Clear remaining query results
            while ($mysqli->next_result()) {

                if ($extra_result = $mysqli->store_result()) {
                    $extra_result->free();
                }
            }

            $error_message = "No user found with the provided email and password.";
        }
    }
}

?>
```