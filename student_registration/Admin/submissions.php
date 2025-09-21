<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

// Fetch all submissions with student and assignment info
$sql = "SELECT s.id, st.name AS student_name, a.title AS assignment_title, s.file, s.submitted_at, s.grade
        FROM submissions s
        JOIN students st ON s.user_id = st.id
        JOIN assignments a ON s.assignment_id = a.id
        ORDER BY s.submitted_at DESC";
$stmt = $pdo->query($sql);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assignment Submissions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f4f6f9; padding: 20px; }
h1 { text-align: center; margin-bottom: 30px; }
.table-wrapper { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
table { width: 100%; }
th, td { text-align: center; vertical-align: middle; }
th { background: #0d6efd; color: #fff; }
td a { text-decoration: none; }
.btn-download { background: #198754; color: #fff; border-radius: 8px; padding: 5px 10px; font-weight: 600; transition: 0.3s; }
.btn-download:hover { transform: scale(1.05); box-shadow: 0 3px 8px rgba(0,0,0,0.2); }
</style>
</head>
<body>

<h1>📂 Student Assignment Submissions</h1>

<div class="table-wrapper">
    <?php if($submissions): ?>
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Assignment</th>
                <th>File</th>
                <th>Submitted At</th>
                <th>Grade</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($submissions as $sub): ?>
            <tr>
                <td><?= htmlspecialchars($sub['id']) ?></td>
                <td><?= htmlspecialchars($sub['student_name']) ?></td>
                <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                <td>
                    <?php if($sub['file']): ?>
                        <a href="uploads/<?= htmlspecialchars($sub['file']) ?>" class="btn-download" download><i class="fas fa-download"></i> Download</a>
                    <?php else: ?>
                        No file
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($sub['submitted_at']) ?></td>
                <td><?= htmlspecialchars($sub['grade'] ?? 'Not graded') ?></td>
                <td>
                    <a href="grade_submission.php?id=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">Grade</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="text-center text-muted">No submissions yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
