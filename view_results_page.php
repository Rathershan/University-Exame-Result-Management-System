<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

// Security: Regenerate session ID periodically
if (!isset($_SESSION['created']) || (time() - $_SESSION['created'] > 1800)) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Grade to GPA conversion function
function getGpaPoint($grade) {
    $grade_map = [
        'A+' => 4.00,
        'A'  => 3.7,
        'A-' => 3.5,
        'B+' => 3.3,
        'B'  => 3.00,
        'B-' => 2.7,
        'C+' => 2.3,
        'C'  => 2.00,
        'C-' => 1.7,
        'D+' => 1.3,
        'D'  => 1.00,
        'F'  => 0.0
    ];
    return $grade_map[strtoupper($grade)] ?? null;
}

function calculateGpa($grades) {
    $total_points = 0;
    $count = 0;
    foreach ($grades as $grade) {
        $point = getGpaPoint($grade);
        if ($point !== null) {
            $total_points += $point;
            $count++;
        }
    }
    return $count === 0 ? '-' : number_format($total_points / $count, 2);
}

// Fetch dropdown values using prepared statements
function fetchDropdownValues($conn, $table, $column) {
    $values = [];
    $stmt = $conn->prepare("SELECT DISTINCT $column FROM $table ORDER BY $column");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $values[] = $row;
    }
    $stmt->close();
    return $values;
}

$departments = fetchDropdownValues($conn, 'students', 'department');
$degree_programs = fetchDropdownValues($conn, 'students', 'degree_program');
$batches = fetchDropdownValues($conn, 'students', 'batch');

// Filter inputs with proper sanitization
$filter_keys = ['department', 'degree_program', 'batch', 'semester'];
$filters = [];
foreach ($filter_keys as $key) {
    $filters[$key] = $_POST[$key] ?? $_GET[$key] ?? '';
    $$key = $conn->real_escape_string($filters[$key]);
}

