<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - GWUIM</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f7fa;
        }

        .page-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .notification-card {
            background: #fff;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
            transition: 0.3s;
        }

        .notification-card:hover {
            transform: translateY(-3px);
        }

        .notification-icon {
            font-size: 1.8rem;
            margin-right: 1rem;
        }

        .unread {
            border-left: 5px solid #0d6efd;
            background-color: #f0f6ff;
        }

        .time {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">🔔 Notifications</h4>
            <small>Welcome, <?= htmlspecialchars($admin_name) ?></small>
        </div>
        <a href="dashboard.php" class="btn btn-light btn-sm">
            <i class="lni lni-arrow-left"></i> Back
        </a>
    </div>

    <!-- Notifications List -->
    <div class="mx-auto" style="max-width:1000px;">

        <!-- Notification 1 -->
        <div class="notification-card unread d-flex align-items-start">
            <i class="lni lni-alarm notification-icon text-primary"></i>
            <div>
                <h6 class="mb-1">New Results Uploaded</h6>
                <p class="mb-1">Semester 2 results have been successfully published.</p>
                <span class="time">10 minutes ago</span>
            </div>
        </div>

        <!-- Notification 2 -->
        <div class="notification-card d-flex align-items-start">
            <i class="lni lni-user notification-icon text-success"></i>
            <div>
                <h6 class="mb-1">New Student Added</h6>
                <p class="mb-1">A new student was added to the Computer Science department.</p>
                <span class="time">Today, 9:15 AM</span>
            </div>
        </div>

        <!-- Notification 3 -->
        <div class="notification-card d-flex align-items-start">
            <i class="lni lni-warning notification-icon text-warning"></i>
            <div>
                <h6 class="mb-1">Pending Result Approval</h6>
                <p class="mb-1">Some results are waiting for admin verification.</p>
                <span class="time">Yesterday</span>
            </div>
        </div>

        <!-- Empty State (optional) -->
        <!--
        <div class="text-center text-muted mt-5">
            <i class="lni lni-alarm" style="font-size:3rem;"></i>
            <p class="mt-2">No notifications available</p>
        </div>
        -->

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
