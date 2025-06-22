<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['teacher']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get teacher information
$query = "SELECT * FROM teachers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if teacher is a class teacher
if (!$teacher['is_class_teacher'] || !$teacher['class_assigned']) {
    header('Location: teacher_dashboard.php');
    exit();
}

// Get students in the assigned class
$query = "SELECT * FROM students WHERE class = ? ORDER BY full_name";
$stmt = $db->prepare($query);
$stmt->execute([$teacher['class_assigned']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get attendance statistics for each student
$attendance_stats = [];
foreach ($students as $student) {
    $query = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
              FROM attendance WHERE student_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$student['id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $attendance_percentage = $stats['total_days'] > 0 ? 
        round(($stats['present_days'] / $stats['total_days']) * 100, 1) : 0;
    
    $attendance_stats[$student['id']] = [
        'total_days' => $stats['total_days'],
        'present_days' => $stats['present_days'],
        'absent_days' => $stats['absent_days'],
        'percentage' => $attendance_percentage
    ];
}

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class - Teacher Dashboard</title>
    <link rel="stylesheet" href="css/teacher.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Teacher Panel</h3>
                <p><?php echo htmlspecialchars($teacher['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="teacher_dashboard.php">Dashboard</a></li>
                <li><a href="teacher_class.php" class="active">My Class</a></li>
                <li><a href="teacher_attendance.php">Mark Attendance</a></li>
                <li><a href="teacher_marks.php">Manage Marks</a></li>
                <li><a href="teacher_timetable.php">My Timetable</a></li>
                <li><a href="teacher_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>My Class - <?php echo htmlspecialchars($teacher['class_assigned']); ?></h2>
                <p>Manage students in your assigned class</p>
            </div>

            <!-- Class Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($students); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $total_percentage = 0;
                        $count = 0;
                        foreach ($attendance_stats as $stats) {
                            if ($stats['total_days'] > 0) {
                                $total_percentage += $stats['percentage'];
                                $count++;
                            }
                        }
                        echo $count > 0 ? round($total_percentage / $count, 1) : 0;
                        ?>%
                    </div>
                    <div class="stat-label">Average Attendance</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $good_attendance = 0;
                        foreach ($attendance_stats as $stats) {
                            if ($stats['percentage'] >= 75) $good_attendance++;
                        }
                        echo $good_attendance;
                        ?>
                    </div>
                    <div class="stat-label">Good Attendance (≥75%)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo htmlspecialchars($teacher['class_assigned']); ?></div>
                    <div class="stat-label">Class</div>
                </div>
            </div>

            <!-- Students List -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--teacher-primary), var(--teacher-secondary)); color: white;">Students in Class <?php echo htmlspecialchars($teacher['class_assigned']); ?></h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Parent Name</th>
                            <th>Parent Contact</th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['contact_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['parent_contact'] ?? 'N/A'); ?></td>
                            <td><?php echo $attendance_stats[$student['id']]['total_days']; ?></td>
                            <td style="color: var(--teacher-success);"><?php echo $attendance_stats[$student['id']]['present_days']; ?></td>
                            <td style="color: var(--teacher-danger);"><?php echo $attendance_stats[$student['id']]['absent_days']; ?></td>
                            <td>
                                <span style="color: <?php echo $attendance_stats[$student['id']]['percentage'] >= 75 ? 'var(--teacher-success)' : 'var(--teacher-danger)'; ?>;">
                                    <?php echo $attendance_stats[$student['id']]['percentage']; ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem;">
                                No students assigned to this class yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Mark Today's Attendance</h3>
                    <p>Quickly mark attendance for today's date.</p>
                    <a href="teacher_attendance.php?class=<?php echo urlencode($teacher['class_assigned']); ?>&date=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">Mark Attendance</a>
                </div>
                <div class="card">
                    <h3>Add Marks</h3>
                    <p>Add or update marks for your subjects.</p>
                    <a href="teacher_marks.php?class=<?php echo urlencode($teacher['class_assigned']); ?>" class="btn btn-primary">Manage Marks</a>
                </div>
                <div class="card">
                    <h3>Class Performance</h3>
                    <p>View detailed performance analytics for your class.</p>
                    <button class="btn btn-primary" onclick="showPerformanceModal()">View Analytics</button>
                </div>
            </div>
        </main>
    </div>

    <!-- Performance Modal -->
    <div id="performanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; max-width: 600px; width: 90%; max-height: 80%; overflow-y: auto;">
            <h3>Class Performance Analytics</h3>
            <div style="margin: 1rem 0;">
                <h4>Attendance Summary</h4>
                <ul>
                    <li>Students with excellent attendance (≥90%): 
                        <?php 
                        $excellent = 0;
                        foreach ($attendance_stats as $stats) {
                            if ($stats['percentage'] >= 90) $excellent++;
                        }
                        echo $excellent;
                        ?>
                    </li>
                    <li>Students with good attendance (75-89%): 
                        <?php 
                        $good = 0;
                        foreach ($attendance_stats as $stats) {
                            if ($stats['percentage'] >= 75 && $stats['percentage'] < 90) $good++;
                        }
                        echo $good;
                        ?>
                    </li>
                    <li>Students with poor attendance (<75%): 
                        <?php 
                        $poor = 0;
                        foreach ($attendance_stats as $stats) {
                            if ($stats['percentage'] < 75) $poor++;
                        }
                        echo $poor;
                        ?>
                    </li>
                </ul>
            </div>
            <button onclick="closePerformanceModal()" class="btn btn-primary">Close</button>
        </div>
    </div>

    <script>
        function showPerformanceModal() {
            document.getElementById('performanceModal').style.display = 'block';
        }
        
        function closePerformanceModal() {
            document.getElementById('performanceModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('performanceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePerformanceModal();
            }
        });
    </script>
</body>
</html>