<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/pdf_rotate.php'; // ✅ now using external rotation class

if (!isset($_SESSION['student'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student']['id'];
$course_id  = (int)($_GET['course_id'] ?? 0);

// Verify course completion
$stmt = $pdo->prepare("
    SELECT c.title, e.progress 
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = :student_id AND c.id = :course_id
    LIMIT 1
");
$stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course || (int)$course['progress'] < 100) {
    die("❌ You must complete this course before downloading the certificate.");
}

$student_name = $_SESSION['student']['name'] ?? "Student";
$course_title = $course['title'];
$date         = date("F j, Y");

// Check / insert cert_id
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

// Generate PDF
$pdf = new PDF_Rotate('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetDrawColor(218,165,32);
$pdf->SetLineWidth(3);
$pdf->Rect(10, 10, 277, 190);

// Logo (optional)
$logoPath = __DIR__ . '/../assets/image/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 120, 15, 50);
}
$pdf->Ln(40);

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

$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, "Certificate ID: $cert_id", 0, 0, 'R');

$pdf->SetFont('Arial', 'B', 50);
$pdf->SetTextColor(240,240,240);
$pdf->RotatedText(70, 150, "ONLINE SCHOOL", 45);

$pdf->Output("D", "Certificate_{$cert_id}.pdf");
exit;
