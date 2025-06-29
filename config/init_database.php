<?php
$host = 'localhost';
$username = 'root';
$password = '2001';
$database = 'school_management';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    $pdo->exec("USE $database");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            user_type ENUM('admin', 'teacher', 'student', 'other_staff') NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teachers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            teacher_id VARCHAR(20) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            level ENUM('ordinary', 'advanced') NOT NULL,
            subjects TEXT,
            contact_number VARCHAR(15),
            address TEXT,
            is_class_teacher BOOLEAN DEFAULT FALSE,
            class_assigned VARCHAR(10),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS other_staff (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            staff_id VARCHAR(20) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            position VARCHAR(50),
            contact_number VARCHAR(15),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            student_id VARCHAR(20) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            class VARCHAR(10) NOT NULL,
            contact_number VARCHAR(15),
            parent_name VARCHAR(100),
            parent_contact VARCHAR(15),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS timetables (
            id INT AUTO_INCREMENT PRIMARY KEY,
            class VARCHAR(10) NOT NULL,
            day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
            period1_subject VARCHAR(50),
            period1_teacher_id INT,
            period2_subject VARCHAR(50),
            period2_teacher_id INT,
            period3_subject VARCHAR(50),
            period3_teacher_id INT,
            period4_subject VARCHAR(50),
            period4_teacher_id INT,
            period5_subject VARCHAR(50),
            period5_teacher_id INT,
            period6_subject VARCHAR(50),
            period6_teacher_id INT,
            period7_subject VARCHAR(50),
            period7_teacher_id INT,
            period8_subject VARCHAR(50),
            period8_teacher_id INT,
            FOREIGN KEY (period1_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period2_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period3_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period4_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period5_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period6_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period7_teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (period8_teacher_id) REFERENCES teachers(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            class VARCHAR(10) NOT NULL,
            date DATE NOT NULL,
            status ENUM('present', 'absent', 'late') NOT NULL,
            marked_by INT NOT NULL,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (marked_by) REFERENCES teachers(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS marks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            class VARCHAR(10) NOT NULL,
            subject VARCHAR(50) NOT NULL,
            term ENUM('1', '2', '3') NOT NULL,
            marks DECIMAL(5,2) NOT NULL,
            added_by INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (added_by) REFERENCES teachers(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            type ENUM('student', 'staff') NOT NULL,
            recipient_id INT,
            recipient_name VARCHAR(100),
            date DATE NOT NULL,
            added_by INT NOT NULL,
            FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            location VARCHAR(100),
            added_by INT NOT NULL,
            FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            message TEXT NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read BOOLEAN DEFAULT FALSE
        )
    ");

    $pdo->exec("
        INSERT IGNORE INTO users (username, password, user_type, email) 
        VALUES ('admin', 'admin123', 'admin', 'admin@school.com')
    ");

    $pdo->exec("
        INSERT IGNORE INTO users (username, password, user_type, email) 
        VALUES ('teacher1', 'teacher123', 'teacher', 'teacher1@school.com')
    ");

    $pdo->exec("
        INSERT IGNORE INTO teachers (user_id, teacher_id, full_name, level, subjects, is_class_teacher, class_assigned) 
        VALUES (2, 'T001', 'John Smith', 'advanced', 'Mathematics, Physics', 1, '10A')
    ");

    $pdo->exec("
        INSERT IGNORE INTO users (username, password, user_type, email) 
        VALUES ('student1', 'student123', 'student', 'student1@school.com')
    ");

    $pdo->exec("
        INSERT IGNORE INTO students (user_id, student_id, full_name, class, parent_name, parent_contact) 
        VALUES (3, 'S001', 'Alice Johnson', '10A', 'Robert Johnson', '555-0123')
    ");

    $pdo->exec("
        INSERT IGNORE INTO users (username, password, user_type, email) 
        VALUES ('staff1', 'staff123', 'other_staff', 'staff1@school.com')
    ");

    $pdo->exec("
        INSERT IGNORE INTO other_staff (user_id, staff_id, full_name, position, contact_number) 
        VALUES (4, 'ST001', 'Mary Wilson', 'Librarian', '555-0456')
    ");

    echo "Database initialized successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>