<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

$message = '';

// Handle form submission
if ($_POST && isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = trim($_POST['location']);
    
    $query = "INSERT INTO events (title, description, start_date, end_date, location, added_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$title, $description, $start_date, $end_date, $location, $user['id']])) {
        $message = '<div class="alert alert-success">Event added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding event.</div>';
    }
}

// Get all events
$query = "SELECT e.*, u.username as added_by_user FROM events e 
          LEFT JOIN users u ON e.added_by = u.id 
          ORDER BY e.start_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - Admin Dashboard</title>
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
                <li><a href="admin_events.php" class="active">Events</a></li>
                <li><a href="admin_achievements.php">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Events Management</h2>
                <p>Add and manage school events</p>
            </div>

            <?php echo $message; ?>

            <!-- Add Event Form -->
            <div class="card">
                <h3>Add New Event</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <div>
                            <label>Event Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div>
                            <label>Location</label>
                            <input type="text" name="location" class="form-control" placeholder="Event location">
                        </div>
                        <div>
                            <label>Start Date & Time *</label>
                            <input type="datetime-local" name="start_date" class="form-control" required>
                        </div>
                        <div>
                            <label>End Date & Time *</label>
                            <input type="datetime-local" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <label>Description *</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Event description"></textarea>
                    </div>
                    <div style="margin-top: 2rem;">
                        <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                    </div>
                </form>
            </div>

            <!-- Events List -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">All Events</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($event['start_date'])); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($event['end_date'])); ?></td>
                            <td><?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars(substr($event['description'], 0, 50)); ?>
                                    <?php if (strlen($event['description']) > 50): ?>...<?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($event['added_by_user']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No events added yet.
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