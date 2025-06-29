<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Require admin access
requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get statistics
$stats = [];

// Total users
$query = "SELECT COUNT(*) as count FROM users WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total students
$query = "SELECT COUNT(*) as count FROM students";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total teachers
$query = "SELECT COUNT(*) as count FROM teachers";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_teachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent feedback
$query = "SELECT COUNT(*) as count FROM feedback WHERE is_read = 0";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['unread_feedback'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

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
    <title>Admin Dashboard - Leads International</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
                <p><?php echo htmlspecialchars($user['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active">Dashboard</a></li>
                <li><a href="admin_users.php">User Management</a></li>
                <li><a href="admin_timetable.php">Timetable Management</a></li>
                <li><a href="admin_feedback.php">Feedback</a></li>
                <li><a href="admin_reports.php">Reports</a></li>
                <li><a href="admin_events.php">Events</a></li>
                <li><a href="admin_achievements.php">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h2>Administrator Dashboard</h2>
                <p>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_teachers']; ?></div>
                    <div class="stat-label">Teachers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['unread_feedback']; ?></div>
                    <div class="stat-label">Unread Feedback</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cards-grid">
                <div class="card">
                    <h3>User Management</h3>
                    <p>Add new users, manage existing accounts, and control access permissions.</p>
                    <a href="admin_users.php" class="btn btn-primary">Manage Users</a>
                </div>
                <div class="card">
                    <h3>Timetable Management</h3>
                    <p>Create and manage class timetables, assign teachers to subjects and periods.</p>
                    <a href="admin_timetable.php" class="btn btn-primary">Manage Timetables</a>
                </div>
                <div class="card">
                    <h3>System Reports</h3>
                    <p>Generate comprehensive reports on attendance, marks, and system usage.</p>
                    <a href="admin_reports.php" class="btn btn-primary">View Reports</a>
                </div>
                <div class="card">
                    <h3>Events & Achievements</h3>
                    <p>Manage school events and record student and staff achievements.</p>
                    <a href="admin_events.php" class="btn btn-primary">Manage Events</a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">Recent System Activity</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo date('Y-m-d H:i'); ?></td>
                            <td>System</td>
                            <td>Database initialized</td>
                            <td><span style="color: var(--admin-success);">Success</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('Y-m-d H:i'); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>Administrator login</td>
                            <td><span style="color: var(--admin-success);">Success</span></td>
                        </tr>
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