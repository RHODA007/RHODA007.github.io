<?php
session_start();
if(!isset($_SESSION['instructor'])){
    header('Location: login.php');
    exit;
}

require '../db_connect.php'; // Adjust path if needed

$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Handle grading/feedback submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])){
    $submission_id = (int)$_POST['submission_id'];
    $grade = $_POST['grade'] ?: null;
    $feedback = trim($_POST['feedback']) ?: null;

    $stmt = $pdo->prepare("UPDATE submissions SET grade=?, feedback=? WHERE id=?");
    if($stmt->execute([$grade, $feedback, $submission_id])){
        $message = "<div class='alert alert-success'>Feedback/Grade updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to update feedback/grade.</div>";
    }
}

// Fetch submissions for all courses taught by instructor
$submissionsStmt = $pdo->prepare("
    SELECT 
        s.id AS submission_id, s.file, s.submitted_at, s.grade, s.feedback,
        a.id AS assignment_id, a.title AS assignment_title,
        st.id AS student_id, st.name AS student_name, st.email AS student_email
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN students st ON s.user_id = st.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY s.submitted_at DESC
");
$submissionsStmt->execute([$instructor_id]);
$submissions = $submissionsStmt->fetchAll(PDO::FETCH_ASSOC);

function filePreview($file){
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $url = "../uploads/assignments/$file";
    if(in_array($ext,['jpg','jpeg','png','gif','webp'])){
        return "<img src='$url' style='max-width:80px; max-height:80px; border-radius:6px;' />";
    } elseif($ext==='pdf'){
        return "<a href='$url' target='_blank'>📄 PDF</a>";
    } else {
        return "<a href='$url' target='_blank'>$file</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor Submissions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:Arial,sans-serif; margin:0; }
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background:#1f1f2c; color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8da; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:8px; }
.file-preview img { max-width:80px; max-height:80px; border-radius:6px; }
textarea { resize:none; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header"><h3>Instructor Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php" class="active"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <h2 class="mb-4">Student Submissions</h2>
  <?= $message ?>

  <?php if($submissions): ?>
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Student</th>
        <th>Email</th>
        <th>Assignment</th>
        <th>File</th>
        <th>Submitted At</th>
        <th>Grade</th>
        <th>Feedback</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($submissions as $sub): ?>
      <tr>
        <td><?= htmlspecialchars($sub['student_name']) ?></td>
        <td><?= htmlspecialchars($sub['student_email']) ?></td>
        <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
        <td><?= filePreview($sub['file']); ?></td>
        <td><?= htmlspecialchars($sub['submitted_at']) ?></td>
        <td>
          <form method="post">
            <input type="hidden" name="submission_id" value="<?= $sub['submission_id'] ?>">
            <input type="number" name="grade" min="0" max="100" value="<?= $sub['grade'] ?? '' ?>" class="form-control form-control-sm mb-1">
        </td>
        <td>
            <textarea name="feedback" class="form-control form-control-sm" rows="2"><?= $sub['feedback'] ?? '' ?></textarea>
        </td>
        <td>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-paper-plane"></i> Send</button>
            </form>
            <a href="../uploads/assignments/<?= $sub['file'] ?>" target="_blank" class="btn btn-primary btn-sm mt-1"><i class="fas fa-eye"></i> View</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p>No submissions yet.</p>
  <?php endif; ?>
</div>

</body>
</html>
