<?php
session_start();
require 'db_connect.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch all students
$students = $pdo->query("SELECT id, name FROM students")->fetchAll();

// Handle AJAX voice note upload
if (isset($_POST['voice_receiver_id']) && isset($_FILES['voice_note'])) {
    $receiver_id = $_POST['voice_receiver_id'];
    $file = $_FILES['voice_note'];

    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['mp3', 'wav', 'ogg'];
        if (!in_array(strtolower($ext), $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid audio format']);
            exit;
        }

        $filename = uniqid() . "." . $ext;
        $uploadDir = __DIR__ . "/uploads/voice_notes/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, voice_note, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['admin_id'], $receiver_id, $filename]);
            echo json_encode(['status' => 'success', 'message' => 'Voice note sent successfully!']);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload voice note']);
    exit;
}

// Handle text message form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    if (!empty($receiver_id) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['admin_id'], $receiver_id, $message]);
            $success = "✅ Message sent successfully!";
        } catch (PDOException $e) {
            $error = "❌ Database Error: " . $e->getMessage();
        }
    } else {
        $error = "⚠ Please select a student and type a message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Send Message</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { margin: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
.sidebar { width: 240px; background: #212529; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding-top: 30px; }
.sidebar h3 { text-align: center; margin-bottom: 40px; font-weight: bold; }
.sidebar a { display: block; padding: 12px 20px; color: #ddd; text-decoration: none; transition: 0.3s; border-radius: 8px; margin: 4px 12px; }
.sidebar a:hover, .sidebar a.active { background: #0d6efd; color: #fff; }
.content { margin-left: 260px; padding: 40px; flex: 1; }
.card { border: none; border-radius: 16px; background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.btn-primary { border-radius: 8px; padding: 10px; font-weight: 600; }
textarea { resize: none; }
#recordBtn { margin-top: 10px; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>📘 Admin Panel</h2>
  <a href="dashboard.php">🏠 Dashboard</a>
  <a href="courses.php">📚 Courses</a>
  <a href="assign_course.php">📝 Assign Courses</a>
  <a href="assignments.php">📂 Assignments</a>
  <a href="messages.php" class="bg-white text-dark fw-bold">💬 Messages</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<!-- Content -->
<div class="content">
  <div class="card p-4">
    <h3 class="mb-4">💬 Send Message to Student</h3>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Text Message Form -->
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Select Student</label>
        <select name="receiver_id" class="form-select" required>
          <option value="">-- Choose Student --</option>
          <?php foreach ($students as $student): ?>
            <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" rows="4" class="form-control" placeholder="Type your message..." required></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100">Send Message</button>
    </form>

    <hr class="my-4">

    <!-- Voice Note -->
    <h5>🎤 Send Voice Note</h5>
    <div class="mb-3">
      <label class="form-label">Select Student</label>
      <select id="voiceReceiver" class="form-select">
        <option value="">-- Choose Student --</option>
        <?php foreach ($students as $student): ?>
          <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button id="recordBtn" class="btn btn-success">Start Recording</button>
    <p id="recordStatus" class="mt-2 text-muted"></p>
  </div>
</div>

<script>
let mediaRecorder, audioChunks = [], isRecording = false;
const recordBtn = document.getElementById('recordBtn');
const recordStatus = document.getElementById('recordStatus');

recordBtn.addEventListener('click', async () => {
    const receiverId = document.getElementById('voiceReceiver').value;
    if (!receiverId) { alert('Select a student'); return; }

    if (!isRecording) {
        if (!navigator.mediaDevices) { alert('Audio recording not supported'); return; }
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = () => sendVoiceNote(receiverId);
        mediaRecorder.start();
        isRecording = true;
        recordBtn.textContent = 'Stop Recording';
        recordStatus.textContent = 'Recording...';
    } else {
        mediaRecorder.stop();
        isRecording = false;
        recordBtn.textContent = 'Start Recording';
        recordStatus.textContent = 'Processing...';
    }
});

function sendVoiceNote(receiverId) {
    const blob = new Blob(audioChunks, { type: 'audio/mp3' });
    const formData = new FormData();
    formData.append('voice_note', blob, 'voicenote.mp3');
    formData.append('voice_receiver_id', receiverId);

    fetch('send_message.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            recordStatus.textContent = data.message;
        })
        .catch(err => {
            recordStatus.textContent = 'Error sending voice note';
        });
}
</script>

</body>
</html>
