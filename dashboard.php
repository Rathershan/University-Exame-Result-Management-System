<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get admin name for welcome message
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - GWUIM</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-btn {
            padding: 1.5rem;
            font-size: 1.1rem;
            height: 120px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: none;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .card-btn i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .card-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
            text-decoration: none;
        }

        .card-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.1), rgba(255,255,255,0));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card-btn:hover::after {
            opacity: 1;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
        }

        .btn-success {
            background-color: var(--success-color);
        }

        .btn-warning {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background-color: white;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background-color: white;
        }

        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-outline-success {
            border: 2px solid var(--success-color);
            color: var(--success-color);
            background-color: white;
        }

        .btn-outline-success:hover {
            background-color: var(--success-color);
            color: white;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
            color: var(--dark-color);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eee;
        }

        .logout-btn {
            transition: all 0.3s;
        }

        .logout-btn:hover {
            color: #ff6b6b !important;
        }

        .welcome-text {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="dashboard-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h4 mb-1 fw-bold">GWUIM - Exam Management System</h1>
            <p class="welcome-text mb-0">Welcome back, <?= htmlspecialchars($admin_name) ?>!</p>
        </div>
        <nav>
            <a href="logout.php" class="text-white text-decoration-none logout-btn">
                <i class="lni lni-exit"></i> Logout
            </a>
        </nav>
    </div>
</header>

<!-- Main Content -->
<div class="container dashboard-container">
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <!-- Management Section -->
            <h3 class="section-title">Student Management</h3>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a href="add_student.php" class="btn btn-primary card-btn">
                        <i class="lni lni-user-plus"></i>
                        Add Student
                    </a>
                </div>
                <div class="col">
                    <a href="student_overview.php" class="btn btn-outline-primary card-btn">
                        <i class="lni lni-users"></i>
                        View Students
                    </a>
                </div>
                <div class="col">
                    <a href="upload_students.php" class="btn btn-info card-btn">
                        <i class="lni lni-upload"></i>
                        Bulk Upload
                    </a>
                </div>
            </div>

            <!-- Subject Management -->
            <h3 class="section-title">Subject Management</h3>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a href="add_subject.php" class="btn btn-secondary card-btn">
                        <i class="lni lni-book"></i>
                        Add Subject
                    </a>
                </div>
                <div class="col">
                    <a href="subject_overview.php" class="btn btn-outline-secondary card-btn">
                        <i class="lni lni-library"></i>
                        View Subjects
                    </a>
                </div>
               
            </div>

            <!-- Results Management -->
            <h3 class="section-title">Results Management</h3>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a href="result_entry.php" class="btn btn-success card-btn">
                        <i class="lni lni-pencil-alt"></i>
                        Add Results
                    </a>
                </div>
                <div class="col">
                    <a href="view_results_page.php" class="btn btn-outline-success card-btn">
                        <i class="lni lni-bar-chart"></i>
                        View Results
                    </a>
                </div>
               
            </div>

            <!-- Communication Section -->
            <h3 class="section-title">Communication</h3>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <div class="col">
                    <a href="admin_file_upload.php" class="btn btn-warning card-btn">
                        <i class="lni lni-file"></i>
                        Send Messages/Files
                    </a>
                </div>
                <div class="col">
                    <a href="" class="btn btn-outline-warning card-btn">
                        <i class="lni lni-alarm"></i>
                        Notifications
                    </a>
                </div>
            </div>

            
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add active class to clicked card temporarily
    document.querySelectorAll('.card-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.add('active');
            setTimeout(() => {
                this.classList.remove('active');
            }, 300);
        });
    });
</script>
</body>
</html>