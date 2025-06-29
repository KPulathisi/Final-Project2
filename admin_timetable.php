<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['admin']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

$message = '';

// Handle form submission
if ($_POST && isset($_POST['save_timetable'])) {
    $class = $_POST['class'];
    $day = $_POST['day'];
    
    // Check if timetable exists for this class and day
    $check_query = "SELECT id FROM timetables WHERE class = ? AND day = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$class, $day]);
    $existing = $check_stmt->fetch();
    
    if ($existing) {
        // Update existing timetable
        $query = "UPDATE timetables SET 
                  period1_subject = ?, period1_teacher_id = ?,
                  period2_subject = ?, period2_teacher_id = ?,
                  period3_subject = ?, period3_teacher_id = ?,
                  period4_subject = ?, period4_teacher_id = ?,
                  period5_subject = ?, period5_teacher_id = ?,
                  period6_subject = ?, period6_teacher_id = ?,
                  period7_subject = ?, period7_teacher_id = ?,
                  period8_subject = ?, period8_teacher_id = ?
                  WHERE class = ? AND day = ?";
        
        $params = [];
        for ($i = 1; $i <= 8; $i++) {
            $params[] = $_POST["period{$i}_subject"] ?: null;
            $params[] = $_POST["period{$i}_teacher"] ?: null;
        }
        $params[] = $class;
        $params[] = $day;
        
        $stmt = $db->prepare($query);
        if ($stmt->execute($params)) {
            $message = '<div class="alert alert-success">Timetable updated successfully!</div>';
        }
    } else {
        // Insert new timetable
        $query = "INSERT INTO timetables (class, day, 
                  period1_subject, period1_teacher_id,
                  period2_subject, period2_teacher_id,
                  period3_subject, period3_teacher_id,
                  period4_subject, period4_teacher_id,
                  period5_subject, period5_teacher_id,
                  period6_subject, period6_teacher_id,
                  period7_subject, period7_teacher_id,
                  period8_subject, period8_teacher_id)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [$class, $day];
        for ($i = 1; $i <= 8; $i++) {
            $params[] = $_POST["period{$i}_subject"] ?: null;
            $params[] = $_POST["period{$i}_teacher"] ?: null;
        }
        
        $stmt = $db->prepare($query);
        if ($stmt->execute($params)) {
            $message = '<div class="alert alert-success">Timetable created successfully!</div>';
        }
    }
}

// Get teachers for dropdown
$teachers_query = "SELECT id, full_name FROM teachers ORDER BY full_name";
$teachers_stmt = $db->prepare($teachers_query);
$teachers_stmt->execute();
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get existing timetable if selected
$selected_timetable = null;
if (isset($_GET['class']) && isset($_GET['day'])) {
    $query = "SELECT * FROM timetables WHERE class = ? AND day = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['class'], $_GET['day']]);
    $selected_timetable = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Timetable Management - Admin Dashboard</title>
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
                <li><a href="admin_timetable.php" class="active">Timetable Management</a></li>
                <li><a href="admin_feedback.php">Feedback</a></li>
                <li><a href="admin_reports.php">Reports</a></li>
                <li><a href="admin_events.php">Events</a></li>
                <li><a href="admin_achievements.php">Achievements</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Timetable Management</h2>
                <p>Create and manage class timetables</p>
            </div>

            <?php echo $message; ?>

            <!-- Timetable Selection -->
            <div class="card">
                <h3>Select Class and Day</h3>
                <form method="GET">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <label>Class</label>
                            <select name="class" class="form-control" required>
                                <option value="">Select Class</option>
                                <option value="9A" <?php echo (isset($_GET['class']) && $_GET['class'] == '9A') ? 'selected' : ''; ?>>9A</option>
                                <option value="9B" <?php echo (isset($_GET['class']) && $_GET['class'] == '9B') ? 'selected' : ''; ?>>9B</option>
                                <option value="10A" <?php echo (isset($_GET['class']) && $_GET['class'] == '10A') ? 'selected' : ''; ?>>10A</option>
                                <option value="10B" <?php echo (isset($_GET['class']) && $_GET['class'] == '10B') ? 'selected' : ''; ?>>10B</option>
                                <option value="11A" <?php echo (isset($_GET['class']) && $_GET['class'] == '11A') ? 'selected' : ''; ?>>11A</option>
                                <option value="11B" <?php echo (isset($_GET['class']) && $_GET['class'] == '11B') ? 'selected' : ''; ?>>11B</option>
                                <option value="12A" <?php echo (isset($_GET['class']) && $_GET['class'] == '12A') ? 'selected' : ''; ?>>12A</option>
                                <option value="12B" <?php echo (isset($_GET['class']) && $_GET['class'] == '12B') ? 'selected' : ''; ?>>12B</option>
                            </select>
                        </div>
                        <div>
                            <label>Day</label>
                            <select name="day" class="form-control" required>
                                <option value="">Select Day</option>
                                <option value="Monday" <?php echo (isset($_GET['day']) && $_GET['day'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
                                <option value="Tuesday" <?php echo (isset($_GET['day']) && $_GET['day'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
                                <option value="Wednesday" <?php echo (isset($_GET['day']) && $_GET['day'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
                                <option value="Thursday" <?php echo (isset($_GET['day']) && $_GET['day'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
                                <option value="Friday" <?php echo (isset($_GET['day']) && $_GET['day'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
                            </select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Load Timetable</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (isset($_GET['class']) && isset($_GET['day'])): ?>
            <!-- Timetable Form -->
            <div class="card">
                <h3>Timetable for <?php echo htmlspecialchars($_GET['class']); ?> - <?php echo htmlspecialchars($_GET['day']); ?></h3>
                <form method="POST">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($_GET['class']); ?>">
                    <input type="hidden" name="day" value="<?php echo htmlspecialchars($_GET['day']); ?>">
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $periods = [
                                    1 => '07:45 - 08:30',
                                    2 => '08:30 - 09:10',
                                    3 => '09:10 - 09:50',
                                    4 => '09:50 - 10:30',
                                    5 => '10:50 - 11:30',
                                    6 => '11:30 - 12:10',
                                    7 => '12:10 - 12:50',
                                    8 => '12:50 - 13:30'
                                ];
                                
                                foreach ($periods as $period => $time): 
                                    $subject_value = $selected_timetable ? $selected_timetable["period{$period}_subject"] : '';
                                    $teacher_value = $selected_timetable ? $selected_timetable["period{$period}_teacher_id"] : '';
                                ?>
                                <tr>
                                    <td><?php echo $period; ?></td>
                                    <td><?php echo $time; ?></td>
                                    <td>
                                        <input type="text" name="period<?php echo $period; ?>_subject" 
                                               class="form-control" value="<?php echo htmlspecialchars($subject_value); ?>"
                                               placeholder="Subject name">
                                    </td>
                                    <td>
                                        <select name="period<?php echo $period; ?>_teacher" class="form-control">
                                            <option value="">Select Teacher</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?php echo $teacher['id']; ?>" 
                                                        <?php echo ($teacher_value == $teacher['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($teacher['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" name="save_timetable" class="btn btn-primary">Save Timetable</button>
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
    </style>
</body>
</html>