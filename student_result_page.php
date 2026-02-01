<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: student_login.php");
    exit();
}

include("db_connect.php");

$student_id = $_SESSION['student_id'];

// Fetch student information
$student_query = "SELECT * FROM students WHERE student_id = '" . mysqli_real_escape_string($conn, $student_id) . "' LIMIT 1";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);

// Fetch subjects and grades
$result_query = "SELECT r.subject_id, sub.subject_name, r.grade
                 FROM results r
                 JOIN subjects sub ON r.subject_id = sub.subject_id
                 WHERE r.student_id = '" . mysqli_real_escape_string($conn, $student_id) . "'";
$result_data = mysqli_query($conn, $result_query);

$grades = [];
while ($row = mysqli_fetch_assoc($result_data)) {
    $grades[] = $row;
}

function getGpaPoint($grade) {
    switch (strtoupper($grade)) {
        case 'A+': case 'A': return 4.0;
        case 'A-': return 3.7;
        case 'B+': return 3.3;
        case 'B': return 3.0;
        case 'B-': return 2.7;
        case 'C+': return 2.3;
        case 'C': return 2.0;
        case 'C-': return 1.7;
        case 'D+': return 1.3;
        case 'D': return 1.0;
        case 'F': return 0.0;
        default: return null;
    }
}

function calculateGPA($grades) {
    $total_points = 0;
    $count = 0;
    foreach ($grades as $g) {
        $point = getGpaPoint($g['grade']);
        if ($point !== null) {
            $total_points += $point;
            $count++;
        }
    }
    return $count > 0 ? round($total_points / $count, 2) : 'N/A';
}

$gpa = calculateGPA($grades);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Result</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="bg-primary text-white p-3">
    <div class="container">
        <h2>Student Result Page</h2>
    </div>
</header>

<div class="container mt-4">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Student Information</h5>
            <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($student['full_name']) ?></p>
            <p><strong>Department:</strong> <?= htmlspecialchars($student['department']) ?></p>
            <p><strong>Batch:</strong> <?= htmlspecialchars($student['batch']) ?></p>
            <p><strong>Year:</strong> <?= htmlspecialchars($student['year']) ?></p>
            <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Exam Results (Grade Only)</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject ID</th>
                        <th>Subject </th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['subject_id']) ?></td>
                        <td><?= htmlspecialchars($g['subject_name']) ?></td>
                        <td><?= htmlspecialchars($g['grade']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="mt-3"><strong>GPA:</strong> <?= $gpa ?></p>
            <form method="post" action="download_result_pdf.php">
                <button type="submit" class="btn btn-success">Download as PDF</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
