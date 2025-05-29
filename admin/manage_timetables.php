<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect("/login.php");
}

// Initialize variables
$class_id = $weekday = "";
$success_message = $error_message = "";

// Get all classes
$classes = getAllClasses();

// Get all teachers
$sql_teachers = "SELECT id, name, level, subjects FROM teachers WHERE status = 'approved' ORDER BY name";
$result_teachers = mysqli_query($conn, $sql_teachers);
$teachers = array();
while ($row = mysqli_fetch_assoc($result_teachers)) {
    $teachers[] = $row;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["save_timetable"])) {
        $class_id = sanitizeInput($_POST["class_id"]);
        $weekday = sanitizeInput($_POST["weekday"]);
        
        // Check if inputs are valid
        if (empty($class_id) || empty($weekday)) {
            $error_message = "Class and weekday are required.";
        } else {
            // Process periods
            $period_subjects = array();
            $period_teachers = array();
            
            for ($i = 1; $i <= 8; $i++) {
                $subject_key = "period_" . $i . "_subject";
                $teacher_key = "period_" . $i . "_teacher";
                
                $period_subjects[$i] = isset($_POST[$subject_key]) ? sanitizeInput($_POST[$subject_key]) : null;
                $period_teachers[$i] = isset($_POST[$teacher_key]) ? sanitizeInput($_POST[$teacher_key]) : null;
            }
            
            // Check if timetable for this class and weekday already exists
            $check_sql = "SELECT id FROM timetables WHERE class_id = '$class_id' AND weekday = '$weekday'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update existing timetable
                $update_sql = "UPDATE timetables SET 
                               period_1_subject = '{$period_subjects[1]}', period_1_teacher = {$period_teachers[1]},
                               period_2_subject = '{$period_subjects[2]}', period_2_teacher = {$period_teachers[2]},
                               period_3_subject = '{$period_subjects[3]}', period_3_teacher = {$period_teachers[3]},
                               period_4_subject = '{$period_subjects[4]}', period_4_teacher = {$period_teachers[4]},
                               period_5_subject = '{$period_subjects[5]}', period_5_teacher = {$period_teachers[5]},
                               period_6_subject = '{$period_subjects[6]}', period_6_teacher = {$period_teachers[6]},
                               period_7_subject = '{$period_subjects[7]}', period_7_teacher = {$period_teachers[7]},
                               period_8_subject = '{$period_subjects[8]}', period_8_teacher = {$period_teachers[8]}'
                               WHERE class_id = '$class_id' AND weekday = '$weekday'";
                               
                if (mysqli_query($conn, $update_sql)) {
                    $success_message = "Timetable updated successfully for $class_id on $weekday.";
                    
                    // Update teacher timetables
                    updateTeacherTimetables($class_id, $weekday, $period_subjects, $period_teachers);
                } else {
                    $error_message = "Error updating timetable: " . mysqli_error($conn);
                }
            } else {
                // Insert new timetable
                $insert_sql = "INSERT INTO timetables (
                                class_id, weekday, 
                                period_1_subject, period_1_teacher,
                                period_2_subject, period_2_teacher,
                                period_3_subject, period_3_teacher,
                                period_4_subject, period_4_teacher,
                                period_5_subject, period_5_teacher,
                                period_6_subject, period_6_teacher,
                                period_7_subject, period_7_teacher,
                                period_8_subject, period_8_teacher
                               ) VALUES (
                                '$class_id', '$weekday',
                                '{$period_subjects[1]}', {$period_teachers[1]},
                                '{$period_subjects[2]}', {$period_teachers[2]},
                                '{$period_subjects[3]}', {$period_teachers[3]},
                                '{$period_subjects[4]}', {$period_teachers[4]},
                                '{$period_subjects[5]}', {$period_teachers[5]},
                                '{$period_subjects[6]}', {$period_teachers[6]},
                                '{$period_subjects[7]}', {$period_teachers[7]},
                                '{$period_subjects[8]}', {$period_teachers[8]}
                               )";
                
                if (mysqli_query($conn, $insert_sql)) {
                    $success_message = "Timetable created successfully for $class_id on $weekday.";
                    
                    // Update teacher timetables
                    updateTeacherTimetables($class_id, $weekday, $period_subjects, $period_teachers);
                } else {
                    $error_message = "Error creating timetable: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Function to update teacher timetables
function updateTeacherTimetables($class_id, $weekday, $period_subjects, $period_teachers) {
    global $conn;
    
    // First clear existing entries for this class and day
    for ($i = 1; $i <= 8; $i++) {
        if (!empty($period_teachers[$i])) {
            $teacher_id = $period_teachers[$i];
            
            // Check if teacher already has a timetable for this day
            $check_sql = "SELECT id FROM teacher_timetables WHERE teacher_id = $teacher_id AND weekday = '$weekday'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update existing record
                $update_sql = "UPDATE teacher_timetables SET period_{$i}_class = '$class_id', period_{$i}_subject = '{$period_subjects[$i]}' 
                              WHERE teacher_id = $teacher_id AND weekday = '$weekday'";
                mysqli_query($conn, $update_sql);
            } else {
                // Create a blank timetable for this teacher and day
                $period_classes = array_fill(1, 8, "NULL");
                $period_subjects_arr = array_fill(1, 8, "NULL");
                
                // Set the specific period
                $period_classes[$i] = "'$class_id'";
                $period_subjects_arr[$i] = "'{$period_subjects[$i]}'";
                
                $insert_sql = "INSERT INTO teacher_timetables (
                                teacher_id, weekday, 
                                period_1_class, period_1_subject,
                                period_2_class, period_2_subject,
                                period_3_class, period_3_subject,
                                period_4_class, period_4_subject,
                                period_5_class, period_5_subject,
                                period_6_class, period_6_subject,
                                period_7_class, period_7_subject,
                                period_8_class, period_8_subject
                               ) VALUES (
                                $teacher_id, '$weekday',
                                {$period_classes[1]}, {$period_subjects_arr[1]},
                                {$period_classes[2]}, {$period_subjects_arr[2]},
                                {$period_classes[3]}, {$period_subjects_arr[3]},
                                {$period_classes[4]}, {$period_subjects_arr[4]},
                                {$period_classes[5]}, {$period_subjects_arr[5]},
                                {$period_classes[6]}, {$period_subjects_arr[6]},
                                {$period_classes[7]}, {$period_subjects_arr[7]},
                                {$period_classes[8]}, {$period_subjects_arr[8]}
                               )";
                               
                mysqli_query($conn, $insert_sql);
            }
        }
    }
}

