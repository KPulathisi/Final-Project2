<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get report data
$reports = [];

// Attendance Report
$query = "SELECT 
            s.class,
            COUNT(DISTINCT s.id) as total_students,
            COUNT(a.id) as total_attendance_records,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count
          FROM students s
          LEFT JOIN attendance a ON s.id = a.student_id
          GROUP BY s.class
          ORDER BY s.class";
$stmt = $db->prepare($query);
$stmt->execute();
$attendance_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marks Report
$query = "SELECT 
            s.class,
            m.subject,
            COUNT(m.id) as total_marks,
            AVG(m.marks) as average_marks,
            MIN(m.marks) as min_marks,
            MAX(m.marks) as max_marks
          FROM students s
          LEFT JOIN marks m ON s.id = m.student_id
          WHERE m.marks IS NOT NULL
          GROUP BY s.class, m.subject
          ORDER BY s.class, m.subject";
$stmt = $db->prepare($query);
$stmt->execute();
$marks_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

// User Statistics
$query = "SELECT 
            user_type,
            COUNT(*) as count,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count
          FROM users
          GROUP BY user_type";
$stmt = $db->prepare($query);
$stmt->execute();
$user_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
                <p><?php echo htmlspecialchars($user['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_users.php">User Management</a></li>
                <li><a href="admin_timetable.php">Timetable Management</a></li>
                <li><a href="admin_feedback.php">Feedback</a></li>
                <li><a href="admin_reports.php" class="active">Reports</a></li>
                <li><a href="admin_events.php">Events</a></li>
                <li><a href="admin_achievements.php">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>System Reports</h2>
                <p>Comprehensive reports on school data</p>
            </div>

            <!-- User Statistics -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">User Statistics</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>User Type</th>
                            <th>Total Users</th>
                            <th>Active Users</th>
                            <th>Inactive Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_stats as $stat): ?>
                        <tr>
                            <td><?php echo ucfirst(str_replace('_', ' ', $stat['user_type'])); ?></td>
                            <td><?php echo $stat['count']; ?></td>
                            <td style="color: var(--admin-success);"><?php echo $stat['active_count']; ?></td>
                            <td style="color: var(--admin-danger);"><?php echo $stat['count'] - $stat['active_count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Attendance Report -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">Attendance Report by Class</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Total Students</th>
                            <th>Attendance Records</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_report as $report): ?>
                        <?php 
                        $attendance_rate = $report['total_attendance_records'] > 0 ? 
                            round(($report['present_count'] / $report['total_attendance_records']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['class']); ?></td>
                            <td><?php echo $report['total_students']; ?></td>
                            <td><?php echo $report['total_attendance_records']; ?></td>
                            <td style="color: var(--admin-success);"><?php echo $report['present_count']; ?></td>
                            <td style="color: var(--admin-danger);"><?php echo $report['absent_count']; ?></td>
                            <td>
                                <span style="color: <?php echo $attendance_rate >= 75 ? 'var(--admin-success)' : 'var(--admin-danger)'; ?>;">
                                    <?php echo $attendance_rate; ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($attendance_report)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No attendance data available.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Marks Report -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">Academic Performance Report</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Total Marks Entered</th>
                            <th>Average Marks</th>
                            <th>Minimum</th>
                            <th>Maximum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marks_report as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['class']); ?></td>
                            <td><?php echo htmlspecialchars($report['subject']); ?></td>
                            <td><?php echo $report['total_marks']; ?></td>
                            <td>
                                <span style="color: <?php echo $report['average_marks'] >= 60 ? 'var(--admin-success)' : ($report['average_marks'] >= 40 ? 'var(--admin-warning)' : 'var(--admin-danger)'); ?>;">
                                    <?php echo round($report['average_marks'], 1); ?>
                                </span>
                            </td>
                            <td><?php echo $report['min_marks']; ?></td>
                            <td><?php echo $report['max_marks']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($marks_report)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No marks data available.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Export Options -->
            <div class="card">
                <h3>Export Reports</h3>
                <p>Generate and download reports in various formats</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button class="btn btn-primary" onclick="exportReport('attendance')">Export Attendance Report</button>
                    <button class="btn btn-primary" onclick="exportReport('marks')">Export Marks Report</button>
                    <button class="btn btn-primary" onclick="exportReport('users')">Export User Statistics</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function exportReport(type) {
            // Simple CSV export functionality
            let csvContent = "data:text/csv;charset=utf-8,";
            let data = [];
            
            if (type === 'attendance') {
                csvContent += "Class,Total Students,Attendance Records,Present,Absent,Attendance Rate\n";
                <?php foreach ($attendance_report as $report): ?>
                <?php $rate = $report['total_attendance_records'] > 0 ? round(($report['present_count'] / $report['total_attendance_records']) * 100, 1) : 0; ?>
                csvContent += "<?php echo $report['class']; ?>,<?php echo $report['total_students']; ?>,<?php echo $report['total_attendance_records']; ?>,<?php echo $report['present_count']; ?>,<?php echo $report['absent_count']; ?>,<?php echo $rate; ?>%\n";
                <?php endforeach; ?>
            } else if (type === 'marks') {
                csvContent += "Class,Subject,Total Marks,Average,Minimum,Maximum\n";
                <?php foreach ($marks_report as $report): ?>
                csvContent += "<?php echo $report['class']; ?>,<?php echo $report['subject']; ?>,<?php echo $report['total_marks']; ?>,<?php echo round($report['average_marks'], 1); ?>,<?php echo $report['min_marks']; ?>,<?php echo $report['max_marks']; ?>\n";
                <?php endforeach; ?>
            } else if (type === 'users') {
                csvContent += "User Type,Total Users,Active Users,Inactive Users\n";
                <?php foreach ($user_stats as $stat): ?>
                csvContent += "<?php echo ucfirst(str_replace('_', ' ', $stat['user_type'])); ?>,<?php echo $stat['count']; ?>,<?php echo $stat['active_count']; ?>,<?php echo $stat['count'] - $stat['active_count']; ?>\n";
                <?php endforeach; ?>
            }
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", type + "_report_" + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>