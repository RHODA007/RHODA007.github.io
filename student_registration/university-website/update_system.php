<?php
session_start();
if (!isset($_SESSION['student'])) {
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    exit;
}

require_once 'includes/config.php';
$student_id = $_SESSION['student']['id'];

// Fetch current student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$response = ['status'=>'error','message'=>'Unknown error'];

// === REMOVE PROFILE PICTURE ===
if (isset($_POST['remove_pic'])) {
    $currentPic = $student['profile_pic'];
    if ($currentPic && file_exists('uploads/'.$currentPic)) unlink('uploads/'.$currentPic);
    $pdo->prepare("UPDATE students SET profile_pic=NULL WHERE id=?")->execute([$student_id]);
    $response = ['status'=>'success','message'=>'Profile picture removed'];
    echo json_encode($response);
    exit;
}

// === UPDATE PROFILE ===
if (isset($_POST['name']) && isset($_POST['email'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $profile_pic = $student['profile_pic'];

    // Handle new profile picture
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = 'uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time().'_'.basename($_FILES['profile_pic']['name']);
        $targetFile = $targetDir.$fileName;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            if ($profile_pic && file_exists($targetDir.$profile_pic)) unlink($targetDir.$profile_pic);
            $profile_pic = $fileName;
        }
    }

    if ($name && $email) {
        $pdo->prepare("UPDATE students SET name=?, email=?, profile_pic=? WHERE id=?")
            ->execute([$name,$email,$profile_pic,$student_id]);
        $_SESSION['student']['name'] = $name;
        $response = ['status'=>'success','message'=>'Profile updated successfully','file'=>$profile_pic];
    } else {
        $response = ['status'=>'error','message'=>'Name and Email cannot be empty'];
    }

    echo json_encode($response);
    exit;
}

// === CHANGE PASSWORD ===
if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($current && $new && $confirm) {
        if (password_verify($current, $student['password'])) {
            if ($new === $confirm) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE students SET password=? WHERE id=?")->execute([$newHash,$student_id]);
                $response = ['status'=>'success','message'=>'Password updated successfully'];
            } else {
                $response = ['status'=>'error','message'=>'New password and confirmation do not match'];
            }
        } else {
            $response = ['status'=>'error','message'=>'Current password is incorrect'];
        }
    } else {
        $response = ['status'=>'error','message'=>'All password fields are required'];
    }

    echo json_encode($response);
    exit;
}

echo json_encode($response);
