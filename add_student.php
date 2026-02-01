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

// Initialize variables
$success = "";
$errors = [];
$formData = [
    'department' => '',
    'degree_program' => '',
    'batch' => '',
    'Academic_Year' => '',
    'semester' => '',
    'student_id' => '',
    'full_name' => ''
];

// Department options
$departments = [
    "Department of Technology",
    "Department of IMR",
    "Department of IHS"
];

// Degree program options
$degreePrograms = [
    "BHSc in HICT",
    "BHSc in BHBT",
    "BSc in HTHM",
    "BSc in BSYP",
    "BHSc in BIPT",
    "BSc in BSMR"
];

// Academic Year options
$academicYears = ["2020/2021", "2021/2022", "2022/2023", "2023/2024","2024/2025","2025/2026"];

// Semester options
$semesters = ["Semester I", "Semester II"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $formData['department'] = htmlspecialchars(trim($_POST['department'] ?? ''));
    $formData['degree_program'] = htmlspecialchars(trim($_POST['degree_program'] ?? ''));
    $formData['batch'] = htmlspecialchars(trim($_POST['batch'] ?? ''));
    $formData['Academic_Year'] = htmlspecialchars(trim($_POST['Academic_Year'] ?? ''));
    $formData['semester'] = htmlspecialchars(trim($_POST['semester'] ?? ''));
    $formData['student_id'] = strtoupper(trim($_POST['student_id'] ?? ''));
    $formData['full_name'] = htmlspecialchars(trim($_POST['student_name'] ?? ''));

    // Validate inputs
    if (empty($formData['department'])) $errors['department'] = "Department is required";
    if (empty($formData['degree_program'])) $errors['degree_program'] = "Degree program is required";
    if (empty($formData['batch'])) $errors['batch'] = "Batch is required";
    if (empty($formData['Academic_Year'])) $errors['Academic_Year'] = "Academic Year is required";
    if (empty($formData['semester'])) $errors['semester'] = "Semester is required";
    if (empty($formData['student_id'])) $errors['student_id'] = "Student ID is required";
    if (empty($formData['full_name'])) $errors['full_name'] = "Student name is required";

    // Validate student ID format if not empty
    if (!empty($formData['student_id']) && !preg_match('/^GWU-[A-Z]{4}-\d{4}-\d{2}$/', $formData['student_id'])) {
        $errors['student_id'] = "Invalid Student ID format (e.g., GWU-HICT-2023-01)";
    }

    if (empty($errors)) {
        // Check for duplicate student (case-insensitive student_id)
        $stmt = $conn->prepare("SELECT 1 FROM students WHERE LOWER(student_id) = LOWER(?) AND Academic_Year = ? AND semester = ?");
        $stmt->bind_param("sss", $formData['student_id'], $formData['Academic_Year'], $formData['semester']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors['duplicate'] = "This student is already registered for {$formData['Academic_Year']} - {$formData['semester']}!";
        } else {
            // Insert new student
            $insertStmt = $conn->prepare("INSERT INTO students (student_id, full_name, department, degree_program, batch, Academic_Year, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssssss", 
                $formData['student_id'], 
                $formData['full_name'], 
                $formData['department'], 
                $formData['degree_program'], 
                $formData['batch'], 
                $formData['Academic_Year'], 
                $formData['semester']
            );

            if ($insertStmt->execute()) {
                $success = "Student added successfully!";
                // Clear form on success
                $formData = array_map(function() { return ''; }, $formData);
            } else {
                $errors['database'] = "Failed to add student: " . $insertStmt->error;
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Student - GWUIM</title>
    <link rel="icon" type="image/png" href="logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .is-invalid {
            border-color: var(--accent-color);
        }
        
        .invalid-feedback {
            color: var(--accent-color);
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1a252f;
            border-color: #1a252f;
        }
        
        .form-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">GWUIM - Admin Panel</h1>
                <nav class="d-flex gap-3">
                    <a href="dashboard.php" class="text-white text-decoration-none"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    <a href="logout.php" class="text-white text-decoration-none"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold form-title"><i class="fas fa-user-plus me-2"></i>Exam Registaion</h3>
            <a href="upload_students.php" class="btn btn-success">
                <i class="fas fa-file-upload me-1"></i> File Upload(.xlsx)
            </a>
        </div>

        <div class="form-container">
            <form method="POST" id="studentForm" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="department">Department</label>
                        <select id="department" name="department" class="form-select <?= !empty($errors['department']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>" <?= $formData['department'] == $dept ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['department'])): ?>
                            <div class="invalid-feedback"><?= $errors['department'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="degree_program">Degree Program</label>
                        <select id="degree_program" name="degree_program" class="form-select <?= !empty($errors['degree_program']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Select Degree Program --</option>
                            <?php foreach ($degreePrograms as $program): ?>
                                <option value="<?= htmlspecialchars($program) ?>" <?= $formData['degree_program'] == $program ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($program) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['degree_program'])): ?>
                            <div class="invalid-feedback"><?= $errors['degree_program'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="batch">Batch</label>
                        <input type="text" id="batch" name="batch" class="form-control <?= !empty($errors['batch']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g., 2022/2023" value="<?= htmlspecialchars($formData['batch']) ?>" required>
                        <?php if (!empty($errors['batch'])): ?>
                            <div class="invalid-feedback"><?= $errors['batch'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="Academic_Year">Academic Year</label>
                        <select id="Academic_Year" name="Academic_Year" class="form-select <?= !empty($errors['Academic_Year']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Select Academic Year --</option>
                            <?php foreach ($academicYears as $yr): ?>
                                <option value="<?= htmlspecialchars($yr) ?>" <?= $formData['Academic_Year'] == $yr ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($yr) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['Academic_Year'])): ?>
                            <div class="invalid-feedback"><?= $errors['Academic_Year'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="semester">Semester</label>
                        <select id="semester" name="semester" class="form-select <?= !empty($errors['semester']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Select Semester --</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= htmlspecialchars($sem) ?>" <?= $formData['semester'] == $sem ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sem) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['semester'])): ?>
                            <div class="invalid-feedback"><?= $errors['semester'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control <?= !empty($errors['student_id']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g., GWU-HICT-2023-01" value="<?= htmlspecialchars($formData['student_id']) ?>" 
                           pattern="^GWU-[A-Z]{4}-\d{4}-\d{2}$" required>
                    <?php if (!empty($errors['student_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['student_id'] ?></div>
                    <?php endif; ?>
                    <div class="info-text mt-1">Format: GWU-ABCD-YYYY-NN (e.g., GWU-HICT-2023-01)</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="student_name">Student Name</label>
                    <input type="text" id="student_name" name="student_name" class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="Enter student's full name" value="<?= htmlspecialchars($formData['full_name']) ?>" required>
                    <?php if (!empty($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i> Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show success/error messages using SweetAlert2
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= addslashes($success) ?>',
                timer: 3000,
                showConfirmButton: false
            });
        <?php elseif (!empty($errors['duplicate']) || !empty($errors['database'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= !empty($errors['duplicate']) ? addslashes($errors['duplicate']) : addslashes($errors['database']) ?>'
            });
        <?php endif; ?>

        // Client-side validation for student ID format
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const studentId = document.querySelector('input[name="student_id"]');
            const pattern = /^GWU-[A-Z]{4}-\d{4}-\d{2}$/;
            
            if (!pattern.test(studentId.value)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Student ID',
                    text: 'Please use the format: GWU-ABCD-YYYY-NN (e.g., GWU-HICT-2023-01)'
                });
                studentId.focus();
            }
        });
    </script>
</body>
</html>
