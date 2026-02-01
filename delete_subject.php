<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

// Get subject ID from URL
if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    // Delete query
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("s", $subject_id);

    if ($stmt->execute()) {
        header("Location: subject_overview.php?msg=deleted");
        exit();
    } else {
        echo "❌ Error deleting record.";
    }
    $stmt->close();
} else {
    echo "❗ No subject_id specified.";
}
?>
