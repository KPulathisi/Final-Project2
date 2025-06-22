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

// Organize marks by subject and term
$marks_by_subject = [];
$total_marks = 0;
$total_subjects = 0;

foreach ($marks_records as $record) {
    $marks_by_subject[$record['subject']][$record['term']] = $record['marks'];
    $total_marks += $record['marks'];
    $total_subjects++;
}

$average_marks = $total_subjects > 0 ? round($total_marks / $total_subjects, 2) : 0;

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Marks - Student Dashboard</title>
    <link rel="stylesheet" href="css/student.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Student Panel</h3>
                <p><?php echo htmlspecialchars($student['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php">Dashboard</a></li>
                <li><a href="student_attendance.php">My Attendance</a></li>
                <li><a href="student_marks.php" class="active">My Marks</a></li>
                <li><a href="student_timetable.php">Class Timetable</a></li>
                <li><a href="student_report.php">Report Card</a></li>
                <li><a href="student_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>My Academic Marks</h2>
                <p>View your marks and academic performance</p>
            </div>

            <!-- Performance Summary -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $average_marks; ?></div>
                    <div class="stat-label">Overall Average</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($marks_by_subject); ?></div>
                    <div class="stat-label">Subjects</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_subjects; ?></div>
                    <div class="stat-label">Total Assessments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        if ($average_marks >= 90) echo 'A+';
                        elseif ($average_marks >= 80) echo 'A';
                        elseif ($average_marks >= 70) echo 'B';
                        elseif ($average_marks >= 60) echo 'C';
                        elseif ($average_marks >= 50) echo 'D';
                        else echo 'F';
                        ?>
                    </div>
                    <div class="stat-label">Overall Grade</div>
                </div>
            </div>

            <!-- Performance Status -->
            <div class="card">
                <h3>Academic Performance Status</h3>
                <div style="padding: 1rem;">
                    <?php if ($average_marks >= 80): ?>
                        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; border: 1px solid #c3e6cb;">
                            <strong>Excellent Performance!</strong> You are performing exceptionally well. Keep up the excellent work!
                        </div>
                    <?php elseif ($average_marks >= 60): ?>
                        <div style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 5px; border: 1px solid #ffeaa7;">
                            <strong>Good Performance</strong> You are doing well, but there's room for improvement in some subjects.
                        </div>
                    <?php elseif ($average_marks >= 40): ?>
                        <div style="background: #ffeaa7; color: #856404; padding: 1rem; border-radius: 5px; border: 1px solid #ffd32a;">
                            <strong>Average Performance</strong> You need to focus more on your studies to improve your grades.
                        </div>
                    <?php else: ?>
                        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; border: 1px solid #f5c6cb;">
                            <strong>Needs Improvement</strong> Your performance needs significant improvement. Please seek help from teachers.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Marks by Subject -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Marks by Subject</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Term 1</th>
                            <th>Term 2</th>
                            <th>Term 3</th>
                            <th>Average</th>
                            <th>Grade</th>
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
                        
                        // Calculate grade
                        $grade = '';
                        if ($subject_average >= 90) $grade = 'A+';
                        elseif ($subject_average >= 80) $grade = 'A';
                        elseif ($subject_average >= 70) $grade = 'B';
                        elseif ($subject_average >= 60) $grade = 'C';
                        elseif ($subject_average >= 50) $grade = 'D';
                        else $grade = 'F';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($subject); ?></strong></td>
                            <td><?php echo isset($terms['1']) ? $terms['1'] : '-'; ?></td>
                            <td><?php echo isset($terms['2']) ? $terms['2'] : '-'; ?></td>
                            <td><?php echo isset($terms['3']) ? $terms['3'] : '-'; ?></td>
                            <td><strong><?php echo $subject_average; ?></strong></td>
                            <td>
                                <span style="color: 
                                    <?php 
                                    if ($subject_average >= 80) echo 'var(--student-success)';
                                    elseif ($subject_average >= 60) echo 'var(--student-warning)';
                                    else echo 'var(--student-danger)';
                                    ?>; font-weight: bold;">
                                    <?php echo $grade; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($marks_by_subject)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No marks recorded yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Detailed Marks History -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Detailed Marks History</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Term</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marks_records as $record): ?>
                        <?php
                        $grade = '';
                        if ($record['marks'] >= 90) $grade = 'A+';
                        elseif ($record['marks'] >= 80) $grade = 'A';
                        elseif ($record['marks'] >= 70) $grade = 'B';
                        elseif ($record['marks'] >= 60) $grade = 'C';
                        elseif ($record['marks'] >= 50) $grade = 'D';
                        else $grade = 'F';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['subject']); ?></td>
                            <td>Term <?php echo $record['term']; ?></td>
                            <td><strong><?php echo $record['marks']; ?></strong></td>
                            <td>
                                <span style="color: 
                                    <?php 
                                    if ($record['marks'] >= 80) echo 'var(--student-success)';
                                    elseif ($record['marks'] >= 60) echo 'var(--student-warning)';
                                    else echo 'var(--student-danger)';
                                    ?>; font-weight: bold;">
                                    <?php echo $grade; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($record['added_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($marks_records)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                No marks recorded yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Performance Chart -->
            <div class="card">
                <h3>Subject Performance Overview</h3>
                <div style="padding: 1rem;">
                    <?php if (!empty($marks_by_subject)): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <?php foreach ($marks_by_subject as $subject => $terms): ?>
                        <?php
                        $term_total = 0;
                        $term_count = 0;
                        foreach ($terms as $mark) {
                            $term_total += $mark;
                            $term_count++;
                        }
                        $subject_average = $term_count > 0 ? round($term_total / $term_count, 2) : 0;
                        ?>
                        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 1rem; text-align: center;">
                            <h4><?php echo htmlspecialchars($subject); ?></h4>
                            <div style="font-size: 2rem; font-weight: bold; color: 
                                <?php 
                                if ($subject_average >= 80) echo 'var(--student-success)';
                                elseif ($subject_average >= 60) echo 'var(--student-warning)';
                                else echo 'var(--student-danger)';
                                ?>;">
                                <?php echo $subject_average; ?>
                            </div>
                            <p>Average Score</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p>No performance data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>