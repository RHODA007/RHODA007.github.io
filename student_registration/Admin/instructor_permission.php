<?php
session_start();
require 'db_connect.php';

// Fetch unapproved instructors
$stmt = $pdo->query("SELECT * FROM instructors WHERE unapproved = 1 AND approved = 0 ORDER BY created_at DESC");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Approve instructor
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $stmt = $pdo->prepare("UPDATE instructors SET approved = 1, unapproved = 0 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: instructor_permission.php");
    exit;
}

// Reject instructor
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $stmt = $pdo->prepare("UPDATE instructors SET approved = 0, unapproved = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: instructor_permission.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Instructor Permissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom:20px; }
        .cv-link { text-decoration:none; color:#0d6efd; font-weight:500; }
        .cv-link:hover { text-decoration:underline; }
        .actions a { margin-right:5px; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>Pending Instructor Approvals</h3>
    <?php if(empty($instructors)): ?>
        <div class="alert alert-info">No pending instructors.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach($instructors as $inst): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm p-3">
                        <h5><?= htmlspecialchars($inst['name']) ?></h5>
                        <p><strong>Email:</strong> <?= htmlspecialchars($inst['email']) ?></p>
                        <p><strong>Education:</strong> <?= htmlspecialchars($inst['education']) ?></p>
                        <p><strong>Experience:</strong> <?= htmlspecialchars($inst['experience']) ?></p>
                        <p><strong>Achievements:</strong> <?= htmlspecialchars($inst['achievements']) ?></p>
                        <p>
                            <strong>CV:</strong> 
                            <?php if($inst['cv']): ?>
                                <a href="../<?= htmlspecialchars($inst['cv']) ?>" target="_blank" class="cv-link">View CV</a>
                            <?php else: ?>
                                Not uploaded
                            <?php endif; ?>
                        </p>
                        <div class="actions">
                            <a href="?approve=<?= $inst['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="?reject=<?= $inst['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
