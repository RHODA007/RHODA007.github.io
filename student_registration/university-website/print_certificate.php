<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

require 'includes/config.php'; 
require 'includes/pdf_rotate.php'; // <-- your custom rotate class

$student_id = $_SESSION['student']['id'];
$course_id  = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// ✅ Verify completion
$stmt = $pdo->prepare("
    SELECT c.title, e.progress 
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = :student_id AND c.id = :course_id
    LIMIT 1
");
$stmt->execute(['student_id'=>$student_id,'course_id'=>$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course || (int)$course['progress'] < 100) {
    die("<div style='padding:20px;font-family:sans-serif;color:red;'>❌ You must complete this course before printing the certificate.</div>");
}

$student_name = $_SESSION['student']['name'] ?? "Student";
$course_title = $course['title'];
$date = date("F j, Y");

// ✅ Get/insert certificate ID
$check = $pdo->prepare("SELECT cert_id FROM certificates WHERE student_id=? AND course_id=?");
$check->execute([$student_id, $course_id]);
$row = $check->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $cert_id = $row['cert_id'];
} else {
    $cert_id = strtoupper(substr(md5(uniqid($student_id.$course_id, true)), 0, 10));
    $insert = $pdo->prepare("INSERT INTO certificates (student_id, course_id, cert_id) VALUES (?, ?, ?)");
    $insert->execute([$student_id, $course_id, $cert_id]);
}

// ✅ Build PDF and save temporarily
$pdf = new PDF_Rotate('L', 'mm', 'A4');
$pdf->AddPage();

// Border
$pdf->SetDrawColor(218,165,32);
$pdf->SetLineWidth(3);
$pdf->Rect(10, 10, 277, 190);

// Logo
$logoPath = __DIR__ . "/assets/image/logo.png";
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 120, 15, 50);
}
$pdf->Ln(40);

// Title
$pdf->SetFont('Times', 'B', 30);
$pdf->Cell(0, 20, 'Certificate of Completion', 0, 1, 'C');

$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 15, "This certifies that", 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 28);
$pdf->Cell(0, 20, $student_name, 0, 1, 'C');

$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 15, "has successfully completed the course:", 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 20);
$pdf->MultiCell(0, 15, $course_title, 0, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, "Date: " . $date, 0, 1, 'C');

$pdf->Ln(20);
$pdf->Cell(0, 10, "__________________________", 0, 1, 'C');
$pdf->Cell(0, 10, "Administrator", 0, 1, 'C');

// Certificate ID
$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, "Certificate ID: $cert_id", 0, 0, 'R');

// Watermark
$pdf->SetFont('Arial', 'B', 50);
$pdf->SetTextColor(240,240,240);
$pdf->RotatedText(70, 150, "ONLINE SCHOOL", 45);

$tmpFile = "temp_certificate_{$student_id}_{$course_id}.pdf";
$pdf->Output("F", $tmpFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Print Certificate</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; margin:0; min-height:100vh; background:#f8f9fa; }
body.dark-mode { background:#121212; color:#f8f9fa; }
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background: rgba(31,31,47,0.95); color:#fff; display:flex; flex-direction:column; }
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
iframe { width:100%; height:80vh; border:1px solid #ccc; border-radius:8px; }
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
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="print_certificate.php?course_id=<?= $course_id ?>" class="active"><i class="fas fa-certificate"></i> Certificate</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="card p-4">
    <h4 class="mb-3"><i class="fas fa-certificate"></i> Your Certificate</h4>
    <iframe src="<?= $tmpFile ?>#toolbar=0" title="Certificate"></iframe>
    <div class="mt-3 text-center">
      <button class="btn btn-primary" onclick="printCertificate()"><i class="fas fa-print"></i> Print</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printCertificate(){
    var win = window.open("<?= $tmpFile ?>", "_blank");
    win.addEventListener("load", () => win.print());
}
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
