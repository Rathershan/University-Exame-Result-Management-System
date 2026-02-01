<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die("Access Denied");
}

include("db_connect.php");

$department = $_GET['department'] ?? '';
$batch = $_GET['batch'] ?? '';

function getGpaPoint($grade) {
    switch (strtoupper($grade)) {
        case 'A+': case 'A': return 4.0;
        case 'A-': return 3.7;
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

function calculateGpa($grades_array) {
    $total = 0; $count = 0;
    foreach ($grades_array as $grade) {
        $point = getGpaPoint($grade);
        if ($point !== null) {
            $total += $point;
            $count++;
        }
    }
    return $count > 0 ? round($total / $count, 2) : 'N/A';
}

// ✅ Handle PUBLISH & HOLD Results
$safe_department = mysqli_real_escape_string($conn, $department);
$safe_batch = mysqli_real_escape_string($conn, $batch);

if (isset($_POST['publish_results'])) {
    $update_query = "UPDATE results r
                     JOIN students s ON r.student_id = s.student_id
                     SET r.publish_status = 'published'
                     WHERE 1";
    if ($department) $update_query .= " AND s.department = '$safe_department'";
    if ($batch) $update_query .= " AND s.batch = '$safe_batch'";
    mysqli_query($conn, $update_query);
    $success = "✅ Results published successfully.";
}

if (isset($_POST['hold_results'])) {
    $update_query = "UPDATE results r
                     JOIN students s ON r.student_id = s.student_id
                     SET r.publish_status = 'hold'
                     WHERE 1";
    if ($department) $update_query .= " AND s.department = '$safe_department'";
    if ($batch) $update_query .= " AND s.batch = '$safe_batch'";
    mysqli_query($conn, $update_query);
    $success = "⏸️ Results put on hold successfully.";
}

// Subject data
$subject_query = "SELECT subject_id, subject_name FROM subjects WHERE 1";
if ($department) $subject_query .= " AND department = '$safe_department'";
if ($batch) $subject_query .= " AND batch = '$safe_batch'";
$subject_query .= " ORDER BY subject_id ASC";
$subjects = mysqli_query($conn, $subject_query);
$subjects_array = [];
while ($row = mysqli_fetch_assoc($subjects)) {
    $subjects_array[] = $row;
}

// Student results
$results_query = "SELECT r.student_id, s.full_name, r.subject_id, r.grade
                  FROM results r 
                  JOIN students s ON r.student_id = s.student_id 
                  WHERE 1";
if ($department) $results_query .= " AND s.department = '$safe_department'";
if ($batch) $results_query .= " AND s.batch = '$safe_batch'";
$results_query .= " ORDER BY r.student_id";
$results_result = mysqli_query($conn, $results_query);
$student_results = [];
$student_info = [];
while ($row = mysqli_fetch_assoc($results_result)) {
    $sid = $row['student_id'];
    $student_results[$sid][$row['subject_id']] = $row['grade'];
    $student_info[$sid] = $row['full_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Results with GPA</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            vertical-align: middle;
            text-align: center;
        }
        .table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
<header class="bg-dark text-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h4">GWUIM - View Results (Admin)</h1>
        <nav>
            <a href="dashboard.php" class="text-white me-3">Dashboard</a>
            <a href="logout.php" class="text-white">Logout</a>
        </nav>
    </div>
</header>

<div class="container mt-3">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success text-center"><?= $success ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
        <a href="#" class="btn btn-success" id="downloadBtn">Download PDF</a>
    </div>

    <form method="GET" class="row g-3 mb-4 justify-content-center text-center">
        <div class="col-md-4">
            <label for="department" class="form-label">Department</label>
            <select class="form-select" id="department" name="department">
                <option value="">Select Department</option>
                <option value="Department of Technology" <?= $department == 'Department of Technology' ? 'selected' : '' ?>>Technology</option>
                <option value="Department of IMR" <?= $department == 'Department of IMR' ? 'selected' : '' ?>>IMR</option>
                <option value="Department of IHS" <?= $department == 'Department of IHS' ? 'selected' : '' ?>>IHS</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="batch" class="form-label">Batch</label>
            <input type="text" id="batch" name="batch" class="form-control" value="<?= htmlspecialchars($batch) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- ✅ Publish & Hold Buttons -->
    <div class="text-center mb-4">
        <button type="button" class="btn btn-info" onclick="checkResultStatus()">Check Result Status</button>

        <form method="POST" class="d-inline-block ms-2">
            <button type="submit" name="publish_results" class="btn btn-success" id="publishBtn" disabled>📢 Publish All Results</button>
        </form>

        <form method="POST" class="d-inline-block ms-2">
            <button type="submit" name="hold_results" class="btn btn-danger">⏸️ Hold All Results</button>
        </form>
    </div>

    <div class="table-container">
        <table class="table table-bordered table-hover align-middle">
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
                    <tr><td colspan="<?= count($subjects_array) + 3 ?>" class="text-center">No results found for selected filters.</td></tr>
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
                            <td>
                                <a href="edit_result.php?student_id=<?= urlencode($sid) ?>" class="btn btn-sm btn-warning">Edit</a>
                            </td>
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
    const batch = document.getElementById("batch").value;
    fetch(`check_result_status.php?department=${department}&batch=${batch}`)
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
        const batch = '<?= urlencode($batch) ?>';
        window.location.href = `Download.php?department=${department}&batch=${batch}`;
    }
});
</script>
</body>
</html>
