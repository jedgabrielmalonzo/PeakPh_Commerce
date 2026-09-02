<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");
require_once("footer_functions.php");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $formData = [
        'company_name' => $_POST['company_name'] ?? '',
        'company_description' => $_POST['company_description'] ?? '',
        'contact_email' => $_POST['contact_email'] ?? '',
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'facebook_link' => $_POST['facebook_link'] ?? '',
        'instagram_link' => $_POST['instagram_link'] ?? '',
        'youtube_link' => $_POST['youtube_link'] ?? '',
        'tiktok_link' => $_POST['tiktok_link'] ?? '',
        'twitter_link' => $_POST['twitter_link'] ?? '',
        'copyright_text' => $_POST['copyright_text'] ?? ''
    ];
    
    if (saveFooterData($formData)) {
        header("Location: footer.php?status=updated");
    } else {
        header("Location: footer.php?status=error");
    }
    exit;
}

// If accessed directly without POST, redirect back
header("Location: footer.php");
exit;
?>