// Load existing timetable if class and weekday are selected
$timetable = array();
if (!empty($class_id) && !empty($weekday)) {
    $sql = "SELECT * FROM timetables WHERE class_id = '$class_id' AND weekday = '$weekday'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $timetable = mysqli_fetch_assoc($result);
    }
}

// Prepare subjects based on class level
$class_level = '';
if (!empty($class_id)) {
    // Extract grade number from class ID
    preg_match('/Grade (\d+)/', $class_id, $matches);
    $grade = isset($matches[1]) ? (int)$matches[1] : 0;
    
    if ($grade >= 12) {
        $class_level = 'Advanced Level';
        $subjects = getSubjectsByLevel('Advanced Level');
    } else {
        $class_level = 'Ordinary Level';
        $subjects = getSubjectsByLevel('Ordinary Level');
    }
}

// Get all timetables for viewing
$sql_all_timetables = "SELECT DISTINCT class_id FROM timetables ORDER BY class_id";
$result_all_timetables = mysqli_query($conn, $sql_all_timetables);
$all_classes = array();
while ($row = mysqli_fetch_assoc($result_all_timetables)) {
    $all_classes[] = $row['class_id'];
}

// Get all teachers for viewing their timetables
$sql_all_teachers = "SELECT id, name FROM teachers WHERE status = 'approved' ORDER BY name";
$result_all_teachers = mysqli_query($conn, $sql_all_teachers);
?>

<h2>Timetable Management</h2>

<?php
if (!empty($success_message)) {
    echo displaySuccess($success_message);
} elseif (!empty($error_message)) {
    echo displayError($error_message);
}
?>

