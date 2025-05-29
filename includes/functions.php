<?php
/**
 * Common Functions
 * Contains all the reusable functions for the application
 */

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user type
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function isStaff() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'staff';
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
}

function isTeacher() {
    if (!isStaff()) return false;
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT id FROM teachers WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    return mysqli_num_rows($result) > 0;
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit;
}

// Clean and sanitize input data
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($conn) {
        $data = mysqli_real_escape_string($conn, $data);
    }
    return $data;
}

// Display error message
function displayError($message) {
    return "<div class='error-message'>$message</div>";
}

// Display success message
function displaySuccess($message) {
    return "<div class='success-message'>$message</div>";
}

// Get user details from ID
function getUserDetails($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

// Get teacher details from user ID
function getTeacherDetailsByUserId($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM teachers WHERE teacher_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

// Get teacher details from teacher ID
function getTeacherDetails($teacher_id) {
    global $conn;
    
    $sql = "SELECT * FROM teachers WHERE id = $teacher_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

// Get student details from user ID
function getStudentDetailsByUserId($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM students WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

// Get student details from student ID
function getStudentDetails($student_id) {
    global $conn;
    
    $sql = "SELECT * FROM students WHERE id = $student_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

// Check if teacher has first period for a class
function hasFirstPeriod($teacher_id, $class_id) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM timetables 
            WHERE class_id = '$class_id' AND 
            (period_1_teacher = $teacher_id)";
    
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'] > 0;
}

// Check if teacher teaches a class
function teachesClass($teacher_id, $class_id) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM timetables 
            WHERE class_id = '$class_id' AND 
            (period_1_teacher = $teacher_id OR 
             period_2_teacher = $teacher_id OR 
             period_3_teacher = $teacher_id OR 
             period_4_teacher = $teacher_id OR 
             period_5_teacher = $teacher_id OR 
             period_6_teacher = $teacher_id OR 
             period_7_teacher = $teacher_id OR 
             period_8_teacher = $teacher_id)";
    
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'] > 0;
}

// Get all classes a teacher teaches
function getTeacherClasses($teacher_id) {
    global $conn;
    
    $sql = "SELECT DISTINCT class_id FROM timetables 
            WHERE period_1_teacher = $teacher_id OR 
                  period_2_teacher = $teacher_id OR 
                  period_3_teacher = $teacher_id OR 
                  period_4_teacher = $teacher_id OR 
                  period_5_teacher = $teacher_id OR 
                  period_6_teacher = $teacher_id OR 
                  period_7_teacher = $teacher_id OR 
                  period_8_teacher = $teacher_id";
    
    $result = mysqli_query($conn, $sql);
    $classes = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row['class_id'];
    }
    
    return $classes;
}

// Get subjects taught by a teacher to a specific class
function getTeacherSubjects($teacher_id, $class_id) {
    global $conn;
    
    $subjects = array();
    
    $sql = "SELECT 
                period_1_subject, period_2_subject, period_3_subject, period_4_subject,
                period_5_subject, period_6_subject, period_7_subject, period_8_subject
            FROM timetables 
            WHERE class_id = '$class_id' AND 
                (period_1_teacher = $teacher_id OR 
                 period_2_teacher = $teacher_id OR 
                 period_3_teacher = $teacher_id OR 
                 period_4_teacher = $teacher_id OR 
                 period_5_teacher = $teacher_id OR 
                 period_6_teacher = $teacher_id OR 
                 period_7_teacher = $teacher_id OR 
                 period_8_teacher = $teacher_id)";
    
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        for ($i = 1; $i <= 8; $i++) {
            $period_col = "period_{$i}_subject";
            $teacher_col = "period_{$i}_teacher";
            
            if (!empty($row[$period_col]) && !in_array($row[$period_col], $subjects)) {
                $subjects[] = $row[$period_col];
            }
        }
    }
    
    return $subjects;
}

// Get all students in a class
function getStudentsByClass($class_id) {
    global $conn;
    
    $sql = "SELECT * FROM students WHERE class = '$class_id' ORDER BY name";
    $result = mysqli_query($conn, $sql);
    
    $students = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    
    return $students;
}

// Get list of all classes
function getAllClasses() {
    $ol_classes = array();
    $al_classes = array();
    
    // O/L classes (Grade 6-10, sections A-H)
    for ($grade = 6; $grade <= 10; $grade++) {
        for ($section = 'A'; $section <= 'H'; $section++) {
            $ol_classes[] = "Grade $grade $section";
        }
    }
    
    // A/L classes (Grade 12-13)
    for ($grade = 12; $grade <= 13; $grade++) {
        $al_classes[] = "Grade $grade";
    }
    
    return array('OL' => $ol_classes, 'AL' => $al_classes);
}

// Get list of subjects by level
function getSubjectsByLevel($level) {
    $subjects = array();
    
    if ($level == 'Ordinary Level') {
        $subjects = array(
            'Mathematics',
            'Science',
            'English',
            'History',
            'Geography',
            'Religion',
            'Music',
            'Art',
            'Physical Education',
            'Information Technology'
        );
    } else { // Advanced Level
        $subjects = array(
            'Physics',
            'Chemistry',
            'Biology',
            'Combined Mathematics',
            'Information Technology',
            'Business Studies',
            'Accounting',
            'Economics',
            'Political Science',
            'Geography',
            'Logic & Scientific Method',
            'English'
        );
    }
    
    return $subjects;
}

// Calculate student rank in class for a subject
function calculateStudentRank($student_id, $class_id, $subject, $term) {
    global $conn;
    
    $sql = "SELECT s.id, s.name, m.marks
            FROM students s
            JOIN marks m ON s.id = m.student_id
            WHERE s.class = '$class_id'
            AND m.subject = '$subject'
            AND m.term = '$term'
            ORDER BY m.marks DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $rank = 0;
    $count = 1;
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['id'] == $student_id) {
            $rank = $count;
            break;
        }
        $count++;
    }
    
    return $rank;
}

// Calculate student average for a term
function calculateStudentAverage($student_id, $term) {
    global $conn;
    
    $sql = "SELECT AVG(marks) as average
            FROM marks
            WHERE student_id = $student_id
            AND term = '$term'";
    
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    return round($row['average'], 2);
}
?>