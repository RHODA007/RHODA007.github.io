<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['student']) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$user_type = isset($_SESSION['student']) ? 'student' : 'admin';
$user_id = $_SESSION[$user_type]['id'];

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($current && $new && $confirm) {
    $stmt = $pdo->prepare("SELECT password FROM " . ($user_type === 'student' ? "students" : "admins") . " WHERE id=?");
    $stmt->execute([$user_id]);
    $hashed = $stmt->fetchColumn();

    if (password_verify($current, $hashed)) {
        if ($new === $confirm) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE " . ($user_type === 'student' ? "students" : "admins") . " SET password=? WHERE id=?")
                ->execute([$newHash, $user_id]);
            echo json_encode(['status'=>'success','message'=>'Password updated successfully.']);
        } else {
            echo json_encode(['status'=>'error','message'=>'New password and confirmation do not match.']);
        }
    } else {
        echo json_encode(['status'=>'error','message'=>'Current password is incorrect.']);
    }
} else {
    echo json_encode(['status'=>'error','message'=>'All password fields are required.']);
}
