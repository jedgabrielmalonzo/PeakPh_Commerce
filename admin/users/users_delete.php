<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

$userId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$userId) {
    header("Location: users.php?error=invalid");
    exit;
}

// Fetch user to check if exists and avoid deleting self
$stmt = $conn->prepare("SELECT id, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: users.php?error=not_found");
    exit;
}

$targetUser = $result->fetch_assoc();
$stmt->close();

// Prevent admin from deleting their own currently logged-in account
if (isset($_SESSION['admin_email']) && strtolower($_SESSION['admin_email']) === strtolower($targetUser['email'])) {
    header("Location: users.php?error=self_delete");
    exit;
}

// Delete user
$delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$delStmt->bind_param("i", $userId);
$delStmt->execute();
$delStmt->close();

header("Location: users.php?status=deleted");
exit;
