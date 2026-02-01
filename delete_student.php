<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        if ($stmt->execute()) {
            header("Location: student_overview.php?msg=deleted");
            exit();
        } else {
            echo "❌ Delete failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "❌ Prepare failed: " . $conn->error;
    }
} else {
    echo "⚠️ Invalid request.";
}
?>
