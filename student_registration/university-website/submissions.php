<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

require 'includes/config.php'; // Adjust path if needed
$student_id = $_SESSION['student']['id'];
$message = "";

// Handle new assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id']) && isset($_FILES['assignment_file'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $file = $_FILES['assignment_file'];

    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = '../uploads/assignments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            // ✅ use user_id not student_id
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, user_id, file, submitted_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$assignment_id, $student_id, $filename]);
            $message = "<div class='alert alert-success'>Assignment submitted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to upload file.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Error with uploaded file.</div>";
    }
}

// Fetch assignments (all assignments for display)
$assignmentsStmt = $pdo->query("SELECT id, title, due_date, description FROM assignments ORDER BY due_date ASC");
$assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student submissions with grade & feedback
$subStmt = $pdo->prepare("
    SELECT s.id, s.assignment_id, a.title AS assignment_title, a.description, s.file, s.submitted_at, s.grade, s.feedback
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    WHERE s.user_id = ?
    ORDER BY s.submitted_at DESC
");
$subStmt->execute([$student_id]);
$submissions = $subStmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function filePreview($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $url = "../uploads/assignments/$file";
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        return "<img src='$url' style='max-width:100px; max-height:100px; border-radius:6px;' />";
    } elseif ($ext === 'pdf') {
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
<title>Submissions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; margin:0; min-height:100vh; background:#f8f9fa; }
body.dark-mode { background:#121212; color:#f8f9fa; }
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background: rgba(31,31,47,0.95); color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#0d6efd; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main-content { margin-left:240px; padding:20px; }
.card { border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); background:#fff; }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }
.submission-card { border-radius:8px; border:1px solid #ddd; padding:12px; margin-bottom:15px; background:#fff; display:flex; justify-content: space-between; align-items:center; }
.submission-card:hover { box-shadow:0 2px 8px rgba(0,0,0,0.12); }
.file-preview img { max-width:120px; max-height:120px; border-radius:6px; display:block; margin-top:5px; }
.grade-feedback { margin-top:10px; }
.grade-feedback span { font-weight:bold; }
.modal-body img { max-width:100%; height:auto; border-radius:6px; margin-bottom:10px; }
</style>
</head>
<body>

<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<div class="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="submissions.php" class="active"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">
    <div class="card p-4">
        <?= $message ?>
        <h4 class="mb-3">Submit Assignment</h4>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Select Assignment</label>
                <select name="assignment_id" class="form-select" required>
                    <option value="">-- Choose Assignment --</option>
                    <?php foreach($assignments as $a): ?>
                        <option value="<?= esc($a['id']); ?>"><?= esc($a['title']); ?> (Due: <?= esc($a['due_date']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload File</label>
                <input type="file" name="assignment_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Submit</button>
        </form>

        <hr>

        <h5 class="mb-3">My Submissions</h5>
        <?php if($submissions): foreach($submissions as $s): ?>
            <div class="submission-card">
                <div>
                    <strong><?= esc($s['assignment_title']); ?></strong>
                    <p>Submitted at: <?= esc($s['submitted_at']); ?></p>
                </div>
                <div>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewAssignmentModal<?= $s['id'] ?>">View Assignment</button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="viewAssignmentModal<?= $s['id'] ?>" tabindex="-1" aria-labelledby="viewAssignmentLabel<?= $s['id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="viewAssignmentLabel<?= $s['id'] ?>"><?= esc($s['assignment_title']); ?> - Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Description:</strong><br><?= nl2br(esc($s['description'])); ?></p>
                    <p><strong>Submitted File:</strong><br><?= filePreview($s['file']); ?></p>
                    <p class="grade-feedback"><span>Grade:</span> <?= $s['grade'] ?? 'Not graded yet' ?></p>
                    <p class="grade-feedback"><span>Instructor Feedback:</span><br><?= $s['feedback'] ?? 'No feedback yet' ?></p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>

        <?php endforeach; else: ?>
            <p>No submissions yet.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode")==="enabled"){ body.classList.add("dark-mode"); toggleBtn.textContent="☀️"; }
toggleBtn.addEventListener("click", ()=>{
    body.classList.toggle("dark-mode");
    toggleBtn.textContent = body.classList.contains("dark-mode") ? "☀️" : "🌙";
    localStorage.setItem("dark-mode", body.classList.contains("dark-mode")?"enabled":"disabled");
});
</script>

</body>
</html>
