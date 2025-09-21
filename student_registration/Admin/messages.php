<?php
session_start();
require 'db_connect.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// ----------------- HANDLE SEND (TEXT + FILE) -----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $msg = trim($_POST['message']);
    $receiver_id = $_POST['receiver_id'] ?? null;

    // File upload handling
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

    if (!empty($msg) || $filePath) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, attachment, created_at, seen) 
                               VALUES (?, ?, ?, ?, NOW(), 0)");
        $stmt->execute([$admin_id, $receiver_id, $msg, $filePath]);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Empty message"]);
    }
    exit;
}

// ----------------- HANDLE VOICE NOTE UPLOAD -----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voice_receiver_id']) && isset($_FILES['voice_note'])) {
    $receiver_id = $_POST['voice_receiver_id'];
    $file = $_FILES['voice_note'];

    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['mp3','wav','ogg'];
        if (!in_array(strtolower($ext), $allowed)) {
            echo json_encode(['status'=>'error','message'=>'Invalid audio format']);
            exit;
        }

        $filename = uniqid() . "." . $ext;
        $uploadDir = __DIR__ . "/uploads/voice_notes/";
        if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, voice_note, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$admin_id, $receiver_id, $filename]);
            echo json_encode(['status'=>'success','message'=>'Voice note sent successfully!']);
            exit;
        }
    }
    echo json_encode(['status'=>'error','message'=>'Failed to upload voice note']);
    exit;
}

// ----------------- HANDLE FETCH -----------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action']==='fetch') {
    $instructor_id = (int)$_GET['instructor_id'];

    $pdo->prepare("UPDATE messages SET seen=1 WHERE sender_id=? AND receiver_id=? AND seen=0")
        ->execute([$instructor_id,$admin_id]);

    $convStmt = $pdo->prepare("
        SELECT m.*, 
               CASE WHEN m.sender_id=:admin_id THEN 'You' ELSE i.name END AS sender_name,
               i.photo
        FROM messages m
        JOIN instructors i ON (i.id = CASE WHEN m.sender_id != :admin_id2 THEN m.sender_id ELSE m.receiver_id END)
        WHERE ((m.sender_id=:instructor_id AND m.receiver_id=:admin_id3)
            OR (m.sender_id=:admin_id4 AND m.receiver_id=:instructor_id2))
        ORDER BY m.created_at ASC
    ");
    $convStmt->execute([
        'admin_id'=>$admin_id,
        'admin_id2'=>$admin_id,
        'admin_id3'=>$admin_id,
        'admin_id4'=>$admin_id,
        'instructor_id'=>$instructor_id,
        'instructor_id2'=>$instructor_id
    ]);
    $messages = $convStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages);
    exit;
}

// ----------------- FETCH INSTRUCTORS -----------------
$instructorsStmt = $pdo->query("SELECT id,name,photo FROM instructors ORDER BY name ASC");
$instructors = $instructorsStmt->fetchAll(PDO::FETCH_ASSOC);
$selectedInstructorId = $_GET['instructor_id'] ?? null;

