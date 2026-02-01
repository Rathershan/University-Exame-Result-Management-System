<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

function getGpaPoint($grade) {
    switch (strtoupper($grade)) {
        case 'A+': return 4.0;
        case 'A': return 3.7;
        case 'A-': return 3.5;
        case 'B+': return 3.3;
        case 'B': return 3.0;
        case 'B-': return 2.7;
        case 'C+': return 2.3;
        case 'C': return 2.0;
        case 'C-': return 1.7;
        case 'D+': return 1.3;
        case 'D': return 1.0;
        case 'F': return 0.0;
        default: return null;
    }
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

$departments = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT department FROM students ORDER BY department"), MYSQLI_ASSOC);
$degree_programs = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT degree_program FROM students ORDER BY degree_program"), MYSQLI_ASSOC);
$batches = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT batch FROM students ORDER BY batch"), MYSQLI_ASSOC);

$department = $_POST['department'] ?? $_GET['department'] ?? '';
$degree_program = $_POST['degree_program'] ?? $_GET['degree_program'] ?? '';
$batch = $_POST['batch'] ?? $_GET['batch'] ?? '';
$semester = $_POST['semester'] ?? $_GET['semester'] ?? '';

$safe_department = mysqli_real_escape_string($conn, $department);
$safe_degree_program = mysqli_real_escape_string($conn, $degree_program);
$safe_batch = mysqli_real_escape_string($conn, $batch);
$safe_semester = mysqli_real_escape_string($conn, $semester);

if (isset($_POST['publish_results'])) {
    $check_sql = "SELECT r.student_id FROM results r 
                  JOIN students s ON r.student_id = s.student_id 
                  WHERE s.department = '$safe_department' 
                    AND s.degree_program = '$safe_degree_program' 
                    AND s.batch = '$safe_batch' 
                    AND s.semester = '$safe_semester' LIMIT 1";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE results r 
                       JOIN students s ON r.student_id = s.student_id 
                       SET r.published = 1, r.published_date = NOW() 
                       WHERE s.department = '$safe_department' 
                         AND s.degree_program = '$safe_degree_program' 
                         AND s.batch = '$safe_batch' 
                         AND s.semester = '$safe_semester'";
        mysqli_query($conn, $update_sql);

        mysqli_query($conn, "INSERT INTO results_status (department, degree_program, batch, semester, published_date, status) 
                             VALUES ('$safe_department', '$safe_degree_program', '$safe_batch', '$safe_semester', NOW(), 'published')");

        echo "<script>alert('✅ Results have been published successfully!'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('❌ No results found for the selected filter. Cannot publish.');</script>";
    }
    exit;
}

$subjects_array = [];
if ($safe_department && $safe_degree_program && $safe_batch && $safe_semester) {
    $subject_sql = "SELECT subject_id, subject_name FROM subjects 
                    WHERE department = '$safe_department' 
                      AND degree_program = '$safe_degree_program' 
                      AND batch = '$safe_batch' 
                      AND semester = '$safe_semester' 
                    ORDER BY subject_id ASC";
    $subjects_result = mysqli_query($conn, $subject_sql);
    while ($row = mysqli_fetch_assoc($subjects_result)) {
        $subjects_array[] = $row;
    }
}

$student_results = [];
$student_info = [];

if ($safe_department && $safe_degree_program && $safe_batch && $safe_semester) {
    $results_sql = "SELECT r.student_id, s.full_name, r.subject_id, r.grade 
                    FROM results r 
                    JOIN students s ON r.student_id = s.student_id 
                    WHERE s.department = '$safe_department' 
                      AND s.degree_program = '$safe_degree_program' 
                      AND s.batch = '$safe_batch' 
                      AND s.semester = '$safe_semester' 
                    ORDER BY r.student_id";
    $results_result = mysqli_query($conn, $results_sql);
    while ($row = mysqli_fetch_assoc($results_result)) {
        $sid = $row['student_id'];
        $student_results[$sid][$row['subject_id']] = $row['grade'];
        $student_info[$sid] = $row['full_name'];
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Results with GPA</title>
    <link rel="icon" type="image/png" href="logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f9f9f9; }
        .container { margin-top: 40px; }
        .table-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
    </style>
</head>
<body>
<header class="bg-dark text-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h4 fw-bolder">GWUIM - Admin Panel</h1>
        <nav class="d-flex gap-3">
            <a href="dashboard.php" class="text-white text-decoration-none">Dashboard</a>
            <a href="logout.php" class="text-white text-decoration-none">Logout</a>
        </nav>
    </div>
</header>

<div class="container my-4">
    <!-- Filter Form -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="department" class="form-label">Department</label>
            <select id="department" name="department" class="form-select" required>
                <option value="">-- Select --</option>
               <?php foreach ($departments as $d): ?>
    <option value="<?= htmlspecialchars($d['department']) ?>" <?= ($department == $d['department']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($d['department']) ?>
    </option>
<?php endforeach; ?>

            </select>
        </div>
        <div class="col-md-4">
            <label for="degree_program" class="form-label">Degree Program</label>
            <select id="degree_program" name="degree_program" class="form-select" required>
                <option value="">-- Select --</option><?php foreach ($degree_programs as $dp): ?>
    <option value="<?= htmlspecialchars($dp['degree_program']) ?>" <?= ($degree_program == $dp['degree_program']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($dp['degree_program']) ?>
    </option>
<?php endforeach; ?>

            </select>
        </div>
        <div class="col-md-4">
            <label for="batch" class="form-label">Batch</label>
            <select id="batch" name="batch" class="form-select" required>
                <option value="">-- Select --</option><?php foreach ($batches as $b): ?>
    <option value="<?= htmlspecialchars($b['batch']) ?>" <?= ($batch == $b['batch']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($b['batch']) ?>
    </option>
<?php endforeach; ?>

            </select>
        </div>
        <div class="col-md-3">
    <label class="form-label">Semester</label>
    <select name="semester" class="form-select" required>
        <option value="">-- Select Semester --</option>
        <option value="Semester I" <?= ($semester == 'Semester I') ? 'selected' : '' ?>>Semester I</option>
        <option value="Semester II" <?= ($semester == 'Semester II') ? 'selected' : '' ?>>Semester II</option>
    </select>
</div>


            </select>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </div>
    </form>

    <div class="mb-3 text-end">
       <!-- <a href="#" class="btn btn-success" id="downloadBtn">Download PDF</a> -->
        <button type="button" class="btn btn-info" onclick="checkResultStatus()">Check Result Status</button>
        <form method="POST" class="d-inline-block ms-2">
            <input type="hidden" name="department" value="<?= htmlspecialchars($department) ?>">
            <input type="hidden" name="degree_program" value="<?= htmlspecialchars($degree_program) ?>">
            <input type="hidden" name="batch" value="<?= htmlspecialchars($batch) ?>">
            <input type="hidden" name="semester" value="<?= htmlspecialchars($semester) ?>">
            <button type="submit" name="publish_results" class="btn btn-warning" id="publishBtn" disabled>
                📢 Publish All Results
            </button>
        </form>
    </div>

    <div class="table-container">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <?php foreach ($subjects_array as $sub): ?>
                        <th><?= htmlspecialchars($sub['subject_id']) ?></th>
                    <?php endforeach; ?>
                    <th>GPA</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($student_info)): ?>
                    <tr>
                        <td colspan="<?= count($subjects_array) + 3 ?>" class="text-center">No results found for selected filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($student_info as $sid => $name): ?>
                        <tr>
                            <td><?= htmlspecialchars($sid) ?></td>
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
                            <td><?= calculateGpa($grades_for_gpa) ?></td>
                            <td><a href="edit_result.php?student_id=<?= urlencode($sid) ?>" class="btn btn-sm btn-warning">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function checkResultStatus() {
    const department = document.getElementById("department").value;
    const degree_program = document.getElementById("degree_program").value;
    const batch = document.getElementById("batch").value;
    const semester = document.querySelector('select[name="semester"]').value;
       fetch(`check_result_status.php?department=${encodeURIComponent(department)}&degree_program=${encodeURIComponent(degree_program)}&batch=${encodeURIComponent(batch)}&semester=${encodeURIComponent(semester)}`)
        .then(res => res.json())
        .then(data => {
            const publishBtn = document.getElementById("publishBtn");
            if (data.complete) {
                alert("✅ All results are complete. You can publish now.");
                publishBtn.disabled = false;
            } else {
                alert(`❌ Some results are missing.\nRequired: ${data.required}, Entered: ${data.actual}`);
                publishBtn.disabled = true;
            }
        });
}

document.getElementById('downloadBtn').addEventListener('click', function (e) {
    e.preventDefault();
    if (confirm("Are you sure to download?")) {
        const department = '<?= urlencode($department) ?>';
        const degree_program = '<?= urlencode($degree_program) ?>';
        const batch = '<?= urlencode($batch) ?>';
        window.location.href = `Download.php?department=${department}&degree_program=${degree_program}&batch=${batch}`;
    }
});
</script>
</body>
</html>
