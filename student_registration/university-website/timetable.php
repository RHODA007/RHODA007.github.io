<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit;
}

// Fetch timetable (all instructors/courses)
$stmt = $pdo->query("SELECT t.*, i.name as instructor_name 
                     FROM timetable t 
                     JOIN instructors i ON t.instructor_id = i.id
                     ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time");
$timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Timetable</title>
</head>
<body>
<h2>Class Timetable</h2>

<table border="1">
    <tr>
        <th>Course</th><th>Instructor</th><th>Day</th><th>Time</th><th>Venue</th>
    </tr>
    <?php foreach ($timetable as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= htmlspecialchars($row['instructor_name']) ?></td>
            <td><?= $row['day_of_week'] ?></td>
            <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
