<?php
session_start();
include_once 'includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get dashboard statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$total_subjects = $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'];
$total_results = $conn->query("SELECT COUNT(*) as count FROM results")->fetch_assoc()['count'];

// Get recent students
$recent_students = $conn->query("SELECT s.*, c.class_name, c.section 
                                FROM students s 
                                JOIN classes c ON s.class_id = c.id 
                                ORDER BY s.created_at DESC LIMIT 5");

// Get recent results
$recent_results = $conn->query("SELECT r.*, st.name as student_name, st.roll_number, 
                                sub.subject_name, e.exam_name 
                                FROM results r 
                                JOIN students st ON r.student_id = st.id 
                                JOIN subjects sub ON r.subject_id = sub.id 
                                JOIN exams e ON r.exam_id = e.id 
                                ORDER BY r.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Record Management System</title>
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
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
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
                            <a class="nav-link active" href="index.php">
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="text-muted">Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-gradient text-white me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Total Students</h6>
                                    <h3 class="mb-0"><?php echo $total_students; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success bg-gradient text-white me-3">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Total Classes</h6>
                                    <h3 class="mb-0"><?php echo $total_classes; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-gradient text-white me-3">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Total Subjects</h6>
                                    <h3 class="mb-0"><?php echo $total_subjects; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info bg-gradient text-white me-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Total Results</h6>
                                    <h3 class="mb-0"><?php echo $total_results; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Students -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-plus text-primary"></i> Recent Students
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Roll No</th>
                                                <th>Class</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($student = $recent_students->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $student['name']; ?></td>
                                                <td><?php echo $student['roll_number']; ?></td>
                                                <td><?php echo $student['class_name'] . ' ' . $student['section']; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Results -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar text-success"></i> Recent Results
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Subject</th>
                                                <th>Marks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($result = $recent_results->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $result['student_name']; ?></td>
                                                <td><?php echo $result['subject_name']; ?></td>
                                                <td>
                                                    <span class="badge bg-success"><?php echo $result['marks_obtained']; ?></span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
