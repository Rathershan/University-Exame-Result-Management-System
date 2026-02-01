<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

$success = "";
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $success = "✅ Subject deleted successfully!";
}

// --- Filter Logic ---
$conditions = [];
if (!empty($_GET['department'])) {
    $department = mysqli_real_escape_string($conn, $_GET['department']);
    $conditions[] = "department = '$department'";
}
if (!empty($_GET['degree_program'])) {
    $degree_program = mysqli_real_escape_string($conn, $_GET['degree_program']);
    $conditions[] = "degree_program = '$degree_program'";
}
if (!empty($_GET['batch'])) {
    $batch = mysqli_real_escape_string($conn, $_GET['batch']);
    $conditions[] = "batch = '$batch'";
}
if (!empty($_GET['semester'])) {
    $semester = mysqli_real_escape_string($conn, $_GET['semester']);
    $conditions[] = "semester = '$semester'";
}

$where = "";
if (!empty($conditions)) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

$sql = "SELECT * FROM subjects $where ORDER BY subject_id ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subject Overview</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>

<header class="bg-dark text-white py-3 mb-4">
    <div class="container d-flex justify-content-between">
        <h1 class="h4">GWUIM - Admin Panel</h1>
        <nav>
            <a href="dashboard.php" class="text-white me-3">Dashboard</a>
            <a href="logout.php" class="text-white">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Subject Overview</h3>
        <a href="add_subject.php" class="btn btn-primary">+ Add New Subject</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="department" class="form-control" placeholder="Department"
                   value="<?= htmlspecialchars($_GET['department'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="degree_program" class="form-control" placeholder="Degree Program"
                   value="<?= htmlspecialchars($_GET['degree_program'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="batch" class="form-control" placeholder="Batch"
                   value="<?= htmlspecialchars($_GET['batch'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="semester" class="form-control" placeholder="Semester"
                   value="<?= htmlspecialchars($_GET['semester'] ?? '') ?>">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            <a href="subject_overview.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Subject ID</th>
                    <th>Subject Name</th>
                    <th>Department</th>
                    <th>Degree Program</th>
                    <th>Batch</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $count = 1; ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['subject_id']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['degree_program']) ?></td>
                            <td><?= htmlspecialchars($row['batch']) ?></td>
                            <td><?= htmlspecialchars($row['Academic_Year']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td>
                                <a href="edit_subject.php?subject_id=<?= urlencode($row['subject_id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete_subject.php?subject_id=<?= urlencode($row['subject_id']) ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this subject?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No subjects found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
