<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

$message = '';
$messageType = '';

// Handle Add User form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $raw_password = $_POST['password'] ?? '';
  $role = in_array($_POST['role'] ?? '', ['Admin', 'User']) ? $_POST['role'] : 'User';
  $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';

  if (empty($username) || empty($email) || empty($raw_password)) {
    header("Location: users.php?error=invalid");
    exit;
  }

  // Check if email already exists
  $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $checkStmt->bind_param("s", $email);
  $checkStmt->execute();
  $checkStmt->store_result();

  if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    header("Location: users.php?error=email_exists");
    exit;
  }
  $checkStmt->close();

  $password = password_hash($raw_password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $username, $email, $password, $role, $status);
  $stmt->execute();
  $stmt->close();

  header("Location: users.php?status=added");
  exit;
}

// Handle Edit User form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
  $userId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $raw_password = trim($_POST['password'] ?? '');
  $role = in_array($_POST['role'] ?? '', ['Admin', 'User']) ? $_POST['role'] : 'User';
  $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';

  if (!$userId || empty($username) || empty($email)) {
    header("Location: users.php?error=invalid");
    exit;
  }

  // Check if email is already used by another user
  $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
  $checkStmt->bind_param("si", $email, $userId);
  $checkStmt->execute();
  $checkStmt->store_result();

  if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    header("Location: users.php?error=email_exists");
    exit;
  }
  $checkStmt->close();

  // If password is provided, update password as well
  if (!empty($raw_password)) {
    $password = password_hash($raw_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $username, $email, $password, $role, $status, $userId);
  } else {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $username, $email, $role, $status, $userId);
  }

  $stmt->execute();
  $stmt->close();

  header("Location: users.php?status=updated");
  exit;
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users - PeakPH</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../../Css/admin.css">
  <style>
    .alert-box {
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-success {
      background-color: #e8f5e9;
      color: #2e7d32;
      border: 1px solid #a5d6a7;
    }
    .alert-danger {
      background-color: #ffebee;
      color: #c62828;
      border: 1px solid #ef9a9a;
    }
    .modal-content small {
      display: block;
      color: #666;
      margin-top: 4px;
      font-size: 12px;
    }
    .modal-actions-btn {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    .modal-actions-btn button {
      margin-top: 0;
    }
    .modal-actions-btn .btn-cancel {
      background: #757575;
    }
    .modal-actions-btn .btn-cancel:hover {
      background: #424242;
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <h2>User Management</h2>
    <button onclick="logout()">Logout</button>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h3>Menu</h3>
    <a href="../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="../inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
    <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
    <a href="users.php" class="menu-link active"><i class="bi bi-people"></i> Users</a>

    <button class="collapsible" onclick="toggleContentManager()">
      <i class="bi bi-folder"></i> Content Manager
      <span id="arrow" style="float:right;">&#9660;</span>
    </button>
    <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
      <a href="../content/carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
      <a href="../content/bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
      <a href="../content/new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
      <a href="../content/footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="content">
    <h2>User Accounts</h2>

    <!-- Status Messages -->
    <?php if (isset($_GET['status'])): ?>
      <?php if ($_GET['status'] === 'added'): ?>
        <div class="alert-box alert-success">
          <i class="bi bi-check-circle-fill"></i> User added successfully!
        </div>
      <?php elseif ($_GET['status'] === 'updated'): ?>
        <div class="alert-box alert-success">
          <i class="bi bi-check-circle-fill"></i> User updated successfully!
        </div>
      <?php elseif ($_GET['status'] === 'deleted'): ?>
        <div class="alert-box alert-success">
          <i class="bi bi-check-circle-fill"></i> User deleted successfully!
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert-box alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?php
          if ($_GET['error'] === 'email_exists') {
            echo "Email is already in use by another account.";
          } elseif ($_GET['error'] === 'self_delete') {
            echo "You cannot delete your own account while logged in.";
          } elseif ($_GET['error'] === 'not_found') {
            echo "User not found.";
          } else {
            echo "An error occurred. Please check the entered data.";
          }
        ?>
      </div>
    <?php endif; ?>

    <!-- Search + Add -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
      <input type="text" id="searchUser" placeholder="🔍 Search user by name or email..." style="padding:8px; flex:1; border:1px solid #27ae60; border-radius:6px;">
      <button onclick="openModal()" style="background:#27ae60; color:#fff; border:none; padding:10px 15px; border-radius:6px; cursor:pointer; font-weight:600;">+ Add User</button>
    </div>

    <!-- User Table -->
    <table>
      <thead>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="usersTable">
        <?php if ($users && $users->num_rows > 0): ?>
          <?php while ($row = $users->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id']; ?></td>
              <td><?= htmlspecialchars($row['username']); ?></td>
              <td><?= htmlspecialchars($row['email']); ?></td>
              <td>
                <span class="tag <?= $row['role'] === 'Admin' ? 'blue' : 'orange'; ?>">
                  <?= htmlspecialchars($row['role']); ?>
                </span>
              </td>
              <td>
                <span class="tag <?= $row['status'] === 'Active' ? 'green' : 'orange'; ?>">
                  <?= htmlspecialchars($row['status']); ?>
                </span>
              </td>
              <td>
                <button class="edit-btn" onclick='openEditModal(<?= htmlspecialchars(json_encode([
                    "id" => $row["id"],
                    "username" => $row["username"],
                    "email" => $row["email"],
                    "role" => $row["role"],
                    "status" => $row["status"]
                ]), ENT_QUOTES, "UTF-8"); ?>)'>Edit</button>
                <button class="delete-btn" onclick="deleteUser(<?= (int)$row['id']; ?>, '<?= htmlspecialchars(addslashes($row['username']), ENT_QUOTES, 'UTF-8'); ?>')">Delete</button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal (Add User) -->
  <div class="modal" id="userModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">&times;</span>
      <h2>Add New User</h2>
      <form method="POST" action="users.php">
        <input type="hidden" name="add_user" value="1">

        <label for="add_username">Username</label>
        <input type="text" id="add_username" name="username" required>

        <label for="add_email">Email</label>
        <input type="email" id="add_email" name="email" required>

        <label for="add_password">Password</label>
        <input type="password" id="add_password" name="password" required>

        <label for="add_role">Role</label>
        <select id="add_role" name="role">
          <option value="User">User</option>
          <option value="Admin">Admin</option>
        </select>

        <label for="add_status">Status</label>
        <select id="add_status" name="status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>

        <div class="modal-actions-btn">
          <button type="submit">Add User</button>
          <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal (Edit User) -->
  <div class="modal" id="editUserModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeEditModal()">&times;</span>
      <h2>Edit User</h2>
      <form method="POST" action="users.php">
        <input type="hidden" name="edit_user" value="1">
        <input type="hidden" name="id" id="edit_user_id" value="">

        <p style="font-size: 13px; color: #555; margin-bottom: 10px;">
          Editing Account: <strong id="edit_display_id">#</strong>
        </p>

        <label for="edit_username">Username</label>
        <input type="text" id="edit_username" name="username" required>

        <label for="edit_email">Email</label>
        <input type="email" id="edit_email" name="email" required>

        <label for="edit_password">New Password</label>
        <input type="password" id="edit_password" name="password" placeholder="••••••••">
        <small>Leave blank to keep the current password.</small>

        <label for="edit_role">Role</label>
        <select id="edit_role" name="role">
          <option value="User">User</option>
          <option value="Admin">Admin</option>
        </select>

        <label for="edit_status">Status</label>
        <select id="edit_status" name="status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>

        <div class="modal-actions-btn">
          <button type="submit">Save Changes</button>
          <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- JS -->
  <script>
    // Search filter
    document.getElementById("searchUser").addEventListener("keyup", function() {
      const value = this.value.toLowerCase();
      const rows = document.querySelectorAll("#usersTable tr");
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
      });
    });

    // Add Modal functions
    function openModal() {
      document.getElementById("userModal").style.display = "flex";
    }
    function closeModal() {
      document.getElementById("userModal").style.display = "none";
    }

    // Edit Modal functions
    function openEditModal(user) {
      document.getElementById("edit_user_id").value = user.id;
      document.getElementById("edit_display_id").textContent = "#" + user.id + " (" + user.username + ")";
      document.getElementById("edit_username").value = user.username;
      document.getElementById("edit_email").value = user.email;
      document.getElementById("edit_password").value = "";
      document.getElementById("edit_role").value = user.role;
      document.getElementById("edit_status").value = user.status;
      document.getElementById("editUserModal").style.display = "flex";
    }
    function closeEditModal() {
      document.getElementById("editUserModal").style.display = "none";
    }

    // Close modals on backdrop click
    window.onclick = function(e) {
      const addModal = document.getElementById("userModal");
      const editModal = document.getElementById("editUserModal");
      if (e.target === addModal) closeModal();
      if (e.target === editModal) closeEditModal();
    };

    // Delete user handler
    function deleteUser(id, username) {
      if (confirm("Are you sure you want to delete user '" + username + "' (ID: " + id + ")?")) {
        window.location.href = "users_delete.php?id=" + id;
      }
    }

    // Toggle Content Manager function
    function toggleContentManager() {
      const links = document.getElementById("contentManagerLinks");
      const arrow = document.getElementById("arrow");
      if (links.style.display === "none") {
        links.style.display = "block";
        arrow.innerHTML = "&#9660;";
      } else {
        links.style.display = "none";
        arrow.innerHTML = "&#9654;";
      }
    }

    // Logout function
    function logout() {
      window.location.href = "../logout.php";
    }
  </script>
</body>
</html>
