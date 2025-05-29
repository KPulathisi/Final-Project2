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

// Get class ID from query parameter
$class_id = isset($_GET['class']) ? sanitizeInput($_GET['class']) : '';

// Check if teacher has first period for this class
if (empty($class_id) || !hasFirstPeriod($teacher_id, $class_id)) {
    echo displayError("You do not have permission to manage this class.");
    include_once '../includes/footer.php';
    exit;
}

// Initialize variables
$student_id = $name = $dob = $contact_info = $parent_name = $parent_contact = $address = "";
$success_message = $error_message = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add Student
    if (isset($_POST["add_student"])) {
        $student_id = sanitizeInput($_POST["student_id"]);
        $name = sanitizeInput($_POST["name"]);
        $dob = sanitizeInput($_POST["dob"]);
        $contact_info = sanitizeInput($_POST["contact_info"]);
        $parent_name = sanitizeInput($_POST["parent_name"]);
        $parent_contact = sanitizeInput($_POST["parent_contact"]);
        $address = sanitizeInput($_POST["address"]);
        
        // Validate input
        if (empty($student_id) || empty($name) || empty($dob) || empty($contact_info) || empty($parent_name) || empty($parent_contact) || empty($address)) {
            $error_message = "All fields are required.";
        } else {
            // Check if student ID already exists
            $check_sql = "SELECT id FROM students WHERE student_id = '$student_id'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error_message = "Student ID already exists.";
            } else {
                // Insert student
                $sql = "INSERT INTO students (student_id, name, class, dob, contact_info, parent_name, parent_contact, address) 
                        VALUES ('$student_id', '$name', '$class_id', '$dob', '$contact_info', '$parent_name', '$parent_contact', '$address')";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Student added successfully.";
                    $student_id = $name = $dob = $contact_info = $parent_name = $parent_contact = $address = "";
                } else {
                    $error_message = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Mark Attendance
    elseif (isset($_POST["mark_attendance"])) {
        $date = sanitizeInput($_POST["date"]);
        $attendance_data = isset($_POST["attendance"]) ? $_POST["attendance"] : array();
        
        if (empty($date)) {
            $error_message = "Please select a date.";
        } elseif (empty($attendance_data)) {
            $error_message = "No attendance data provided.";
        } else {
            // Insert or update attendance records
            $success_counter = 0;
            $error_counter = 0;
            
            foreach ($attendance_data as $student_id => $status) {
                $status = sanitizeInput($status);
                
                // Check if attendance record exists for this student and date
                $check_sql = "SELECT id FROM attendance WHERE student_id = $student_id AND date = '$date'";
                $check_result = mysqli_query($conn, $check_sql);
                
                if (mysqli_num_rows($check_result) > 0) {
                    // Update existing record
                    $attendance_id = mysqli_fetch_assoc($check_result)['id'];
                    $update_sql = "UPDATE attendance SET status = '$status', marked_by = $teacher_id WHERE id = $attendance_id";
                    
                    if (mysqli_query($conn, $update_sql)) {
                        $success_counter++;
                    } else {
                        $error_counter++;
                    }
                } else {
                    // Insert new record
                    $insert_sql = "INSERT INTO attendance (student_id, class_id, date, status, marked_by) 
                                  VALUES ($student_id, '$class_id', '$date', '$status', $teacher_id)";
                                  
                    if (mysqli_query($conn, $insert_sql)) {
                        $success_counter++;
                    } else {
                        $error_counter++;
                    }
                }
            }
            
            if ($error_counter > 0) {
                $error_message = "Some attendance records could not be saved. $success_counter succeeded, $error_counter failed.";
            } else {
                $success_message = "Attendance recorded successfully for $success_counter students.";
            }
        }
    }
    
    // Edit Student
    elseif (isset($_POST["edit_student"])) {
        $id = sanitizeInput($_POST["id"]);
        $student_id = sanitizeInput($_POST["student_id"]);
        $name = sanitizeInput($_POST["name"]);
        $dob = sanitizeInput($_POST["dob"]);
        $contact_info = sanitizeInput($_POST["contact_info"]);
        $parent_name = sanitizeInput($_POST["parent_name"]);
        $parent_contact = sanitizeInput($_POST["parent_contact"]);
        $address = sanitizeInput($_POST["address"]);
        
        // Validate input
        if (empty($student_id) || empty($name) || empty($dob) || empty($contact_info) || empty($parent_name) || empty($parent_contact) || empty($address)) {
            $error_message = "All fields are required.";
        } else {
            // Check if student ID already exists and is not this student
            $check_sql = "SELECT id FROM students WHERE student_id = '$student_id' AND id != $id";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error_message = "Student ID already exists.";
            } else {
                // Update student
                $sql = "UPDATE students SET 
                        student_id = '$student_id',
                        name = '$name',
                        dob = '$dob',
                        contact_info = '$contact_info',
                        parent_name = '$parent_name',
                        parent_contact = '$parent_contact',
                        address = '$address'
                        WHERE id = $id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Student updated successfully.";
                } else {
                    $error_message = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Delete Student
    elseif (isset($_POST["delete_student"])) {
        $id = sanitizeInput($_POST["id"]);
        
        // Delete student
        $sql = "DELETE FROM students WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Student deleted successfully.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all students in this class
$sql = "SELECT * FROM students WHERE class = '$class_id' ORDER BY name";
$result_students = mysqli_query($conn, $sql);

// Get student by ID for editing
$edit_student = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = sanitizeInput($_GET['edit']);
    $edit_sql = "SELECT * FROM students WHERE id = $edit_id AND class = '$class_id'";
    $edit_result = mysqli_query($conn, $edit_sql);
    
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_student = mysqli_fetch_assoc($edit_result);
    }
}
?>

<h2>Manage Students for <?php echo $class_id; ?></h2>

<?php
if (!empty($success_message)) {
    echo displaySuccess($success_message);
} elseif (!empty($error_message)) {
    echo displayError($error_message);
}
?>

<div class="tabs">
    <button class="tablink" onclick="openTab('add', this)" id="defaultOpen">Add Student</button>
    <button class="tablink" onclick="openTab('list', this)">Students List</button>
    <button class="tablink" onclick="openTab('attendance', this)">Mark Attendance</button>
</div>

<!-- Add Student Form -->
<div id="add" class="tabcontent">
    <div class="card">
        <h3 class="card-title"><?php echo isset($edit_student) ? "Edit Student" : "Add New Student"; ?></h3>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?class=$class_id"); ?>" method="post">
            <?php if (isset($edit_student)): ?>
                <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="student_id">Student ID</label>
                <input type="text" id="student_id" name="student_id" value="<?php echo isset($edit_student) ? $edit_student['student_id'] : $student_id; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($edit_student) ? $edit_student['name'] : $name; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" value="<?php echo isset($edit_student) ? $edit_student['dob'] : $dob; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact_info">Contact Number</label>
                <input type="text" id="contact_info" name="contact_info" value="<?php echo isset($edit_student) ? $edit_student['contact_info'] : $contact_info; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="parent_name">Parent/Guardian Name</label>
                <input type="text" id="parent_name" name="parent_name" value="<?php echo isset($edit_student) ? $edit_student['parent_name'] : $parent_name; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="parent_contact">Parent/Guardian Contact</label>
                <input type="text" id="parent_contact" name="parent_contact" value="<?php echo isset($edit_student) ? $edit_student['parent_contact'] : $parent_contact; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" required><?php echo isset($edit_student) ? $edit_student['address'] : $address; ?></textarea>
            </div>
            
            <div class="form-group">
                <?php if (isset($edit_student)): ?>
                    <input type="submit" name="edit_student" value="Update Student">
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?class=$class_id"); ?>" class="button button-secondary">Cancel</a>
                <?php else: ?>
                    <input type="submit" name="add_student" value="Add Student">
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Students List -->
<div id="list" class="tabcontent">
    <div class="card">
        <h3 class="card-title">Students in <?php echo $class_id; ?></h3>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Contact</th>
                        <th>Parent/Guardian</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result_students) > 0) {
                        while ($row = mysqli_fetch_assoc($result_students)) {
                            echo "<tr>";
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td>" . $row["student_id"] . "</td>";
                            echo "<td>" . $row["name"] . "</td>";
                            echo "<td>" . $row["dob"] . "</td>";
                            echo "<td>" . $row["contact_info"] . "</td>";
                            echo "<td>" . $row["parent_name"] . " (" . $row["parent_contact"] . ")</td>";
                            echo "<td>";
                            echo "<a href='" . htmlspecialchars($_SERVER["PHP_SELF"] . "?class=$class_id&edit=" . $row["id"]) . "' class='button button-secondary'>Edit</a> ";
                            echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"] . "?class=$class_id") . "' style='display: inline;'>";
                            echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
                            echo "<input type='submit' name='delete_student' value='Delete' class='button-danger' 
                                  onclick='return confirm(\"Are you sure you want to delete this student?\")'>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No students found in this class</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mark Attendance -->
<div id="attendance" class="tabcontent">
    <div class="card">
        <h3 class="card-title">Mark Attendance for <?php echo $class_id; ?></h3>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?class=$class_id"); ?>" method="post">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($result_students, 0); // Reset result set pointer
                        
                        if (mysqli_num_rows($result_students) > 0) {
                            while ($row = mysqli_fetch_assoc($result_students)) {
                                $student_id = $row["id"];
                                $student_name = $row["name"];
                                
                                // Check if attendance has already been marked for this student today
                                $check_attendance = "SELECT status FROM attendance WHERE student_id = $student_id AND date = '" . date('Y-m-d') . "'";
                                $attendance_result = mysqli_query($conn, $check_attendance);
                                $current_status = (mysqli_num_rows($attendance_result) > 0) ? mysqli_fetch_assoc($attendance_result)['status'] : '';
                                
                                echo "<tr>";
                                echo "<td>" . $row["student_id"] . "</td>";
                                echo "<td>" . $student_name . "</td>";
                                echo "<td>";
                                echo "<select name='attendance[$student_id]' required>";
                                echo "<option value='present'" . ($current_status == 'present' ? ' selected' : '') . ">Present</option>";
                                echo "<option value='absent'" . ($current_status == 'absent' ? ' selected' : '') . ">Absent</option>";
                                echo "<option value='late'" . ($current_status == 'late' ? ' selected' : '') . ">Late</option>";
                                echo "</select>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No students found in this class</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="form-group">
                <input type="submit" name="mark_attendance" value="Save Attendance">
            </div>
        </form>
    </div>
</div>

<style>
/* Tab styling */
.tabs {
    overflow: hidden;
    margin-bottom: 20px;
}

.tablink {
    background-color: #344CB7;
    color: white;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 10px 15px;
    font-size: 16px;
    margin-right: 5px;
    border-radius: 4px 4px 0 0;
    transition: background-color 0.3s ease;
}

.tablink:hover {
    background-color: #000957;
}

.tablink.active {
    background-color: #000957;
}

.tabcontent {
    display: none;
    padding: 20px 0;
    border-top: none;
}
</style>

<script>
// Tab functionality
function openTab(tabName, element) {
    // Hide all tab content
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    
    // Remove active class from all tabs
    var tablinks = document.getElementsByClassName("tablink");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    
    // Show the selected tab content and add active class to the button
    document.getElementById(tabName).style.display = "block";
    element.className += " active";
}

// Open the default tab
document.getElementById("defaultOpen").click();

<?php if (isset($edit_student)): ?>
// If editing a student, open the add tab
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('.tablink[onclick*="add"]').click();
});
<?php endif; ?>
</script>

<?php
// Include the footer
include_once '../includes/footer.php';
?>