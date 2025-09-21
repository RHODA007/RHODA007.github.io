<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $pagination = $_POST['pagination'] ?? 10;
    $sorting = $_POST['sorting'] ?? 'asc';

    $stmt = $pdo->prepare("UPDATE system_settings SET pagination=?, sorting=? WHERE id=1");
    $stmt->execute([$pagination, $sorting]);

    $_SESSION['msg'] = "System settings updated ⚡";
    header("Location: settings.php");
    exit;
}
