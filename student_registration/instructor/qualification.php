<?php
session_start();
require_once 'includes/config.php';

// Use session ID for instructor if logged in
$instructor_id = $_SESSION['instructor']['id'] ?? null;
if (!$instructor_id) {
    die("❌ Invalid instructor session.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $education    = trim($_POST['education']);
    $experience   = trim($_POST['experience']);
    $achievements = trim($_POST['achievements']);
    $bio          = trim($_POST['bio']);

    // Handle multiple certificate uploads
    $uploadedCertificates = [];
    if (!empty($_FILES['certificates']['name'][0])) {
        $certDir = 'uploads/certificates/';
        if (!is_dir($certDir)) mkdir($certDir, 0777, true);

        foreach ($_FILES['certificates']['tmp_name'] as $index => $tmpName) {
            $filename = basename($_FILES['certificates']['name'][$index]);
            $targetFile = $certDir . time() . "_" . preg_replace('/\s+/', '_', $filename);
            if (move_uploaded_file($tmpName, $targetFile)) {
                $uploadedCertificates[] = $targetFile;
            }
        }
    }
    $certificates = !empty($uploadedCertificates) ? implode(',', $uploadedCertificates) : '';

    // Handle CV upload
    $cvFile = '';
    if (!empty($_FILES['cv']['name'])) {
        $cvDir = 'uploads/cv/';
        if (!is_dir($cvDir)) mkdir($cvDir, 0777, true);

        $cvFilename = basename($_FILES['cv']['name']);
        $cvTarget = $cvDir . time() . "_" . preg_replace('/\s+/', '_', $cvFilename);
        if (move_uploaded_file($_FILES['cv']['tmp_name'], $cvTarget)) {
            $cvFile = $cvTarget;
        } else {
            $error = "❌ Failed to upload CV.";
        }
    }

    // Update DB if no error
    if (!$error) {
        $stmt = $pdo->prepare("
            UPDATE instructors 
            SET education = ?, experience = ?, achievements = ?, bio = ?, certificates = ?, cv = ?, unapproved = 1, approved = 0
            WHERE id = ?
        ");
        if ($stmt->execute([$education, $experience, $achievements, $bio, $certificates, $cvFile, $instructor_id])) {
            $success = "✅ Your qualification details, achievements, and CV have been submitted for admin review.";
        } else {
            $error = "❌ Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor Qualification</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
.cv-card { max-width: 700px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
.cv-card h3 { text-align: center; margin-bottom: 25px; color: #1f1f2c; }
.preview img { max-width: 100px; margin-right: 10px; margin-bottom: 10px; border:1px solid #ccc; padding:2px; border-radius:4px; }
.preview span { display:block; margin-bottom:5px; }
</style>
</head>
<body>

<div class="cv-card">
    <h3>Instructor Qualification Form</h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label">Educational Level</label>
            <select name="education" class="form-select" required>
                <option value="">Select your education</option>
                <option value="High School">High School</option>
                <option value="Bachelor's Degree">Bachelor's Degree</option>
                <option value="Master's Degree">Master's Degree</option>
                <option value="PhD">PhD</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Years of Experience</label>
            <input type="number" name="experience" class="form-control" min="0" max="50" placeholder="e.g., 5" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Achievements</label>
            <textarea name="achievements" class="form-control" rows="3" placeholder="List your accomplishments, awards, or notable projects..." required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Short Bio</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Briefly describe yourself and your professional background..." required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Certificates / Documents (PDF, JPG, PNG)</label>
            <input type="file" name="certificates[]" class="form-control" multiple id="certificatesInput">
            <div id="certificatesPreview" class="preview"></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload Your CV (PDF)</label>
            <input type="file" name="cv" class="form-control" accept=".pdf" id="cvInput" required>
            <div id="cvPreview" class="preview"></div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit for Admin Review</button>
    </form>
</div>

<script>
// Certificates preview
const certInput = document.getElementById('certificatesInput');
const certPreview = document.getElementById('certificatesPreview');
let certificatesFiles = [];

certInput.addEventListener('change', function() {
    certificatesFiles = [...certificatesFiles, ...Array.from(this.files)];
    updateCertPreview();
});

function updateCertPreview() {
    certPreview.innerHTML = '';
    certificatesFiles.forEach((file, index) => {
        const div = document.createElement('div');
        div.style.display = 'inline-block';
        div.style.position = 'relative';
        div.style.margin = '5px';

        if(file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            div.appendChild(img);
        } else {
            const span = document.createElement('span');
            span.textContent = file.name;
            div.appendChild(span);
        }

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '✕';
        removeBtn.style.position = 'absolute';
        removeBtn.style.top = '0';
        removeBtn.style.right = '0';
        removeBtn.style.background = 'red';
        removeBtn.style.color = 'white';
        removeBtn.style.border = 'none';
        removeBtn.style.borderRadius = '50%';
        removeBtn.style.width = '20px';
        removeBtn.style.height = '20px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.addEventListener('click', () => {
            certificatesFiles.splice(index, 1);
            updateCertPreview();
        });

        div.appendChild(removeBtn);
        certPreview.appendChild(div);
    });

    const dataTransfer = new DataTransfer();
    certificatesFiles.forEach(file => dataTransfer.items.add(file));
    certInput.files = dataTransfer.files;
}

// CV preview
document.getElementById('cvInput').addEventListener('change', function() {
    const preview = document.getElementById('cvPreview');
    preview.innerHTML = '';
    if(this.files[0]) {
        const span = document.createElement('span');
        span.textContent = this.files[0].name;
        preview.appendChild(span);
    }
});
</script>

</body>
</html>
