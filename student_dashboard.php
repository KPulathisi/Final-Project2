<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Require student access
requireUserType(['student']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get student information
$query = "SELECT * FROM students WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get statistics for this student
$stats = [];

// Attendance statistics
$query = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
          FROM attendance WHERE student_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$student['id']]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

$stats['attendance_percentage'] = $attendance['total_days'] > 0 ? 
    round(($attendance['present_days'] / $attendance['total_days']) * 100, 1) : 0;
$stats['total_days'] = $attendance['total_days'];
$stats['present_days'] = $attendance['present_days'];
$stats['absent_days'] = $attendance['absent_days'];

// Recent marks
$query = "SELECT AVG(marks) as avg_marks FROM marks WHERE student_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$student['id']]);
$marks_data = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['average_marks'] = $marks_data['avg_marks'] ? round($marks_data['avg_marks'], 1) : 0;

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Leeds International</title>
    <link rel="stylesheet" href="css/student.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Student Panel</h3>
                <p><?php echo htmlspecialchars($student['full_name']); ?></p>
                <small><?php echo htmlspecialchars($student['student_id']); ?></small>
                <small>Class: <?php echo htmlspecialchars($student['class']); ?></small>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active">Dashboard</a></li>
                <li><a href="student_attendance.php">My Attendance</a></li>
                <li><a href="student_marks.php">My Marks</a></li>
                <li><a href="student_timetable.php">Class Timetable</a></li>
                <li><a href="student_report.php">Report Card</a></li>
                <li><a href="student_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h2>Student Dashboard</h2>
                <p>Welcome back, <?php echo htmlspecialchars($student['full_name']); ?>!</p>
                <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['attendance_percentage']; ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['present_days']; ?></div>
                    <div class="stat-label">Days Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['absent_days']; ?></div>
                    <div class="stat-label">Days Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['average_marks']; ?></div>
                    <div class="stat-label">Average Marks</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cards-grid">
                <div class="card">
                    <h3>View Attendance</h3>
                    <p>Check your daily attendance record and view attendance statistics.</p>
                    <a href="student_attendance.php" class="btn btn-primary">View Attendance</a>
                </div>
                <div class="card">
                    <h3>View Marks</h3>
                    <p>See your marks for all subjects across different terms.</p>
                    <a href="student_marks.php" class="btn btn-primary">View Marks</a>
                </div>
                <div class="card">
                    <h3>Class Timetable</h3>
                    <p>Check your class schedule and daily periods.</p>
                    <a href="student_timetable.php" class="btn btn-primary">View Timetable</a>
                </div>
                <div class="card">
                    <h3>Generate Report</h3>
                    <p>Generate and download your academic report card.</p>
                    <a href="student_report.php" class="btn btn-primary">Get Report</a>
                </div>
            </div>

            <!-- Student Information -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">My Information</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Student ID</strong></td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Full Name</strong></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Class</strong></td>
                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Contact Number</strong></td>
                            <td><?php echo htmlspecialchars($student['contact_number'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Parent Name</strong></td>
                            <td><?php echo htmlspecialchars($student['parent_name'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Parent Contact</strong></td>
                            <td><?php echo htmlspecialchars($student['parent_contact'] ?? 'Not provided'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Performance Summary -->
            <?php if ($stats['total_days'] > 0): ?>
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Performance Summary</h3>
                <div style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Attendance Status</h4>
                            <p style="font-size: 1.2rem; color: <?php echo $stats['attendance_percentage'] >= 75 ? 'var(--student-success)' : 'var(--student-danger)'; ?>;">
                                <?php echo $stats['attendance_percentage'] >= 75 ? 'Good' : 'Needs Improvement'; ?>
                            </p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Academic Performance</h4>
                            <p style="font-size: 1.2rem; color: <?php echo $stats['average_marks'] >= 60 ? 'var(--student-success)' : ($stats['average_marks'] >= 40 ? 'var(--student-warning)' : 'var(--student-danger)'); ?>;">
                                <?php 
                                if ($stats['average_marks'] >= 60) echo 'Excellent';
                                elseif ($stats['average_marks'] >= 40) echo 'Good';
                                else echo 'Needs Improvement';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Add active class to current sidebar item
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
            const currentPath = window.location.pathname;
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath.split('/').pop()) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>