<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

// Security: Regenerate session ID periodically
if (!isset($_SESSION['created']) || (time() - $_SESSION['created'] > 600)) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Initialize variables
$success = "";
$error = "";
$filters = [
    'department' => '',
    'degree_program' => '',
    'batch' => '',
    'semester' => ''
];

// Get filter options using prepared statements
function getFilterOptions($conn, $table, $column) {
    $options = [];
    $stmt = $conn->prepare("SELECT DISTINCT $column FROM $table ORDER BY $column");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    $stmt->close();
    return $options;
}

$departments = getFilterOptions($conn, 'students', 'department');
$degree_programs = getFilterOptions($conn, 'students', 'degree_program');
$batches = getFilterOptions($conn, 'students', 'batch');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    foreach ($filters as $key => $value) {
        $filters[$key] = $conn->real_escape_string($_POST[$key] ?? '');
    }
    
    $student_id = $conn->real_escape_string($_POST['student_id'] ?? '');
    $marks = $_POST['marks'] ?? [];

    if (isset($_POST['filter'])) {
        // Just applying filters - no action needed
    } elseif (isset($_POST['submit_results'])) {
        if ($student_id && !empty($marks)) {
            $conn->begin_transaction();
            
            try {
                // Check if results already exist
                $check = $conn->prepare("SELECT 1 FROM results WHERE student_id = ?");
                $check->bind_param("s", $student_id);
                $check->execute();
                $result = $check->get_result();
                
                if ($result->num_rows > 0) {
                    throw new Exception("Results already exist for this student. Please use the edit option.");
                }
                
                // Process each subject mark
                foreach ($marks as $subject_id => $mark) {
                    $subject_id = $conn->real_escape_string($subject_id);
                    $mark = strtoupper(trim($mark));
                    
                    // Validate mark
                    if ($mark === "AB" || $mark === "WH" || $mark === "NS") {
                        $marks_value = $mark;
                        $grade = $mark;
                    } elseif (is_numeric($mark) && $mark >= 0 && $mark <= 100) {
                        $marks_value = (int)$mark;
                        $grade = calculateGrade($marks_value);
                    } else {
                        throw new Exception("Invalid mark format for subject $subject_id");
                    }
                    
                    // Insert result
                    $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, marks, grade) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $student_id, $subject_id, $marks_value, $grade);
                    $stmt->execute();
                    $stmt->close();
                }
                
                $conn->commit();
                $success = "Results submitted successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
            
            $check->close();
        } else {
            $error = "Please select a student and enter marks for all subjects";
        }
    }
}

