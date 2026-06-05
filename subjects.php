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

// Add Subject
if (isset($_POST['add_subject'])) {
    $subject_name = sanitize($_POST['subject_name']);
    $subject_code = sanitize($_POST['subject_code']);
    $class_id = sanitize($_POST['class_id']);
    $max_marks = sanitize($_POST['max_marks']);
    
    $sql = "INSERT INTO subjects (subject_name, subject_code, class_id, max_marks) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $subject_name, $subject_code, $class_id, $max_marks);
    
    if ($stmt->execute()) {
        $message = "Subject added successfully!";
        $message_type = "success";
    } else {
        $message = "Error adding subject: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Edit Subject
if (isset($_POST['edit_subject'])) {
    $id = sanitize($_POST['subject_id']);
    $subject_name = sanitize($_POST['subject_name']);
    $subject_code = sanitize($_POST['subject_code']);
    $class_id = sanitize($_POST['class_id']);
    $max_marks = sanitize($_POST['max_marks']);
    
    $sql = "UPDATE subjects SET subject_name=?, subject_code=?, class_id=?, max_marks=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiii", $subject_name, $subject_code, $class_id, $max_marks, $id);
    
    if ($stmt->execute()) {
        $message = "Subject updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating subject: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete Subject
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    
    // Check if subject has results
    $check_sql = "SELECT COUNT(*) as count FROM results WHERE subject_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $result_count = $check_result->fetch_assoc()['count'];
    
    if ($result_count > 0) {
        $message = "Cannot delete subject. It has $result_count result(s) associated.";
        $message_type = "error";
    } else {
        $sql = "DELETE FROM subjects WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Subject deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting subject: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Get subject for editing
$edit_subject = null;
if (isset($_GET['edit'])) {
    $id = sanitize($_GET['edit']);
    $sql = "SELECT * FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_subject = $result->fetch_assoc();
    $stmt->close();
}

// Search and filter functionality
$search = '';
$class_filter = '';

if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
}
if (isset($_GET['class_filter'])) {
    $class_filter = sanitize($_GET['class_filter']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get subjects
$sql = "SELECT sub.*, c.class_name, c.section,
        (SELECT COUNT(*) FROM results WHERE subject_id = sub.id) as result_count
        FROM subjects sub 
        JOIN classes c ON sub.class_id = c.id WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (sub.subject_name LIKE '%$search%' OR sub.subject_code LIKE '%$search%')";
}
if (!empty($class_filter)) {
    $sql .= " AND sub.class_id = $class_filter";
}

$sql .= " ORDER BY c.class_name, c.section, sub.subject_name LIMIT $limit OFFSET $offset";
$subjects = $conn->query($sql);

// Get total subjects for pagination
$count_sql = "SELECT COUNT(*) as count FROM subjects sub 
             JOIN classes c ON sub.class_id = c.id WHERE 1=1";

if (!empty($search)) {
    $count_sql .= " AND (sub.subject_name LIKE '%$search%' OR sub.subject_code LIKE '%$search%')";
}
if (!empty($class_filter)) {
    $count_sql .= " AND sub.class_id = $class_filter";
}

$total_result = $conn->query($count_sql);
$total_subjects = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_subjects / $limit);

// Get classes for dropdown
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name, section");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - Student Record Management System</title>
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
                            <a class="nav-link active" href="subjects.php">
                                <i class="fas fa-book"></i> Subjects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="exams.php">
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
                    <h1 class="h2">Manage Subjects</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                            <i class="fas fa-plus"></i> Add Subject
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
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="search" placeholder="Search by subject name or code..." value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-5">
                                <select class="form-select" name="class_filter">
                                    <option value="">All Classes</option>
                                    <?php while ($class = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $class_filter == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
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

                <!-- Subjects Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Subject Code</th>
                                        <th>Class</th>
                                        <th>Max Marks</th>
                                        <th>Results Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-book text-primary"></i>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($subject['subject_code']); ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $subject['class_name'] . ' ' . $subject['section']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $subject['max_marks']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $subject['result_count']; ?></span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $subject['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $subject['id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Are you sure?')">
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
                            <?php echo generatePagination($total_pages, $page, 'subjects.php?search=' . $search . '&class_filter=' . $class_filter); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Subject Name *</label>
                            <input type="text" class="form-control" name="subject_name" placeholder="e.g., Mathematics, Physics" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" name="subject_code" placeholder="e.g., MATH101, PHY101">
                        </div>
                        <div class="row">
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Maximum Marks *</label>
                                <input type="number" class="form-control" name="max_marks" value="100" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <?php if ($edit_subject): ?>
    <div class="modal fade show" id="editSubjectModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <a href="subjects.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="subject_id" value="<?php echo $edit_subject['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Subject Name *</label>
                            <input type="text" class="form-control" name="subject_name" value="<?php echo $edit_subject['subject_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" name="subject_code" value="<?php echo $edit_subject['subject_code']; ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <?php 
                                    $classes->data_seek(0);
                                    while ($class = $classes->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $edit_subject['class_id'] ? 'selected' : ''; ?>>
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Maximum Marks *</label>
                                <input type="number" class="form-control" name="max_marks" value="<?php echo $edit_subject['max_marks']; ?>" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="subjects.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="edit_subject" class="btn btn-primary">Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
