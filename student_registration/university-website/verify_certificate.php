<?php
session_start();
require_once 'includes/config.php';

// Ensure only logged-in students access
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

$cert_id = $_GET['cert_id'] ?? '';
$result = null;

if ($cert_id) {
    $stmt = $pdo->prepare("
        SELECT s.name AS student_name, c.title AS course_title, cert.issued_at
        FROM certificates cert
        JOIN students s ON cert.student_id = s.id
        JOIN courses c ON cert.course_id = c.id
        WHERE cert.cert_id = ?
    ");
    $stmt->execute([$cert_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Certificate</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="download_certificate.php" class="active"><i class="fas fa-certificate"></i> Download Certificate</a></li>
    <li><a href="verify_certificate.php" class="active"><i class="fas fa-certificate"></i> Verify Certificate</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main">
  <div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-certificate text-primary"></i> Certificate Verification</h2>

    <!-- Search Form -->
    <form method="get" class="mb-4 d-flex gap-2">
      <input type="text" name="cert_id" placeholder="Enter Certificate ID"
             value="<?= esc($cert_id); ?>"
             class="form-control" required>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Verify
      </button>
    </form>

    <!-- Result Section -->
    <?php if ($cert_id): ?>
      <?php if ($result): ?>
        <div class="card shadow-sm border-success mb-4" style="max-width:600px;">
          <div class="card-body">
            <h5 class="card-title text-success">
              <i class="fas fa-check-circle"></i> Valid Certificate
            </h5>
            <p><b>Certificate ID:</b> <?= esc($cert_id); ?></p>
            <p><b>Student:</b> <?= esc($result['student_name']); ?></p>
            <p><b>Course:</b> <?= esc($result['course_title']); ?></p>
            <p><b>Issued on:</b> <?= esc($result['issued_at']); ?></p>

            <hr>
            <p><b>Verification QR:</b></p>
            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?= urlencode('http://localhost/student_registration/verify_certificate.php?cert_id='.$cert_id); ?>"
                 alt="QR Code" class="img-thumbnail">

            <div class="mt-3">
              <a href="print_certificate.php?cert_id=<?= urlencode($cert_id); ?>" target="_blank"
                 class="btn btn-outline-secondary">
                <i class="fas fa-print"></i> Print
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-danger">
          <i class="fas fa-times-circle"></i> Invalid Certificate ID
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
