<?php
// Include necessary files
require_once 'config.php';
require_once 'functions.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link rel="stylesheet" href="/project/assets/css/styles.css">

</head>
<body>
    <header>
        <div class="logo-container">
            <img src="/project/assets/images/logo.jpeg" alt="School Logo" class="logo">
            <h1>Excellence High School</h1>
        </div>
        <nav>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php">Dashboard</a>
                    <a href="admin/manage_users.php">Manage Users</a>
                    <a href="admin/manage_teachers.php">Manage Teachers</a>
                    <a href="admin/manage_staff.php">Manage Staff</a>
                    <a href="admin/manage_timetables.php">Timetables</a>
                    <a href="admin/view_feedback.php">Feedback</a>
                <?php elseif (isTeacher()): ?>
                    <a href="staff/index.php">Dashboard</a>
                    <?php
                    // Get teacher details
                    $teacher = getTeacherDetailsByUserId($_SESSION['user_id']);
                    if ($teacher) {
                        $teacher_id = $teacher['id'];
                        $classes = getTeacherClasses($teacher_id);
                        
                        foreach ($classes as $class) {
                            if (hasFirstPeriod($teacher_id, $class)) {
                                echo "<a href='staff/manage_students.php?class=$class'>Class $class</a>";
                            }
                        }
                    }
                    ?>
                    <a href="staff/manage_marks.php">Manage Marks</a>
                    <a href="staff/view_timetable.php">My Timetable</a>
                <?php elseif (isStudent()): ?>
                    <a href="student/index.php">Dashboard</a>
                    <a href="student/view_attendance.php">My Attendance</a>
                    <a href="student/view_marks.php">My Marks</a>
                    <a href="student/view_timetable.php">My Timetable</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>