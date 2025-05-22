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

// Get attendance records
$sql = "SELECT a.*, t.name as teacher_name
        FROM attendance a
        JOIN teachers t ON a.marked_by = t.id
        WHERE a.student_id = $student_id
        ORDER BY a.date DESC";
$result = mysqli_query($conn, $sql);

// Calculate attendance summary
$sql_summary = "SELECT 
                 COUNT(*) as total_days,
                 SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                 SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                 SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
               FROM attendance
               WHERE student_id = $student_id";
$result_summary = mysqli_query($conn, $sql_summary);
$summary = mysqli_fetch_assoc($result_summary);

// Calculate attendance percentage
$attendance_percentage = 0;
if ($summary['total_days'] > 0) {
    $attendance_percentage = round(($summary['present_days'] / $summary['total_days']) * 100, 2);
}

// Get monthly attendance
$sql_monthly = "SELECT 
                 DATE_FORMAT(date, '%Y-%m') as month,
                 COUNT(*) as total_days,
                 SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                 SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                 SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
               FROM attendance
               WHERE student_id = $student_id
               GROUP BY DATE_FORMAT(date, '%Y-%m')
               ORDER BY month DESC";
$result_monthly = mysqli_query($conn, $sql_monthly);
?>

<h2>My Attendance</h2>

<!-- Attendance Summary -->
<div class="card mb-20">
    <h3 class="card-title">Attendance Summary</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Total Days</th>
                    <th>Present Days</th>
                    <th>Absent Days</th>
                    <th>Late Days</th>
                    <th>Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $summary['total_days']; ?></td>
                    <td><?php echo $summary['present_days']; ?></td>
                    <td><?php echo $summary['absent_days']; ?></td>
                    <td><?php echo $summary['late_days']; ?></td>
                    <td>
                        <?php echo $attendance_percentage; ?>%
                        <?php
                        if ($attendance_percentage >= 90) {
                            echo " <span style='color: green;'>(Excellent)</span>";
                        } elseif ($attendance_percentage >= 80) {
                            echo " <span style='color: #577BC1;'>(Good)</span>";
                        } elseif ($attendance_percentage >= 70) {
                            echo " <span style='color: orange;'>(Average)</span>";
                        } else {
                            echo " <span style='color: red;'>(Poor)</span>";
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Monthly Attendance -->
<div class="card mb-20">
    <h3 class="card-title">Monthly Attendance</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Days</th>
                    <th>Present Days</th>
                    <th>Absent Days</th>
                    <th>Late Days</th>
                    <th>Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_monthly) > 0) {
                    while ($row = mysqli_fetch_assoc($result_monthly)) {
                        $month_percentage = 0;
                        if ($row['total_days'] > 0) {
                            $month_percentage = round(($row['present_days'] / $row['total_days']) * 100, 2);
                        }
                        
                        echo "<tr>";
                        echo "<td>" . date('F Y', strtotime($row['month'] . '-01')) . "</td>";
                        echo "<td>" . $row['total_days'] . "</td>";
                        echo "<td>" . $row['present_days'] . "</td>";
                        echo "<td>" . $row['absent_days'] . "</td>";
                        echo "<td>" . $row['late_days'] . "</td>";
                        echo "<td>" . $month_percentage . "%</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No attendance records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detailed Attendance Records -->
<div class="card">
    <h3 class="card-title">Detailed Attendance Records</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Status</th>
                    <th>Marked By</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $date = date('Y-m-d', strtotime($row['date']));
                        $day = date('l', strtotime($row['date']));
                        $status = ucfirst($row['status']);
                        $status_class = '';
                        
                        if ($row['status'] == 'present') {
                            $status_class = 'text-success';
                        } elseif ($row['status'] == 'absent') {
                            $status_class = 'text-danger';
                        } elseif ($row['status'] == 'late') {
                            $status_class = 'text-warning';
                        }
                        
                        echo "<tr>";
                        echo "<td>" . $date . "</td>";
                        echo "<td>" . $day . "</td>";
                        echo "<td class='$status_class'>" . $status . "</td>";
                        echo "<td>" . $row['teacher_name'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No attendance records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.text-success {
    color: green;
    font-weight: bold;
}

.text-danger {
    color: red;
    font-weight: bold;
}

.text-warning {
    color: orange;
    font-weight: bold;
}
</style>

<?php
// Include the footer
include_once '../includes/footer.php';
?>