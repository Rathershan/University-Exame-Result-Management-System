<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include("db_connect.php");

$success = "";
$error = "";

// Handle upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'];
    $message = trim($_POST['message']);
    $file = $_FILES['file'];

    if ($file['error'] === 0) {
        $filename = basename($file['name']);
        $targetDir = "uploads/";

        // ✅ Create uploads folder if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . time() . "_" . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            if ($student_id === 'ALL') {
                // Upload to all students
                $students = $conn->query("SELECT student_id FROM students");
                while ($row = $students->fetch_assoc()) {
                    $sid = $row['student_id'];
                    $stmt = $conn->prepare("INSERT INTO admin_files (student_id, file_name, file_path, message, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssss", $sid, $filename, $targetPath, $message);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Upload to selected student
                $stmt = $conn->prepare("INSERT INTO admin_files (student_id, file_name, file_path, message, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $student_id, $filename, $targetPath, $message);
                $stmt->execute();
                $stmt->close();
            }

            $success = "✅ File and message uploaded successfully!";
        } else {
            $error = "❌ Failed to move uploaded file.";
        }
    } else {
        $error = "❌ File upload error.";
    }
}

// Handle deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM admin_files WHERE id = $id");
    $success = " File deleted successfully.";
}

// Get students
$students = $conn->query("SELECT student_id, full_name FROM students ORDER BY student_id ASC");

// Get uploaded files
$files = $conn->query("SELECT * FROM admin_files ORDER BY uploaded_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File and Message</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body> 
     <!-- Header -->
    <header class="header py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">GWUIM - Admin Panel</h1>
                <nav class="d-flex gap-3">
                    <a href="dashboard.php" class="text-white text-decoration-none"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    <a href="logout.php" class="text-white text-decoration-none"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

<div class="container mt-3">
   
    <h3>📤 Upload PDF and Message to Students</h3>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label for="student_id" class="form-label">Select Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">-- Choose Student --</option>
                <option value="ALL">📤 All Students</option>
                <?php while ($s = $students->fetch_assoc()): ?>
                    <option value="<?= $s['student_id'] ?>"><?= $s['student_id'] ?> (<?= $s['full_name'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" rows="3" placeholder="Enter your message (optional)"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload PDF</label>
            <input type="file" name="file" class="form-control" accept="application/pdf" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <h5>📚 Uploaded Files:</h5>
    <ul class="list-group">
        <?php while ($row = $files->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($row['student_id']) ?> - <?= htmlspecialchars($row['file_name']) ?>
                <?php if (!empty($row['message'])): ?>
                    <span class="badge bg-info text-dark">Message: <?= htmlspecialchars($row['message']) ?></span>
                <?php endif; ?>
                <div>
                    <a href="<?= $row['file_path'] ?>" class="btn btn-sm btn-success" download>Download</a>
                    <a href="?delete=1&id=<?= $row['id'] ?>" onclick="return confirm('Delete this file?')" class="btn btn-sm btn-danger">Delete</a>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
</div>
</body>
</html>
