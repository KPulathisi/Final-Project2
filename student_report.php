<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['student']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get student information
$query = "SELECT * FROM students WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get marks records
$query = "SELECT * FROM marks WHERE student_id = ? ORDER BY subject, term";
$stmt = $db->prepare($query);
$stmt->execute([$student['id']]);
$marks_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get attendance statistics
$query = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
          FROM attendance WHERE student_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$student['id']]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

$attendance_percentage = $attendance['total_days'] > 0 ? 
    round(($attendance['present_days'] / $attendance['total_days']) * 100, 1) : 0;

// Organize marks by subject
$marks_by_subject = [];
$total_marks = 0;
$total_subjects = 0;

foreach ($marks_records as $record) {
    $marks_by_subject[$record['subject']][$record['term']] = $record['marks'];
    $total_marks += $record['marks'];
    $total_subjects++;
}

$overall_average = $total_subjects > 0 ? round($total_marks / $total_subjects, 2) : 0;

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - Student Dashboard</title>
    <link rel="stylesheet" href="css/student.css">
    <style>
        @media print {
            .sidebar, .no-print { display: none !important; }
            .dashboard-container { grid-template-columns: 1fr !important; }
            .main-content { padding: 1rem !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar no-print">
            <div class="sidebar-header">
                <h3>Student Panel</h3>
                <p><?php echo htmlspecialchars($student['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php">Dashboard</a></li>
                <li><a href="student_attendance.php">My Attendance</a></li>
                <li><a href="student_marks.php">My Marks</a></li>
                <li><a href="student_timetable.php">Class Timetable</a></li>
                <li><a href="student_report.php" class="active">Report Card</a></li>
                <li><a href="student_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Academic Report Card</h2>
                <p>Comprehensive academic performance report</p>
                <div class="no-print" style="margin-top: 1rem;">
                    <button onclick="window.print()" class="btn btn-primary">Print Report</button>
                </div>
            </div>

            <!-- Report Header -->
            <div class="card" style="text-align: center; margin-bottom: 2rem;">
                <h1 style="color: var(--student-primary); margin-bottom: 0.5rem;">Leeds International</h1>
                <h2 style="color: var(--student-secondary); margin-bottom: 1rem;">Academic Report Card</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: left;">
                    <div>
                        <strong>Student Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?><br>
                        <strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?><br>
                        <strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?>
                    </div>
                    <div>
                        <strong>Academic Year:</strong> <?php echo date('Y'); ?><br>
                        <strong>Report Date:</strong> <?php echo date('F j, Y'); ?><br>
                        <strong>Parent/Guardian:</strong> <?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>

            <!-- Academic Performance -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Academic Performance</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Term 1</th>
                            <th>Term 2</th>
                            <th>Term 3</th>
                            <th>Average</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marks_by_subject as $subject => $terms): ?>
                        <?php
                        $term_total = 0;
                        $term_count = 0;
                        foreach ($terms as $mark) {
                            $term_total += $mark;
                            $term_count++;
                        }
                        $subject_average = $term_count > 0 ? round($term_total / $term_count, 2) : 0;
                        
                        // Calculate grade and remarks
                        if ($subject_average >= 75) {
                            $grade = 'A';
                            $remarks = 'Excellent';
                        } elseif ($subject_average >= 65) {
                            $grade = 'B';
                            $remarks = 'Very Good';
                        } elseif ($subject_average >= 55) {
                            $grade = 'C';
                            $remarks = 'Good';
                        } elseif ($subject_average >= 35) {
                            $grade = 'S';
                            $remarks = 'Needs Improvement';
                        } else {
                            $grade = 'F';
                            $remarks = 'Unsatisfactory';
                        }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($subject); ?></strong></td>
                            <td><?php echo isset($terms['1']) ? $terms['1'] : '-'; ?></td>
                            <td><?php echo isset($terms['2']) ? $terms['2'] : '-'; ?></td>
                            <td><?php echo isset($terms['3']) ? $terms['3'] : '-'; ?></td>
                            <td><strong><?php echo $subject_average; ?></strong></td>
                            <td><strong><?php echo $grade; ?></strong></td>
                            <td><?php echo $remarks; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($marks_by_subject)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No academic records available.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Statistics -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Academic Summary</h3>
                    <div style="padding: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <strong>Overall Average:</strong> <?php echo $overall_average; ?><br>
                                <strong>Overall Grade:</strong> 
                                <?php 
                                if ($overall_average >= 75) echo 'A';
                                elseif ($overall_average >= 65) echo 'B';
                                elseif ($overall_average >= 55) echo 'C';
                                elseif ($overall_average >= 35) echo 'S';
                                else echo 'F';
                                ?><br>
                                <strong>Subjects Taken:</strong> <?php echo count($marks_by_subject); ?>
                            </div>
                            <div>
                                <strong>Class Rank:</strong> N/A<br>
                                <strong>Total Assessments:</strong> <?php echo $total_subjects; ?><br>
                                <strong>Academic Status:</strong> 
                                <?php echo $overall_average >= 50 ? 'Pass' : 'Fail'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>Attendance Summary</h3>
                    <div style="padding: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <strong>Total Days:</strong> <?php echo $attendance['total_days']; ?><br>
                                <strong>Days Present:</strong> <?php echo $attendance['present_days']; ?><br>
                                <strong>Days Absent:</strong> <?php echo $attendance['absent_days']; ?>
                            </div>
                            <div>
                                <strong>Days Late:</strong> <?php echo $attendance['late_days']; ?><br>
                                <strong>Attendance Rate:</strong> <?php echo $attendance_percentage; ?>%<br>
                                <strong>Attendance Status:</strong> 
                                <?php echo $attendance_percentage >= 75 ? 'Satisfactory' : 'Poor'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teacher's Comments -->
            <div class="card">
                <h3>Teacher's Comments</h3>
                <div style="padding: 1rem;">
                    <?php if ($overall_average >= 80): ?>
                        <p><strong>Excellent Performance:</strong> <?php echo htmlspecialchars($student['full_name']); ?> has demonstrated exceptional academic performance throughout the term. Continue the excellent work!</p>
                    <?php elseif ($overall_average >= 60): ?>
                        <p><strong>Good Performance:</strong> <?php echo htmlspecialchars($student['full_name']); ?> shows good understanding of the subjects. With continued effort, even better results can be achieved.</p>
                    <?php elseif ($overall_average >= 40): ?>
                        <p><strong>Satisfactory Performance:</strong> <?php echo htmlspecialchars($student['full_name']); ?> needs to put in more effort to improve academic performance. Additional support may be beneficial.</p>
                    <?php else: ?>
                        <p><strong>Needs Improvement:</strong> <?php echo htmlspecialchars($student['full_name']); ?> requires significant improvement in academic performance. Please schedule a meeting with teachers for additional support.</p>
                    <?php endif; ?>
                    
                    <?php if ($attendance_percentage < 75): ?>
                        <p><strong>Attendance Concern:</strong> Poor attendance is affecting academic performance. Regular attendance is crucial for success.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grading Scale -->
            <div class="card">
                <h3>Grading Scale</h3>
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem; text-align: center;">
                        <div style="padding: 0.5rem; background: #d4edda; border-radius: 3px;"><strong>A (75-100)</strong><br>Excellent</div>
                        <div style="padding: 0.5rem; background: #d1ecf1; border-radius: 3px;"><strong>B (65-74)</strong><br>Very Good</div>
                        <div style="padding: 0.5rem; background: #fff3cd; border-radius: 3px;"><strong>C (55-64)</strong><br>Good</div>
                        <div style="padding: 0.5rem; background: #fdcb6e; border-radius: 3px;"><strong>S (35-54)</strong><br>Needs Improvement</div>
                        <div style="padding: 0.5rem; background: #f8d7da; border-radius: 3px;"><strong>F (0-34)</strong><br>Unsatisfactory</div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="card">
                <h3>Signatures</h3>
                <div style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                        <div style="text-align: center;">
                            <div style="border-bottom: 1px solid #000; margin-bottom: 0.5rem; height: 50px;"></div>
                            <strong>Class Teacher</strong><br>
                            <small>Date: ___________</small>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-bottom: 1px solid #000; margin-bottom: 0.5rem; height: 50px;"></div>
                            <strong>Principal</strong><br>
                            <small>Date: ___________</small>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-bottom: 1px solid #000; margin-bottom: 0.5rem; height: 50px;"></div>
                            <strong>Parent/Guardian</strong><br>
                            <small>Date: ___________</small>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>