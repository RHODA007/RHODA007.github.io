<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

// Fetch current student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $file = $_FILES['profile_pic'] ?? null;
    $filename = $student['profile_pic'];

    // Handle profile picture upload
    if($file && $file['tmp_name']) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $_SESSION['student_id'] . '.' . $ext;
        move_uploaded_file($file['tmp_name'], 'uploads/' . $filename);
    }

    // Update database
    $update = $pdo->prepare("UPDATE students SET name = ?, profile_pic = ? WHERE id = ?");
    if($update->execute([$name, $filename, $_SESSION['student_id']])) {
        $success = "Profile updated successfully!";
        $_SESSION['student_name'] = $name; // update session
    } else {
        $error = "Failed to update profile.";
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="glass-card text-center">
        <h4>Edit Profile</h4>
        <?php if($success) echo "<p class='text-success'>$success</p>"; ?>
        <?php if($error) echo "<p class='text-danger'>$error</p>"; ?>

        <form method="POST" enctype="multipart/form-data" class="mt-3">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control">
                <?php if($student['profile_pic']): ?>
                    <img src="uploads/<?= htmlspecialchars($student['profile_pic']) ?>" 
                         alt="Profile" style="width:80px; height:80px; border-radius:50%; margin-top:10px;">
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
