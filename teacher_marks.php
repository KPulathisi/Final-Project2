<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireUserType(['teacher']);

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get teacher information
$query = "SELECT * FROM teachers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';

// Handle marks submission
if ($_POST && isset($_POST['save_marks'])) {
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $term = $_POST['term'];
    
    foreach ($_POST['marks'] as $student_id => $marks) {
        if (!empty($marks)) {
            // Check if marks already exist
            $check_query = "SELECT id FROM marks WHERE student_id = ? AND class = ? AND subject = ? AND term = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$student_id, $class, $subject, $term]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                // Update existing marks
                $query = "UPDATE marks SET marks = ?, added_by = ?, added_at = NOW() WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$marks, $teacher['id'], $existing['id']]);
            } else {
                // Insert new marks
                $query = "INSERT INTO marks (student_id, class, subject, term, marks, added_by) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$student_id, $class, $subject, $term, $marks, $teacher['id']]);
            }
        }
    }
    
    $message = '<div class="alert alert-success">Marks saved successfully!</div>';
}

// Get students and existing marks
$students = [];
$existing_marks = [];
$selected_class = $_GET['class'] ?? '';
$selected_subject = $_GET['subject'] ?? '';
$selected_term = $_GET['term'] ?? '1';

if ($selected_class && $selected_subject) {
    $query = "SELECT * FROM students WHERE class = ? ORDER BY full_name";
    $stmt = $db->prepare($query);
    $stmt->execute([$selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get existing marks
    $query = "SELECT student_id, marks FROM marks WHERE class = ? AND subject = ? AND term = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$selected_class, $selected_subject, $selected_term]);
    $marks_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($marks_records as $record) {
        $existing_marks[$record['student_id']] = $record['marks'];
    }
}

if (isset($_GET['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Marks - Teacher Dashboard</title>
    <link rel="stylesheet" href="css/teacher.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Teacher Panel</h3>
                <p><?php echo htmlspecialchars($teacher['full_name']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="teacher_dashboard.php">Dashboard</a></li>
                <li><a href="teacher_class.php">My Class</a></li>
                <li><a href="teacher_attendance.php">Mark Attendance</a></li>
                <li><a href="teacher_marks.php" class="active">Manage Marks</a></li>
                <li><a href="teacher_timetable.php">My Timetable</a></li>
                <li><a href="teacher_profile.php">Profile</a></li>
                <li><a href="?logout=1" style="color: #ff6b6b;">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Manage Marks</h2>
                <p>Add and update student marks</p>
            </div>

            <?php echo $message; ?>

            <!-- Selection Form -->
            <div class="card">
                <h3>Select Class, Subject and Term</h3>
                <form method="GET">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <label>Class</label>
                            <select name="class" class="form-control" required>
                                <option value="">Select Class</option>
                                <option value="9A" <?php echo ($selected_class == '9A') ? 'selected' : ''; ?>>9A</option>
                                <option value="9B" <?php echo ($selected_class == '9B') ? 'selected' : ''; ?>>9B</option>
                                <option value="10A" <?php echo ($selected_class == '10A') ? 'selected' : ''; ?>>10A</option>
                                <option value="10B" <?php echo ($selected_class == '10B') ? 'selected' : ''; ?>>10B</option>
                                <option value="11A" <?php echo ($selected_class == '11A') ? 'selected' : ''; ?>>11A</option>
                                <option value="11B" <?php echo ($selected_class == '11B') ? 'selected' : ''; ?>>11B</option>
                                <option value="12A" <?php echo ($selected_class == '12A') ? 'selected' : ''; ?>>12A</option>
                                <option value="12B" <?php echo ($selected_class == '12B') ? 'selected' : ''; ?>>12B</option>
                            </select>
                        </div>
                        <div>
                            <label>Subject</label>
                            <select name="subject" class="form-control" required>
                                <option value="">Select Subject</option>
                                <option value="Mathematics" <?php echo ($selected_subject == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                                <option value="English" <?php echo ($selected_subject == 'English') ? 'selected' : ''; ?>>English</option>
                                <option value="Science" <?php echo ($selected_subject == 'Science') ? 'selected' : ''; ?>>Science</option>
                                <option value="History" <?php echo ($selected_subject == 'History') ? 'selected' : ''; ?>>History</option>
                                <option value="Geography" <?php echo ($selected_subject == 'Geography') ? 'selected' : ''; ?>>Geography</option>
                                <option value="Physics" <?php echo ($selected_subject == 'Physics') ? 'selected' : ''; ?>>Physics</option>
                                <option value="Chemistry" <?php echo ($selected_subject == 'Chemistry') ? 'selected' : ''; ?>>Chemistry</option>
                                <option value="Biology" <?php echo ($selected_subject == 'Biology') ? 'selected' : ''; ?>>Biology</option>
                            </select>
                        </div>
                        <div>
                            <label>Term</label>
                            <select name="term" class="form-control" required>
                                <option value="1" <?php echo ($selected_term == '1') ? 'selected' : ''; ?>>Term 1</option>
                                <option value="2" <?php echo ($selected_term == '2') ? 'selected' : ''; ?>>Term 2</option>
                                <option value="3" <?php echo ($selected_term == '3') ? 'selected' : ''; ?>>Term 3</option>
                            </select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Load Students</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (!empty($students)): ?>
            <!-- Marks Form -->
            <div class="card">
                <h3>Enter Marks for <?php echo htmlspecialchars($selected_class); ?> - <?php echo htmlspecialchars($selected_subject); ?> - Term <?php echo $selected_term; ?></h3>
                <form method="POST">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($selected_class); ?>">
                    <input type="hidden" name="subject" value="<?php echo htmlspecialchars($selected_subject); ?>">
                    <input type="hidden" name="term" value="<?php echo htmlspecialchars($selected_term); ?>">
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Marks (out of 100)</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td>
                                        <input type="number" name="marks[<?php echo $student['id']; ?>]" 
                                               class="form-control" min="0" max="100" step="0.01"
                                               value="<?php echo isset($existing_marks[$student['id']]) ? $existing_marks[$student['id']] : ''; ?>"
                                               onchange="calculateGrade(this)">
                                    </td>
                                    <td>
                                        <span class="grade-display" id="grade_<?php echo $student['id']; ?>">
                                            <?php 
                                            if (isset($existing_marks[$student['id']])) {
                                                $marks = $existing_marks[$student['id']];
                                                if ($marks >= 90) echo 'A+';
                                                elseif ($marks >= 80) echo 'A';
                                                elseif ($marks >= 70) echo 'B';
                                                elseif ($marks >= 60) echo 'C';
                                                elseif ($marks >= 50) echo 'D';
                                                else echo 'F';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem; text-align: center;">
                        <button type="submit" name="save_marks" class="btn btn-primary">Save Marks</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--teacher-accent);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--teacher-dark);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .grade-display {
            font-weight: bold;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            color: white;
        }
        
        .table input[type="number"] {
            width: 100px;
            padding: 0.5rem;
        }
    </style>

    <script>
        function calculateGrade(input) {
            const marks = parseFloat(input.value);
            const studentId = input.name.match(/\[(\d+)\]/)[1];
            const gradeSpan = document.getElementById('grade_' + studentId);
            
            let grade = '';
            let color = '';
            
            if (marks >= 75) {
                grade = 'A';
                color = '#28a745';
            } else if (marks >= 65) {
                grade = 'B';
                color = '#28a745';
            } else if (marks >= 55) {
                grade = 'C';
                color = '#17a2b8';
            } else if (marks >= 35) {
                grade = 'S';
                color = '#fd7e14';
            } else if (marks >= 0) {
                grade = 'F';
                color = '#dc3545';
            }
            
            gradeSpan.textContent = grade;
            gradeSpan.style.backgroundColor = color;
        }
    </script>
</body>
</html>