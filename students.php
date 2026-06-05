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

// Add Student
if (isset($_POST['add_student'])) {
    $roll_number = sanitize($_POST['roll_number']);
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $class_id = sanitize($_POST['class_id']);
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $admission_date = sanitize($_POST['admission_date']);
    
    // Check if roll number already exists
    $check_sql = "SELECT id FROM students WHERE roll_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $roll_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "Roll number already exists!";
        $message_type = "error";
    } else {
        $sql = "INSERT INTO students (roll_number, name, email, phone, address, class_id, date_of_birth, gender, admission_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssisss", $roll_number, $name, $email, $phone, $address, $class_id, $date_of_birth, $gender, $admission_date);
        
        if ($stmt->execute()) {
            $message = "Student added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding student: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Edit Student
if (isset($_POST['edit_student'])) {
    $id = sanitize($_POST['student_id']);
    $roll_number = sanitize($_POST['roll_number']);
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $class_id = sanitize($_POST['class_id']);
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $status = sanitize($_POST['status']);
    
    $sql = "UPDATE students SET roll_number=?, name=?, email=?, phone=?, address=?, class_id=?, date_of_birth=?, gender=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssisssi", $roll_number, $name, $email, $phone, $address, $class_id, $date_of_birth, $gender, $status, $id);
    
    if ($stmt->execute()) {
        $message = "Student updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating student: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete Student
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Student deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting student: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Get student for editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = sanitize($_GET['edit']);
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_student = $result->fetch_assoc();
    $stmt->close();
}

// Search functionality
$search = '';
if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get students
$sql = "SELECT s.*, c.class_name, c.section 
        FROM students s 
        JOIN classes c ON s.class_id = c.id";

if (!empty($search)) {
    $sql .= " WHERE s.name LIKE '%$search%' OR s.roll_number LIKE '%$search%'";
}

$sql .= " ORDER BY s.created_at DESC LIMIT $limit OFFSET $offset";
$students = $conn->query($sql);

// Get total students for pagination
$count_sql = "SELECT COUNT(*) as count FROM students s";
if (!empty($search)) {
    $count_sql .= " WHERE s.name LIKE '%$search%' OR s.roll_number LIKE '%$search%'";
}
$total_result = $conn->query($count_sql);
$total_students = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_students / $limit);

// Get classes for dropdown
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name, section");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Record Management System</title>
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
                            <a class="nav-link active" href="students.php">
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
                    <h1 class="h2">Manage Students</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-plus"></i> Add Student
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
                                <input type="text" class="form-control" name="search" placeholder="Search by name or roll number..." value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Roll No</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $student['roll_number']; ?></td>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><?php echo $student['email']; ?></td>
                                        <td><?php echo $student['phone']; ?></td>
                                        <td><?php echo $student['class_name'] . ' ' . $student['section']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $student['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo $student['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="student_results.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info btn-action">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="?delete=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Are you sure?')">
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
                            <?php echo generatePagination($total_pages, $page, 'students.php?search=' . $search); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Roll Number *</label>
                                <input type="text" class="form-control" name="roll_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php while ($class = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Admission Date</label>
                                <input type="date" class="form-control" name="admission_date">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <?php if ($edit_student): ?>
    <div class="modal fade show" id="editStudentModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <a href="students.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="student_id" value="<?php echo $edit_student['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Roll Number *</label>
                                <input type="text" class="form-control" name="roll_number" value="<?php echo $edit_student['roll_number']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" value="<?php echo $edit_student['name']; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $edit_student['email']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo $edit_student['phone']; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <?php 
                                    $classes->data_seek(0);
                                    while ($class = $classes->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $edit_student['class_id'] ? 'selected' : ''; ?>>
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $edit_student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $edit_student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $edit_student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?php echo $edit_student['date_of_birth']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="Active" <?php echo $edit_student['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo $edit_student['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo $edit_student['address']; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="edit_student" class="btn btn-primary">Update Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
