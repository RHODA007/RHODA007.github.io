<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Database connection
require 'db_connect.php';

// Search handling
$search = $_GET['search'] ?? '';
$limit = 5; // Students per page
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Query with search + pagination
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute(["%$search%", "%$search%"]);

    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE name LIKE ? OR email LIKE ?");
    $totalStmt->execute(["%$search%", "%$search%"]);
    $total = $totalStmt->fetchColumn();
} else {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM students");
    $total = $totalStmt->fetchColumn();
}

$students = $stmt->fetchAll();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #89f7fe, #66a6ff);
      transition: background 0.4s, color 0.4s;
    }
    body.dark-mode { background: linear-gradient(135deg, #1e1e1e, #121212); color: #f1f1f1; }

    /* Glass Card */
    .glass-card {
      background: rgba(255, 255, 255, 0.25);
      border-radius: 20px;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.18);
      padding: 40px;
      text-align: center;
      animation: fadeInUp 1s ease;
      width: 95%;
      max-width: 1200px;
    }

    h1 { font-weight: 600; color: #333; margin-bottom: 20px; }

    .btn-custom {
      border-radius: 30px;
      padding: 12px 24px;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-custom:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

    /* Table Styling */
    table { border-radius: 12px; overflow: hidden; background: rgba(255,255,255,0.15); backdrop-filter: blur(5px); }
    thead { background: rgba(255,255,255,0.25); font-weight: 600; color: #333; }
    tbody tr { transition: 0.3s; opacity: 0; transform: translateY(10px); animation: rowFadeIn 0.5s forwards; }
    tbody tr:hover { background: rgba(255,255,255,0.25); }
    td, th { vertical-align: middle !important; text-align: center; }
    td img { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 2px 6px rgba(0,0,0,0.2); }

    /* Highlight search matches */
    tbody tr.highlight { background: rgba(255, 255, 255, 0.35) !important; animation: highlightFade 1s ease; }

    @keyframes fadeInUp { 0% { opacity:0; transform: translateY(40px); } 100% { opacity:1; transform: translateY(0); } }
    @keyframes rowFadeIn { to { opacity: 1; transform: translateY(0); } }
    @keyframes highlightFade { 0% { background: rgba(255,255,0,0.5); } 100% { background: rgba(255,255,255,0.35); } }

    /* Pagination */
    .pagination a { margin: 0 5px; padding: 8px 14px; border-radius: 12px; text-decoration: none; background: rgba(255,255,255,0.25); color: #333; font-weight:600; transition: all 0.3s; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
    .pagination a.active { background: #4CAF50; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.2);}
    .pagination a:hover { background: rgba(255,255,255,0.35); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

    /* Responsive tweaks */
    @media (max-width: 768px) { td img { width:40px; height:40px; } }
  </style>
</head>
<body>
<div class="glass-card">
  <h1>🎓 Student Management System</h1>

  <!-- Search Form --
  <form method="GET" action="index.php" class="mb-4">
    <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary btn-custom">🔍 Search</button>
  </form>

  !-- Students Table --
  ?php if($students): ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Registered</th><th>Photo</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        ?php foreach($students as $student): ?>
        <tr class="?= ($search && (stripos($student['name'],$search)!==false || stripos($student['email'],$search)!==false)) ? 'highlight' : '' ?>">
          <td>?= htmlspecialchars($student['id']) ?></td>
          <td>?= htmlspecialchars($student['name']) ?></td>
          <td>?= htmlspecialchars($student['email']) ?></td>
          <td>?= htmlspecialchars($student['phone']) ?></td>
          <td>?= htmlspecialchars($student['dob']) ?></td>
          <td>?= htmlspecialchars($student['created_at']) ?></td>
          <td>
            ?php if($student['photo']): ?>
              <img src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Photo">
            ?php else: ?>
              <span class="text-muted">No Photo</span>
            ?php endif; ?>
          </td>
          <td>
            <a href="edit.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-primary btn-custom">✏ Edit</a>
            <a href="delete.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-danger btn-custom" onclick="return confirm('Are you sure?');">🗑 Delete</a>
          </td>
        </tr>
        ?php endforeach; ?>
      </tbody>
    </table>
  </div>

  !-- Pagination --
  <div class="pagination mt-3 text-center">
    ?php for($i=1;$i<=$totalPages;$i++): ?>
      <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
    ?php endfor; ?>
  </div>
  ?php else: ?>
    <p class="text-muted">No students registered yet.</p>
  ?php endif; ?>  

  !-- Actions -->
  <div class="mt-4 d-flex justify-content-center gap-3">
    <a href="add.php" class="btn btn-success btn-custom">➕ Register Student</a>
    <form method="POST" action="export.php">
      <button type="submit" class="btn btn-info btn-custom">📂 Export PDF</button>
      <a href="view.php" class="btn btn-success btn-custom">View Students</a>
    </form>
  </div>

  <!-- Dark Mode Toggle -->
  <button id="toggleDark" class="btn btn-dark btn-sm mt-4">🌙 Toggle Dark Mode</button>
</div>

<script>
  const toggleBtn = document.getElementById('toggleDark');
  const body = document.body;
  if(localStorage.getItem("dark-mode")==="enabled") body.classList.add("dark-mode");
  toggleBtn.addEventListener("click", ()=> {
    body.classList.toggle("dark-mode");
    localStorage.setItem("dark-mode", body.classList.contains("dark-mode") ? "enabled":"disabled");
  });
</script>
</body>
</html>
