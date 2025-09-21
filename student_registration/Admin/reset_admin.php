<?php
require 'db_connect.php';

// New password
$password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
$stmt->execute([$password, 'admin']);

echo "Admin password updated.";
?>