<!-- Create/Edit Timetable Form -->
<div class="card mb-20">
    <h3 class="card-title">Create/Edit Timetable</h3>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="timetable-form">
        <div class="form-group">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id" required>
                <option value="">Select Class</option>
                <optgroup label="Ordinary Level">
                    <?php foreach ($classes['OL'] as $class): ?>
                        <option value="<?php echo $class; ?>" <?php if ($class_id == $class) echo "selected"; ?>>
                            <?php echo $class; ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Advanced Level">
                    <?php foreach ($classes['AL'] as $class): ?>
                        <option value="<?php echo $class; ?>" <?php if ($class_id == $class) echo "selected"; ?>>
                            <?php echo $class; ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>
        
        <div class="form-group">
            <label for="weekday">Day of Week</label>
            <select id="weekday" name="weekday" required>
                <option value="">Select Day</option>
                <option value="Monday" <?php if ($weekday == "Monday") echo "selected"; ?>>Monday</option>
                <option value="Tuesday" <?php if ($weekday == "Tuesday") echo "selected"; ?>>Tuesday</option>
                <option value="Wednesday" <?php if ($weekday == "Wednesday") echo "selected"; ?>>Wednesday</option>
                <option value="Thursday" <?php if ($weekday == "Thursday") echo "selected"; ?>>Thursday</option>
                <option value="Friday" <?php if ($weekday == "Friday") echo "selected"; ?>>Friday</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="button" id="load-timetable-btn">Load Timetable</button>
        </div>
        
        <div id="timetable-container" <?php if (empty($class_id) || empty($weekday)) echo "style='display:none;'"; ?>>
            <h4>Timetable for <?php echo "$class_id - $weekday"; ?></h4>
            
            <div class="table-container">
                <table class="timetable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Period 1: 07:50 - 08:30 -->
                        <tr>
                            <td>07:50 - 08:30</td>
                            <td>
                                <select name="period_1_subject" class="period-subject" data-period="1">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_1_subject']) && $timetable['period_1_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_1_teacher" class="period-teacher" data-period="1">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_1_teacher']) && $timetable['period_1_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Period 2: 08:30 - 09:10 -->
                        <tr>
                            <td>08:30 - 09:10</td>
                            <td>
                                <select name="period_2_subject" class="period-subject" data-period="2">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_2_subject']) && $timetable['period_2_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_2_teacher" class="period-teacher" data-period="2">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_2_teacher']) && $timetable['period_2_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Period 3: 09:10 - 09:50 -->
                        <tr>
                            <td>09:10 - 09:50</td>
                            <td>
                                <select name="period_3_subject" class="period-subject" data-period="3">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_3_subject']) && $timetable['period_3_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_3_teacher" class="period-teacher" data-period="3">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_3_teacher']) && $timetable['period_3_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Period 4: 09:50 - 10:30 -->
                        <tr>
                            <td>09:50 - 10:30</td>
                            <td>
                                <select name="period_4_subject" class="period-subject" data-period="4">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_4_subject']) && $timetable['period_4_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_4_teacher" class="period-teacher" data-period="4">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_4_teacher']) && $timetable['period_4_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Interval: 10:30 - 10:50 -->
                        <tr class="interval">
                            <td colspan="3">Interval: 10:30 - 10:50</td>
                        </tr>
                        
                        <!-- Period 5: 10:50 - 11:30 -->
                        <tr>
                            <td>10:50 - 11:30</td>
                            <td>
                                <select name="period_5_subject" class="period-subject" data-period="5">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_5_subject']) && $timetable['period_5_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_5_teacher" class="period-teacher" data-period="5">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_5_teacher']) && $timetable['period_5_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Period 6: 11:30 - 12:10 -->
                        <tr>
                            <td>11:30 - 12:10</td>
                            <td>
                                <select name="period_6_subject" class="period-subject" data-period="6">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_6_subject']) && $timetable['period_6_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_6_teacher" class="period-teacher" data-period="6">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_6_teacher']) && $timetable['period_6_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Period 7: 12:10 - 12:50 -->
                        <tr>
                            <td>12:10 - 12:50</td>
                            <td>
                                <select name="period_7_subject" class="period-subject" data-period="7">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_7_subject']) && $timetable['period_7_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_7_teacher" class="period-teacher" data-period="7">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_7_teacher']) && $timetable['period_7_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Period 8: 12:50 - 13:30 -->
                        <tr>
                            <td>12:50 - 13:30</td>
                            <td>
                                <select name="period_8_subject" class="period-subject" data-period="8">
                                    <option value="">Select Subject</option>
                                    <?php if (!empty($class_level) && isset($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>" <?php if (isset($timetable['period_8_subject']) && $timetable['period_8_subject'] == $subject) echo "selected"; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="period_8_teacher" class="period-teacher" data-period="8">
                                    <option value="NULL">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <?php if ($teacher['level'] == $class_level || empty($class_level)): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php if (isset($timetable['period_8_teacher']) && $timetable['period_8_teacher'] == $teacher['id']) echo "selected"; ?>>
                                                <?php echo $teacher['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="form-group">
                <input type="submit" name="save_timetable" value="Save Timetable">
            </div>
        </div>
    </form>
</div>

<!-- View Timetables -->
<div class="card mb-20">
    <h3 class="card-title">View Class Timetables</h3>
    
    <form id="view-timetable-form">
        <div class="form-group">
            <label for="view_class">Select Class</label>
            <select id="view_class" name="view_class">
                <option value="">Select Class</option>
                <?php foreach ($all_classes as $class): ?>
                    <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <button type="button" id="view-class-timetable-btn">View Timetable</button>
        </div>
    </form>
    
    <div id="view-timetable-result" style="display:none;">
        <!-- Timetable will be displayed here -->
    </div>
</div>

<!-- View Teacher Timetables -->
<div class="card">
    <h3 class="card-title">View Teacher Timetables</h3>
    
    <form id="view-teacher-timetable-form">
        <div class="form-group">
            <label for="view_teacher">Select Teacher</label>
            <select id="view_teacher" name="view_teacher">
                <option value="">Select Teacher</option>
                <?php while ($row = mysqli_fetch_assoc($result_all_teachers)): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <button type="button" id="view-teacher-timetable-btn">View Timetable</button>
        </div>
    </form>
    
    <div id="view-teacher-timetable-result" style="display:none;">
        <!-- Teacher Timetable will be displayed here -->
    </div>
</div>

<!-- JavaScript for dynamic form interactions -->
<script>
// Load timetable when class and weekday are selected
document.getElementById('load-timetable-btn').addEventListener('click', function() {
    var classId = document.getElementById('class_id').value;
    var weekday = document.getElementById('weekday').value;
    
    if (classId && weekday) {
        document.getElementById('timetable-form').submit();
    } else {
        alert('Please select both class and weekday.');
    }
});

// View class timetable
document.getElementById('view-class-timetable-btn').addEventListener('click', function() {
    var classId = document.getElementById('view_class').value;
    
    if (classId) {
        // Make an AJAX request to get the timetable
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'view_class_timetable.php?class_id=' + encodeURIComponent(classId), true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('view-timetable-result').innerHTML = xhr.responseText;
                document.getElementById('view-timetable-result').style.display = 'block';
            }
        };
        
        xhr.send();
    } else {
        alert('Please select a class.');
    }
});

// View teacher timetable
document.getElementById('view-teacher-timetable-btn').addEventListener('click', function() {
    var teacherId = document.getElementById('view_teacher').value;
    
    if (teacherId) {
        // Make an AJAX request to get the teacher's timetable
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'view_teacher_timetable.php?teacher_id=' + encodeURIComponent(teacherId), true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('view-teacher-timetable-result').innerHTML = xhr.responseText;
                document.getElementById('view-teacher-timetable-result').style.display = 'block';
            }
        };
        
        xhr.send();
    } else {
        alert('Please select a teacher.');
    }
});

