<?php
session_start();
require 'includes/config.php';

if(!isset($_SESSION['instructor'])){
    header("Location: login.php");
    exit;
}

// Get schedule ID from query parameter
$schedule_id = $_GET['schedule_id'] ?? null;

if($schedule_id){
    // Fetch the schedule and ensure the instructor owns it
    $stmt = $pdo->prepare("SELECT platform_link, course_id, day, start_time 
                           FROM schedules 
                           WHERE id=? AND instructor_id=?");
    $stmt->execute([$schedule_id, $_SESSION['instructor']['id']]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if($schedule && !empty($schedule['platform_link'])){
        // Redirect to the Zoom / online link
        header("Location: " . $schedule['platform_link']);
        exit;
    } else {
        echo "<div style='padding:20px; font-family:Arial,sans-serif; color:red;'>
                Invalid schedule or Zoom link not set.
              </div>";
        exit;
    }
} else {
    echo "<div style='padding:20px; font-family:Arial,sans-serif; color:red;'>
            Schedule ID missing.
          </div>";
    exit;
}
