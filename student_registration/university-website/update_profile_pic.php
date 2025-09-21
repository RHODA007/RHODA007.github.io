<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['student']) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Determine user type
$user_type = isset($_SESSION['student']) ? 'student' : 'admin';
$user_id = $_SESSION[$user_type]['id'];

// Fetch existing data
$stmt = $pdo->prepare("SELECT profile_pic FROM " . ($user_type === 'student' ? "students" : "admins") . " WHERE id=?");
$stmt->execute([$user_id]);
$currentPic = $stmt->fetchColumn();

if (!empty($_FILES['profile_pic']['name'])) {
    $targetDir = '../uploads/';
    $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
        // Update database
        $pdo->prepare("UPDATE " . ($user_type === 'student' ? "students" : "admins") . " SET profile_pic=? WHERE id=?")
            ->execute([$fileName, $user_id]);

        // Update session
        $_SESSION[$user_type]['profile_pic'] = $fileName;
        echo json_encode(['status'=>'success','message'=>'Profile picture updated successfully.','file'=>$fileName]);
        exit;
    }
}

echo json_encode(['status'=>'error','message'=>'No file uploaded.']);
