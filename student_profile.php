<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['student']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get student information
$query = "SELECT s.*, u.email FROM students s 
          JOIN users u ON s.user_id = u.id 
          WHERE s.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $contact_number = trim($_POST['contact_number']);
    $parent_name = trim($_POST['parent_name']);
    
    $parent_contact = trim($_POST['parent_contact']);
    $email = trim($_POST['email']);
    
    // Update student table
    $query = "UPDATE students SET contact_number = ?, parent_name = ?, parent_contact = ? WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $student_updated = $stmt->execute([$contact_number, $parent_name, $parent_contact, $user['id']]);
    
    // Update users table
    $query = "UPDATE users SET email = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $user_updated = $stmt->execute([$email, $user['id']]);
    
    if ($student_updated && $user_updated) {
        $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        // Refresh student data
        $query = "SELECT s.*, u.email FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user['id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>My Profile - Student Dashboard</title>
    <link rel="stylesheet" href="css/student.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Student Panel</h3>
                <p><?php echo htmlspecialchars($student['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php">Dashboard</a></li>
                <li><a href="student_attendance.php">My Attendance</a></li>
                <li><a href="student_marks.php">My Marks</a></li>
                <li><a href="student_timetable.php">Class Timetable</a></li>
                <li><a href="student_report.php">Report Card</a></li>
                <li><a href="student_profile.php" class="active">Profile</a></li>
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
                    <h3>Student Information</h3>
                    <div class="table-container">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td><strong>Student ID</strong></td>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Full Name</strong></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Class</strong></td>
                                    <td><?php echo htmlspecialchars($student['class']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Username</strong></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td><?php echo htmlspecialchars($student['email'] ?? 'Not provided'); ?></td>
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
                                   value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['contact_number'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Parent/Guardian Name</label>
                            <input type="text" name="parent_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['parent_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Parent/Guardian Contact</label>
                            <input type="text" name="parent_contact" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['parent_contact'] ?? ''); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Academic Statistics -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Academic Statistics</h3>
                <div style="padding: 2rem;">
                    <?php
                    // Get academic statistics
                    $stats = [];
                    
                    // Total marks and average
                    $query = "SELECT COUNT(*) as total_assessments, AVG(marks) as average_marks FROM marks WHERE student_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$student['id']]);
                    $marks_stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Attendance statistics
                    $query = "SELECT 
                                COUNT(*) as total_days,
                                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                              FROM attendance WHERE student_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$student['id']]);
                    $attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $attendance_percentage = $attendance_stats['total_days'] > 0 ? 
                        round(($attendance_stats['present_days'] / $attendance_stats['total_days']) * 100, 1) : 0;
                    ?>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Total Assessments</h4>
                            <p style="font-size: 2rem; color: var(--student-accent); font-weight: bold;"><?php echo $marks_stats['total_assessments']; ?></p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Average Marks</h4>
                            <p style="font-size: 2rem; color: var(--student-accent); font-weight: bold;"><?php echo round($marks_stats['average_marks'], 1); ?></p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Attendance Rate</h4>
                            <p style="font-size: 2rem; color: var(--student-accent); font-weight: bold;"><?php echo $attendance_percentage; ?>%</p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Days Present</h4>
                            <p style="font-size: 2rem; color: var(--student-accent); font-weight: bold;"><?php echo $attendance_stats['present_days']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="student_marks.php" class="btn btn-primary">View My Marks</a>
                        <a href="student_attendance.php" class="btn btn-primary">Check Attendance</a>
                        <a href="student_timetable.php" class="btn btn-primary">View Timetable</a>
                        <a href="student_report.php" class="btn btn-primary">Generate Report Card</a>
                    </div>
                </div>

                <div class="card">
                    <h3>Important Information</h3>
                    <div style="padding: 1rem;">
                        <p><strong>School Email:</strong> info@greenwoodacademy.edu</p>
                        <p><strong>School Phone:</strong> (555) 123-4567</p>
                        <p><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 4:00 PM</p>
                        <p><strong>Emergency Contact:</strong> (555) 123-4567 ext. 911</p>
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
            border-color: var(--student-accent);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--student-dark);
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