<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['other_staff']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get staff information
$query = "SELECT os.*, u.email FROM other_staff os 
          JOIN users u ON os.user_id = u.id 
          WHERE os.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $contact_number = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    
    // Update staff table
    $query = "UPDATE other_staff SET contact_number = ? WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $staff_updated = $stmt->execute([$contact_number, $user['id']]);
    
    // Update users table
    $query = "UPDATE users SET email = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $user_updated = $stmt->execute([$email, $user['id']]);
    
    if ($staff_updated && $user_updated) {
        $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        // Refresh staff data
        $query = "SELECT os.*, u.email FROM other_staff os 
                  JOIN users u ON os.user_id = u.id 
                  WHERE os.user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user['id']]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = '<div class="alert alert-danger">Error updating profile.</div>';
    }
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
    <title>My Profile - Staff Dashboard</title>
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
                <li><a href="staff_profile.php" class="active">Profile</a></li>
                <li><a href="staff_announcements.php">Announcements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>My Profile</h2>
                <p>View and update your profile information</p>
            </div>

            <?php echo $message; ?>

            <!-- Profile Information -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Staff Information</h3>
                    <div class="table-container">
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
                                    <td><strong>Username</strong></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td><?php echo htmlspecialchars($staff['email'] ?? 'Not provided'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Number</strong></td>
                                    <td><?php echo htmlspecialchars($staff['contact_number'] ?? 'Not provided'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h3>Update Contact Information</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['contact_number'] ?? ''); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Work Information -->
            <div class="card">
                <h3>Work Information</h3>
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 5px;">
                            <h4 style="color: var(--staff-primary);">Department</h4>
                            <p style="font-size: 1.2rem; color: var(--staff-accent); font-weight: bold;">
                                <?php 
                                $position = $staff['position'] ?? 'Staff';
                                if (strpos(strtolower($position), 'librarian') !== false) echo 'Library';
                                elseif (strpos(strtolower($position), 'accountant') !== false) echo 'Finance';
                                elseif (strpos(strtolower($position), 'nurse') !== false) echo 'Health';
                                elseif (strpos(strtolower($position), 'security') !== false) echo 'Security';
                                else echo 'Administration';
                                ?>
                            </p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 5px;">
                            <h4 style="color: var(--staff-primary);">Position</h4>
                            <p style="font-size: 1.2rem; color: var(--staff-accent); font-weight: bold;"><?php echo htmlspecialchars($staff['position'] ?? 'Staff Member'); ?></p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 5px;">
                            <h4 style="color: var(--staff-primary);">Work Schedule</h4>
                            <p style="font-size: 1.2rem; color: var(--staff-accent); font-weight: bold;">Mon-Fri<br>8:00 AM - 4:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="staff_announcements.php" class="btn btn-primary">View Announcements</a>
                        <a href="other_staff_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                        <button onclick="window.print()" class="btn btn-primary">Print Profile</button>
                    </div>
                </div>

                <div class="card">
                    <h3>Important Contacts</h3>
                    <div style="padding: 1rem;">
                        <p><strong>Administration:</strong> 047-123-4567 ext. 100</p>
                        <p><strong>IT Support:</strong> 047-123-4567 ext. 200</p>
                        <p><strong>Human Resources:</strong> 047-123-4567 ext. 300</p>
                        <p><strong>Emergency:</strong> 047-123-4567 ext. 911</p>
                        <p><strong>Main Office:</strong> info@leedsinternational.edu</p>
                    </div>
                </div>
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
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--staff-accent);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--staff-dark);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
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