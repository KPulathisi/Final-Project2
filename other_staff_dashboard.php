<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Require other staff access
requireUserType(['other_staff']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get staff information
$query = "SELECT * FROM other_staff WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>Staff Dashboard - Leeds International</title>
    <link rel="stylesheet" href="css/staff.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Panel</h3>
                <p><?php echo htmlspecialchars($staff['full_name']); ?></p>
                <small><?php echo htmlspecialchars($staff['staff_id']); ?></small>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active">Dashboard</a></li>
                <li><a href="staff_profile.php">Profile</a></li>
                <li><a href="staff_announcements.php">Announcements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h2>Staff Dashboard</h2>
                <p>Welcome back, <?php echo htmlspecialchars($staff['full_name']); ?>!</p>
                <p><strong>Position:</strong> <?php echo htmlspecialchars($staff['position'] ?? 'Staff Member'); ?></p>
            </div>

            <!-- Quick Information -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Profile Information</h3>
                    <p>View and update your personal profile information.</p>
                    <a href="staff_profile.php" class="btn btn-primary">View Profile</a>
                </div>
                <div class="card">
                    <h3>School Announcements</h3>
                    <p>Stay updated with the latest school news and announcements.</p>
                    <a href="staff_announcements.php" class="btn btn-primary">View Announcements</a>
                </div>
                <div class="card">
                    <h3>Contact Information</h3>
                    <p>Find contact details for administration and other departments.</p>
                    <a href="#contact-info" class="btn btn-primary">View Contacts</a>
                </div>
                <div class="card">
                    <h3>Help & Support</h3>
                    <p>Get help with system usage and report technical issues.</p>
                    <a href="#help" class="btn btn-primary">Get Help</a>
                </div>
            </div>

            <!-- Staff Information -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--staff-primary), var(--staff-secondary)); color: white;">My Information</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Staff ID</strong></td>
                            <td><?php echo htmlspecialchars($staff['staff_id']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Full Name</strong></td>
                            <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Position</strong></td>
                            <td><?php echo htmlspecialchars($staff['position'] ?? 'Staff Member'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Contact Number</strong></td>
                            <td><?php echo htmlspecialchars($staff['contact_number'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td><?php echo htmlspecialchars($user['username']); ?>@leedsinternational.edu</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Contact Information -->
            <div class="table-container" id="contact-info">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--staff-primary), var(--staff-secondary)); color: white;">Important Contacts</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Administration Office</strong></td>
                            <td>047-123-4567 ext. 100</td>
                        </tr>
                        <tr>
                            <td><strong>IT Support</strong></td>
                            <td>047-123-4567 ext. 200</td>
                        </tr>
                        <tr>
                            <td><strong>Human Resources</strong></td>
                            <td>047-123-4567 ext. 300</td>
                        </tr>
                        <tr>
                            <td><strong>Emergency Contact</strong></td>
                            <td>047-123-4567 ext. 911</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Help Section -->
            <div class="card" id="help">
                <h3>Help & Support</h3>
                <p>If you need assistance with the system or have any questions, please contact:</p>
                <ul style="margin: 1rem 0; padding-left: 2rem;">
                    <li><strong>Technical Issues:</strong> IT Support at ext. 200</li>
                    <li><strong>Account Issues:</strong> Administration at ext. 100</li>
                    <li><strong>General Questions:</strong> Human Resources at ext. 300</li>
                </ul>
                <p>You can also email us at <strong>support@leedsinternational.edu</strong> for any assistance.</p>
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