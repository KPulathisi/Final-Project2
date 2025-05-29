<?php
// Include necessary files
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "Access denied";
    exit;
}

// Get teacher ID from request
$teacher_id = isset($_GET['teacher_id']) ? sanitizeInput($_GET['teacher_id']) : '';

if (empty($teacher_id)) {
    echo "Teacher ID is required";
    exit;
}

// Get teacher details
$sql_teacher = "SELECT name FROM teachers WHERE id = $teacher_id";
$result_teacher = mysqli_query($conn, $sql_teacher);

if (mysqli_num_rows($result_teacher) == 0) {
    echo "<p>Teacher not found.</p>";
    exit;
}

$teacher_name = mysqli_fetch_assoc($result_teacher)['name'];

// Get timetable data for the teacher
$sql = "SELECT * FROM teacher_timetables WHERE teacher_id = $teacher_id ORDER BY FIELD(weekday, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')";
$result = mysqli_query($conn, $sql);

// Check if timetable exists
if (mysqli_num_rows($result) == 0) {
    echo "<p>No timetable found for this teacher.</p>";
    exit;
}

// Store timetable data by weekday
$timetable_data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $timetable_data[$row['weekday']] = $row;
}

// Display the timetable
echo "<h3>Timetable for $teacher_name</h3>";
?>

<div class="table-container">
    <table class="timetable">
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
            <!-- Period 1: 07:50 - 08:30 -->
            <tr>
                <td>07:50 - 08:30</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_1_class'])) {
                        $class = $timetable_data[$day]['period_1_class'];
                        $subject = $timetable_data[$day]['period_1_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Period 2: 08:30 - 09:10 -->
            <tr>
                <td>08:30 - 09:10</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_2_class'])) {
                        $class = $timetable_data[$day]['period_2_class'];
                        $subject = $timetable_data[$day]['period_2_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Period 3: 09:10 - 09:50 -->
            <tr>
                <td>09:10 - 09:50</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_3_class'])) {
                        $class = $timetable_data[$day]['period_3_class'];
                        $subject = $timetable_data[$day]['period_3_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Period 4: 09:50 - 10:30 -->
            <tr>
                <td>09:50 - 10:30</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_4_class'])) {
                        $class = $timetable_data[$day]['period_4_class'];
                        $subject = $timetable_data[$day]['period_4_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Interval: 10:30 - 10:50 -->
            <tr class="interval">
                <td colspan="6">Interval: 10:30 - 10:50</td>
            </tr>
            
            <!-- Period 5: 10:50 - 11:30 -->
            <tr>
                <td>10:50 - 11:30</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_5_class'])) {
                        $class = $timetable_data[$day]['period_5_class'];
                        $subject = $timetable_data[$day]['period_5_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Period 6: 11:30 - 12:10 -->
            <tr>
                <td>11:30 - 12:10</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_6_class'])) {
                        $class = $timetable_data[$day]['period_6_class'];
                        $subject = $timetable_data[$day]['period_6_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Period 7: 12:10 - 12:50 -->
            <tr>
                <td>12:10 - 12:50</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_7_class'])) {
                        $class = $timetable_data[$day]['period_7_class'];
                        $subject = $timetable_data[$day]['period_7_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
            
            <!-- Period 8: 12:50 - 13:30 -->
            <tr>
                <td>12:50 - 13:30</td>
                <?php
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    echo "<td class='period-cell'>";
                    if (isset($timetable_data[$day]) && !empty($timetable_data[$day]['period_8_class'])) {
                        $class = $timetable_data[$day]['period_8_class'];
                        $subject = $timetable_data[$day]['period_8_subject'];
                        
                        if (!empty($subject)) {
                            echo "<strong>$subject</strong><br>";
                            echo "Class: $class";
                        } else {
                            echo "Free period";
                        }
                    } else {
                        echo "Free period";
                    }
                    echo "</td>";
                }
                ?>
            </tr>
        </tbody>
    </table>
</div>

<p><button type="button" onclick="window.print();">Print Timetable</button></p>