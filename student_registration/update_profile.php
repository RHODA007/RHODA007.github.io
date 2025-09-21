<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // If password is filled, update it too
    if(!empty($password)){
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin SET name=?, email=?, password=? WHERE id=?");
        $stmt->execute([$name, $email, $hashed, $_SESSION['admin']]);
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET name=?, email=? WHERE id=?");
        $stmt->execute([$name, $email, $_SESSION['admin']]);
    }

    $_SESSION['msg'] = "Profile updated successfully ✅";
    header("Location: settings.php");
    exit;
}
