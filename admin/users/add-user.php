<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $status = $_POST["status"];

    if (!empty($username) && !empty($email) && !empty($password)) {
        $sql = "INSERT INTO users (username, email, password, role, status) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $password, $role, $status);

        if ($stmt->execute()) {
            $message = "✅ User added successfully!";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
    } else {
        $message = "⚠️ Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add User - PeakPH</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
  <div class="form-container">
    <h2>Add New User</h2>
    
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form method="POST" action="">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" required>

      <label for="email">Email</label>
      <input type="email" name="email" id="email" required>

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>

      <label for="role">Role</label>
      <select name="role" id="role">
        <option value="User">User</option>
        <option value="Admin">Admin</option>
      </select>

      <label for="status">Status</label>
      <select name="status" id="status">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>

      <button type="submit">Add User</button>
    </form>

    <a href="users.php" class="back-btn">← Back to User List</a>
  </div>
</body>
</html>
