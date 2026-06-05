<?php
session_start();
include_once 'includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get result ID
$result_id = isset($_GET['id']) ? sanitize($_GET['id']) : 0;

// Get result details
$sql = "SELECT r.*, s.name as student_name, s.roll_number, s.email, s.phone, s.address, s.date_of_birth, s.gender,
        sub.subject_name, sub.subject_code, sub.max_marks, e.exam_name, e.exam_type,
        c.class_name, c.section
        FROM results r 
        JOIN students s ON r.student_id = s.id 
        JOIN subjects sub ON r.subject_id = sub.id 
        JOIN exams e ON r.exam_id = e.id
        JOIN classes c ON s.class_id = c.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $result_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Result not found!");
}

$result_data = $result->fetch_assoc();
$stmt->close();

// Get all results for this student in the same exam for complete report
$all_results_sql = "SELECT r.*, sub.subject_name, sub.subject_code, sub.max_marks
                   FROM results r 
                   JOIN subjects sub ON r.subject_id = sub.id 
                   WHERE r.student_id = ? AND r.exam_id = ?
                   ORDER BY sub.subject_name";
$all_results_stmt = $conn->prepare($all_results_sql);
$all_results_stmt->bind_param("ii", $result_data['student_id'], $result_data['exam_id']);
$all_results_stmt->execute();
$all_results = $all_results_stmt->get_result();

// Calculate totals
$total_marks = 0;
$total_max_marks = 0;
while ($row = $all_results->fetch_assoc()) {
    $total_marks += $row['marks_obtained'];
    $total_max_marks += $row['max_marks'];
}
$percentage = $total_max_marks > 0 ? round(($total_marks / $total_max_marks) * 100, 2) : 0;

// Calculate overall grade
if ($percentage >= 90) $overall_grade = 'A+';
elseif ($percentage >= 80) $overall_grade = 'A';
elseif ($percentage >= 70) $overall_grade = 'B+';
elseif ($percentage >= 60) $overall_grade = 'B';
elseif ($percentage >= 50) $overall_grade = 'C+';
elseif ($percentage >= 40) $overall_grade = 'C';
else $overall_grade = 'F';

$all_results->data_seek(0);
$all_results_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Report - <?php echo htmlspecialchars($result_data['student_name']); ?></title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 15px;
            }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        
        .result-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        
        .school-name {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .result-title {
            font-size: 20px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .results-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .results-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .results-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .grade-A { background: #d4edda; color: #155724; }
        .grade-B { background: #cce5ff; color: #004085; }
        .grade-C { background: #fff3cd; color: #856404; }
        .grade-F { background: #f8d7da; color: #721c24; }
        
        .summary-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
        }
        
        .footer-section {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 40px;
        }
        
        .signature-label {
            font-size: 12px;
            color: #666;
        }
        
        .print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Result
        </button>
    </div>

    <div class="result-header">
        <div class="school-name">Student Record Management System</div>
        <div class="result-title">Examination Result Report</div>
        <div style="color: #888; font-size: 14px;">
            Generated on: <?php echo date('F d, Y H:i:s'); ?>
        </div>
    </div>

    <div class="student-info">
        <h4 style="margin-top: 0; color: #667eea;">Student Information</h4>
        <div class="info-row">
            <div class="info-label">Name:</div>
            <div class="info-value"><?php echo htmlspecialchars($result_data['student_name']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Roll Number:</div>
            <div class="info-value"><?php echo htmlspecialchars($result_data['roll_number']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Class:</div>
            <div class="info-value"><?php echo htmlspecialchars($result_data['class_name'] . ' ' . $result_data['section']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value"><?php echo htmlspecialchars($result_data['email'] ?: 'N/A'); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Phone:</div>
            <div class="info-value"><?php echo htmlspecialchars($result_data['phone'] ?: 'N/A'); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Address:</div>
            <div class="info-value"><?php echo htmlspecialchars($result_data['address'] ?: 'N/A'); ?></div>
        </div>
    </div>

    <div class="summary-section">
        <h4 style="margin-top: 0; text-align: center;">Exam Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Exam Name</div>
                <div class="summary-value"><?php echo htmlspecialchars($result_data['exam_name']); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Exam Type</div>
                <div class="summary-value"><?php echo htmlspecialchars($result_data['exam_type']); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Subjects</div>
                <div class="summary-value"><?php echo $all_results->num_rows; ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Overall Grade</div>
                <div class="summary-value"><?php echo $overall_grade; ?></div>
            </div>
        </div>
    </div>

    <h4 style="margin-bottom: 15px; color: #667eea;">Subject-wise Results</h4>
    <table class="results-table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Max Marks</th>
                <th>Marks Obtained</th>
                <th>Percentage</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sno = 1;
            while ($row = $all_results->fetch_assoc()): 
                $subject_percentage = round(($row['marks_obtained'] / $row['max_marks']) * 100, 2);
                
                // Calculate grade
                if ($subject_percentage >= 90) $grade = 'A+';
                elseif ($subject_percentage >= 80) $grade = 'A';
                elseif ($subject_percentage >= 70) $grade = 'B+';
                elseif ($subject_percentage >= 60) $grade = 'B';
                elseif ($subject_percentage >= 50) $grade = 'C+';
                elseif ($subject_percentage >= 40) $grade = 'C';
                else $grade = 'F';
                
                $grade_class = substr($grade, 0, 1);
            ?>
            <tr>
                <td><?php echo $sno++; ?></td>
                <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                <td><?php echo $row['max_marks']; ?></td>
                <td><strong><?php echo $row['marks_obtained']; ?></strong></td>
                <td><?php echo $subject_percentage; ?>%</td>
                <td>
                    <span class="grade-badge grade-<?php echo $grade_class; ?>">
                        <?php echo $grade; ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="background: #667eea; color: white; font-weight: bold;">
                <td colspan="3">TOTAL</td>
                <td><?php echo $total_max_marks; ?></td>
                <td><?php echo $total_marks; ?></td>
                <td><?php echo $percentage; ?>%</td>
                <td>
                    <span class="grade-badge" style="background: white; color: #667eea;">
                        <?php echo $overall_grade; ?>
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-section">
        <p><strong>Result Status:</strong> 
            <?php 
            if ($percentage >= 60) {
                echo '<span style="color: #28a745;">✓ PASSED</span>';
            } else {
                echo '<span style="color: #dc3545;">✗ FAILED</span>';
            }
            ?>
        </p>
        <p style="font-size: 12px; margin-top: 20px;">
            This is a computer-generated result report and does not require signature.
        </p>
    </div>

    <div class="signature-area">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Class Teacher</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Principal</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Parent's Signature</div>
        </div>
    </div>

    <script>
        // Auto-print when URL contains print parameter
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>