function calculateGrade($marks) {
    if (!is_numeric($marks)) return $marks;
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

// Get students and subjects based on filters
$students = [];
$subjects = [];

if ($filters['department'] && $filters['degree_program'] && $filters['batch'] && $filters['semester']) {
    // Get students
    $stmt = $conn->prepare("SELECT student_id, full_name FROM students WHERE department=? AND degree_program=? AND batch=? AND semester=?");
    $stmt->bind_param("ssss", $filters['department'], $filters['degree_program'], $filters['batch'], $filters['semester']);
    $stmt->execute();
    $students = $stmt->get_result();
    $stmt->close();
    
    // Get subjects
    $stmt = $conn->prepare("SELECT subject_id, subject_name FROM subjects WHERE department=? AND degree_program=? AND batch=? AND semester=?");
    $stmt->bind_param("ssss", $filters['department'], $filters['degree_program'], $filters['batch'], $filters['semester']);
    $stmt->execute();
    $subjects = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Results - GWUIM</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50ff;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .results-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .subject-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .subject-code {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .subject-name {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .status-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            animation: slideIn 0.3s, fadeOut 0.5s 3s forwards;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes fadeOut {
            to { opacity: 0; }
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-submit:hover {
            background-color: #168bf9ff;
            border-color: #0283fbff;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0"><i class="fas fa-poll me-2"></i>Results Entry System</h1>
                <nav class="d-flex gap-3">
                    <a href="dashboard.php" class="text-white text-decoration-none"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    <a href="logout.php" class="text-white text-decoration-none"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if ($success): ?>
        <div class="alert-message">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php elseif ($error): ?>
        <div class="alert-message">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container my-4">
        <!-- Filter Form -->
        <div class="filter-card">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-select" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= htmlspecialchars($d['department']) ?>" 
                                <?= ($filters['department'] == $d['department']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['department']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="degree_program" class="form-label">Degree Program</label>
                    <select id="degree_program" name="degree_program" class="form-select" required>
                        <option value="">-- Select Program --</option>
                        <?php foreach ($degree_programs as $dp): ?>
                            <option value="<?= htmlspecialchars($dp['degree_program']) ?>" 
                                <?= ($filters['degree_program'] == $dp['degree_program']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dp['degree_program']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="batch" class="form-label">Academic Year</label>
                    <select id="batch" name="batch" class="form-select" required>
                        <option value="">-- Academic Year --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= htmlspecialchars($b['batch']) ?>" 
                                <?= ($filters['batch'] == $b['batch']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['batch']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select id="semester" name="semester" class="form-select" required>
                        <option value="">-- Select Semester --</option>
                        <option value="Semester I" <?= ($filters['semester'] == 'Semester I') ? 'selected' : '' ?>>Semester I</option>
                        <option value="Semester II" <?= ($filters['semester'] == 'Semester II') ? 'selected' : '' ?>>Semester II</option>
                    </select>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" name="filter" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Entry Form -->
        <?php if ($students && $students->num_rows > 0 && $subjects && $subjects->num_rows > 0): ?>
            <div class="results-card">
                <form method="POST">
                    <input type="hidden" name="department" value="<?= htmlspecialchars($filters['department']) ?>">
                    <input type="hidden" name="degree_program" value="<?= htmlspecialchars($filters['degree_program']) ?>">
                    <input type="hidden" name="batch" value="<?= htmlspecialchars($filters['batch']) ?>">
                    <input type="hidden" name="semester" value="<?= htmlspecialchars($filters['semester']) ?>">

                    <div class="mb-4">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select id="student_id" name="student_id" class="form-select" required>
                            <option value="">-- Select Student --</option>
                            <?php while ($s = $students->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($s['student_id']) ?>">
                                    <?= htmlspecialchars($s['student_id']) ?> - <?= htmlspecialchars($s['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <h5 class="mb-4"><i class="fas fa-book-open me-2"></i>Enter Marks or Status:</h5>
                    
                    <div class="row">
                        <?php while ($sub = $subjects->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="subject-label">
                                    <span class="subject-code"><?= htmlspecialchars($sub['subject_id']) ?></span> - 
                                    <span class="subject-name"><?= htmlspecialchars($sub['subject_name']) ?></span>
                                </div>
                                <input type="text" name="marks[<?= htmlspecialchars($sub['subject_id']) ?>]" 
                                       class="form-control" 
                                       placeholder="Enter marks (0-100) or AB/WH/NR" 
                                       required
                                       pattern="^(100|\d{1,2}|AB|WH|NR)$"
                                       title="Enter a number (0-100) or AB/WH/NR">
                                <div class="status-info">
                                    AB=Absent, WH=Withheld, NS=Not Registered
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" name="submit_results" class="btn btn-submit btn-lg">
                            <i class="fas fa-save me-2"></i> Submit Results
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($filters['department'] && $filters['degree_program'] && $filters['batch'] && $filters['semester']): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                <?php if ($students && $students->num_rows === 0): ?>
                    No students found for the selected criteria
                <?php else: ?>
                    No subjects found for the selected criteria
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Close alert when close button is clicked
        document.querySelectorAll('.alert .btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert-message').remove();
            });
        });
        
        // Input validation for marks
        document.querySelectorAll('input[name^="marks"]').forEach(input => {
            input.addEventListener('input', function() {
                const value = this.value.toUpperCase();
                if (value === 'AB' || value === 'WH' || value === 'NS') {
                    this.value = value;
                }
            });
        });
    </script>
</body>
</html>