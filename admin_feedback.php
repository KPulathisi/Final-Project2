<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Handle mark as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $feedback_id = $_GET['mark_read'];
    $query = "UPDATE feedback SET is_read = 1 WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$feedback_id]);
    header('Location: admin_feedback.php');
    exit();
}

// Get all feedback
$query = "SELECT * FROM feedback ORDER BY submitted_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - Admin Dashboard</title>
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
                <li><a href="admin_feedback.php" class="active">Feedback</a></li>
                <li><a href="admin_reports.php">Reports</a></li>
                <li><a href="admin_events.php">Events</a></li>
                <li><a href="admin_achievements.php">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Feedback Management</h2>
                <p>View and manage user feedback</p>
            </div>

            <!-- Feedback List -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">All Feedback</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback_list as $feedback): ?>
                        <tr style="<?php echo !$feedback['is_read'] ? 'background-color: #fff3cd;' : ''; ?>">
                            <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['email'] ?? 'N/A'); ?></td>
                            <td>
                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars(substr($feedback['message'], 0, 100)); ?>
                                    <?php if (strlen($feedback['message']) > 100): ?>...<?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($feedback['submitted_at'])); ?></td>
                            <td>
                                <span style="color: <?php echo $feedback['is_read'] ? 'var(--admin-success)' : 'var(--admin-warning)'; ?>;">
                                    <?php echo $feedback['is_read'] ? 'Read' : 'Unread'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$feedback['is_read']): ?>
                                    <a href="?mark_read=<?php echo $feedback['id']; ?>" 
                                       class="btn btn-primary" style="font-size: 0.8rem; padding: 0.3rem 0.8rem;">
                                        Mark as Read
                                    </a>
                                <?php endif; ?>
                                <button onclick="showFullMessage('<?php echo htmlspecialchars($feedback['message'], ENT_QUOTES); ?>')" 
                                        class="btn btn-primary" style="font-size: 0.8rem; padding: 0.3rem 0.8rem;">
                                    View Full
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($feedback_list)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No feedback submitted yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for full message -->
    <div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; max-width: 600px; width: 90%;">
            <h3>Full Message</h3>
            <div id="fullMessageContent" style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 5px; max-height: 300px; overflow-y: auto;"></div>
            <button onclick="closeModal()" class="btn btn-primary">Close</button>
        </div>
    </div>

    <script>
        function showFullMessage(message) {
            document.getElementById('fullMessageContent').textContent = message;
            document.getElementById('messageModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>