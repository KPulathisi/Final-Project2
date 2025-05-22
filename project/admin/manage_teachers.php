<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect("/login.php");
}

// Initialize variables
$teacher_id = $name = $level = $subjects = $contact_info = $email = $address = "";
$error_message = $success_message = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add Teacher
    if (isset($_POST["add_teacher"])) {
        $teacher_id = sanitizeInput($_POST["teacher_id"]);
        $name = sanitizeInput($_POST["name"]);
        $level = sanitizeInput($_POST["level"]);
        
        // Handle multi-select subjects
        if (isset($_POST["subjects"]) && is_array($_POST["subjects"])) {
            $subjects = implode(", ", $_POST["subjects"]);
        } else {
            $subjects = "";
        }
        
        $contact_info = sanitizeInput($_POST["contact_info"]);
        $email = sanitizeInput($_POST["email"]);
        $address = sanitizeInput($_POST["address"]);
        
        // Validate input
        if (empty($teacher_id) || empty($name) || empty($level) || empty($subjects) || empty($contact_info) || empty($email) || empty($address)) {
            $error_message = "All fields are required.";
        } else {
            // Check if teacher ID already exists
            $check_sql = "SELECT id FROM teachers WHERE teacher_id = '$teacher_id'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error_message = "Teacher ID already exists.";
            } else {
                // Insert teacher
                $sql = "INSERT INTO teachers (teacher_id, name, level, subjects, contact_info, email, address, status) 
                        VALUES ('$teacher_id', '$name', '$level', '$subjects', '$contact_info', '$email', '$address', 'pending')";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Teacher registration successful. Pending approval.";
                    $teacher_id = $name = $level = $subjects = $contact_info = $email = $address = "";
                } else {
                    $error_message = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Approve Teacher
    elseif (isset($_POST["approve_teacher"])) {
        $id = sanitizeInput($_POST["id"]);
        
        // Generate username and password
        $teacher_data = getTeacherDetails($id);
        $username = strtolower(str_replace(' ', '', $teacher_data['name'])) . mt_rand(100, 999);
        $password = substr(md5(time()), 0, 8); // Generate a random 8-character password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user account
        $sql_user = "INSERT INTO users (username, password, user_type) VALUES ('$username', '$hashed_password', 'staff')";
        
        if (mysqli_query($conn, $sql_user)) {
            $user_id = mysqli_insert_id($conn);
            
            // Update teacher status and link to user account
            $sql_teacher = "UPDATE teachers SET status = 'approved', user_id = $user_id WHERE id = $id";
            
            if (mysqli_query($conn, $sql_teacher)) {
                $success_message = "Teacher approved. Username: $username, Password: $password";
            } else {
                $error_message = "Error updating teacher: " . mysqli_error($conn);
                
                // Rollback the user creation if teacher update fails
                mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
            }
        } else {
            $error_message = "Error creating user account: " . mysqli_error($conn);
        }
    }
    
    // Reject Teacher
    elseif (isset($_POST["reject_teacher"])) {
        $id = sanitizeInput($_POST["id"]);
        
        $sql = "DELETE FROM teachers WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Teacher registration rejected.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all teachers
$sql_all = "SELECT * FROM teachers ORDER BY status, name";
$result_all = mysqli_query($conn, $sql_all);

// Get pending teachers
$sql_pending = "SELECT * FROM teachers WHERE status = 'pending' ORDER BY name";
$result_pending = mysqli_query($conn, $sql_pending);

// Get subjects by level
$ol_subjects = getSubjectsByLevel('Ordinary Level');
$al_subjects = getSubjectsByLevel('Advanced Level');
?>

<h2>Manage Teachers</h2>

<?php
if (!empty($success_message)) {
    echo displaySuccess($success_message);
} elseif (!empty($error_message)) {
    echo displayError($error_message);
}
?>

<!-- Teacher Registration Form -->
<div class="card mb-20">
    <h3 class="card-title">Register New Teacher</h3>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="teacher_id">Teacher ID</label>
            <input type="text" id="teacher_id" name="teacher_id" value="<?php echo $teacher_id; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="level">Level</label>
            <select id="level" name="level" required onchange="updateSubjects(this.value)">
                <option value="">Select Level</option>
                <option value="Ordinary Level" <?php if ($level == "Ordinary Level") echo "selected"; ?>>Ordinary Level</option>
                <option value="Advanced Level" <?php if ($level == "Advanced Level") echo "selected"; ?>>Advanced Level</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="subjects">Subjects</label>
            <div id="subject-container">
                <!-- Subjects will be dynamically loaded based on level selection -->
                <p>Please select a level first</p>
            </div>
        </div>
        
        <div class="form-group">
            <label for="contact_info">Contact Number</label>
            <input type="text" id="contact_info" name="contact_info" value="<?php echo $contact_info; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" required><?php echo $address; ?></textarea>
        </div>
        
        <div class="form-group">
            <input type="submit" name="add_teacher" value="Register Teacher">
        </div>
    </form>
</div>

<!-- Pending Teacher Approvals -->
<div class="card mb-20">
    <h3 class="card-title">Pending Teacher Approvals</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Teacher ID</th>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Subjects</th>
                    <th>Contact Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_pending) > 0) {
                    while ($row = mysqli_fetch_assoc($result_pending)) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["teacher_id"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["level"] . "</td>";
                        echo "<td>" . $row["subjects"] . "</td>";
                        echo "<td>" . $row["contact_info"] . "</td>";
                        echo "<td>";
                        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' style='display: inline;'>";
                        echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
                        echo "<input type='submit' name='approve_teacher' value='Approve' class='button-success'> ";
                        echo "<input type='submit' name='reject_teacher' value='Reject' class='button-danger' 
                              onclick='return confirm(\"Are you sure you want to reject this teacher?\")'>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No pending teacher approvals</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All Teachers List -->
<div class="card">
    <h3 class="card-title">All Teachers</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Teacher ID</th>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Subjects</th>
                    <th>Contact Info</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_all) > 0) {
                    while ($row = mysqli_fetch_assoc($result_all)) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["teacher_id"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["level"] . "</td>";
                        echo "<td>" . $row["subjects"] . "</td>";
                        echo "<td>" . $row["contact_info"] . "</td>";
                        echo "<td>" . ucfirst($row["status"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No teachers found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript for dynamic subject selection -->
<script>
function updateSubjects(level) {
    const subjectContainer = document.getElementById('subject-container');
    
    if (level === 'Ordinary Level') {
        let html = '<div class="checkbox-group">';
        <?php
        foreach ($ol_subjects as $subject) {
            echo "html += '<label><input type=\"checkbox\" name=\"subjects[]\" value=\"" . $subject . "\"> " . $subject . "</label>';\n";
        }
        ?>
        html += '</div>';
        subjectContainer.innerHTML = html;
    } else if (level === 'Advanced Level') {
        let html = '<div class="checkbox-group">';
        <?php
        foreach ($al_subjects as $subject) {
            echo "html += '<label><input type=\"checkbox\" name=\"subjects[]\" value=\"" . $subject . "\"> " . $subject . "</label>';\n";
        }
        ?>
        html += '</div>';
        subjectContainer.innerHTML = html;
    } else {
        subjectContainer.innerHTML = '<p>Please select a level first</p>';
    }
}

// Initialize subjects based on initial value
const initialLevel = document.getElementById('level').value;
if (initialLevel) {
    updateSubjects(initialLevel);
}
</script>

<style>
.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 5px;
    width: auto;
}
</style>

<?php
// Include the footer
include_once '../includes/footer.php';
?>