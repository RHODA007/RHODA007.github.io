<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['student'])) { 
    echo json_encode([]);
    exit; 
}

$studentId = $_SESSION['student']['id'] ?? 0;

$stmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$studentId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
