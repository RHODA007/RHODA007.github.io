<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';
require 'libs/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);

// Title
$pdf->Cell(0,10,'Registered Students',0,1,'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(10,10,'ID',1);
$pdf->Cell(50,10,'Name',1);
$pdf->Cell(60,10,'Email',1);
$pdf->Cell(30,10,'Phone',1);
$pdf->Cell(30,10,'DOB',1);
$pdf->Ln();

// Fetch students
$stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Table Body
$pdf->SetFont('Arial','',12);
foreach($students as $student){
    $pdf->Cell(10,10,$student['id'],1);
    $pdf->Cell(50,10,$student['name'],1);
    $pdf->Cell(60,10,$student['email'],1);
    $pdf->Cell(30,10,$student['phone'],1);
    $pdf->Cell(30,10,$student['dob'],1);
    $pdf->Ln();
}

// Output the PDF
$pdf->Output('D','students.pdf'); // D = download
?>
