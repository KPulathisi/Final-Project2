<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['student']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get student information
$query = "SELECT * FROM students WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get timetable for student's class
$query = "SELECT * FROM timetables WHERE class = ? ORDER BY 
          CASE day 
            WHEN 'Monday' THEN 1 
            WHEN 'Tuesday' THEN 2 
            WHEN 'Wednesday' THEN 3 
            WHEN 'Thursday' THEN 4 
            WHEN 'Friday' THEN 5 
          END";
$stmt = $db->prepare($query);
$stmt->execute([$student['class']]);
$timetable_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get teacher names for the timetable
$teacher_names = [];
$teacher_ids = [];
foreach ($timetable_data as $row) {
    for ($i = 1; $i <= 8; $i++) {
        $teacher_id = $row["period{$i}_teacher_id"];
        if ($teacher_id && !in_array($teacher_id, $teacher_ids)) {
            $teacher_ids[] = $teacher_id;
        }
    }
}

if (!empty($teacher_ids)) {
    $placeholders = str_repeat('?,', count($teacher_ids) - 1) . '?';
    $query = "SELECT id, full_name FROM teachers WHERE id IN ($placeholders)";
    $stmt = $db->prepare($query);
    $stmt->execute($teacher_ids);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($teachers as $teacher) {
        $teacher_names[$teacher['id']] = $teacher['full_name'];
    }
}

// Organize timetable by day
$timetable = [];
foreach ($timetable_data as $row) {
    $timetable[$row['day']] = $row;
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
    <title>Class Timetable - Student Dashboard</title>
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
                <li><a href="student_timetable.php" class="active">Class Timetable</a></li>
                <li><a href="student_report.php">Report Card</a></li>
                <li><a href="student_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Class Timetable - <?php echo htmlspecialchars($student['class']); ?></h2>
                <p>Your weekly class schedule</p>
            </div>

            <!-- Current Day Highlight -->
            <div class="card">
                <h3>Today's Schedule - <?php echo date('l, F j, Y'); ?></h3>
                <div style="padding: 1rem;">
                    <?php
                    $today = date('l');
                    if (isset($timetable[$today])):
                        $today_schedule = $timetable[$today];
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
                        
                        $has_classes = false;
                        for ($period = 1; $period <= 8; $period++) {
                            $subject = $today_schedule["period{$period}_subject"];
                            $teacher_id = $today_schedule["period{$period}_teacher_id"];
                            
                            if ($subject) {
                                $has_classes = true;
                                $teacher_name = isset($teacher_names[$teacher_id]) ? $teacher_names[$teacher_id] : 'TBA';
                                echo "<div style='margin: 0.5rem 0; padding: 0.75rem; background: #f0f9ff; border-left: 4px solid var(--student-accent); border-radius: 5px;'>";
                                echo "<strong>Period $period ({$periods[$period]})</strong><br>";
                                echo "<span style='font-size: 1.1rem; color: var(--student-primary);'>$subject</span><br>";
                                echo "<small>Teacher: $teacher_name</small>";
                                echo "</div>";
                            }
                        }
                        
                        if (!$has_classes) {
                            echo "<p>No classes scheduled for today.</p>";
                        }
                    else{
                        echo "<p>No timetable available for today.</p>";
                    }
                    endif;
                    ?>
                </div>
            </div>

            <!-- Weekly Timetable -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: linear-gradient(135deg, var(--student-primary), var(--student-secondary)); color: white;">Weekly Timetable</h3>
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
                                <?php 
                                if (isset($timetable[$day])) {
                                    $subject = $timetable[$day]["period{$period}_subject"];
                                    $teacher_id = $timetable[$day]["period{$period}_teacher_id"];
                                    
                                    if ($subject) {
                                        $teacher_name = isset($teacher_names[$teacher_id]) ? $teacher_names[$teacher_id] : 'TBA';
                                        echo "<div style='background: var(--student-accent); color: white; padding: 0.5rem; border-radius: 5px; text-align: center;'>";
                                        echo "<strong>$subject</strong><br>";
                                        echo "<small>$teacher_name</small>";
                                        echo "</div>";
                                    } else {
                                        echo "<div style='color: #999; text-align: center; padding: 0.5rem;'>Free Period</div>";
                                    }
                                } else {
                                    echo "<div style='color: #999; text-align: center; padding: 0.5rem;'>No Data</div>";
                                }
                                ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Break Times -->
            <div class="card">
                <h3>Interval</h3>
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <!-- <h4 style="color: var(--student-primary);">Interval</h4> -->
                            <p style="font-size: 1.2rem; font-weight: bold;">10:30 - 10:50</p>
                            <small>20 minutes</small>
                        </div>
                        <!-- <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px;">
                            <h4 style="color: var(--student-primary);">Meditation</h4>
                            <p style="font-size: 1.2rem; font-weight: bold;">10:45 - 10:50</p>
                            <small>45 minutes</small>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Subject Summary -->
            <div class="card">
                <h3>Weekly Subject Summary</h3>
                <div style="padding: 1rem;">
                    <?php
                    $subject_count = [];
                    foreach ($timetable as $day => $schedule) {
                        for ($period = 1; $period <= 8; $period++) {
                            $subject = $schedule["period{$period}_subject"];
                            if ($subject) {
                                $subject_count[$subject] = isset($subject_count[$subject]) ? $subject_count[$subject] + 1 : 1;
                            }
                        }
                    }
                    
                    if (!empty($subject_count)):
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <?php foreach ($subject_count as $subject => $count): ?>
                        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 5px; border: 1px solid #ddd;">
                            <h4 style="color: var(--student-primary);"><?php echo htmlspecialchars($subject); ?></h4>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--student-accent);"><?php echo $count; ?></p>
                            <small>periods/week</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p>No subjects scheduled yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>