<?php
session_start();
include_once 'includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Handle form submissions
$message = '';
$message_type = '';

// Add Exam
if (isset($_POST['add_exam'])) {
    $exam_name = sanitize($_POST['exam_name']);
    $exam_type = sanitize($_POST['exam_type']);
    $class_id = sanitize($_POST['class_id']);
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $status = sanitize($_POST['status']);
    
    $sql = "INSERT INTO exams (exam_name, exam_type, class_id, start_date, end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisss", $exam_name, $exam_type, $class_id, $start_date, $end_date, $status);
    
    if ($stmt->execute()) {
        $message = "Exam added successfully!";
        $message_type = "success";
    } else {
        $message = "Error adding exam: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Edit Exam
if (isset($_POST['edit_exam'])) {
    $id = sanitize($_POST['exam_id']);
    $exam_name = sanitize($_POST['exam_name']);
    $exam_type = sanitize($_POST['exam_type']);
    $class_id = sanitize($_POST['class_id']);
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $status = sanitize($_POST['status']);
    
    $sql = "UPDATE exams SET exam_name=?, exam_type=?, class_id=?, start_date=?, end_date=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisssi", $exam_name, $exam_type, $class_id, $start_date, $end_date, $status, $id);
    
    if ($stmt->execute()) {
        $message = "Exam updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating exam: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete Exam
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    
    // Check if exam has results
    $check_sql = "SELECT COUNT(*) as count FROM results WHERE exam_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $result_count = $check_result->fetch_assoc()['count'];
    
    if ($result_count > 0) {
        $message = "Cannot delete exam. It has $result_count result(s) associated.";
        $message_type = "error";
    } else {
        $sql = "DELETE FROM exams WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Exam deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting exam: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Get exam for editing
$edit_exam = null;
if (isset($_GET['edit'])) {
    $id = sanitize($_GET['edit']);
    $sql = "SELECT * FROM exams WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_exam = $result->fetch_assoc();
    $stmt->close();
}

// Search and filter functionality
$search = '';
$class_filter = '';
$status_filter = '';

if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
}
if (isset($_GET['class_filter'])) {
    $class_filter = sanitize($_GET['class_filter']);
}
if (isset($_GET['status_filter'])) {
    $status_filter = sanitize($_GET['status_filter']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Get exams
$sql = "SELECT e.*, c.class_name, c.section,
        (SELECT COUNT(*) FROM results WHERE exam_id = e.id) as result_count
        FROM exams e 
        JOIN classes c ON e.class_id = c.id WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (e.exam_name LIKE '%$search%' OR e.exam_type LIKE '%$search%')";
}
if (!empty($class_filter)) {
    $sql .= " AND e.class_id = $class_filter";
}
if (!empty($status_filter)) {
    $sql .= " AND e.status = '$status_filter'";
}

$sql .= " ORDER BY e.start_date DESC LIMIT $limit OFFSET $offset";
$exams = $conn->query($sql);

// Get total exams for pagination
$count_sql = "SELECT COUNT(*) as count FROM exams e 
             JOIN classes c ON e.class_id = c.id WHERE 1=1";

if (!empty($search)) {
    $count_sql .= " AND (e.exam_name LIKE '%$search%' OR e.exam_type LIKE '%$search%')";
}
if (!empty($class_filter)) {
    $count_sql .= " AND e.class_id = $class_filter";
}
if (!empty($status_filter)) {
    $count_sql .= " AND e.status = '$status_filter'";
}

$total_result = $conn->query($count_sql);
$total_exams = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_exams / $limit);

// Get classes for dropdown
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name, section");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams - Student Record Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .status-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-graduation-cap"></i> SRMS
                        </h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="students.php">
                                <i class="fas fa-users"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="results.php">
                                <i class="fas fa-chart-line"></i> Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="classes.php">
                                <i class="fas fa-school"></i> Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="subjects.php">
                                <i class="fas fa-book"></i> Subjects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="exams.php">
                                <i class="fas fa-clipboard-list"></i> Exams
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Exams</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
                            <i class="fas fa-plus"></i> Add Exam
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <?php echo $message_type == 'success' ? displaySuccess($message) : displayError($message); ?>
                <?php endif; ?>

                <!-- Search and Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search by exam name..." value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="class_filter">
                                    <option value="">All Classes</option>
                                    <?php while ($class = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $class_filter == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="Upcoming" <?php echo $status_filter == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="Ongoing" <?php echo $status_filter == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Exams Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Type</th>
                                        <th>Class</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Results</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($exam = $exams->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-clipboard-list text-primary"></i>
                                            <?php echo htmlspecialchars($exam['exam_name']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $exam['exam_type']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $exam['class_name'] . ' ' . $exam['section']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($exam['start_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($exam['end_date'])); ?></td>
                                        <td>
                                            <span class="badge status-badge bg-<?php 
                                                if ($exam['status'] == 'Upcoming') echo 'warning';
                                                elseif ($exam['status'] == 'Ongoing') echo 'primary';
                                                else echo 'success';
                                            ?>">
                                                <?php echo $exam['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark"><?php echo $exam['result_count']; ?></span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $exam['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="exam_results.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-info btn-action">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="?delete=<?php echo $exam['id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <?php echo generatePagination($total_pages, $page, 'exams.php?search=' . $search . '&class_filter=' . $class_filter . '&status_filter=' . $status_filter); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Exam Modal -->
    <div class="modal fade" id="addExamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Exam Name *</label>
                            <input type="text" class="form-control" name="exam_name" placeholder="e.g., Mid Term Examination" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exam Type *</label>
                                <select class="form-select" name="exam_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Mid Term">Mid Term</option>
                                    <option value="Final Term">Final Term</option>
                                    <option value="Unit Test">Unit Test</option>
                                    <option value="Assignment">Assignment</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php 
                                    $classes->data_seek(0);
                                    while ($class = $classes->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                <option value="Upcoming">Upcoming</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Exam Modal -->
    <?php if ($edit_exam): ?>
    <div class="modal fade show" id="editExamModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Exam</h5>
                    <a href="exams.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="exam_id" value="<?php echo $edit_exam['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Exam Name *</label>
                            <input type="text" class="form-control" name="exam_name" value="<?php echo $edit_exam['exam_name']; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exam Type *</label>
                                <select class="form-select" name="exam_type" required>
                                    <option value="Mid Term" <?php echo $edit_exam['exam_type'] == 'Mid Term' ? 'selected' : ''; ?>>Mid Term</option>
                                    <option value="Final Term" <?php echo $edit_exam['exam_type'] == 'Final Term' ? 'selected' : ''; ?>>Final Term</option>
                                    <option value="Unit Test" <?php echo $edit_exam['exam_type'] == 'Unit Test' ? 'selected' : ''; ?>>Unit Test</option>
                                    <option value="Assignment" <?php echo $edit_exam['exam_type'] == 'Assignment' ? 'selected' : ''; ?>>Assignment</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <?php 
                                    $classes->data_seek(0);
                                    while ($class = $classes->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $edit_exam['class_id'] ? 'selected' : ''; ?>>
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo $edit_exam['start_date']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $edit_exam['end_date']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                <option value="Upcoming" <?php echo $edit_exam['status'] == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="Ongoing" <?php echo $edit_exam['status'] == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="Completed" <?php echo $edit_exam['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="exams.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="edit_exam" class="btn btn-primary">Update Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