function esc($v){ return htmlspecialchars($v, ENT_QUOTES,'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Chat Inbox</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body{font-family:Poppins,sans-serif;background:#f4f6f9;margin:0;padding:20px;}
h1{text-align:center;margin-bottom:30px;color:#333;}
.chat-container{display:flex;gap:20px;max-width:1200px;margin:0 auto;height:80vh;}
.user-list{width:250px;background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);overflow-y:auto;}
.user-list-item{padding:15px;display:flex;align-items:center;gap:10px;cursor:pointer;border-bottom:1px solid #eee;transition:0.2s;}
.user-list-item:hover{background:#f1f1f1;}
.user-list-item.active{background:#0d6efd;color:#fff;}
.user-photo{width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid #ddd;}
.chat-box{flex:1;background:#fff;border-radius:12px;display:flex;flex-direction:column;box-shadow:0 4px 12px rgba(0,0,0,0.08);overflow:hidden;}
.chat-header{padding:15px;border-bottom:1px solid #eee;font-weight:600;font-size:1.1rem;display:flex;align-items:center;gap:10px;}
.chat-messages{flex:1;padding:15px;overflow-y:auto;display:flex;flex-direction:column;gap:15px;background:#f9f9f9;}
.message{max-width:70%;padding:10px 15px;border-radius:15px;word-wrap:break-word;position:relative;}
.message.admin{align-self:flex-end;background:#0d6efd;color:#fff;border-bottom-right-radius:0;}
.message.instructor{align-self:flex-start;background:#e9ecef;color:#333;border-bottom-left-radius:0;}
.attachment a{color:#0d6efd;text-decoration:none;}
.chat-input{display:flex;border-top:1px solid #eee;padding:10px;gap:5px;}
.chat-input input[type=text]{flex:1;border-radius:20px;border:1px solid #ccc;padding:8px 15px;}
.chat-input input[type=file]{max-width:200px;}
.chat-input button{border-radius:20px;padding:8px 15px;background:#0d6efd;color:#fff;border:none;}
</style>
</head>
<body>

<h1>📬 Admin Chat with Instructors</h1>

<div class="chat-container">
  <!-- Instructor list -->
  <div class="user-list">
    <?php foreach ($instructors as $ins): ?>
      <a href="?instructor_id=<?= $ins['id'] ?>" class="user-list-item <?= $ins['id']==$selectedInstructorId?'active':'' ?>">
        <img src="<?= !empty($ins['photo'])?'uploads/'.$ins['photo']:'https://via.placeholder.com/50' ?>" class="user-photo">
        <span><?= esc($ins['name']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Chat box -->
  <div class="chat-box">
    <div class="chat-header">
      <?php 
      $selectedInstructor = array_filter($instructors, fn($i)=>$i['id']==$selectedInstructorId);
      $selectedInstructor = $selectedInstructor ? array_values($selectedInstructor)[0] : null;
      ?>
      <?php if($selectedInstructor): ?>
        <img src="<?= !empty($selectedInstructor['photo'])?'uploads/'.$selectedInstructor['photo']:'https://via.placeholder.com/40' ?>" class="user-photo" style="width:40px;height:40px;">
        <?= esc($selectedInstructor['name']) ?>
      <?php else: ?>
        Select an instructor to view messages
      <?php endif; ?>
    </div>

    <div class="chat-messages" id="chat-messages"></div>

    <?php if($selectedInstructor): ?>
    <form class="chat-input" id="chat-form" enctype="multipart/form-data">
      <input type="text" id="chat-input" name="message" placeholder="Type your message...">
      <input type="file" name="attachment">
      <button type="submit" id="send-btn"><i class="fas fa-paper-plane"></i></button>
    </form>

    <div class="mt-2">
      <button id="recordBtn" class="btn btn-success btn-sm">🎤 Start Recording</button>
      <span id="recordStatus" class="text-muted ms-2"></span>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
const adminId = <?= (int)$admin_id ?>;
const instructorId = <?= $selectedInstructorId ? (int)$selectedInstructorId:'null' ?>;
let mediaRecorder, audioChunks=[], isRecording=false;

function fetchMessages(){
  if(!instructorId) return;
  fetch(`messages.php?action=fetch&instructor_id=${instructorId}`)
    .then(res=>res.json())
    .then(data=>{
      let html='';
      data.forEach(msg=>{
        const isAdmin = msg.sender_id==adminId;
        html+=`<div class="message ${isAdmin?'admin':'instructor'}">`;
        if(msg.message) html+=msg.message;
        if(msg.attachment){
          const ext = msg.attachment.split('.').pop().toLowerCase();
          if(['jpg','jpeg','png','gif'].includes(ext)){
            html+=`<div class="attachment"><img src="${msg.attachment}" alt="Attachment"></div>`;
          } else if(['mp3','wav','ogg'].includes(ext)){
            html+=`<div class="attachment"><audio controls src="${msg.attachment}"></audio></div>`;
          } else {
            html+=`<div class="attachment"><a href="${msg.attachment}" download><i class="fa fa-paperclip"></i> Download file</a></div>`;
          }
        }
        if(msg.voice_note){
          html+=`<div class="attachment"><audio controls src="uploads/voice_notes/${msg.voice_note}"></audio></div>`;
        }
        html+=`<div style="font-size:0.7rem;color:#666;margin-top:3px;">${msg.created_at}${!isAdmin && msg.seen==1?' ✓ Seen':''}</div>`;
        html+=`</div>`;
      });
      document.getElementById("chat-messages").innerHTML=html;
      document.getElementById("chat-messages").scrollTop=document.getElementById("chat-messages").scrollHeight;
    });
}

// Send text/file message
document.getElementById("chat-form")?.addEventListener("submit",(e)=>{
  e.preventDefault();
  const formData=new FormData(e.target);
  formData.append("action","send");
  formData.append("receiver_id",instructorId);
  fetch("messages.php",{method:"POST",body:formData})
    .then(res=>res.json()).then(()=>{ e.target.reset(); fetchMessages(); });
});

// Voice note recording
const recordBtn=document.getElementById('recordBtn');
const recordStatus=document.getElementById('recordStatus');

recordBtn.addEventListener('click', async ()=>{
  if(!instructorId){ alert('Select an instructor'); return; }
  if(!isRecording){
    const stream=await navigator.mediaDevices.getUserMedia({audio:true});
    mediaRecorder=new MediaRecorder(stream);
    audioChunks=[];
    mediaRecorder.ondataavailable=e=>audioChunks.push(e.data);
    mediaRecorder.onstop=()=>sendVoiceNote(instructorId);
    mediaRecorder.start();
    isRecording=true;
    recordBtn.textContent='⏹ Stop Recording';
    recordStatus.textContent='Recording...';
  } else {
    mediaRecorder.stop();
    isRecording=false;
    recordBtn.textContent='🎤 Start Recording';
    recordStatus.textContent='Processing...';
  }
});

function sendVoiceNote(receiverId){
  const blob=new Blob(audioChunks,{type:'audio/mp3'});
  const formData=new FormData();
  formData.append('voice_note',blob,'voicenote.mp3');
  formData.append('voice_receiver_id',receiverId);
  fetch('messages.php',{method:'POST',body:formData})
    .then(res=>res.json())
    .then(data=>recordStatus.textContent=data.message)
    .catch(()=>recordStatus.textContent='Error sending voice note');
}

// Auto-refresh
setInterval(fetchMessages,3000);
fetchMessages();
</script>

</body>
</html>
