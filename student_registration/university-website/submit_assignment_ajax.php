<?php
session_start();
header('Content-Type: application/json');
require_once 'includes/config.php';

if (!isset($_SESSION['student'])) {
    echo json_encode(['status'=>'danger','message'=>'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student']['id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!empty($_FILES['assignment_file']['name']) && !empty($_POST['assignment_id'])) {
        $assignment_id = (int)$_POST['assignment_id'];
        $file = $_FILES['assignment_file'];

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = time() . '_' . uniqid() . '.' . $ext;
        $targetDir = __DIR__ . '/../uploads/';
        $targetFile = $targetDir . $newFileName;

        if(move_uploaded_file($file['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, user_id, file, submitted_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$assignment_id, $student_id, $newFileName]);

            // Fetch assignment title
            $titleStmt = $pdo->prepare("SELECT title FROM assignments WHERE id=?");
            $titleStmt->execute([$assignment_id]);
            $title = $titleStmt->fetchColumn();

            echo json_encode([
                'status'=>'success',
                'message'=>'Assignment submitted successfully!',
                'assignment_title'=>$title,
                'submitted_at'=>date('Y-m-d H:i:s'),
                'file'=>$newFileName
            ]);
            exit;
        } else {
            echo json_encode(['status'=>'danger','message'=>'Failed to upload file.']);
            exit;
        }
    } else {
        echo json_encode(['status'=>'danger','message'=>'All fields are required.']);
        exit;
    }
}
?>