// Auto-select teachers based on subject
document.querySelectorAll('.period-subject').forEach(function(element) {
    element.addEventListener('change', function() {
        var period = this.getAttribute('data-period');
        var subject = this.value;
        var teacherSelect = document.querySelector('.period-teacher[data-period="' + period + '"]');
        
        // Reset teacher selection
        teacherSelect.selectedIndex = 0;
        
        // Enable/disable teacher options based on whether they teach the selected subject
        if (subject) {
            Array.from(teacherSelect.options).forEach(function(option) {
                if (option.value === 'NULL') return; // Skip the "Select Teacher" option
                
                var teacherId = option.value;
                var teacherSubjects = getTeacherSubjects(teacherId);
                
                // Check if the teacher teaches this subject
                if (teacherSubjects.includes(subject)) {
                    option.disabled = false;
                } else {
                    option.disabled = true;
                }
            });
        } else {
            // If no subject selected, enable all teachers
            Array.from(teacherSelect.options).forEach(function(option) {
                option.disabled = false;
            });
        }
    });
});

// Get subjects taught by a teacher
function getTeacherSubjects(teacherId) {
    var subjects = [];
    
    <?php foreach ($teachers as $teacher): ?>
    if (teacherId == '<?php echo $teacher['id']; ?>') {
        subjects = '<?php echo $teacher['subjects']; ?>'.split(', ');
    }
    <?php endforeach; ?>
    
    return subjects;
}
</script>

<?php
// Include the footer
include_once '../includes/footer.php';
?>