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

$student_id = $student_details['id'];
$class_id = $student_details['class'];

// Get attendance summary
$sql_attendance = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
                  FROM attendance
                  WHERE student_id = $student_id";
$result_attendance = mysqli_query($conn, $sql_attendance);
$attendance_summary = mysqli_fetch_assoc($result_attendance);

// Calculate attendance percentage
$attendance_percentage = 0;
if ($attendance_summary['total_days'] > 0) {
    $attendance_percentage = round(($attendance_summary['present_days'] / $attendance_summary['total_days']) * 100, 2);
}

// Get marks summary for current term (Term 3)
$current_term = "Term 3";
$sql_marks = "SELECT subject, marks
              FROM marks
              WHERE student_id = $student_id
              AND term = '$current_term'";
$result_marks = mysqli_query($conn, $sql_marks);

// Calculate average marks
$total_marks = 0;
$subject_count = 0;
$marks_summary = array();

while ($row = mysqli_fetch_assoc($result_marks)) {
    $marks_summary[] = $row;
    $total_marks += $row['marks'];
    $subject_count++;
}

$average_marks = 0;
if ($subject_count > 0) {
    $average_marks = round($total_marks / $subject_count, 2);
}
?>

<h2>Student Dashboard</h2>
<p>Welcome, <?php echo $_SESSION["username"]; ?>! You are logged in as a Student.</p>

<div class="dashboard-container">
    <!-- Student Information Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">My Information</h3>
        <div class="dashboard-card-content">
            <p><strong>Name:</strong> <?php echo $student_details['name']; ?></p>
            <p><strong>Student ID:</strong> <?php echo $student_details['student_id']; ?></p>
            <p><strong>Class:</strong> <?php echo $student_details['class']; ?></p>
            <p><strong>Date of Birth:</strong> <?php echo $student_details['dob']; ?></p>
        </div>
    </div>
    
    <!-- Attendance Summary Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Attendance Summary</h3>
        <div class="dashboard-card-content">
            <p><strong>Present Days:</strong> <?php echo $attendance_summary['present_days']; ?></p>
            <p><strong>Absent Days:</strong> <?php echo $attendance_summary['absent_days']; ?></p>
            <p><strong>Late Days:</strong> <?php echo $attendance_summary['late_days']; ?></p>
            <p><strong>Attendance Rate:</strong> <?php echo $attendance_percentage; ?>%</p>
        </div>
        <a href="view_attendance.php" class="dashboard-card-link">View Full Attendance</a>
    </div>
    
    <!-- Academic Performance Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">Academic Performance</h3>
        <div class="dashboard-card-content">
            <p><strong>Current Term:</strong> <?php echo $current_term; ?></p>
            <p><strong>Average Marks:</strong> <?php echo $average_marks; ?>%</p>
            <p><strong>Subjects:</strong> <?php echo $subject_count; ?></p>
        </div>
        <a href="view_marks.php" class="dashboard-card-link">View Full Report</a>
    </div>
    
    <!-- Timetable Card -->
    <div class="dashboard-card">
        <h3 class="dashboard-card-title">My Timetable</h3>
        <div class="dashboard-card-content">
            <p>View your class schedule for the week.</p>
        </div>
        <a href="view_timetable.php" class="dashboard-card-link">View Timetable</a>
    </div>
</div>

<!-- Recent Marks Summary -->
<div class="card mt-20">
    <h3 class="card-title">Recent Academic Performance (<?php echo $current_term; ?>)</h3>
    
    <?php if ($subject_count > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Marks</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($marks_summary as $mark): ?>
                        <tr>
                            <td><?php echo $mark['subject']; ?></td>
                            <td><?php echo $mark['marks']; ?>%</td>
                            <td>
                                <?php
                                $marks = $mark['marks'];
                                if ($marks >= 90) echo "A+";
                                elseif ($marks >= 80) echo "A";
                                elseif ($marks >= 70) echo "B+";
                                elseif ($marks >= 60) echo "B";
                                elseif ($marks >= 50) echo "C+";
                                elseif ($marks >= 40) echo "C";
                                elseif ($marks >= 30) echo "D";
                                else echo "F";
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No marks recorded for the current term.</p>
    <?php endif; ?>
</div>

<?php
// Include the footer
include_once '../includes/footer.php';
?>