<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['student'])) { exit("Not logged in"); }
$student_id = $_SESSION['student']['id'];

$stmt = $pdo->prepare("
    SELECT c.id AS course_id, c.title, c.description, sc.progress, sc.updated_at, i.name AS instructor_name
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    JOIN instructors i ON c.instructor_id = i.id
    WHERE sc.student_id = ?
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$courses) {
    echo "<p>No courses yet. Contact admin.</p>";
    exit;
}

foreach ($courses as $c) {
    $progress = (int)$c['progress'];
    $badge = $progress==100 ? "success" : ($progress>=50 ? "warning" : "secondary");
    echo "
    <div class='card-chart'>
        <h5><i class='fas fa-book-open'></i> ".htmlspecialchars($c['title'])."</h5>
        <p class='small text-muted'>".htmlspecialchars($c['description'])."</p>
        <div class='progress mb-2'>
            <div class='progress-bar bg-primary' role='progressbar' style='width: {$progress}%'>{$progress}%</div>
        </div>
        <span class='badge bg-$badge'>".($progress==100?'Completed':'In Progress')."</span>
        <p class='mt-2'><i class='fas fa-user'></i> Updated by <b>".htmlspecialchars($c['instructor_name'])."</b> on 
            ".date("M d, Y h:i A", strtotime($c['updated_at']))."
        </p>
        <a href='messages.php?course_id={$c['course_id']}' class='btn btn-sm btn-outline-info'>
            <i class='fas fa-comment'></i> Ask Instructor
        </a>
    </div>
    ";
}
