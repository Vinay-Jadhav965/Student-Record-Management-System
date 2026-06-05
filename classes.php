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

// Add Class
if (isset($_POST['add_class'])) {
    $class_name = sanitize($_POST['class_name']);
    $section = sanitize($_POST['section']);
    
    // Check if class already exists
    $check_sql = "SELECT id FROM classes WHERE class_name = ? AND section = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $class_name, $section);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "Class already exists!";
        $message_type = "error";
    } else {
        $sql = "INSERT INTO classes (class_name, section) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $class_name, $section);
        
        if ($stmt->execute()) {
            $message = "Class added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding class: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Edit Class
if (isset($_POST['edit_class'])) {
    $id = sanitize($_POST['class_id']);
    $class_name = sanitize($_POST['class_name']);
    $section = sanitize($_POST['section']);
    
    $sql = "UPDATE classes SET class_name=?, section=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $class_name, $section, $id);
    
    if ($stmt->execute()) {
        $message = "Class updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating class: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete Class
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    
    // Check if class has students
    $check_sql = "SELECT COUNT(*) as count FROM students WHERE class_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $student_count = $check_result->fetch_assoc()['count'];
    
    if ($student_count > 0) {
        $message = "Cannot delete class. It has $student_count student(s) assigned.";
        $message_type = "error";
    } else {
        $sql = "DELETE FROM classes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Class deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting class: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Get class for editing
$edit_class = null;
if (isset($_GET['edit'])) {
    $id = sanitize($_GET['edit']);
    $sql = "SELECT * FROM classes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_class = $result->fetch_assoc();
    $stmt->close();
}

// Search functionality
$search = '';
if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get classes
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count,
        (SELECT COUNT(*) FROM subjects WHERE class_id = c.id) as subject_count
        FROM classes c";

if (!empty($search)) {
    $sql .= " WHERE c.class_name LIKE '%$search%' OR c.section LIKE '%$search%'";
}

$sql .= " ORDER BY c.class_name, c.section LIMIT $limit OFFSET $offset";
$classes = $conn->query($sql);

// Get total classes for pagination
$count_sql = "SELECT COUNT(*) as count FROM classes c";
if (!empty($search)) {
    $count_sql .= " WHERE c.class_name LIKE '%$search%' OR c.section LIKE '%$search%'";
}
$total_result = $conn->query($count_sql);
$total_classes = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_classes / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - Student Record Management System</title>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
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
                            <a class="nav-link active" href="classes.php">
                                <i class="fas fa-school"></i> Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="subjects.php">
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
                    <h1 class="h2">Manage Classes</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                            <i class="fas fa-plus"></i> Add Class
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <?php echo $message_type == 'success' ? displaySuccess($message) : displayError($message); ?>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" placeholder="Search by class name or section..." value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Classes Grid -->
                <div class="row">
                    <?php while ($class = $classes->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-school text-primary"></i>
                                        <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="?edit=<?php echo $class['id']; ?>">
                                                <i class="fas fa-edit text-warning"></i> Edit
                                            </a></li>
                                            <li><a class="dropdown-item" href="?delete=<?php echo $class['id']; ?>" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash text-danger"></i> Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <i class="fas fa-users text-info fa-2x mb-2"></i>
                                            <h6 class="text-muted mb-1">Students</h6>
                                            <h4 class="mb-0"><?php echo $class['student_count']; ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <i class="fas fa-book text-success fa-2x mb-2"></i>
                                            <h6 class="text-muted mb-1">Subjects</h6>
                                            <h4 class="mb-0"><?php echo $class['subject_count']; ?></h4>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="class_students.php?id=<?php echo $class['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-user-graduate"></i> View Students
                                    </a>
                                    <a href="class_subjects.php?id=<?php echo $class['id']; ?>" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-book-open"></i> View Subjects
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <?php echo generatePagination($total_pages, $page, 'classes.php?search=' . $search); ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div class="modal fade" id="addClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Class Name *</label>
                            <input type="text" class="form-control" name="class_name" placeholder="e.g., 1st Year, 2nd Year" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" placeholder="e.g., A, B, C">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <?php if ($edit_class): ?>
    <div class="modal fade show" id="editClassModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Class</h5>
                    <a href="classes.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="class_id" value="<?php echo $edit_class['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Class Name *</label>
                            <input type="text" class="form-control" name="class_name" value="<?php echo $edit_class['class_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" value="<?php echo $edit_class['section']; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="classes.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="edit_class" class="btn btn-primary">Update Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
