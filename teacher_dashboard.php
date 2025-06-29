<?php
date_default_timezone_set('Asia/Kolkata'); // Set to your desired timezone
require_once 'config/database.php';
require_once 'includes/session.php';

// Require teacher access
requireUserType(['teacher']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get teacher information
$query = "SELECT * FROM teachers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Get statistics for this teacher
$stats = [];

// Students in assigned class (if class teacher)
if ($teacher['is_class_teacher'] && $teacher['class_assigned']) {
    $query = "SELECT COUNT(*) as count FROM students WHERE class = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$teacher['class_assigned']]);
    $stats['class_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} else {
    $stats['class_students'] = 0;
}

// Recent attendance marked
$query = "SELECT COUNT(*) as count FROM attendance WHERE marked_by = ? AND date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $db->prepare($query);
$stmt->execute([$teacher['id']]);
$stats['recent_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

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
    <title>Teacher Dashboard - Leeds International</title>
    <link rel="stylesheet" href="css/teacher.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Teacher Panel</h3>
                <p><?php echo htmlspecialchars($teacher['full_name']); ?></p>
                <small><?php echo htmlspecialchars($teacher['teacher_id']); ?></small>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active">Dashboard</a></li>
                <?php if ($teacher['is_class_teacher']): ?>
                    <li><a href="teacher_class.php">My Class</a></li>
                    <li><a href="teacher_attendance.php">Mark Attendance</a></li>
                <?php endif; ?>
                <li><a href="teacher_marks.php">Manage Marks</a></li>
                <li><a href="teacher_timetable.php">My Timetable</a></li>
                <li><a href="teacher_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h2>Teacher Dashboard</h2>
                <p>Welcome back, <?php echo htmlspecialchars($teacher['full_name']); ?>!</p>
                <?php if ($teacher['is_class_teacher']): ?>
                    <p><strong>Class Teacher for:</strong> <?php echo htmlspecialchars($teacher['class_assigned']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <?php if ($teacher['is_class_teacher']): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['class_students']; ?></div>
                        <div class="stat-label">Students in My Class</div>
                    </div>
                <?php endif; ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['recent_attendance']; ?></div>
                    <div class="stat-label">Attendance Marked (7 days)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo ucfirst($teacher['level']); ?></div>
                    <div class="stat-label">Teacher Level</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo date('H:i'); ?></div>
                    <div class="stat-label">Current Time</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cards-grid">
                <?php if ($teacher['is_class_teacher']): ?>
                    <div class="card">
                        <h3>Mark Attendance</h3>
                        <p>Mark daily attendance for students in class <?php echo htmlspecialchars($teacher['class_assigned']); ?>.</p>
                        <a href="teacher_attendance.php" class="btn btn-primary">Mark Attendance</a>
                    </div>
                    <div class="card">
                        <h3>Manage Class</h3>
                        <p>View and manage students in your assigned class.</p>
                        <a href="teacher_class.php" class="btn btn-primary">Manage Class</a>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <h3>Manage Marks</h3>
                    <p>Add and update student marks for your subjects.</p>
                    <a href="teacher_marks.php" class="btn btn-primary">Manage Marks</a>
                </div>
                <div class="card">
                    <h3>View Timetable</h3>
                    <p>Check your teaching schedule and assigned periods.</p>
                    <a href="teacher_timetable.php" class="btn btn-primary">View Timetable</a>
                </div>
            </div>

            <!-- Teacher Information -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--teacher-primary), var(--teacher-secondary)); color: white;">My Information</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Teacher ID</strong></td>
                            <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Full Name</strong></td>
                            <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Level</strong></td>
                            <td><?php echo ucfirst($teacher['level']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Subjects</strong></td>
                            <td><?php echo htmlspecialchars($teacher['subjects'] ?? 'Not specified'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Contact Number</strong></td>
                            <td><?php echo htmlspecialchars($teacher['contact_number'] ?? 'Not provided'); ?></td>
                        </tr>
                        <?php if ($teacher['is_class_teacher']): ?>
                            <tr>
                                <td><strong>Assigned Class</strong></td>
                                <td><?php echo htmlspecialchars($teacher['class_assigned']); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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