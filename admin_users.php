<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $user_type = $_POST['user_type'];
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        
        // Insert user
        $query = "INSERT INTO users (username, password, user_type, email) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$username, $password, $user_type, $email])) {
            $user_id = $db->lastInsertId();
            
            // Insert into appropriate table based on user type
            if ($user_type == 'teacher') {
                $teacher_id = $_POST['teacher_id'];
                $level = $_POST['level'];
                $subjects = $_POST['subjects'];
                $is_class_teacher = isset($_POST['is_class_teacher']) ? 1 : 0;
                $class_assigned = $_POST['class_assigned'] ?? null;
                
                $query = "INSERT INTO teachers (user_id, teacher_id, full_name, level, subjects, is_class_teacher, class_assigned) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$user_id, $teacher_id, $full_name, $level, $subjects, $is_class_teacher, $class_assigned]);
            } elseif ($user_type == 'student') {
                $student_id = $_POST['student_id'];
                $class = $_POST['class'];
                $parent_name = $_POST['parent_name'];
                $parent_contact = $_POST['parent_contact'];
                
                $query = "INSERT INTO students (user_id, student_id, full_name, class, parent_name, parent_contact) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$user_id, $student_id, $full_name, $class, $parent_name, $parent_contact]);
            } elseif ($user_type == 'other_staff') {
                $staff_id = $_POST['staff_id'];
                $position = $_POST['position'];
                
                $query = "INSERT INTO other_staff (user_id, staff_id, full_name, position) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$user_id, $staff_id, $full_name, $position]);
            }
            
            $message = '<div class="alert alert-success">User added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding user.</div>';
        }
    } elseif (isset($_POST['toggle_status'])) {
        $user_id = $_POST['user_id'];
        $current_status = $_POST['current_status'];
        $new_status = $current_status ? 0 : 1;
        
        $query = "UPDATE users SET is_active = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$new_status, $user_id])) {
            $message = '<div class="alert alert-success">User status updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating user status.</div>';
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$user_id])) {
            $message = '<div class="alert alert-success">User deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting user.</div>';
        }
    }
}

// Get all users
$query = "SELECT u.*, 
          CASE 
            WHEN u.user_type = 'teacher' THEN t.full_name
            WHEN u.user_type = 'student' THEN s.full_name
            WHEN u.user_type = 'other_staff' THEN os.full_name
            ELSE u.username
          END as full_name
          FROM users u
          LEFT JOIN teachers t ON u.id = t.user_id
          LEFT JOIN students s ON u.id = s.user_id
          LEFT JOIN other_staff os ON u.id = os.user_id
          ORDER BY u.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
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
                <li><a href="admin_users.php" class="active">User Management</a></li>
                <li><a href="admin_timetable.php">Timetable Management</a></li>
                <li><a href="admin_feedback.php">Feedback</a></li>
                <li><a href="admin_reports.php">Reports</a></li>
                <li><a href="admin_events.php">Events</a></li>
                <li><a href="admin_achievements.php">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>User Management</h2>
                <p>Add, edit, and manage system users</p>
            </div>

            <?php echo $message; ?>

            <!-- Add User Form -->
            <div class="card">
                <h3>Add New User</h3>
                <form method="POST" id="addUserForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <div>
                            <label>Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div>
                            <label>Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div>
                            <label>User Type *</label>
                            <select name="user_type" class="form-control" required onchange="showUserTypeFields(this.value)">
                                <option value="">Select Type</option>
                                <option value="admin">Administrator</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="other_staff">Other Staff</option>
                            </select>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div>
                            <label>Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                    </div>

                    <!-- Teacher specific fields -->
                    <div id="teacher_fields" style="display: none; margin-top: 1rem;">
                        <h4>Teacher Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <div>
                                <label>Teacher ID</label>
                                <input type="text" name="teacher_id" class="form-control">
                            </div>
                            <div>
                                <label>Level</label>
                                <select name="level" class="form-control">
                                    <option value="ordinary">Ordinary</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            <div>
                                <label>Subjects</label>
                                <input type="text" name="subjects" class="form-control" placeholder="e.g., Mathematics, Physics">
                            </div>
                            <div>
                                <label>Class Assigned</label>
                                <input type="text" name="class_assigned" class="form-control" placeholder="e.g., 10A">
                            </div>
                        </div>
                        <div style="margin-top: 1rem;">
                            <label>
                                <input type="checkbox" name="is_class_teacher"> Is Class Teacher
                            </label>
                        </div>
                    </div>

                    <!-- Student specific fields -->
                    <div id="student_fields" style="display: none; margin-top: 1rem;">
                        <h4>Student Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <div>
                                <label>Student ID</label>
                                <input type="text" name="student_id" class="form-control">
                            </div>
                            <div>
                                <label>Class</label>
                                <input type="text" name="class" class="form-control" placeholder="e.g., 10A">
                            </div>
                            <div>
                                <label>Parent Name</label>
                                <input type="text" name="parent_name" class="form-control">
                            </div>
                            <div>
                                <label>Parent Contact</label>
                                <input type="text" name="parent_contact" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Other Staff specific fields -->
                    <div id="staff_fields" style="display: none; margin-top: 1rem;">
                        <h4>Staff Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <div>
                                <label>Staff ID</label>
                                <input type="text" name="staff_id" class="form-control">
                            </div>
                            <div>
                                <label>Position</label>
                                <input type="text" name="position" class="form-control" placeholder="e.g., Librarian, Accountant">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>

            <!-- Users List -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white;">All Users</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>User Type</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $u['user_type'])); ?></td>
                            <td><?php echo htmlspecialchars($u['email'] ?? 'N/A'); ?></td>
                            <td>
                                <span style="color: <?php echo $u['is_active'] ? 'var(--admin-success)' : 'var(--admin-danger)'; ?>;">
                                    <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $u['is_active']; ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.3rem 0.8rem;">
                                        <?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                <?php if ($u['user_type'] != 'admin'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn" style="background: var(--admin-danger); color: white; font-size: 0.8rem; padding: 0.3rem 0.8rem;">
                                        Delete
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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

    <script>
        function showUserTypeFields(userType) {
            // Hide all specific fields
            document.getElementById('teacher_fields').style.display = 'none';
            document.getElementById('student_fields').style.display = 'none';
            document.getElementById('staff_fields').style.display = 'none';
            
            // Show relevant fields
            if (userType === 'teacher') {
                document.getElementById('teacher_fields').style.display = 'block';
            } else if (userType === 'student') {
                document.getElementById('student_fields').style.display = 'block';
            } else if (userType === 'other_staff') {
                document.getElementById('staff_fields').style.display = 'block';
            }
        }
    </script>
</body>
</html>