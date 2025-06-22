<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['teacher']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get teacher information
$query = "SELECT * FROM teachers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';

// Handle attendance submission
if ($_POST && isset($_POST['mark_attendance'])) {
    $date = $_POST['date'];
    $class = $_POST['class'];
    
    // Delete existing attendance for this date and class
    $delete_query = "DELETE FROM attendance WHERE date = ? AND class = ?";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->execute([$date, $class]);
    
    // Insert new attendance records
    foreach ($_POST['attendance'] as $student_id => $status) {
        $query = "INSERT INTO attendance (student_id, class, date, status, marked_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$student_id, $class, $date, $status, $teacher['id']]);
    }
    
    $message = '<div class="alert alert-success">Attendance marked successfully!</div>';
}

// Get students for the selected class
$students = [];
$selected_class = $_GET['class'] ?? $teacher['class_assigned'] ?? '';
$selected_date = $_GET['date'] ?? date('Y-m-d');

if ($selected_class) {
    $query = "SELECT * FROM students WHERE class = ? ORDER BY full_name";
    $stmt = $db->prepare($query);
    $stmt->execute([$selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get existing attendance for the selected date
    $existing_attendance = [];
    $query = "SELECT student_id, status FROM attendance WHERE class = ? AND date = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$selected_class, $selected_date]);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($attendance_records as $record) {
        $existing_attendance[$record['student_id']] = $record['status'];
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
    <title>Mark Attendance - Teacher Dashboard</title>
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
                <li><a href="teacher_attendance.php" class="active">Mark Attendance</a></li>
                <li><a href="teacher_marks.php">Manage Marks</a></li>
                <li><a href="teacher_timetable.php">My Timetable</a></li>
                <li><a href="teacher_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Mark Attendance</h2>
                <p>Mark daily attendance for students</p>
            </div>

            <?php echo $message; ?>

            <!-- Class and Date Selection -->
            <div class="card">
                <h3>Select Class and Date</h3>
                <form method="GET">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <label>Class</label>
                            <select name="class" class="form-control" required>
                                <option value="">Select Class</option>
                                <?php if ($teacher['is_class_teacher'] && $teacher['class_assigned']): ?>
                                    <option value="<?php echo $teacher['class_assigned']; ?>" <?php echo ($selected_class == $teacher['class_assigned']) ? 'selected' : ''; ?>>
                                        <?php echo $teacher['class_assigned']; ?>
                                    </option>
                                <?php endif; ?>
                                <option value="9A" <?php echo ($selected_class == '9A') ? 'selected' : ''; ?>>9A</option>
                                <option value="9B" <?php echo ($selected_class == '9B') ? 'selected' : ''; ?>>9B</option>
                                <option value="10A" <?php echo ($selected_class == '10A') ? 'selected' : ''; ?>>10A</option>
                                <option value="10B" <?php echo ($selected_class == '10B') ? 'selected' : ''; ?>>10B</option>
                                <option value="11A" <?php echo ($selected_class == '11A') ? 'selected' : ''; ?>>11A</option>
                                <option value="11B" <?php echo ($selected_class == '11B') ? 'selected' : ''; ?>>11B</option>
                                <option value="12A" <?php echo ($selected_class == '12A') ? 'selected' : ''; ?>>12A</option>
                                <option value="12B" <?php echo ($selected_class == '12B') ? 'selected' : ''; ?>>12B</option>
                            </select>
                        </div>
                        <div>
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" required>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Load Students</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (!empty($students)): ?>
            <!-- Attendance Form -->
            <div class="card">
                <h3>Mark Attendance for <?php echo htmlspecialchars($selected_class); ?> - <?php echo date('F j, Y', strtotime($selected_date)); ?></h3>
                <form method="POST">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($selected_class); ?>">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td>
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                               <?php echo (isset($existing_attendance[$student['id']]) && $existing_attendance[$student['id']] == 'present') ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"
                                               <?php echo (isset($existing_attendance[$student['id']]) && $existing_attendance[$student['id']] == 'absent') ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late"
                                               <?php echo (isset($existing_attendance[$student['id']]) && $existing_attendance[$student['id']] == 'late') ? 'checked' : ''; ?>>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem; text-align: center;">
                        <button type="button" onclick="markAllPresent()" class="btn btn-primary" style="margin-right: 1rem;">Mark All Present</button>
                        <button type="submit" name="mark_attendance" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
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
            border-color: var(--teacher-accent);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--teacher-dark);
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
        
        input[type="radio"] {
            transform: scale(1.2);
            margin: 0;
        }
        
        .table td {
            text-align: center;
        }
        
        .table td:nth-child(2) {
            text-align: left;
        }
    </style>

    <script>
        function markAllPresent() {
            const presentRadios = document.querySelectorAll('input[type="radio"][value="present"]');
            presentRadios.forEach(radio => {
                radio.checked = true;
            });
        }
    </script>
</body>
</html>