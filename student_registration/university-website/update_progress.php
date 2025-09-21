<?php
session_start();
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['instructor'])) {
    $instructor_id = $_SESSION['instructor']['id'];
    $enrollment_id = (int)$_POST['enrollment_id'];
    $progress = max(0, min(100, (int)($_POST['progress'] ?? 0)));
    $grade = trim($_POST['grade'] ?? '');
    $feedback = trim($_POST['feedback'] ?? '');

    // Update progress
    $updateEnroll = $pdo->prepare("
        UPDATE enrollments e
        JOIN courses c ON e.course_id = c.id
        SET e.progress = ?
        WHERE e.id = ? AND c.instructor_id = ?
    ");
    $ok1 = $updateEnroll->execute([$progress, $enrollment_id, $instructor_id]);

    // Update latest submission (grade, feedback)
    $updateSub = $pdo->prepare("
        UPDATE submissions s
        JOIN enrollments e ON e.student_id = s.user_id 
        AND e.course_id = (SELECT course_id FROM enrollments WHERE id = ? LIMIT 1)
        JOIN courses c ON e.course_id = c.id
        SET s.grade = ?, s.feedback = ?
        WHERE e.id = ? AND c.instructor_id = ?
        ORDER BY s.submitted_at DESC
        LIMIT 1
    ");
    $ok2 = $updateSub->execute([$grade, $feedback, $enrollment_id, $instructor_id]);

    echo json_encode([
        "status" => ($ok1 || $ok2) ? "success" : "error"
    ]);
}
