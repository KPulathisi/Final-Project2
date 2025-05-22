<?php
/**
 * Database Configuration
 * Establishes connection to MySQL database
 */

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '2001');
define('DB_NAME', 'sms');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($conn, $sql)) {
    // Select the database
    mysqli_select_db($conn, DB_NAME);
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Create tables if they don't exist
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'staff', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$teachers_table = "CREATE TABLE IF NOT EXISTS teachers (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    level ENUM('Ordinary Level', 'Advanced Level') NOT NULL,
    subjects VARCHAR(255) NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    user_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

$students_table = "CREATE TABLE IF NOT EXISTS students (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    parent_contact VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    user_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

$staff_table = "CREATE TABLE IF NOT EXISTS staff (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    user_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

$timetables_table = "CREATE TABLE IF NOT EXISTS timetables (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    class_id VARCHAR(20) NOT NULL,
    weekday ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    period_1_subject VARCHAR(50) DEFAULT NULL,
    period_1_teacher INT(11) DEFAULT NULL,
    period_2_subject VARCHAR(50) DEFAULT NULL,
    period_2_teacher INT(11) DEFAULT NULL,
    period_3_subject VARCHAR(50) DEFAULT NULL,
    period_3_teacher INT(11) DEFAULT NULL,
    period_4_subject VARCHAR(50) DEFAULT NULL,
    period_4_teacher INT(11) DEFAULT NULL,
    period_5_subject VARCHAR(50) DEFAULT NULL,
    period_5_teacher INT(11) DEFAULT NULL,
    period_6_subject VARCHAR(50) DEFAULT NULL,
    period_6_teacher INT(11) DEFAULT NULL,
    period_7_subject VARCHAR(50) DEFAULT NULL,
    period_7_teacher INT(11) DEFAULT NULL,
    period_8_subject VARCHAR(50) DEFAULT NULL,
    period_8_teacher INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY class_weekday (class_id, weekday)
)";

$teacher_timetables_table = "CREATE TABLE IF NOT EXISTS teacher_timetables (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT(11) NOT NULL,
    weekday ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    period_1_class VARCHAR(20) DEFAULT NULL,
    period_1_subject VARCHAR(50) DEFAULT NULL,
    period_2_class VARCHAR(20) DEFAULT NULL,
    period_2_subject VARCHAR(50) DEFAULT NULL,
    period_3_class VARCHAR(20) DEFAULT NULL,
    period_3_subject VARCHAR(50) DEFAULT NULL,
    period_4_class VARCHAR(20) DEFAULT NULL,
    period_4_subject VARCHAR(50) DEFAULT NULL,
    period_5_class VARCHAR(20) DEFAULT NULL,
    period_5_subject VARCHAR(50) DEFAULT NULL,
    period_6_class VARCHAR(20) DEFAULT NULL,
    period_6_subject VARCHAR(50) DEFAULT NULL,
    period_7_class VARCHAR(20) DEFAULT NULL,
    period_7_subject VARCHAR(50) DEFAULT NULL,
    period_8_class VARCHAR(20) DEFAULT NULL,
    period_8_subject VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY teacher_weekday (teacher_id, weekday),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
)";

$feedbacks_table = "CREATE TABLE IF NOT EXISTS feedbacks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$attendance_table = "CREATE TABLE IF NOT EXISTS attendance (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    class_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    marked_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY student_date (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES teachers(id) ON DELETE CASCADE
)";

$marks_table = "CREATE TABLE IF NOT EXISTS marks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    class_id VARCHAR(20) NOT NULL,
    subject VARCHAR(50) NOT NULL,
    term ENUM('Term 1', 'Term 2', 'Term 3') NOT NULL,
    marks FLOAT NOT NULL,
    out_of FLOAT NOT NULL DEFAULT 100,
    teacher_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY student_subject_term (student_id, subject, term),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
)";

// Execute table creation queries
mysqli_query($conn, $users_table);
mysqli_query($conn, $teachers_table);
mysqli_query($conn, $students_table);
mysqli_query($conn, $staff_table);
mysqli_query($conn, $timetables_table);
mysqli_query($conn, $teacher_timetables_table);
mysqli_query($conn, $feedbacks_table);
mysqli_query($conn, $attendance_table);
mysqli_query($conn, $marks_table);

// Check if admin exists, if not create one
$check_admin = "SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1";
$result = mysqli_query($conn, $check_admin);

if (mysqli_num_rows($result) == 0) {
    // Create default admin user
    $admin_username = "admin";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT); // Default password: admin123
    
    $create_admin = "INSERT INTO users (username, password, user_type) VALUES ('$admin_username', '$admin_password', 'admin')";
    mysqli_query($conn, $create_admin);
}

// Global connection variable
$GLOBALS['conn'] = $conn;

// Function to close database connection
function closeConnection() {
    global $conn;
    mysqli_close($conn);
}

// Register shutdown function to close connection
register_shutdown_function('closeConnection');
?>