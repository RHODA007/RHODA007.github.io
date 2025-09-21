<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['instructor'])) {
    header("Location: login.php");
    exit;
}

$instructor_id = $_SESSION['instructor']['id'] ?? null;

// Fetch students (fix: use students + student_id, not users + user_id)
$studentsStmt = $pdo->prepare("
    SELECT DISTINCT s.id, s.name, s.photo
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY s.name
");
$studentsStmt->execute([$instructor_id]);
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch groups
$groupsStmt = $pdo->prepare("SELECT * FROM groups WHERE instructor_id = ?");
$groupsStmt->execute([$instructor_id]);
$groups = $groupsStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedChatId = $_GET['chat_id'] ?? null;
$chatType = $_GET['type'] ?? null; // "student" or "group"

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor Messages</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:Arial,sans-serif; margin:0; }
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background:#1f1f2c; color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8da; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:8px; padding:20px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
.chat-container { display:flex; gap:20px; height:75vh; }
.user-list { width:250px; background:#fff; border-radius:8px; overflow-y:auto; }
.user-list-item { padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer; border-bottom:1px solid #eee; }
.user-list-item:hover { background:#f1f1f1; }
.user-list-item.active { background:#0d6efd; color:#fff; }
.user-photo { width:40px; height:40px; border-radius:50%; object-fit:cover; }
.chat-box { flex:1; background:#fff; border-radius:8px; display:flex; flex-direction:column; }
.chat-header { padding:12px; border-bottom:1px solid #eee; font-weight:600; display:flex; justify-content:space-between; align-items:center; }
.chat-messages { flex:1; padding:15px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; background:#f9f9f9; }
.message { max-width:70%; padding:10px 15px; border-radius:15px; word-wrap:break-word; }
.message.you { align-self:flex-end; background:#0d6efd; color:#fff; }
.message.them { align-self:flex-start; background:#e9ecef; }
.chat-input { display:flex; border-top:1px solid #eee; padding:10px; gap:5px; }
.chat-input input[type=text] { flex:1; border-radius:20px; border:1px solid #ccc; padding:8px 15px; }
.chat-input button { border-radius:20px; padding:8px 15px; background:#0d6efd; color:#fff; border:none; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header"><h3>Instructor Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <h4 class="mb-3">💬 Instructor Messages</h4>
  <div class="chat-container">
    <!-- Users/Groups list -->
    <div class="user-list card">
      <div class="p-2 fw-bold">Students</div>
      <?php foreach($students as $s): ?>
        <a href="?type=student&chat_id=<?= $s['id'] ?>" class="user-list-item <?= ($chatType==='student' && $s['id']==$selectedChatId)?'active':'' ?>">
          <img src="<?= $s['photo'] ?: 'https://via.placeholder.com/40' ?>" class="user-photo">
          <span><?= esc($s['name']) ?></span>
        </a>
      <?php endforeach; ?>

      <div class="p-2 fw-bold">Groups</div>
      <?php foreach($groups as $g): ?>
        <a href="?type=group&chat_id=<?= $g['id'] ?>" class="user-list-item <?= ($chatType==='group' && $g['id']==$selectedChatId)?'active':'' ?>">
          <i class="fas fa-users"></i> <?= esc($g['group_name']) ?>
        </a>
      <?php endforeach; ?>
      <a href="create_group.php" class="btn btn-sm btn-primary m-2"><i class="fas fa-plus"></i> New Group</a>
    </div>

    <!-- Chat box -->
    <div class="chat-box card">
      <div class="chat-header">
        <div>
          <?= $chatType && $selectedChatId ? ($chatType==='student' ? "Chat with Student" : "Group Chat") : "Select a student or group" ?>
        </div>
        <?php if($chatType==='group' && $selectedChatId): ?>
          <a href="zoom_meeting.php?group_id=<?= $selectedChatId ?>" target="_blank" class="btn btn-sm btn-success">
            <i class="fas fa-video"></i> Start Zoom
          </a>
        <?php endif; ?>
      </div>

      <div id="chat-messages" class="chat-messages"></div>

      <form id="chat-form" class="chat-input">
        <input type="text" id="chat-input-text" placeholder="Type your message...">
        <button type="submit"><i class="fas fa-paper-plane"></i></button>
      </form>
    </div>
  </div>
</div>

<script>
const chatType = "<?= $chatType ?>";
const chatId = "<?= $selectedChatId ?>";

function fetchMessages(){
  if(!chatId) return;
  fetch(`messages_ajax.php?action=fetch&type=${chatType}&id=${chatId}`)
    .then(res=>res.json())
    .then(data=>{
      let html="";
      data.forEach(m=>{
        html+=`<div class="message ${m.sender==='you'?'you':'them'}">${m.text}</div>`;
      });
      const msgBox=document.getElementById("chat-messages");
      msgBox.innerHTML=html;
      msgBox.scrollTop=msgBox.scrollHeight;
    });
}
setInterval(fetchMessages,2000);
fetchMessages();

document.getElementById("chat-form").addEventListener("submit",e=>{
  e.preventDefault();
  const msg=document.getElementById("chat-input-text").value;
  if(msg.trim()==="") return;
  fetch("messages_ajax.php?action=send",{
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body:JSON.stringify({type:chatType,id:chatId,message:msg})
  }).then(r=>r.json()).then(d=>{
    if(d.success){
      document.getElementById("chat-input-text").value="";
      fetchMessages();
    }
  });
});
</script>
</body>
</html>
