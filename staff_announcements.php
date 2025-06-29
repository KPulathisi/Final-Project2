<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['other_staff']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get staff information
$query = "SELECT * FROM other_staff WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent events (as announcements)
$query = "SELECT * FROM events WHERE start_date >= CURDATE() ORDER BY start_date ASC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent achievements (as announcements)
$query = "SELECT * FROM achievements ORDER BY date DESC LIMIT 10";
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
    <title>Announcements - Staff Dashboard</title>
    <link rel="stylesheet" href="css/staff.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Panel</h3>
                <p><?php echo htmlspecialchars($staff['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="other_staff_dashboard.php">Dashboard</a></li>
                <li><a href="staff_profile.php">Profile</a></li>
                <li><a href="staff_announcements.php" class="active">Announcements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>School Announcements</h2>
                <p>Stay updated with the latest school news and events</p>
            </div>

            <!-- Upcoming Events -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--staff-primary), var(--staff-secondary)); color: white;">Upcoming Events</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                            <td>
                                <?php echo date('M j, Y', strtotime($event['start_date'])); ?><br>
                                <small><?php echo date('g:i A', strtotime($event['start_date'])); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($event['location'] ?? 'TBA'); ?></td>
                            <td>
                                <div style="max-width: 300px;">
                                    <?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>
                                    <?php if (strlen($event['description']) > 100): ?>...<?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem;">
                                No upcoming events at this time.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Achievements -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--staff-primary), var(--staff-secondary)); color: white;">Recent Achievements</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Achievement</th>
                            <th>Type</th>
                            <th>Recipient</th>
                            <th>Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achievements as $achievement): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($achievement['title']); ?></strong></td>
                            <td>
                                <span style="color: <?php echo $achievement['type'] == 'student' ? 'var(--staff-accent)' : 'var(--staff-success)'; ?>;">
                                    <?php echo ucfirst($achievement['type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($achievement['recipient_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($achievement['date'])); ?></td>
                            <td>
                                <div style="max-width: 250px;">
                                    <?php echo htmlspecialchars(substr($achievement['description'], 0, 80)); ?>
                                    <?php if (strlen($achievement['description']) > 80): ?>...<?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($achievements)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                No recent achievements to display.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- General Announcements -->
            <div class="cards-grid">
                <div class="card">
                    <h3>General Announcements</h3>
                    <div style="padding: 1rem;">
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                            <h4 style="color: #856404; margin-bottom: 0.5rem;">Staff Meeting Reminder</h4>
                            <p style="color: #856404; margin: 0;">Monthly staff meeting scheduled for the last Friday of the month at 3:00 PM in the conference room.</p>
                        </div>
                        
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #d4edda; border-left: 4px solid #28a745; border-radius: 5px;">
                            <h4 style="color: #155724; margin-bottom: 0.5rem;">New Safety Protocols</h4>
                            <p style="color: #155724; margin: 0;">Please review the updated safety protocols document sent to your email. Implementation begins next week.</p>
                        </div>
                        
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #d1ecf1; border-left: 4px solid #17a2b8; border-radius: 5px;">
                            <h4 style="color: #0c5460; margin-bottom: 0.5rem;">System Maintenance</h4>
                            <p style="color: #0c5460; margin: 0;">The school management system will undergo maintenance this Saturday from 2:00 AM to 6:00 AM.</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>Important Reminders</h3>
                    <div style="padding: 1rem;">
                        <ul style="list-style-type: none; padding: 0;">
                            <li style="margin-bottom: 1rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                <strong>📅 Timesheet Submission:</strong> Submit your monthly timesheet by the 25th of each month.
                            </li>
                            <li style="margin-bottom: 1rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                <strong>🔐 Password Policy:</strong> Change your system password every 90 days for security.
                            </li>
                            <li style="margin-bottom: 1rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                <strong>📞 Emergency Procedures:</strong> Familiarize yourself with emergency evacuation routes.
                            </li>
                            <li style="margin-bottom: 1rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                <strong>📧 Email Etiquette:</strong> Use professional email signatures and reply within 24 hours.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card">
                <h3>Need Help or Have Questions?</h3>
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 5px;">
                            <h4 style="color: var(--staff-primary);">Administration</h4>
                            <p><strong>047-123-4567 ext. 100</strong></p>
                            <small>General inquiries and support</small>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 5px;">
                            <h4 style="color: var(--staff-primary);">IT Support</h4>
                            <p><strong>047-123-4567 ext. 200</strong></p>
                            <small>Technical issues and system help</small>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 5px;">
                            <h4 style="color: var(--staff-primary);">Human Resources</h4>
                            <p><strong>047-123-4567 ext. 300</strong></p>
                            <small>HR policies and procedures</small>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>