<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$successMessage = "";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        include("db_connect.php");

       $header = array_map(function($value) {
       return $value === null ? '' : trim($value);
       }, $rows[0]);
        $fieldMap = [];

        $requiredFields = ['student_id', 'full_name', 'department', 'Degree_Program', 'batch', 'Academic_Year', 'semester'];

        // Map headers to their column index
        foreach ($header as $index => $fieldName) {
            if (in_array($fieldName, $requiredFields)) {
                $fieldMap[$fieldName] = $index;
            }
        }

        // Check if all required fields are present
        foreach ($requiredFields as $field) {
            if (!isset($fieldMap[$field])) {
                $errorMessages[] = "❌ Missing column in Excel: $field";
            }
        }

        if (count($errorMessages) === 0) {
            $inserted = 0;
            $skipped = 0;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $missingFields = [];

                // Read values by mapped index
                $student_id = trim($row[$fieldMap['student_id']] ?? '');
                $full_name = trim($row[$fieldMap['full_name']] ?? '');
                $department = trim($row[$fieldMap['department']] ?? '');
                $Degree_Program = trim($row[$fieldMap['Degree_Program']] ?? '');
                $batch = trim($row[$fieldMap['batch']] ?? '');
                $year = trim($row[$fieldMap['Academic_Year']] ?? '');
                $semester = trim($row[$fieldMap['semester']] ?? '');

                // Validate missing fields
                if (!$student_id) $missingFields[] = "student_id";
                if (!$full_name) $missingFields[] = "full_name";
                if (!$department) $missingFields[] = "department";
                if (!$Degree_Program) $missingFields[] = "Degree_Program";
                if (!$batch) $missingFields[] = "batch";
                if (!$year) $missingFields[] = "Academic_Year";
                if (!$semester) $missingFields[] = "semester";

                if (!empty($missingFields)) {
                    $skipped++;
                    $errorMessages[] = "Row " . ($i + 1) . ": Missing data in field(s): " . implode(", ", $missingFields);
                    continue;
                }

                // Check for duplicate based on student_id + year + semester
                $check = $conn->prepare("SELECT 1 FROM students WHERE student_id = ? AND Academic_Year = ? AND semester = ?");
                $check->bind_param("sss", $student_id, $Academic_Year, $semester);
                $check->execute();
                $result = $check->get_result();

                if ($result->num_rows === 0) {
                    $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, department, Degree_Program, batch, Academic_Year, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $student_id, $full_name, $department, $Degree_Program, $batch, $year, $semester);
                    $stmt->execute();
                    $stmt->close();
                    $inserted++;
                } else {
                    $skipped++;
                    $errorMessages[] = "Row " . ($i + 1) . ": Duplicate entry for student_id '$student_id' with Academic_Year '$Academic_Year' and semester '$semester'";
                }

                $check->close();
            }

            $successMessage = "✅ Excel upload completed. Inserted: $inserted, Skipped: $skipped.";
        }

    } catch (Exception $e) {
        $errorMessages[] = "❌ Failed to process file: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Student Details</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<header class="bg-dark text-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h4 fw-bolder">GWUIM - Admin Panel</h1>
        <nav class="d-flex gap-3">
            <a href="dashboard.php" class="text-white text-decoration-none">Dashboard</a>
            <a href="logout.php" class="text-white text-decoration-none">Logout</a>
        </nav>
    </div>
</header>

<div class="container mt-5">
    <h4 class="mb-4">📁 Upload Excel File</h4>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php foreach ($errorMessages as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <div class="alert alert-info">
        Please upload an Excel file with the following column headers (order doesn't matter):<br>
        <strong>student_id, full_name, department, Degree_Program, batch, Academic_Year, semester</strong>
    </div>

    <form method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label for="file" class="form-label fw-bold">Select Excel File:</label>
            <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required />
        </div>
        <button type="submit" class="btn btn-primary">📤 Upload</button>
        <a href="add_student.php" class="btn btn-secondary">⬅ Back to Add Student</a>
    </form>
</div>
</body>
</html>
