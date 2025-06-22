<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['teacher']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get teacher information
$query = "SELECT t.*, u.email FROM teachers t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    
    // Update teacher table
    $query = "UPDATE teachers SET contact_number = ?, address = ? WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $teacher_updated = $stmt->execute([$contact_number, $address, $user['id']]);
    
    // Update users table
    $query = "UPDATE users SET email = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $user_updated = $stmt->execute([$email, $user['id']]);
    
    if ($teacher_updated && $user_updated) {
        $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        // Refresh teacher data
        $query = "SELECT t.*, u.email FROM teachers t 
                  JOIN users u ON t.user_id = u.id 
                  WHERE t.user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user['id']]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>My Profile - Teacher Dashboard</title>
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
                <li><a href="teacher_class.php">My Class</a></li>
                <li><a href="teacher_attendance.php">Mark Attendance</a></li>
                <li><a href="teacher_marks.php">Manage Marks</a></li>
                <li><a href="teacher_timetable.php">My Timetable</a></li>
                <li><a href="teacher_profile.php" class="active">Profile</a></li>
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
                    <h3>Basic Information</h3>
                    <div class="table-container">
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
                                    <td><strong>Class Teacher</strong></td>
                                    <td><?php echo $teacher['is_class_teacher'] ? 'Yes' : 'No'; ?></td>
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
                </div>

                <div class="card">
                    <h3>Update Contact Information</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($teacher['contact_number'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($teacher['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Teaching Statistics -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--teacher-primary), var(--teacher-secondary)); color: white;">Teaching Statistics</h3>
                <div style="padding: 2rem;">
                    <?php
                    // Get teaching statistics
                    $stats = [];
                    
                    // Total classes taught
                    $query = "SELECT COUNT(DISTINCT class) as total_classes FROM timetables 
                              WHERE period1_teacher_id = ? OR period2_teacher_id = ? OR period3_teacher_id = ? OR period4_teacher_id = ?
                                 OR period5_teacher_id = ? OR period6_teacher_id = ? OR period7_teacher_id = ? OR period8_teacher_id = ?";
                    $params = array_fill(0, 8, $teacher['id']);
                    $stmt = $db->prepare($query);
                    $stmt->execute($params);
                    $stats['total_classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_classes'];
                    
                    // Total attendance marked
                    $query = "SELECT COUNT(*) as total_attendance FROM attendance WHERE marked_by = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$teacher['id']]);
                    $stats['total_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_attendance'];
                    
                    // Total marks entered
                    $query = "SELECT COUNT(*) as total_marks FROM marks WHERE added_by = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$teacher['id']]);
                    $stats['total_marks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_marks'];
                    ?>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f0f8f0; border-radius: 5px;">
                            <h4 style="color: var(--teacher-primary);">Classes Teaching</h4>
                            <p style="font-size: 2rem; color: var(--teacher-accent); font-weight: bold;"><?php echo $stats['total_classes']; ?></p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0f8f0; border-radius: 5px;">
                            <h4 style="color: var(--teacher-primary);">Attendance Records</h4>
                            <p style="font-size: 2rem; color: var(--teacher-accent); font-weight: bold;"><?php echo $stats['total_attendance']; ?></p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0f8f0; border-radius: 5px;">
                            <h4 style="color: var(--teacher-primary);">Marks Entered</h4>
                            <p style="font-size: 2rem; color: var(--teacher-accent); font-weight: bold;"><?php echo $stats['total_marks']; ?></p>
                        </div>
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
            border-color: var(--teacher-accent);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--teacher-dark);
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