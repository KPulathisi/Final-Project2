<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !isStaff()) {
    redirect("/login.php");
}

// Check if the staff member is a teacher
if (!isTeacher()) {
    echo displayError("Access denied. Only teachers can access this page.");
    include_once '../includes/footer.php';
    exit;
}

// Get teacher details
$teacher_details = getTeacherDetailsByUserId($_SESSION['user_id']);
if (!$teacher_details) {
    echo displayError("Teacher information not found.");
    include_once '../includes/footer.php';
    exit;
}

$teacher_id = $teacher_details['id'];

// Get timetable for this teacher
$sql = "SELECT * FROM teacher_timetables 
        WHERE teacher_id = $teacher_id 
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
?>

<h2>My Timetable</h2>

<div class="card">
    <h3 class="card-title">Timetable for <?php echo $teacher_details['name']; ?></h3>
    
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
        
        <div class="mt-20">
            <button type="button" onclick="window.print();" class="button-secondary">Print Timetable</button>
        </div>
    <?php else: ?>
        <p>You don't have a timetable yet. Please contact the administrator.</p>
    <?php endif; ?>
</div>

<?php
// Include the footer
include_once '../includes/footer.php';
?>