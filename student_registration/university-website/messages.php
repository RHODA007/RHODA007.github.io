<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/config.php';

// ✅ Student ID (supports both array/session-only)
if (is_array($_SESSION['student']) && isset($_SESSION['student']['id'])) {
    $student_id = (int)$_SESSION['student']['id'];
} else {
    $student_id = (int)$_SESSION['student'];
}

// Get student’s instructors
$instStmt = $pdo->prepare("
    SELECT DISTINCT i.id, i.name 
    FROM instructors i
    JOIN courses c ON i.id = c.instructor_id
    JOIN student_courses sc ON c.id = sc.course_id
    WHERE sc.student_id = ?
");
$instStmt->execute([$student_id]);
$instructors = $instStmt->fetchAll(PDO::FETCH_ASSOC);

// Which instructor are we chatting with?
$instructor_id = $_GET['instructor_id'] ?? ($instructors[0]['id'] ?? null);

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $body = trim($_POST['message'] ?? '');
    $filePath = null;

    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = "uploads/messages/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['attachment']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
            $filePath = $targetFile;
        }
    }

    if ($body !== '' || $filePath) {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message, attachment, created_at, seen) 
            VALUES (?, ?, ?, ?, NOW(), 0)
        ");
        $stmt->execute([$student_id, $instructor_id, $body, $filePath]);
        header("Location: messages.php?instructor_id=".$instructor_id."&sent=1");
        exit;
    }
}

// Fetch conversation with this instructor
$messages = [];
if ($instructor_id) {
    $convStmt = $pdo->prepare("
        SELECT m.*, 
               CASE WHEN m.sender_id = ? THEN 'You' ELSE i.name END AS sender_name
        FROM messages m
        LEFT JOIN instructors i ON m.sender_id = i.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $convStmt->execute([$student_id, $student_id, $instructor_id, $instructor_id, $student_id]);
    $messages = $convStmt->fetchAll(PDO::FETCH_ASSOC);
}

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
/* Same sidebar styles you already use */
body{margin:0; font-family:'Segoe UI',sans-serif; min-height:100vh; background:#f8f9fa;}
.sidebar{position:fixed;left:0;top:0;width:240px;height:100vh;background:rgba(31,31,47,0.95);display:flex;flex-direction:column;color:#fff;}
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background:#1f1f2c; color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8da; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main-content{margin-left:240px;padding:20px;}
.chat-box{background:#fff;border-radius:12px;padding:16px;height:65vh;overflow-y:auto;}
.message{margin-bottom:15px;max-width:70%;padding:10px 14px;border-radius:12px;}
.message.you{background:#4f46e5;color:#fff;margin-left:auto;}
.message.other{background:#f1f1f1;color:#222;margin-right:auto;}
</style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header"><h3>Student Panel</h3></div>
    <ul class="sidebar-menu">
        <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
        <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
        <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
        <li><a href="timetable.php" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a></li>
        <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
        <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
        <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

<div class="main-content">
    <h2>Messages</h2>

    <!-- Instructor selector -->
    <form method="get" class="mb-3">
        <label>Select Instructor:</label>
        <select name="instructor_id" class="form-select" onchange="this.form.submit()">
            <?php foreach ($instructors as $inst): ?>
                <option value="<?= $inst['id'] ?>" <?= ($inst['id']==$instructor_id?'selected':'') ?>>
                    <?= esc($inst['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="chat-box mb-3">
        <?php if ($messages): ?>
            <?php foreach ($messages as $m): ?>
                <div class="message <?= $m['sender_name']==='You' ? 'you' : 'other' ?>">
                    <div><b><?= esc($m['sender_name']) ?>:</b> <?= esc($m['message']); ?></div>
                    <?php if (!empty($m['attachment'])): ?>
                        <div class="mt-1">
                            <a href="<?= esc($m['attachment']); ?>" target="_blank">📎 Attachment</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No messages yet with this instructor.</p>
        <?php endif; ?>
    </div>

    <?php if ($instructor_id): ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-2">
            <textarea name="message" class="form-control" rows="3" placeholder="Write your message..."></textarea>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input type="file" name="attachment" class="form-control form-control-sm">
            <button type="submit" name="send_message" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane me-1"></i> Send
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
