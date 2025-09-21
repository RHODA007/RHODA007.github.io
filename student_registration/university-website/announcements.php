<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📢 Announcements</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main-content">
    <h1>📢 Announcements</h1>
    <div class="announcements-container">
      <?php foreach ($announcements as $a): ?>
        <div class="announcement-card">
          <h3><?= htmlspecialchars($a['title']) ?></h3>
          <p><?= htmlspecialchars($a['body']) ?></p>
          <span class="date"><?= date("M d, Y", strtotime($a['created_at'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
