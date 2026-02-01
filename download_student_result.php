<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    die("Access Denied");
}
require('fpdf/fpdf.php');
include("db_connect.php");

$student_id = $_SESSION['student_id'];

// Get Student Info
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$student_id'"));

// Get Published Results
$query = "SELECT s.subject_id, s.subject_name, r.grade 
          FROM results r 
          JOIN subjects s ON s.subject_id = r.subject_id 
          WHERE r.student_id = '$student_id' AND r.published = 1";
$result = mysqli_query($conn, $query);

// GPA Calculation
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

$grades = [];
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Header
$pdf->Cell(0, 10, 'Student Result - GWUIM', 0, 1, 'C');
$pdf->Ln(5);

// Student Info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Student ID: " . $student['student_id'], 0, 1);
$pdf->Cell(0, 10, "Name: " . $student['full_name'], 0, 1);
$pdf->Cell(0, 10, "Department: " . $student['department'], 0, 1);
$pdf->Cell(0, 10, "Degree Program: " . $student['degree_program'], 0, 1);
$pdf->Cell(0, 10, "Batch: " . $student['batch'], 0, 1);
$pdf->Cell(0, 10, "Academic Year: " . $student['Academic_Year'], 0, 1);
$pdf->Cell(0, 10, "Semester: " . $student['semester'], 0, 1);
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Subject ID', 1);
$pdf->Cell(90, 10, 'Subject Name', 1);
$pdf->Cell(30, 10, 'Grade', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(40, 10, $row['subject_id'], 1);
    $pdf->Cell(90, 10, $row['subject_name'], 1);
    $pdf->Cell(30, 10, $row['grade'], 1);
    $pdf->Ln();
    $grades[] = $row['grade'];
}

$gpa = calculateGpa($grades);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'GPA', 1);
$pdf->Cell(30, 10, $gpa, 1);

$pdf->Output('D', 'My_Result.pdf');
exit;
?>
