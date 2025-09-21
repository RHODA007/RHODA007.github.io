<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['student'])) { 
    header("Location: login.php"); 
    exit; 
}

// Get logged-in student ID
$stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
$stmt->execute([$_SESSION['email']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$studentId = $student['id'] ?? 0;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id'])) {

    $assignmentId = $_POST['assignment_id'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {

        $uploadDir = 'uploads/';
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmp  = $_FILES['file']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $filePath = $uploadDir . $fileName;

        // Move uploaded file
        if(move_uploaded_file($fileTmp, $filePath)) {

            // Insert submission record
            $stmt = $pdo->prepare("
                INSERT INTO submissions (assignment_id, user_id, file, submitted_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$assignmentId, $studentId, $fileName]);

            $_SESSION['success'] = "Assignment submitted successfully!";
        } else {
            $_SESSION['error'] = "Failed to upload file.";
        }

    } else {
        $_SESSION['error'] = "Please select a file to submit.";
    }

} else {
    $_SESSION['error'] = "Invalid submission.";
}

// Redirect back to dashboard or submissions page
header("Location: submissions.php");
exit;
