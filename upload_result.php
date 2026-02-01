<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
include("db_connect.php");

$success = "";
$error = "";

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

$departments = $conn->query("SELECT DISTINCT department FROM students");
$degree_programs = $conn->query("SELECT DISTINCT degree_program FROM students");
$batches = $conn->query("SELECT DISTINCT batch FROM students");

if (isset($_POST['upload_excel']) && isset($_FILES['marks_file']['tmp_name'])) {
    $department = $_POST['department'] ?? '';
    $degree_program = $_POST['degree_program'] ?? '';
    $batch = $_POST['batch'] ?? '';

    if (!$department || !$degree_program || !$batch) {
        $error = "Please select Department, Degree Program, and Batch before uploading.";
    } else {
        $filePath = $_FILES['marks_file']['tmp_name'];
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $header = array_map('strtolower', array_map('trim', $rows[0]));
            $colCount = count($header);
            $rowsInserted = 0;

            if ($colCount == 3 && $header[0] === 'student_id') {
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $student_id = trim($row[0]);
                    $subject_id = trim($row[1]);
                    $mark = (int)trim($row[2]);

                    $checkStudent = $conn->prepare("SELECT 1 FROM students WHERE student_id = ? AND department = ? AND degree_program = ? AND batch = ?");
                    $checkStudent->bind_param("ssss", $student_id, $department, $degree_program, $batch);
                    $checkStudent->execute();
                    $checkStudent->store_result();
                    if ($checkStudent->num_rows === 0) {
                        $checkStudent->close();
                        continue;
                    }
                    $checkStudent->close();

                    $grade = calculateGrade($mark);

                    $check = $conn->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ?");
                    $check->bind_param("ss", $student_id, $subject_id);
                    $check->execute();
                    $check->store_result();

                    if ($check->num_rows > 0) {
                        $update = $conn->prepare("UPDATE results SET marks = ?, grade = ?, published = 0 WHERE student_id = ? AND subject_id = ?");
                        $update->bind_param("isss", $mark, $grade, $student_id, $subject_id);
                        $update->execute();
                        $update->close();
                    } else {
                        $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, marks, grade, published) VALUES (?, ?, ?, ?, 0)");
                        $stmt->bind_param("ssis", $student_id, $subject_id, $mark, $grade);
                        $stmt->execute();
                        $stmt->close();
                    }
                    $check->close();
                    $rowsInserted++;
                }

            } elseif ($colCount > 2 && $header[0] === 'student_id') {
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $student_id = trim($row[0]);

                    $checkStudent = $conn->prepare("SELECT 1 FROM students WHERE student_id = ? AND department = ? AND degree_program = ? AND batch = ?");
                    $checkStudent->bind_param("ssss", $student_id, $department, $degree_program, $batch);
                    $checkStudent->execute();
                    $checkStudent->store_result();
                    if ($checkStudent->num_rows === 0) {
                        $checkStudent->close();
                        continue;
                    }
                    $checkStudent->close();

                    for ($j = 1; $j < $colCount; $j++) {
                        $subject_id = trim($rows[0][$j]);
                        if (!isset($row[$j]) || $row[$j] === '') continue;
                        $mark = (int)trim($row[$j]);
                        $grade = calculateGrade($mark);

                        $check = $conn->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ?");
                        $check->bind_param("ss", $student_id, $subject_id);
                        $check->execute();
                        $check->store_result();

                        if ($check->num_rows > 0) {
                            $update = $conn->prepare("UPDATE results SET marks = ?, grade = ?, published = 0 WHERE student_id = ? AND subject_id = ?");
                            $update->bind_param("isss", $mark, $grade, $student_id, $subject_id);
                            $update->execute();
                            $update->close();
                        } else {
                            $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, marks, grade, published) VALUES (?, ?, ?, ?, 0)");
                            $stmt->bind_param("ssis", $student_id, $subject_id, $mark, $grade);
                            $stmt->execute();
                            $stmt->close();
                        }
                        $check->close();
                        $rowsInserted++;
                    }
                }

            } else {
                $error = "❌ Invalid Excel format. Use 'student_id,subject_id,marks' OR 'student_id,SUB101,SUB102,...'";
            }

            if (!$error) $success = "✅ Successfully processed $rowsInserted entries!";
        } catch (Exception $e) {
            $error = "❌ Error reading Excel file: " . $e->getMessage();
        }
    }
}
?>
