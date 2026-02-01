<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include("db_connect.php");

$success = "";
$error = "";

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    $stmt = $conn->prepare("SELECT r.subject_id, r.marks, r.grade, s.subject_name 
                            FROM results r 
                            JOIN subjects s ON r.subject_id = s.subject_id 
                            WHERE r.student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $results = $stmt->get_result();
    $data = [];
    while ($row = $results->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
} else {
    $error = "Student ID not provided!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['marks']) && isset($student_id)) {
    $update_success = true;

    foreach ($_POST['marks'] as $subject_id => $mark) {
        $mark = strtoupper(trim($mark));

        if ($mark === "AB" || $mark === "WH") {
            $marks_value = null;
            $grade = $mark;
        } elseif ($mark === "NS") {
            $marks_value = null;
            $grade = "NS";
        } elseif (is_numeric($mark) && $mark >= 0 && $mark <= 100) {
            $marks_value = (int)$mark;
            $grade = calculateGrade($marks_value);
        } else {
            continue;
        }

        $stmt = $conn->prepare("UPDATE results SET marks = ?, grade = ? WHERE student_id = ? AND subject_id = ?");
        if ($marks_value === null) {
            $stmt->bind_param("ssss", $marks_value, $grade, $student_id, $subject_id);
        } else {
            $stmt->bind_param("isss", $marks_value, $grade, $student_id, $subject_id);
        }
        if (!$stmt->execute()) {
            $update_success = false;
        }
        $stmt->close();
    }

    if ($update_success) {
        $_SESSION['success_message'] = "Results updated successfully!";
        header("Location: view_results_page.php");
        exit();
    } else {
        $error = "Failed to update some or all results.";
    }
}

function calculateGrade($marks) {
    if ($marks >= 85) return "A+";
    elseif ($marks >= 70) return "A";
    elseif ($marks >= 65) return "A-";
    elseif ($marks >= 60) return "B+";
    elseif ($marks >= 55) return "B";
    elseif ($marks >= 50) return "B-";
    elseif ($marks >= 45) return "C+";
    elseif ($marks >= 40) return "C";
    elseif ($marks >= 35) return "C-";
    elseif ($marks >= 30) return "D+";
    elseif ($marks >= 25) return "D";
    else return "E";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Results</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">Edit Results for Student: <?= htmlspecialchars($student_id ?? '') ?></h3>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($data)): ?>
        <form method="POST">
            <?php foreach ($data as $row): ?>
                <div class="mb-3">
                    <label class="form-label">
                        <?= htmlspecialchars($row['subject_id']) ?> - <?= htmlspecialchars($row['subject_name']) ?>
                    </label>
                    <input type="text" name="marks[<?= htmlspecialchars($row['subject_id']) ?>]" 
                        value="<?= htmlspecialchars($row['marks'] ?? $row['grade']) ?>" class="form-control">
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Update Results</button>
            <a href="view_results_page.php" class="btn btn-secondary">⬅ Back</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
