<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect("/login.php");
}

// Get counts for dashboard
$sql_users = "SELECT COUNT(*) as count FROM users";
$sql_teachers = "SELECT COUNT(*) as count FROM teachers";
$sql_students = "SELECT COUNT(*) as count FROM students";
$sql_staff = "SELECT COUNT(*) as count FROM staff";
$sql_feedbacks = "SELECT COUNT(*) as count FROM feedbacks";
$sql_pending_teachers = "SELECT COUNT(*) as count FROM teachers WHERE status = 'pending'";
$sql_pending_staff = "SELECT COUNT(*) as count FROM staff WHERE status = 'pending'";

$result_users = mysqli_query($conn, $sql_users);
$result_teachers = mysqli_query($conn, $sql_teachers);
$result_students = mysqli_query($conn, $sql_students);
$result_staff = mysqli_query($conn, $sql_staff);
$result_feedbacks = mysqli_query($conn, $sql_feedbacks);
$result_pending_teachers = mysqli_query($conn, $sql_pending_teachers);
$result_pending_staff = mysqli_query($conn, $sql_pending_staff);

$users_count = mysqli_fetch_assoc($result_users)['count'];
$teachers_count = mysqli_fetch_assoc($result_teachers)['count'];
$students_count = mysqli_fetch_assoc($result_students)['count'];
$staff_count = mysqli_fetch_assoc($result_staff)['count'];
$feedbacks_count = mysqli_fetch_assoc($result_feedbacks)['count'];
$pending_teachers_count = mysqli_fetch_assoc($result_pending_teachers)['count'];
$pending_staff_count = mysqli_fetch_assoc($result_pending_staff)['count'];
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <?php echo $_SESSION["username"]; ?>! You are logged in as an Administrator.</p>

<div class="dashboard-container">
    <!-- User Management Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">User Management</h3>
        <div class="dashboard-card-content">
            <p>Total users: <?php echo $users_count; ?></p>
            <p>Manage all system users and their access levels.</p>
        </div>
        <a href="manage_users.php" class="dashboard-card-link">Manage Users</a>
    </div>
    
    <!-- Teacher Management Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Teacher Management</h3>
        <div class="dashboard-card-content">
            <p>Total teachers: <?php echo $teachers_count; ?></p>
            <p>Pending approvals: <?php echo $pending_teachers_count; ?></p>
            <p>Manage teacher registrations and assignments.</p>
        </div>
        <a href="manage_teachers.php" class="dashboard-card-link">Manage Teachers</a>
    </div>
    
    <!-- Staff Management Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Staff Management</h3>
        <div class="dashboard-card-content">
            <p>Total staff: <?php echo $staff_count; ?></p>
            <p>Pending approvals: <?php echo $pending_staff_count; ?></p>
            <p>Manage non-teaching staff registrations.</p>
        </div>
        <a href="manage_staff.php" class="dashboard-card-link">Manage Staff</a>
    </div>
    
    <!-- Student Management Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Student Records</h3>
        <div class="dashboard-card-content">
            <p>Total students: <?php echo $students_count; ?></p>
            <p>View and manage student records across all classes.</p>
        </div>
        <a href="manage_students.php" class="dashboard-card-link">View Students</a>
    </div>
    
    <!-- Timetable Management Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Timetable Management</h3>
        <div class="dashboard-card-content">
            <p>Create and manage class timetables.</p>
            <p>Assign subjects and teachers to specific periods.</p>
        </div>
        <a href="manage_timetables.php" class="dashboard-card-link">Manage Timetables</a>
    </div>
    
    <!-- Feedback Management Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Feedback</h3>
        <div class="dashboard-card-content">
            <p>Total feedback submissions: <?php echo $feedbacks_count; ?></p>
            <p>View feedback submitted by website visitors.</p>
        </div>
        <a href="view_feedback.php" class="dashboard-card-link">View Feedback</a>
    </div>
</div>

<?php
// Include the footer
include_once '../includes/footer.php';
?>