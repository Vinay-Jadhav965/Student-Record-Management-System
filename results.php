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

// Add Result
if (isset($_POST['add_result'])) {
    $student_id = sanitize($_POST['student_id']);
    $subject_id = sanitize($_POST['subject_id']);
    $exam_id = sanitize($_POST['exam_id']);
    $marks_obtained = sanitize($_POST['marks_obtained']);
    $remarks = sanitize($_POST['remarks']);
    
    // Calculate grade
    $sql = "SELECT max_marks FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $subject = $stmt->get_result()->fetch_assoc();
    $max_marks = $subject['max_marks'];
    
    $percentage = ($marks_obtained / $max_marks) * 100;
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B+';
    elseif ($percentage >= 60) $grade = 'B';
    elseif ($percentage >= 50) $grade = 'C+';
    elseif ($percentage >= 40) $grade = 'C';
    else $grade = 'F';
    
    // Check if result already exists
    $check_sql = "SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND exam_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $student_id, $subject_id, $exam_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "Result already exists for this student, subject, and exam!";
        $message_type = "error";
    } else {
        $sql = "INSERT INTO results (student_id, subject_id, exam_id, marks_obtained, grade, remarks) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidsss", $student_id, $subject_id, $exam_id, $marks_obtained, $grade, $remarks);
        
        if ($stmt->execute()) {
            $message = "Result added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding result: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Edit Result
if (isset($_POST['edit_result'])) {
    $id = sanitize($_POST['result_id']);
    $student_id = sanitize($_POST['student_id']);
    $subject_id = sanitize($_POST['subject_id']);
    $exam_id = sanitize($_POST['exam_id']);
    $marks_obtained = sanitize($_POST['marks_obtained']);
    $remarks = sanitize($_POST['remarks']);
    
    // Calculate grade
    $sql = "SELECT max_marks FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $subject = $stmt->get_result()->fetch_assoc();
    $max_marks = $subject['max_marks'];
    
    $percentage = ($marks_obtained / $max_marks) * 100;
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B+';
    elseif ($percentage >= 60) $grade = 'B';
    elseif ($percentage >= 50) $grade = 'C+';
    elseif ($percentage >= 40) $grade = 'C';
    else $grade = 'F';
    
    $sql = "UPDATE results SET student_id=?, subject_id=?, exam_id=?, marks_obtained=?, grade=?, remarks=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiidssi", $student_id, $subject_id, $exam_id, $marks_obtained, $grade, $remarks, $id);
    
    if ($stmt->execute()) {
        $message = "Result updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating result: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete Result
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    
    $sql = "DELETE FROM results WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Result deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting result: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Get result for editing
$edit_result = null;
if (isset($_GET['edit'])) {
    $id = sanitize($_GET['edit']);
    $sql = "SELECT * FROM results WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_result = $result->fetch_assoc();
    $stmt->close();
}

// Search and filter functionality
$search = '';
$class_filter = '';
$exam_filter = '';

if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
}
if (isset($_GET['class_filter'])) {
    $class_filter = sanitize($_GET['class_filter']);
}
if (isset($_GET['exam_filter'])) {
    $exam_filter = sanitize($_GET['exam_filter']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Get results
$sql = "SELECT r.*, s.name as student_name, s.roll_number, 
        sub.subject_name, sub.max_marks, e.exam_name, e.exam_type,
        c.class_name, c.section
        FROM results r 
        JOIN students s ON r.student_id = s.id 
        JOIN subjects sub ON r.subject_id = sub.id 
        JOIN exams e ON r.exam_id = e.id
        JOIN classes c ON s.class_id = c.id WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (s.name LIKE '%$search%' OR s.roll_number LIKE '%$search%' OR sub.subject_name LIKE '%$search%')";
}
if (!empty($class_filter)) {
    $sql .= " AND s.class_id = $class_filter";
}
if (!empty($exam_filter)) {
    $sql .= " AND r.exam_id = $exam_filter";
}

$sql .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$results = $conn->query($sql);

// Get total results for pagination
$count_sql = "SELECT COUNT(*) as count FROM results r 
              JOIN students s ON r.student_id = s.id 
              JOIN subjects sub ON r.subject_id = sub.id 
              JOIN exams e ON r.exam_id = e.id WHERE 1=1";

if (!empty($search)) {
    $count_sql .= " AND (s.name LIKE '%$search%' OR s.roll_number LIKE '%$search%' OR sub.subject_name LIKE '%$search%')";
}
if (!empty($class_filter)) {
    $count_sql .= " AND s.class_id = $class_filter";
}
if (!empty($exam_filter)) {
    $count_sql .= " AND r.exam_id = $exam_filter";
}

$total_result = $conn->query($count_sql);
$total_results = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_results / $limit);

// Get dropdown data
$students = $conn->query("SELECT s.id, s.name, s.roll_number, c.class_name, c.section 
                         FROM students s 
                         JOIN classes c ON s.class_id = c.id 
                         ORDER BY s.name");
$subjects = $conn->query("SELECT sub.*, c.class_name, c.section 
                         FROM subjects sub 
                         JOIN classes c ON sub.class_id = c.id 
                         ORDER BY c.class_name, c.section, sub.subject_name");
$exams = $conn->query("SELECT e.*, c.class_name, c.section 
                      FROM exams e 
                      JOIN classes c ON e.class_id = c.id 
                      ORDER BY e.start_date DESC");
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name, section");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Student Record Management System</title>
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
        .grade-badge {
            font-size: 0.9rem;
            padding: 0.35rem 0.65rem;
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
                            <a class="nav-link active" href="results.php">
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
                    <h1 class="h2">Manage Results</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResultModal">
                            <i class="fas fa-plus"></i> Add Result
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
                                <input type="text" class="form-control" name="search" placeholder="Search by student, roll, or subject..." value="<?php echo $search; ?>">
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
                                <select class="form-select" name="exam_filter">
                                    <option value="">All Exams</option>
                                    <?php 
                                    $exams->data_seek(0);
                                    while ($exam = $exams->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $exam['id']; ?>" <?php echo $exam_filter == $exam['id'] ? 'selected' : ''; ?>>
                                        <?php echo $exam['exam_name'] . ' - ' . $exam['exam_type']; ?>
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

                <!-- Results Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Roll No</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th>Exam</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($result = $results->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $result['student_name']; ?></td>
                                        <td><?php echo $result['roll_number']; ?></td>
                                        <td><?php echo $result['class_name'] . ' ' . $result['section']; ?></td>
                                        <td><?php echo $result['subject_name']; ?></td>
                                        <td><?php echo $result['exam_name']; ?></td>
                                        <td><?php echo $result['marks_obtained']; ?> / <?php echo $result['max_marks']; ?></td>
                                        <td>
                                            <span class="badge grade-badge bg-<?php 
                                                if ($result['grade'] == 'A+' || $result['grade'] == 'A') echo 'success';
                                                elseif ($result['grade'] == 'B+' || $result['grade'] == 'B') echo 'info';
                                                elseif ($result['grade'] == 'C+' || $result['grade'] == 'C') echo 'warning';
                                                else echo 'danger';
                                            ?>">
                                                <?php echo $result['grade']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $result['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="print_result.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-info btn-action" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="?delete=<?php echo $result['id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Are you sure?')">
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
                            <?php echo generatePagination($total_pages, $page, 'results.php?search=' . $search . '&class_filter=' . $class_filter . '&exam_filter=' . $exam_filter); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Result Modal -->
    <div class="modal fade" id="addResultModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student *</label>
                                <select class="form-select" name="student_id" required id="studentSelect">
                                    <option value="">Select Student</option>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo $student['name'] . ' (' . $student['roll_number'] . ') - ' . $student['class_name'] . ' ' . $student['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject *</label>
                                <select class="form-select" name="subject_id" required id="subjectSelect">
                                    <option value="">Select Subject</option>
                                    <?php 
                                    $subjects->data_seek(0);
                                    while ($subject = $subjects->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $subject['id']; ?>" data-max-marks="<?php echo $subject['max_marks']; ?>">
                                        <?php echo $subject['subject_name'] . ' - ' . $subject['class_name'] . ' ' . $subject['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exam *</label>
                                <select class="form-select" name="exam_id" required>
                                    <option value="">Select Exam</option>
                                    <?php 
                                    $exams->data_seek(0);
                                    while ($exam = $exams->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $exam['id']; ?>">
                                        <?php echo $exam['exam_name'] . ' - ' . $exam['exam_type'] . ' (' . $exam['class_name'] . ' ' . $exam['section'] . ')'; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marks Obtained *</label>
                                <input type="number" class="form-control" name="marks_obtained" id="marksInput" required min="0" step="0.01">
                                <small class="text-muted">Maximum marks: <span id="maxMarksDisplay">100</span></small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_result" class="btn btn-primary">Add Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Result Modal -->
    <?php if ($edit_result): ?>
    <div class="modal fade show" id="editResultModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Result</h5>
                    <a href="results.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="result_id" value="<?php echo $edit_result['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student *</label>
                                <select class="form-select" name="student_id" required>
                                    <?php 
                                    $students->data_seek(0);
                                    while ($student = $students->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo $student['id'] == $edit_result['student_id'] ? 'selected' : ''; ?>>
                                        <?php echo $student['name'] . ' (' . $student['roll_number'] . ') - ' . $student['class_name'] . ' ' . $student['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject *</label>
                                <select class="form-select" name="subject_id" required>
                                    <?php 
                                    $subjects->data_seek(0);
                                    while ($subject = $subjects->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $subject['id']; ?>" <?php echo $subject['id'] == $edit_result['subject_id'] ? 'selected' : ''; ?>>
                                        <?php echo $subject['subject_name'] . ' - ' . $subject['class_name'] . ' ' . $subject['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exam *</label>
                                <select class="form-select" name="exam_id" required>
                                    <?php 
                                    $exams->data_seek(0);
                                    while ($exam = $exams->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $exam['id']; ?>" <?php echo $exam['id'] == $edit_result['exam_id'] ? 'selected' : ''; ?>>
                                        <?php echo $exam['exam_name'] . ' - ' . $exam['exam_type'] . ' (' . $exam['class_name'] . ' ' . $exam['section'] . ')'; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marks Obtained *</label>
                                <input type="number" class="form-control" name="marks_obtained" value="<?php echo $edit_result['marks_obtained']; ?>" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"><?php echo $edit_result['remarks']; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="results.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="edit_result" class="btn btn-primary">Update Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update max marks display when subject is selected
        document.getElementById('subjectSelect').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var maxMarks = selectedOption.getAttribute('data-max-marks');
            document.getElementById('maxMarksDisplay').textContent = maxMarks;
            document.getElementById('marksInput').setAttribute('max', maxMarks);
        });
    </script>
</body>
</html>
