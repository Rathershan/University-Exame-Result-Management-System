<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
require_once("db_connect.php");

// Initialize variables
$formFields = [
    'subject_id' => '',
    'subject_name' => '',
    'department' => '',
    'degree_program' => '',
    'batch' => '',
    'Academic_Year' => '',
    'semester' => ''
];
$errors = array_fill_keys(array_keys($formFields), '');
$showSuccessAlert = false;
$showErrorAlert = false;
$errorMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    foreach ($formFields as $field => $value) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        } else {
            $formFields[$field] = mysqli_real_escape_string($conn, trim($_POST[$field]));
        }
    }

    if (empty($errors['subject_id'])) {
        $check_sql = "SELECT subject_id FROM subjects WHERE subject_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $formFields['subject_id']);
        $stmt->execute();
        $check_result = $stmt->get_result();
        if($check_result && $check_result->num_rows > 0) {
            $errors['subject_id'] = 'Subject ID already exists';
        }
    }

    if(!array_filter($errors)) {
        $sql = "INSERT INTO subjects(subject_id, subject_name, department, degree_program, batch, Academic_Year, semester) 
                VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", 
            $formFields['subject_id'],
            $formFields['subject_name'],
            $formFields['department'],
            $formFields['degree_program'],
            $formFields['batch'],
            $formFields['Academic_Year'],
            $formFields['semester']
        );

        if($stmt->execute()) {
            $showSuccessAlert = true;
            $formFields = array_map(function() { return ''; }, $formFields);
        } else {
            $showErrorAlert = true;
            $errorMessage = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!-- HTML PART STARTS -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Subject</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error-message { color: #dc3545; font-size: 0.875em; }
        .is-invalid { border-color: #dc3545; }
        .header { background-color: #343a40; color: white; padding: 15px 0; }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
        .btn-warning { background-color: #ffc107; border-color: #ffc107; }
        .required-field::after { content: " *"; color: red; }
    </style>
</head>
<body>
<header class="header py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">GWUIM - Admin Panel</h1>
            <nav class="d-flex gap-3">
                <a href="dashboard.php" class="text-white">Dashboard</a>
                <a href="logout.php" class="text-white">Logout</a>
            </nav>
        </div>
    </div>
</header>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bolder mb-0">ADD NEW SUBJECT</h4>
            </div>
            <div class="form-container">
                <form action="add_subject.php" method="POST" id="subjectForm" autocomplete="off" novalidate>
                    <div class="row g-3">
                        <!-- Subject ID -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required-field">Subject ID</label>
                            <input type="text" name="subject_id" class="form-control <?= !empty($errors['subject_id']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Enter Subject ID" value="<?= htmlspecialchars($formFields['subject_id']) ?>" required>
                            <div class="error-message"><?= $errors['subject_id'] ?></div>
                        </div>

                        <!-- Subject Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required-field">Subject Name</label>
                            <input type="text" name="subject_name" class="form-control <?= !empty($errors['subject_name']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Enter Subject Name" value="<?= htmlspecialchars($formFields['subject_name']) ?>" required>
                            <div class="error-message"><?= $errors['subject_name'] ?></div>
                        </div>

                        <!-- Department -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required-field">Department</label>
                            <select name="department" class="form-select <?= !empty($errors['department']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Select Department --</option>
                                <option value="Department of Technology" <?= $formFields['department'] == 'Department of Technology' ? 'selected' : '' ?>>Department of Technology</option>
                                <option value="Department of IMR" <?= $formFields['department'] == 'Department of IMR' ? 'selected' : '' ?>>Department of IMR</option>
                                <option value="Department of IHS" <?= $formFields['department'] == 'Department of IHS' ? 'selected' : '' ?>>Department of IHS</option>
                            </select>
                            <div class="error-message"><?= $errors['department'] ?></div>
                        </div>

                        <!-- Degree Program -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required-field">Degree Program</label>
                            <select name="degree_program" class="form-select <?= !empty($errors['degree_program']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Select Degree Program --</option>
                                <option value="BHSc in HICT" <?= $formFields['degree_program'] == "BHSc in HICT" ? "selected" : "" ?>>BHSc in HICT</option>
                                <option value="BHSc in BHBT" <?= $formFields['degree_program'] == "BHSc in BHBT" ? "selected" : "" ?>>BHSc in BHBT</option>
                                <option value="BSc in HTHM" <?= $formFields['degree_program'] == "BSc in HTHM" ? "selected" : "" ?>>BSc in HTHM</option>
                                <option value="BSc in BSYP" <?= $formFields['degree_program'] == "BSc in BSYP" ? "selected" : "" ?>>BSc in BSYP</option>
                                <option value="BHSc in BIPT" <?= $formFields['degree_program'] == "BHSc in BIPT" ? "selected" : "" ?>>BHSc in BIPT</option>
                                <option value="BSc in BSMR" <?= $formFields['degree_program'] == "BSc in BSMR" ? "selected" : "" ?>>BSc in BSMR</option>
                            </select>
                            <div class="error-message"><?= $errors['degree_program'] ?></div>
                        </div>

                        <!-- Batch -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required-field">Batch</label>
                            <input type="text" name="batch" class="form-control <?= !empty($errors['batch']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($formFields['batch']) ?>" placeholder="e.g. 2022/2023" required>
                            <div class="error-message"><?= $errors['batch'] ?></div>
                        </div>

                        <!-- Academic Year -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold required-field">Academic Year</label>
                            <select name="Academic_Year" class="form-select <?= !empty($errors['Academic_Year']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Select Academic Year --</option>
                                <?php
                                for ($year = 2020; $year <= 2025; $year++) {
                                    $ay = "$year/" . ($year + 1);
                                    echo "<option value='$ay' " . ($formFields['Academic_Year'] == $ay ? "selected" : "") . ">$ay</option>";
                                }
                                ?>
                            </select>
                            <div class="error-message"><?= $errors['Academic_Year'] ?></div>
                        </div>

                        <!-- Semester -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold required-field">Semester</label>
                            <select name="semester" class="form-select <?= !empty($errors['semester']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Select Semester --</option>
                                <option value="Semester I" <?= $formFields['semester'] == 'Semester I' ? 'selected' : '' ?>>Semester I</option>
                                <option value="Semester II" <?= $formFields['semester'] == 'Semester II' ? 'selected' : '' ?>>Semester II</option>
                            </select>
                            <div class="error-message"><?= $errors['semester'] ?></div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 mt-4">
                            <button type="submit" name="submit" class="btn btn-primary me-2">
                                <i class="lni lni-save"></i> Save Subject
                            </button>
                            <button type="reset" class="btn btn-warning me-2">
                                <i class="lni lni-reload"></i> Reset Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('subjectForm');
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
        if (!isValid) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Missing Information', text: 'Please fill in all required fields' });
        }
    });

    form.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('input', function() {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
    });

    <?php if($showSuccessAlert): ?>
        Swal.fire({ icon: 'success', title: 'Success!', text: 'Subject added successfully!', showConfirmButton: false, timer: 2000 }).then(() => {
            window.location.href = 'add_subject.php';
        });
    <?php elseif($showErrorAlert): ?>
        Swal.fire({ icon: 'error', title: 'Error!', text: '<?= addslashes($errorMessage) ?>' });
    <?php endif; ?>
});
</script>
</body>
</html>
