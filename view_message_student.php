<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: student_login.php");
    exit();
}

include("db_connect.php");

$student_id = $_SESSION['student_id'];

// Fetch uploaded files for this student
$stmt = $conn->prepare("SELECT * FROM admin_files WHERE student_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Messages & Files</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>📥 My Messages & PDF Files</h3>

    <?php if (empty($files)): ?>
        <div class="alert alert-warning">You have no messages or files from admin yet.</div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($files as $row): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <strong>📄 <?= htmlspecialchars($row['file_name']) ?></strong><br>
                        <?php if (!empty($row['message'])): ?>
                            <span class="badge bg-info text-dark">📝 Message: <?= htmlspecialchars($row['message']) ?></span><br>
                        <?php endif; ?>
                        <small>🕒 Uploaded At: <?= date("d M Y, h:i A", strtotime($row['uploaded_at'])) ?></small>
                    </div>
                    <a href="<?= $row['file_path'] ?>" class="btn btn-sm btn-success" download>Download</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="mt-4">
        <a href="student_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
