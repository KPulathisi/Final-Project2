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

// Get classes this teacher teaches
$teacher_classes = getTeacherClasses($teacher_id);

if (empty($teacher_classes)) {
    echo displayError("You have not been assigned any classes yet.");
    include_once '../includes/footer.php';
    exit;
}

// Initialize variables
$selected_class = isset($_POST['class_id']) ? sanitizeInput($_POST['class_id']) : (isset($_GET['class']) ? sanitizeInput($_GET['class']) : $teacher_classes[0]);
$selected_subject = isset($_POST['subject']) ? sanitizeInput($_POST['subject']) : (isset($_GET['subject']) ? sanitizeInput($_GET['subject']) : '');
$selected_term = isset($_POST['term']) ? sanitizeInput($_POST['term']) : (isset($_GET['term']) ? sanitizeInput($_GET['term']) : 'Term 1');
$success_message = $error_message = "";

// Validate selected class
if (!in_array($selected_class, $teacher_classes)) {
    $selected_class = $teacher_classes[0];
}

// Get subjects for this teacher and class
$subjects = getTeacherSubjects($teacher_id, $selected_class);

// If no subject is selected or the selected subject is not valid, select the first one
if (empty($selected_subject) || !in_array($selected_subject, $subjects)) {
    $selected_subject = count($subjects) > 0 ? $subjects[0] : '';
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_marks"])) {
    $class_id = sanitizeInput($_POST['class_id']);
    $subject = sanitizeInput($_POST['subject']);
    $term = sanitizeInput($_POST['term']);
    $marks_data = isset($_POST['marks']) ? $_POST['marks'] : array();
    
    // Validate input
    if (empty($class_id) || empty($subject) || empty($term)) {
        $error_message = "Class, subject, and term are required.";
    } elseif (empty($marks_data)) {
        $error_message = "No marks data provided.";
    } else {
        // Save marks for each student
        $success_counter = 0;
        $error_counter = 0;
        
        foreach ($marks_data as $student_id => $mark) {
            $mark = sanitizeInput($mark);
            
            // Validate mark
            if (!is_numeric($mark) || $mark < 0 || $mark > 100) {
                $error_counter++;
                continue;
            }
            
            // Check if mark record exists for this student, subject, and term
            $check_sql = "SELECT id FROM marks 
                         WHERE student_id = $student_id 
                         AND subject = '$subject' 
                         AND term = '$term'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update existing record
                $mark_id = mysqli_fetch_assoc($check_result)['id'];
                $update_sql = "UPDATE marks 
                              SET marks = $mark, 
                                  teacher_id = $teacher_id 
                              WHERE id = $mark_id";
                
                if (mysqli_query($conn, $update_sql)) {
                    $success_counter++;
                } else {
                    $error_counter++;
                }
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO marks 
                              (student_id, class_id, subject, term, marks, out_of, teacher_id) 
                              VALUES 
                              ($student_id, '$class_id', '$subject', '$term', $mark, 100, $teacher_id)";
                
                if (mysqli_query($conn, $insert_sql)) {
                    $success_counter++;
                } else {
                    $error_counter++;
                }
            }
        }
        
        if ($error_counter > 0) {
            $error_message = "Some marks could not be saved. $success_counter succeeded, $error_counter failed.";
        } else {
            $success_message = "Marks saved successfully for $success_counter students.";
        }
        
        // Set the selected values for the form
        $selected_class = $class_id;
        $selected_subject = $subject;
        $selected_term = $term;
    }
}

// Get students for the selected class
$students = getStudentsByClass($selected_class);
?>

<h2>Manage Marks</h2>

<?php
if (!empty($success_message)) {
    echo displaySuccess($success_message);
} elseif (!empty($error_message)) {
    echo displayError($error_message);
}
?>

<div class="card mb-20">
    <h3 class="card-title">Select Class, Subject, and Term</h3>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="select-form">
        <div class="form-group">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id" required onchange="updateSubjects()">
                <?php foreach ($teacher_classes as $class): ?>
                    <option value="<?php echo $class; ?>" <?php if ($selected_class == $class) echo "selected"; ?>>
                        <?php echo $class; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="subject">Subject</label>
            <select id="subject" name="subject" required>
                <?php if (!empty($subjects)): ?>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject; ?>" <?php if ($selected_subject == $subject) echo "selected"; ?>>
                            <?php echo $subject; ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No subjects assigned</option>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="term">Term</label>
            <select id="term" name="term" required>
                <option value="Term 1" <?php if ($selected_term == "Term 1") echo "selected"; ?>>Term 1</option>
                <option value="Term 2" <?php if ($selected_term == "Term 2") echo "selected"; ?>>Term 2</option>
                <option value="Term 3" <?php if ($selected_term == "Term 3") echo "selected"; ?>>Term 3</option>
            </select>
        </div>
        
        <div class="form-group">
            <input type="submit" name="select" value="Select">
        </div>
    </form>
</div>

<?php if (!empty($selected_class) && !empty($selected_subject) && !empty($students)): ?>
    <div class="card">
        <h3 class="card-title">Enter Marks for <?php echo "$selected_class - $selected_subject ($selected_term)"; ?></h3>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
            <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
            <input type="hidden" name="term" value="<?php echo $selected_term; ?>">
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Marks (out of 100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php
                            // Get current mark if it exists
                            $mark_sql = "SELECT marks FROM marks 
                                        WHERE student_id = {$student['id']} 
                                        AND subject = '$selected_subject' 
                                        AND term = '$selected_term'";
                            $mark_result = mysqli_query($conn, $mark_sql);
                            $current_mark = (mysqli_num_rows($mark_result) > 0) ? mysqli_fetch_assoc($mark_result)['marks'] : '';
                            ?>
                            <tr>
                                <td><?php echo $student["student_id"]; ?></td>
                                <td><?php echo $student["name"]; ?></td>
                                <td>
                                    <input type="number" name="marks[<?php echo $student['id']; ?>]" 
                                           value="<?php echo $current_mark; ?>" 
                                           min="0" max="100" required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="form-group">
                <input type="submit" name="save_marks" value="Save Marks">
            </div>
        </form>
    </div>
<?php elseif (!empty($selected_class) && !empty($selected_subject)): ?>
    <div class="card">
        <h3 class="card-title">No Students</h3>
        <p>There are no students in this class.</p>
    </div>
<?php endif; ?>

<script>
// Function to update subjects based on selected class
function updateSubjects() {
    const classId = document.getElementById('class_id').value;
    const form = document.getElementById('select-form');
    
    // Submit the form to refresh the page with the new class
    form.submit();
}
</script>

<?php
// Include the footer
include_once '../includes/footer.php';
?>