<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

$message = '';

// Handle form submission
if ($_POST && isset($_POST['add_achievement'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $recipient_name = trim($_POST['recipient_name']);
    $date = $_POST['date'];
    
    $query = "INSERT INTO achievements (title, description, type, recipient_name, date, added_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$title, $description, $type, $recipient_name, $date, $user['id']])) {
        $message = '<div class="alert alert-success">Achievement added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding achievement.</div>';
    }
}

// Get all achievements
$query = "SELECT a.*, u.username as added_by_user FROM achievements a 
          LEFT JOIN users u ON a.added_by = u.id 
          ORDER BY a.date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements Management - Admin Dashboard</title>
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
                <li><a href="admin_reports.php">Reports</a></li>
                <li><a href="admin_events.php">Events</a></li>
                <li><a href="admin_achievements.php" class="active">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Achievements Management</h2>
                <p>Add and manage student and staff achievements</p>
            </div>

            <?php echo $message; ?>

            <!-- Add Achievement Form -->
            <div class="card">
                <h3>Add New Achievement</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <div>
                            <label>Achievement Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div>
                            <label>Type *</label>
                            <select name="type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="student">Student Achievement</option>
                                <option value="staff">Staff Achievement</option>
                            </select>
                        </div>
                        <div>
                            <label>Recipient Name *</label>
                            <input type="text" name="recipient_name" class="form-control" required placeholder="Name of the recipient">
                        </div>
                        <div>
                            <label>Date *</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <label>Description *</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Achievement description"></textarea>
                    </div>
                    <div style="margin-top: 2rem;">
                        <button type="submit" name="add_achievement" class="btn btn-primary">Add Achievement</button>
                    </div>
                </form>
            </div>

            <!-- Achievements List -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">All Achievements</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Recipient</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achievements as $achievement): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                            <td>
                                <span style="color: <?php echo $achievement['type'] == 'student' ? 'var(--admin-accent)' : 'var(--admin-success)'; ?>;">
                                    <?php echo ucfirst($achievement['type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($achievement['recipient_name']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($achievement['date'])); ?></td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars(substr($achievement['description'], 0, 50)); ?>
                                    <?php if (strlen($achievement['description']) > 50): ?>...<?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($achievement['added_by_user']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($achievements)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No achievements added yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <style>
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--admin-accent);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--admin-dark);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</body>
</html>