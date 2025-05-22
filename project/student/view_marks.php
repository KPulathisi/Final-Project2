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

// Get selected term from GET parameter or default to Term 3
$term = isset($_GET['term']) ? sanitizeInput($_GET['term']) : 'Term 3';

// Get all terms with data for this student
$sql_terms = "SELECT DISTINCT term FROM marks WHERE student_id = $student_id ORDER BY 
             CASE 
                WHEN term = 'Term 1' THEN 1
                WHEN term = 'Term 2' THEN 2
                WHEN term = 'Term 3' THEN 3
                ELSE 4
             END";
$result_terms = mysqli_query($conn, $sql_terms);
$terms = array();
while ($row = mysqli_fetch_assoc($result_terms)) {
    $terms[] = $row['term'];
}

// If no specific term is selected or the selected term doesn't have data,
// select the first available term
if (empty($term) || !in_array($term, $terms)) {
    $term = !empty($terms) ? $terms[0] : 'Term 3';
}

// Get marks for the selected term
$sql_marks = "SELECT m.*, t.name as teacher_name
             FROM marks m
             JOIN teachers t ON m.teacher_id = t.id
             WHERE m.student_id = $student_id
             AND m.term = '$term'
             ORDER BY m.subject";
$result_marks = mysqli_query($conn, $sql_marks);

// Calculate summary
$total_marks = 0;
$subject_count = 0;
$marks_data = array();

while ($row = mysqli_fetch_assoc($result_marks)) {
    $row['rank'] = calculateStudentRank($student_id, $class_id, $row['subject'], $term);
    $marks_data[] = $row;
    $total_marks += $row['marks'];
    $subject_count++;
}

// Calculate average
$average = 0;
if ($subject_count > 0) {
    $average = round($total_marks / $subject_count, 2);
}

// Get average of all students in the class for comparison
$sql_class_avg = "SELECT AVG(m.marks) as class_average
                 FROM marks m
                 JOIN students s ON m.student_id = s.id
                 WHERE s.class = '$class_id'
                 AND m.term = '$term'";
$result_class_avg = mysqli_query($conn, $sql_class_avg);
$class_average = mysqli_fetch_assoc($result_class_avg)['class_average'];
$class_average = round($class_average, 2);

// Calculate overall class rank
$sql_ranks = "SELECT s.id, s.name, AVG(m.marks) as avg_marks
             FROM students s
             JOIN marks m ON s.id = m.student_id
             WHERE s.class = '$class_id'
             AND m.term = '$term'
             GROUP BY s.id
             ORDER BY avg_marks DESC";
$result_ranks = mysqli_query($conn, $sql_ranks);

$overall_rank = 0;
$total_students = 0;
while ($row = mysqli_fetch_assoc($result_ranks)) {
    $total_students++;
    if ($row['id'] == $student_id) {
        $overall_rank = $total_students;
    }
}
?>

<h2>My Academic Report</h2>

<!-- Term Selection -->
<div class="card mb-20">
    <h3 class="card-title">Select Term</h3>
    
    <div class="form-group">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
            <label for="term">Term:</label>
            <select id="term" name="term" onchange="this.form.submit()">
                <?php foreach (['Term 1', 'Term 2', 'Term 3'] as $t): ?>
                    <option value="<?php echo $t; ?>" <?php if ($term == $t) echo "selected"; ?>>
                        <?php echo $t; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Report Card -->
<div class="report-card">
    <div class="report-header">
        <h2>Academic Report Card</h2>
        <h3><?php echo $term; ?> - <?php echo date('Y'); ?></h3>
    </div>
    
    <div class="report-info">
        <div class="report-info-item">
            <div class="report-info-label">Student Name:</div>
            <div><?php echo $student_details['name']; ?></div>
        </div>
        <div class="report-info-item">
            <div class="report-info-label">Student ID:</div>
            <div><?php echo $student_details['student_id']; ?></div>
        </div>
        <div class="report-info-item">
            <div class="report-info-label">Class:</div>
            <div><?php echo $class_id; ?></div>
        </div>
        <div class="report-info-item">
            <div class="report-info-label">Term:</div>
            <div><?php echo $term; ?></div>
        </div>
    </div>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Marks (out of 100)</th>
                <th>Grade</th>
                <th>Class Rank</th>
                <th>Teacher</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($marks_data) > 0) {
                foreach ($marks_data as $mark) {
                    echo "<tr>";
                    echo "<td>" . $mark['subject'] . "</td>";
                    echo "<td>" . $mark['marks'] . "</td>";
                    echo "<td>";
                    
                    // Calculate grade
                    $marks = $mark['marks'];
                    if ($marks >= 90) echo "A+";
                    elseif ($marks >= 80) echo "A";
                    elseif ($marks >= 70) echo "B+";
                    elseif ($marks >= 60) echo "B";
                    elseif ($marks >= 50) echo "C+";
                    elseif ($marks >= 40) echo "C";
                    elseif ($marks >= 30) echo "D";
                    else echo "F";
                    
                    echo "</td>";
                    echo "<td>" . $mark['rank'] . "</td>";
                    echo "<td>" . $mark['teacher_name'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No marks recorded for this term</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <div class="report-summary">
        <h3>Summary</h3>
        <table class="report-table">
            <tr>
                <th>Average</th>
                <th>Class Average</th>
                <th>Overall Class Rank</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <td><?php echo $average; ?>%</td>
                <td><?php echo $class_average; ?>%</td>
                <td>
                    <?php 
                    if ($overall_rank > 0) {
                        echo $overall_rank . " out of " . $total_students;
                    } else {
                        echo "N/A";
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($average >= 90) {
                        echo "Excellent performance!";
                    } elseif ($average >= 80) {
                        echo "Very good performance.";
                    } elseif ($average >= 70) {
                        echo "Good performance.";
                    } elseif ($average >= 60) {
                        echo "Satisfactory performance.";
                    } elseif ($average >= 50) {
                        echo "Average performance. Need to improve.";
                    } elseif ($average >= 40) {
                        echo "Below average. Needs significant improvement.";
                    } else {
                        echo "Poor performance. Immediate attention required.";
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="mt-20 text-center">
        <button type="button" onclick="window.print();" class="button-secondary">Print Report Card</button>
    </div>
</div>

<?php
// Include the footer
include_once '../includes/footer.php';
?>