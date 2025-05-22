<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    redirect("/login.php");
}

// Get student details
$student_details = getStudentDetailsByUserId($_SESSION['user_id']);
if (!$student_details) {
    echo displayError("Student information not found.");
    include_once '../includes/footer.php';
    exit;
}

$class_id = $student_details['class'];

// Get timetable for this class
$sql = "SELECT * FROM timetables 
        WHERE class_id = '$class_id' 
        ORDER BY FIELD(weekday, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')";
$result = mysqli_query($conn, $sql);

// Check if timetable exists
$has_timetable = mysqli_num_rows($result) > 0;

// Store timetable data by weekday
$timetable_data = array();
if ($has_timetable) {
    while ($row = mysqli_fetch_assoc($result)) {
        $timetable_data[$row['weekday']] = $row;
    }
}

// Get all teachers for display
$teachers = array();
$sql_teachers = "SELECT id, name FROM teachers WHERE status = 'approved'";
$result_teachers = mysqli_query($conn, $sql_teachers);
while ($row = mysqli_fetch_assoc($result_teachers)) {
    $teachers[$row['id']] = $row['name'];
}
?>

<h2>My Timetable</h2>

<div class="card">
    <h3 class="card-title">Timetable for <?php echo $class_id; ?></h3>
    
    <?php if ($has_timetable): ?>
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_1_subject'];
                                $teacher_id = $timetable_data[$day]['period_1_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_2_subject'];
                                $teacher_id = $timetable_data[$day]['period_2_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_3_subject'];
                                $teacher_id = $timetable_data[$day]['period_3_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_4_subject'];
                                $teacher_id = $timetable_data[$day]['period_4_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_5_subject'];
                                $teacher_id = $timetable_data[$day]['period_5_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_6_subject'];
                                $teacher_id = $timetable_data[$day]['period_6_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_7_subject'];
                                $teacher_id = $timetable_data[$day]['period_7_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
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
                            if (isset($timetable_data[$day])) {
                                $subject = $timetable_data[$day]['period_8_subject'];
                                $teacher_id = $timetable_data[$day]['period_8_teacher'];
                                $teacher_name = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : 'Not assigned';
                                
                                if (!empty($subject)) {
                                    echo "<strong>$subject</strong><br>";
                                    echo "$teacher_name";
                                } else {
                                    echo "Not scheduled";
                                }
                            } else {
                                echo "Not scheduled";
                            }
                            echo "</td>";
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="mt-20">
            <button type="button" onclick="window.print();" class="button-secondary">Print Timetable</button>
        </div>
    <?php else: ?>
        <p>Timetable for your class is not available yet. Please check back later.</p>
    <?php endif; ?>
</div>

<?php
// Include the footer
include_once '../includes/footer.php';
?>