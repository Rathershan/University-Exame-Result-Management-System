<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || !isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

include("db_connect.php");

$student_id = $_SESSION['student_id'];

// GPA Calculation Functions
function getGpaPoint($grade) {
    switch (strtoupper($grade)) {
        case 'A+':  return 4.00;
        case 'A': return 3.7;
        case 'A-': return 3.5;
        case 'B+': return 3.3;
        case 'B': return 3.00;
        case 'B-': return 2.7;
        case 'C+': return 2.3;
        case 'C': return 2.00;
        case 'C-': return 1.7;
        case 'D+': return 1.3;
        case 'D': return 1.00;
        case 'F': return 0.0;
        default: return null;
    }
}

function calculateGpa($grades) {
    $total = 0;
    $count = 0;
    foreach ($grades as $grade) {
        $point = getGpaPoint($grade);
        if ($point !== null) {
            $total += $point;
            $count++;
        }
    }
    return $count > 0 ? round($total / $count, 2) : 'N/A';
}

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo "<script>alert('❌ Student data not found! Please contact Examdepartment.'); window.location.href='student_dashboard.php';</script>";
    exit();
}

// Fetch results + subject names (ONLY published)
$stmt2 = $conn->prepare("SELECT r.subject_id, s.subject_name, r.grade 
                         FROM results r 
                         JOIN subjects s ON r.subject_id = s.subject_id 
                         WHERE r.student_id = ? AND r.published = 1");
$stmt2->bind_param("s", $student_id);
$stmt2->execute();
$result_data = $stmt2->get_result();

$grades = [];
$results = [];

while ($row = $result_data->fetch_assoc()) {
    $grades[] = $row['grade'];
    $results[] = $row;
}

$gpa = calculateGpa($grades);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Result</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3 class="mb-4">🎓  Exam Results</h3>

        <div class="text-end mb-3">
            <a href="download_student_result.php" class="btn btn-success" target="_blank">⬇️ Download Result</a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Student Details</div>
            <div class="card-body">
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id'] ?? '') ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($student['full_name'] ?? '') ?></p>
                <p><strong>Department:</strong> <?= htmlspecialchars($student['department'] ?? '') ?></p>
                <p><strong>Degree Program:</strong> <?= htmlspecialchars($student['degree_program'] ?? '') ?></p>
                <p><strong>Batch:</strong> <?= htmlspecialchars($student['batch'] ?? '') ?></p>
                <p><strong>Academic Year:</strong> <?= htmlspecialchars($student['Academic_Year'] ?? '') ?></p>
                <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester'] ?? '') ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">Results</div>
            <div class="card-body">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Subject ID</th>
                            <th>Subject Name</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="3">No published results available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['subject_id']) ?></td>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($row['grade']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-info fw-bold">
                                <td colspan="2">GPA</td>
                                <td><?= $gpa ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <a href="student_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
