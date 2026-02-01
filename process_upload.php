<?php
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"]["tmp_name"];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $rowCount = 0;
        $skipped = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) !== 6) {
                $skipped++;
                continue;
            }

            list($student_id, $full_name, $department, $batch, $year, $semester) = $data;

            // Check if student ID already exists
            $check = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $check->bind_param("s", $student_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, department, batch, year, semester) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $student_id, $full_name, $department, $batch, $year, $semester);
                $stmt->execute();
                $stmt->close();
                $rowCount++;
            } else {
                $skipped++;
            }
            $check->close();
        }

        fclose($handle);
        $_SESSION['upload_result'] = "✅ $rowCount records added successfully. ❌ $skipped duplicates skipped.";
    } else {
        $_SESSION['upload_result'] = "❌ Unable to read the file.";
    }
}

header("Location: upload_students.php");
exit();
?>
