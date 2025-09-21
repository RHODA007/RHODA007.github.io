<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $alerts = isset($_POST['alerts']) ? 1 : 0;
    $emails = isset($_POST['emails']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE notifications SET alerts=?, emails=? WHERE id=1");
    $stmt->execute([$alerts, $emails]);

    $_SESSION['msg'] = "Notification settings updated 🔔";
    header("Location: settings.php");
    exit;
}
