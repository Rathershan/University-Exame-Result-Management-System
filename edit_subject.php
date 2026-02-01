<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

$subject_id = $_GET['subject_id'] ?? '';

$success = "";
$error = "";

// Validate subject_id
if ($subject_id) {
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("s", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subject = $result->fetch_assoc();
    $stmt->close();

    if (!$subject) {
        $error = "Subject not found!";
    }
} else {
    $error = "Invalid subject ID.";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $subject_id) {
    $subject_name = $_POST['subject_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $degree_program = $_POST['degree_program'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $academic_year = $_POST['Academic_Year'] ?? '';
    $semester = $_POST['semester'] ?? '';

    $stmt = $conn->prepare("UPDATE subjects SET subject_name=?, department=?, degree_program=?, batch=?, Academic_Year=?, semester=? WHERE subject_id=?");
    $stmt->bind_param("sssssss", $subject_name, $department, $degree_program, $batch, $academic_year, $semester, $subject_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Subject updated successfully!";
        header("Location: subject_overview.php");
        exit();
    } else {
        $error = "Failed to update subject.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Subject</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($subject): ?>
        <form method="post">
            <div class="mb-3">
                <label>Subject ID (read-only)</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($subject['subject_id']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Subject Name</label>
                <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($subject['subject_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label>Department</label>
                <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($subject['department'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label>Degree Program</label>
                <input type="text" name="degree_program" class="form-control" value="<?= htmlspecialchars($subject['degree_program'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label>Batch</label>
                <input type="text" name="batch" class="form-control" value="<?= htmlspecialchars($subject['batch'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label>Academic Year</label>
                <input type="text" name="Academic_Year" class="form-control" value="<?= htmlspecialchars($subject['Academic_Year'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label>Semester</label>
                <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($subject['semester'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Subject</button>
            <a href="subject_overview.php" class="btn btn-secondary">Back</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
