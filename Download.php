<?php
ob_start();
require('fpdf/fpdf.php');
include("db_connect.php");

// Input filters
$department = $_GET['department'] ?? '';
$batch = $_GET['batch'] ?? '';

// Subject list
$subjects_query = "SELECT subject_id FROM subjects WHERE 1";
if ($department) $subjects_query .= " AND department = '" . mysqli_real_escape_string($conn, $department) . "'";
if ($batch) $subjects_query .= " AND batch = '" . mysqli_real_escape_string($conn, $batch) . "'";
$subjects_query .= " ORDER BY subject_id ASC";

$subjects_result = mysqli_query($conn, $subjects_query);
$subject_ids = [];
while ($row = mysqli_fetch_assoc($subjects_result)) {
    $subject_ids[] = $row['subject_id'];
}

// Student grades fetch
$results_query = "SELECT r.student_id, s.full_name, r.subject_id, r.grade 
                  FROM results r 
                  JOIN students s ON r.student_id = s.student_id 
                  WHERE r.published = 1";

if ($department) $results_query .= " AND s.department = '" . mysqli_real_escape_string($conn, $department) . "'";
if ($batch) $results_query .= " AND s.batch = '" . mysqli_real_escape_string($conn, $batch) . "'";
$results_query .= " ORDER BY r.student_id";

$results_result = mysqli_query($conn, $results_query);

// Group by student
$student_data = [];
while ($row = mysqli_fetch_assoc($results_result)) {
    $sid = $row['student_id'];
    $student_data[$sid]['name'] = $row['full_name'];
    $student_data[$sid]['grades'][$row['subject_id']] = $row['grade'];
}

// GPA Calculation
function getGpaPoint($grade) {
    switch (strtoupper($grade)) {
        case 'A+': return 4.0;
        case 'A': return 3.7;
        case 'A-': return 3.5;
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

function calculateGpa($grades) {
    $total = 0; $count = 0;
    foreach ($grades as $g) {
        $point = getGpaPoint($g);
        if ($point !== null) {
            $total += $point;
            $count++;
        }
    }
    return $count > 0 ? round($total / $count, 2) : 'N/A';
}

// Create PDF
$grades = [];
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Header
$pdf->Cell(30, 10, 'Student ID', 1, 0, 'C');
foreach ($subject_ids as $subid) {
    $pdf->Cell(25, 10, $subid, 1, 0, 'C');
}
$pdf->Cell(20, 10, 'GPA', 1, 1, 'C');

// Rows
$pdf->SetFont('Arial', '', 11);
foreach ($student_data as $sid => $data) {
    $pdf->Cell(30, 10, $sid, 1);
    $grades = [];
    foreach ($subject_ids as $subid) {
        $grade = $data['grades'][$subid] ?? '-';
        $grades[] = $grade;
        $pdf->Cell(25, 10, $grade, 1);
    }
    $gpa = calculateGpa($grades);
    $pdf->Cell(20, 10, $gpa, 1);
    $pdf->Ln();
}

// Download PDF
$pdf->Output('D', 'view_results_summary.pdf');
ob_end_flush();
exit;
?>
