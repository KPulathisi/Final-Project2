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

// Get attendance records
$query = "SELECT * FROM attendance WHERE student_id = ? ORDER BY date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$student['id']]);
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_days = count($attendance_records);
$present_days = 0;
$absent_days = 0;
$late_days = 0;

foreach ($attendance_records as $record) {
    switch ($record['status']) {
        case 'present':
            $present_days++;
            break;
        case 'absent':
            $absent_days++;
            break;
        case 'late':
            $late_days++;
            break;
    }
}

$attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - Student Dashboard</title>
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
                <li><a href="student_attendance.php" class="active">My Attendance</a></li>
                <li><a href="student_marks.php">My Marks</a></li>
                <li><a href="student_timetable.php">Class Timetable</a></li>
                <li><a href="student_report.php">Report Card</a></li>
                <li><a href="student_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>My Attendance Record</h2>
                <p>View your attendance history and statistics</p>
            </div>

            <!-- Attendance Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $attendance_percentage; ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $present_days; ?></div>
                    <div class="stat-label">Days Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $absent_days; ?></div>
                    <div class="stat-label">Days Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $late_days; ?></div>
                    <div class="stat-label">Days Late</div>
                </div>
            </div>

            <!-- Attendance Status -->
            <div class="card">
                <h3>Attendance Status</h3>
                <div style="padding: 1rem;">
                    <?php if ($attendance_percentage >= 90): ?>
                        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; border: 1px solid #c3e6cb;">
                            <strong>Excellent Attendance!</strong> Your attendance rate is excellent. Keep up the good work!
                        </div>
                    <?php elseif ($attendance_percentage >= 75): ?>
                        <div style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 5px; border: 1px solid #ffeaa7;">
                            <strong>Good Attendance</strong> Your attendance is satisfactory, but try to improve it further.
                        </div>
                    <?php else: ?>
                        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; border: 1px solid #f5c6cb;">
                            <strong>Poor Attendance</strong> Your attendance is below the required minimum. Please improve your attendance.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Attendance History</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>Class</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($record['date'])); ?></td>
                            <td><?php echo date('l', strtotime($record['date'])); ?></td>
                            <td>
                                <span style="color: 
                                    <?php 
                                    switch($record['status']) {
                                        case 'present': echo 'var(--student-success)'; break;
                                        case 'absent': echo 'var(--student-danger)'; break;
                                        case 'late': echo 'var(--student-warning)'; break;
                                    }
                                    ?>; font-weight: bold;">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($record['class']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($attendance_records)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem;">
                                No attendance records found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Monthly Attendance Chart -->
            <div class="card">
                <h3>Monthly Attendance Summary</h3>
                <div style="padding: 1rem;">
                    <?php
                    // Group attendance by month
                    $monthly_stats = [];
                    foreach ($attendance_records as $record) {
                        $month = date('Y-m', strtotime($record['date']));
                        if (!isset($monthly_stats[$month])) {
                            $monthly_stats[$month] = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0];
                        }
                        $monthly_stats[$month]['total']++;
                        $monthly_stats[$month][$record['status']]++;
                    }
                    
                    if (!empty($monthly_stats)):
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <?php foreach (array_reverse($monthly_stats, true) as $month => $stats): ?>
                        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 1rem; text-align: center;">
                            <h4><?php echo date('F Y', strtotime($month . '-01')); ?></h4>
                            <p><strong>Total Days:</strong> <?php echo $stats['total']; ?></p>
                            <p style="color: var(--student-success);"><strong>Present:</strong> <?php echo $stats['present']; ?></p>
                            <p style="color: var(--student-danger);"><strong>Absent:</strong> <?php echo $stats['absent']; ?></p>
                            <p style="color: var(--student-warning);"><strong>Late:</strong> <?php echo $stats['late']; ?></p>
                            <p><strong>Rate:</strong> <?php echo $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 1) : 0; ?>%</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p>No monthly data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>