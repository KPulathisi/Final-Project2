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

// Get teacher's timetable
$query = "SELECT t.*, t.class, t.day,
          CASE 
            WHEN t.period1_teacher_id = ? THEN CONCAT('Period 1: ', t.period1_subject)
            WHEN t.period2_teacher_id = ? THEN CONCAT('Period 2: ', t.period2_subject)
            WHEN t.period3_teacher_id = ? THEN CONCAT('Period 3: ', t.period3_subject)
            WHEN t.period4_teacher_id = ? THEN CONCAT('Period 4: ', t.period4_subject)
            WHEN t.period5_teacher_id = ? THEN CONCAT('Period 5: ', t.period5_subject)
            WHEN t.period6_teacher_id = ? THEN CONCAT('Period 6: ', t.period6_subject)
            WHEN t.period7_teacher_id = ? THEN CONCAT('Period 7: ', t.period7_subject)
            WHEN t.period8_teacher_id = ? THEN CONCAT('Period 8: ', t.period8_subject)
          END as periods
          FROM timetables t
          WHERE t.period1_teacher_id = ? OR t.period2_teacher_id = ? OR t.period3_teacher_id = ? OR t.period4_teacher_id = ?
             OR t.period5_teacher_id = ? OR t.period6_teacher_id = ? OR t.period7_teacher_id = ? OR t.period8_teacher_id = ?
          ORDER BY 
            CASE t.day 
              WHEN 'Monday' THEN 1 
              WHEN 'Tuesday' THEN 2 
              WHEN 'Wednesday' THEN 3 
              WHEN 'Thursday' THEN 4 
              WHEN 'Friday' THEN 5 
            END, t.class";

$params = array_fill(0, 16, $teacher['id']);
$stmt = $db->prepare($query);
$stmt->execute($params);
$timetable_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize timetable by day and class
$timetable = [];
foreach ($timetable_data as $row) {
    $day = $row['day'];
    $class = $row['class'];
    
    // Check each period for this teacher
    for ($period = 1; $period <= 8; $period++) {
        $teacher_field = "period{$period}_teacher_id";
        $subject_field = "period{$period}_subject";
        
        if ($row[$teacher_field] == $teacher['id']) {
            $timetable[$day][$period] = [
                'class' => $class,
                'subject' => $row[$subject_field]
            ];
        }
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
    <title>My Timetable - Teacher Dashboard</title>
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
                <li><a href="teacher_timetable.php" class="active">My Timetable</a></li>
                <li><a href="teacher_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>My Teaching Timetable</h2>
                <p>View your weekly teaching schedule</p>
            </div>

            <!-- Timetable -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--teacher-primary), var(--teacher-secondary)); color: white;">Weekly Schedule</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $periods = [
                            1 => '07:50 - 08:30',
                            2 => '08:30 - 09:10',
                            3 => '09:10 - 09:50',
                            4 => '09:50 - 10:30',
                            5 => '10:50 - 11:30',
                            6 => '11:30 - 12:10',
                            7 => '12:10 - 12:50',
                            8 => '12:50 - 13:30'
                        ];
                        
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        
                        foreach ($periods as $period => $time): 
                        ?>
                        <tr>
                            <td><strong><?php echo $time; ?></strong></td>
                            <?php foreach ($days as $day): ?>
                            <td>
                                <?php if (isset($timetable[$day][$period])): ?>
                                    <div style="background: var(--teacher-accent); color: white; padding: 0.5rem; border-radius: 5px; text-align: center;">
                                        <strong><?php echo htmlspecialchars($timetable[$day][$period]['subject']); ?></strong><br>
                                        <small>Class: <?php echo htmlspecialchars($timetable[$day][$period]['class']); ?></small>
                                    </div>
                                <?php else: ?>
                                    <div style="color: #999; text-align: center; padding: 0.5rem;">
                                        Free Period
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Teaching Summary -->
            <div class="cards-grid">
                <div class="card">
                    <h3>Teaching Summary</h3>
                    <div style="margin: 1rem 0;">
                        <?php
                        $total_periods = 0;
                        $subjects_taught = [];
                        $classes_taught = [];
                        
                        foreach ($timetable as $day => $periods) {
                            foreach ($periods as $period => $data) {
                                $total_periods++;
                                $subjects_taught[$data['subject']] = true;
                                $classes_taught[$data['class']] = true;
                            }
                        }
                        ?>
                        <p><strong>Total Periods per Week:</strong> <?php echo $total_periods; ?></p>
                        <p><strong>Subjects Teaching:</strong> <?php echo implode(', ', array_keys($subjects_taught)); ?></p>
                        <p><strong>Classes Teaching:</strong> <?php echo implode(', ', array_keys($classes_taught)); ?></p>
                        <?php if ($teacher['is_class_teacher']): ?>
                            <p><strong>Class Teacher for:</strong> <?php echo htmlspecialchars($teacher['class_assigned']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Today's Schedule</h3>
                    <div style="margin: 1rem 0;">
                        <?php
                        $today = date('l'); // Get current day name
                        if (isset($timetable[$today]) && !empty($timetable[$today])):
                        ?>
                            <h4><?php echo $today; ?></h4>
                            <?php foreach ($timetable[$today] as $period => $data): ?>
                                <div style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f8f0; border-radius: 5px;">
                                    <strong>Period <?php echo $period; ?> (<?php echo $periods[$period]; ?>)</strong><br>
                                    <?php echo htmlspecialchars($data['subject']); ?> - Class <?php echo htmlspecialchars($data['class']); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No classes scheduled for today.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>