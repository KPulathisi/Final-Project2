<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !isStaff()) {
    redirect("/login.php");
}

// Check if the staff member is a teacher
$is_teacher = isTeacher();

// Get teacher details if applicable
$teacher_details = null;
$teacher_id = null;
$teacher_classes = array();

if ($is_teacher) {
    $teacher_details = getTeacherDetailsByUserId($_SESSION['user_id']);
    if ($teacher_details) {
        $teacher_id = $teacher_details['id'];
        $teacher_classes = getTeacherClasses($teacher_id);
    }
}
?>

<h2>Staff Dashboard</h2>
<p>Welcome, <?php echo $_SESSION["username"]; ?>! You are logged in as a Staff member.</p>

<?php if ($is_teacher): ?>
    <div class="dashboard-container">
        <!-- Teacher Information Card -->
        <div class="dashboard-card">
            <h3 class="dashboard-card-title">My Information</h3>
            <div class="dashboard-card-content">
                <p><strong>Name:</strong> <?php echo $teacher_details['name']; ?></p>
                <p><strong>Teacher ID:</strong> <?php echo $teacher_details['teacher_id']; ?></p>
                <p><strong>Level:</strong> <?php echo $teacher_details['level']; ?></p>
                <p><strong>Subjects:</strong> <?php echo $teacher_details['subjects']; ?></p>
            </div>
        </div>
        
        <!-- My Classes Card -->
        <div class="dashboard-card">
            <h3 class="dashboard-card-title">My Classes</h3>
            <div class="dashboard-card-content">
                <?php if (count($teacher_classes) > 0): ?>
                    <ul>
                        <?php foreach ($teacher_classes as $class): ?>
                            <li>
                                <?php echo $class; ?>
                                <?php if (hasFirstPeriod($teacher_id, $class)): ?>
                                    <a href="manage_students.php?class=<?php echo $class; ?>">(Manage Students)</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>You have not been assigned to any classes yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Manage Marks Card -->
        <div class="dashboard-card">
            <h3 class="dashboard-card-title">Manage Marks</h3>
            <div class="dashboard-card-content">
                <p>Record and update student marks for your classes.</p>
            </div>
            <a href="manage_marks.php" class="dashboard-card-link">Manage Marks</a>
        </div>
        
        <!-- My Timetable Card -->
        <div class="dashboard-card">
            <h3 class="dashboard-card-title">My Timetable</h3>
            <div class="dashboard-card-content">
                <p>View your teaching schedule for the week.</p>
            </div>
            <a href="view_timetable.php" class="dashboard-card-link">View Timetable</a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <h3 class="card-title">Staff Portal</h3>
        <p>Welcome to the staff portal. You are currently logged in as a non-teaching staff member.</p>
        <p>Please note that as a non-teaching staff member, you have limited access to the system.</p>
    </div>
<?php endif; ?>

<?php
// Include the footer
include_once '../includes/footer.php';
?>