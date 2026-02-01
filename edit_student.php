<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

$student_id = $_GET['student_id'] ?? '';

$success = "";
$error = "";
$student = [];

// Validate student_id
if ($student_id) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();

    if (!$student) {
        $error = "Student not found!";
    }
} else {
    $error = "Invalid student ID.";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $student_id) {
    // Safely fetch POST data
    $student_id_post = $_POST['student_id'] ?? '';
    $student_name = trim($_POST['student_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $degree_program = trim($_POST['degree_program'] ?? '');
    $batch = trim($_POST['batch'] ?? '');
    $Academic_Year = trim($_POST['Academic_Year'] ?? '');
    $semester = trim($_POST['semester'] ?? '');

    // Validate required fields
    if ($student_name && $department && $degree_program && $batch && $Academic_Year && $semester) {
        $stmt = $conn->prepare("UPDATE students SET department=?, degree_program=?, batch=?, Academic_Year=?, semester=?, full_name=? WHERE student_id=?");
        $stmt->bind_param("sssssss", $department, $degree_program, $batch, $Academic_Year, $semester, $student_name, $student_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student updated successfully!";
            header("Location: student_overview.php");
            exit();
        } else {
            $error = "Failed to update student. Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill all the fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h4 class="mb-4">Edit Student</h4>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($student)): ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Student ID (read-only)</label>
            <input type="text" name="student_id" class="form-control" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Student Name</label>
            <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($student['full_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($student['department'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Degree Program</label>
            <input type="text" name="degree_program" class="form-control" value="<?= htmlspecialchars($student['degree_program'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Batch</label>
            <input type="text" name="batch" class="form-control" value="<?= htmlspecialchars($student['batch'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Academic Year</label>
            <input type="text" name="Academic_Year" class="form-control" value="<?= htmlspecialchars($student['Academic_Year'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Semester</label>
            <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($student['semester'] ?? '') ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="student_overview.php" class="btn btn-secondary">Back</a>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
