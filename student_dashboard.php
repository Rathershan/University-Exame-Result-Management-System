<?php
session_start();
date_default_timezone_set("Asia/Colombo");
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: student_login.php");
    exit();
}

// Check session inactivity (15 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) {
    session_unset();
    session_destroy();
    header("Location: student_login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

include("db_connect.php");

// Get student details using prepared statement
$student_id = $_SESSION['student_id'];
$student_name = "";
$batch = "";
$degree_program = "";

$stmt = $conn->prepare("SELECT full_name, batch, degree_program FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $student_name = htmlspecialchars($row['full_name']);
    $batch = htmlspecialchars($row['batch']);
    $degree_program = htmlspecialchars($row['degree_program']);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - GWUIM</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, var(--secondary-color), #34495e);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .welcome-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 3rem;
            border-left: 5px solid var(--primary-color);
        }
        
        .welcome-title {
            color: var(--secondary-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .student-info {
            background: var(--light-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 0.8rem;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border: none;
            height: 100%;
            text-align: center;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .card-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .btn-logout {
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .last-login {
            font-size: 0.9rem;
            color: #6c757d;
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .welcome-container {
                padding: 1.5rem;
            }
            
            .dashboard-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Dashboard</h3>
                    
                </div>
                <div class="d-flex align-items-center">
                    <div class="last-login me-3 d-none d-md-block">
                        Last active: <?php echo date('M j, Y g:i a', $_SESSION['last_activity']); ?>
                    </div>
                    <a href="student_logout.php" class="btn btn-outline-light btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container my-4">
        <!-- Welcome Section -->
        <div class="welcome-container animate__animated animate__fadeIn">
            <h2 class="welcome-title"><i class="fas fa-user me-2"></i>Welcome, <?php echo $student_name; ?></h2>
            
            <div class="student-info">
                <div class="row">
                    <div class="col-md-4 info-item">
                        <div class="info-label">Student ID</div>
                        <div><?php echo $student_id; ?></div>
                    </div>
                    <div class="col-md-4 info-item">
                        <div class="info-label">Degree Program</div>
                        <div><?php echo $degree_program; ?></div>
                    </div>
                    <div class="col-md-4 info-item">
                        <div class="info-label">Batch</div>
                        <div><?php echo $batch; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Welcome to your student dashboard. Here you can access your results and announcements.
            </div>
        </div>
        
        <!-- Dashboard Cards -->
        <div class="row">
            <div class="col-md-6">
                <a href="view_result_student.php" class="text-decoration-none">
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-poll"></i>
                        </div>
                        <h3 class="card-title">View My Results</h3>
                        <p>Check your examination results and academic progress</p>
                        <div class="btn btn-outline-primary mt-2">Access Results</div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6">
                <a href="view_message_student.php" class="text-decoration-none">
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="card-title">Announcements</h3>
                        <p>View important messages and announcements from the university</p>
                        <div class="btn btn-outline-primary mt-2">View Messages</div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Additional Cards (if needed) -->
         <!--
        <div class="row mt-3">
            <div class="col-md-6">
                <a href="" class="text-decoration-none">
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3 class="card-title">Exam Timetable</h3>
                        <p>View your upcoming examination schedule</p>
                        <div class="btn btn-outline-primary mt-2">View Timetable</div>
                    </div>
                </a>
            </div>
   
            
            <div class="col-md-6">
                <a href="https://lms.gwu.ac.lk/login/index.php" class="text-decoration-none">
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="card-title">Study Resources</h3>
                        <p>Access learning materials and past papers</p>
                        <div class="btn btn-outline-primary mt-2">View Resources</div>
                    </div>
                </a>
            </div>
        </div>
    </div> -->

    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> GWUIM - Examination Management System</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation on card hover
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.05)';
            });
        });
    </script>
</body>
</html>