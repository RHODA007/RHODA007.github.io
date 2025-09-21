<?php
require 'db_connect.php';    // Database connection
require 'libs/fpdf.php';     // FPDF library

// Fetch all students
$stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
$students = $stmt->fetchAll();

// Create PDF
$pdf = new FPDF('L','mm','A4'); // Landscape, mm units, A4 page
$pdf->AddPage();
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,10,'🎓 Student Report',0,1,'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(200,200,200);
$pdf->Cell(10,10,'#',1,0,'C',true);
$pdf->Cell(35,10,'Photo',1,0,'C',true);
$pdf->Cell(45,10,'Name',1,0,'C',true);
$pdf->Cell(60,10,'Email',1,0,'C',true);
$pdf->Cell(30,10,'Phone',1,0,'C',true);
$pdf->Cell(25,10,'DOB',1,0,'C',true);
$pdf->Cell(50,10,'Registered',1,1,'C',true);

$pdf->SetFont('Arial','',12);
$i = 1;
foreach($students as $s){
    $pdf->Cell(10,25,$i,1,0,'C');

    // Photo
    if($s['photo'] && file_exists('uploads/'.$s['photo'])){
        $pdf->Cell(35,25,$pdf->Image('uploads/'.$s['photo'],$pdf->GetX()+2,$pdf->GetY()+2,31,21),1,0,'C');
    } else {
        $pdf->Cell(35,25,'No Photo',1,0,'C');
    }

    $pdf->Cell(45,25,$s['name'],1);
    $pdf->Cell(60,25,$s['email'],1);
    $pdf->Cell(30,25,$s['phone'],1);
    $pdf->Cell(25,25,$s['dob'],1);
    $pdf->Cell(50,25,$s['created_at'],1,1);
    $i++;
}

// Output PDF
$pdf->Output('D','students_report.pdf');
