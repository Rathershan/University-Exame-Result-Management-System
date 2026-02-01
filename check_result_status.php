<?php
include("db_connect.php");

$department = $_GET['department'] ?? '';
$batch = $_GET['batch'] ?? '';

// Get students
$student_sql = "SELECT student_id FROM students WHERE 1";
if ($department) $student_sql .= " AND department = '" . mysqli_real_escape_string($conn, $department) . "'";
if ($batch) $student_sql .= " AND batch = '" . mysqli_real_escape_string($conn, $batch) . "'";
$student_result = mysqli_query($conn, $student_sql);
$students = [];
while ($row = mysqli_fetch_assoc($student_result)) {
    $students[] = $row['student_id'];
}

// Get subjects
$subject_sql = "SELECT subject_id FROM subjects WHERE 1";
if ($department) $subject_sql .= " AND department = '" . mysqli_real_escape_string($conn, $department) . "'";
if ($batch) $subject_sql .= " AND batch = '" . mysqli_real_escape_string($conn, $batch) . "'";
$subject_result = mysqli_query($conn, $subject_sql);
$subjects = [];
while ($row = mysqli_fetch_assoc($subject_result)) {
    $subjects[] = $row['subject_id'];
}

// Total required entries
$total_required = count($students) * count($subjects);

// Count actual entries
$result_sql = "SELECT COUNT(*) AS total FROM results r
               JOIN students s ON r.student_id = s.student_id
               WHERE 1";
if ($department) $result_sql .= " AND s.department = '" . mysqli_real_escape_string($conn, $department) . "'";
if ($batch) $result_sql .= " AND s.batch = '" . mysqli_real_escape_string($conn, $batch) . "'";
$actual = mysqli_fetch_assoc(mysqli_query($conn, $result_sql))['total'];

$complete = ($total_required > 0 && $total_required == $actual);

echo json_encode([
    "required" => $total_required,
    "actual" => $actual,
    "complete" => $complete
]);
?>