// Publish results handler
if (isset($_POST['publish_results'])) {
    // Verify results exist before publishing
    $check_sql = "SELECT COUNT(*) as count FROM results r
                 JOIN students s ON r.student_id = s.student_id
                 WHERE s.department = ? AND s.degree_program = ? 
                 AND s.batch = ? AND s.semester = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ssss", $department, $degree_program, $batch, $semester);
    $stmt->execute();
    $check_result = $stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update results
            $update_sql = "UPDATE results r
                          JOIN students s ON r.student_id = s.student_id
                          SET r.published = 1, r.published_date = NOW()
                          WHERE s.department = ? AND s.degree_program = ? 
                          AND s.batch = ? AND s.semester = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssss", $department, $degree_program, $batch, $semester);
            $stmt->execute();
            
            // Update results status
            $status_sql = "INSERT INTO results_status 
                          (department, degree_program, batch, semester, published_date, status)
                          VALUES (?, ?, ?, ?, NOW(), 'published')
                          ON DUPLICATE KEY UPDATE published_date = NOW(), status = 'published'";
            $stmt = $conn->prepare($status_sql);
            $stmt->bind_param("ssss", $department, $degree_program, $batch, $semester);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success_message'] = "Results published successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error publishing results: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "No results found for the selected criteria";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch subjects with prepared statement
$subjects_array = [];
if ($department && $degree_program && $batch && $semester) {
    $subject_sql = "SELECT subject_id, subject_name FROM subjects
                   WHERE department = ? AND degree_program = ? 
                   AND batch = ? AND semester = ?
                   ORDER BY subject_id ASC";
    $stmt = $conn->prepare($subject_sql);
    $stmt->bind_param("ssss", $department, $degree_program, $batch, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects_array[] = $row;
    }
    $stmt->close();
}

// Fetch results with prepared statement
$student_results = [];
$student_info = [];
if ($department && $degree_program && $batch && $semester) {
    $results_sql = "SELECT r.student_id, s.full_name, r.subject_id, r.grade
                   FROM results r
                   JOIN students s ON r.student_id = s.student_id
                   WHERE s.department = ? AND s.degree_program = ? 
                   AND s.batch = ? AND s.semester = ?
                   ORDER BY r.student_id";
    $stmt = $conn->prepare($results_sql);
    $stmt->bind_param("ssss", $department, $degree_program, $batch, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sid = $row['student_id'];
        $student_results[$sid][$row['subject_id']] = $row['grade'];
        $student_info[$sid] = $row['full_name'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Management - GWUIM</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
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
            overflow-x: auto;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            position: sticky;
            top: 0;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .gpa-cell {
            font-weight: 600;
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .action-btns .btn {
            margin: 0 3px;
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
        
        .subject-name {
            font-size: 0.8rem;
            color: #6c757d;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0"><i class="fas fa-poll me-2"></i>Results Management</h1>
                <nav class="d-flex gap-3">
                    <a href="dashboard.php" class="text-white text-decoration-none"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    <a href="logout.php" class="text-white text-decoration-none"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert-message">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert-message">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
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
                    <label for="batch" class="form-label">Academic Year </label>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button type="button" class="btn btn-info" onclick="checkResultStatus()">
                <i class="fas fa-check-circle me-1"></i> Verify Results
            </button>
            <form method="POST" class="d-inline">
                <input type="hidden" name="department" value="<?= htmlspecialchars($filters['department']) ?>">
                <input type="hidden" name="degree_program" value="<?= htmlspecialchars($filters['degree_program']) ?>">
                <input type="hidden" name="batch" value="<?= htmlspecialchars($filters['batch']) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars($filters['semester']) ?>">
                <button type="submit" name="publish_results" class="btn btn-warning" id="publishBtn" disabled>
                    <i class="fas fa-bullhorn me-1"></i> Publish Results
                </button>
            </form>
        </div>

        <!-- Results Table -->
        <div class="results-card">
            <?php if (empty($subjects_array)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> Please select department, program, batch and semester to view results
                </div>
            <?php elseif (empty($student_info)): ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i> No results found for the selected criteria
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <?php foreach ($subjects_array as $sub): ?>
                                    <th>
                                        <?= htmlspecialchars($sub['subject_id']) ?>
                                        <span class="subject-name"><?= htmlspecialchars($sub['subject_name']) ?></span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="gpa-cell">GPA</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($student_info as $sid => $name): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sid) ?></td>
                                    <td><?= htmlspecialchars($name) ?></td>
                                    <?php 
                                    $grades_for_gpa = [];
                                    foreach ($subjects_array as $sub):
                                        $grade = $student_results[$sid][$sub['subject_id']] ?? '-';
                                        echo '<td>' . htmlspecialchars($grade) . '</td>';
                                        if ($grade !== '-' && getGpaPoint($grade) !== null) {
                                            $grades_for_gpa[] = $grade;
                                        }
                                    endforeach;
                                    ?>
                                    <td class="gpa-cell"><?= calculateGpa($grades_for_gpa) ?></td>
                                    <td class="action-btns">
                                        <a href="edit_result.php?student_id=<?= urlencode($sid) ?>&department=<?= urlencode($filters['department']) ?>&program=<?= urlencode($filters['degree_program']) ?>&batch=<?= urlencode($filters['batch']) ?>&semester=<?= urlencode($filters['semester']) ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function checkResultStatus() {
        const department = document.getElementById("department").value;
        const degree_program = document.getElementById("degree_program").value;
        const batch = document.getElementById("batch").value;
        const semester = document.getElementById("semester").value;
        
        if (!department || !degree_program || !batch || !semester) {
            alert("Please select all filter criteria first");
            return;
        }
        
        fetch(`check_result_status.php?department=${encodeURIComponent(department)}&degree_program=${encodeURIComponent(degree_program)}&batch=${encodeURIComponent(batch)}&semester=${encodeURIComponent(semester)}`)
            .then(res => res.json())
            .then(data => {
                const publishBtn = document.getElementById("publishBtn");
                if (data.complete) {
                    alert("✅ All results are complete. You can publish now.");
                    publishBtn.disabled = false;
                } else {
                    alert(`❌ Results are incomplete.\nRequired: ${data.required}, Entered: ${data.actual}`);
                    publishBtn.disabled = true;
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert("Error checking result status");
            });
    }
    
    // Close alert when close button is clicked
    document.querySelectorAll('.alert .btn-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.alert-message').remove();
        });
    });
    </script>
</body>
</html>