<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect("/login.php");
}

// Initialize variables
$staff_id = $name = $position = $contact_info = $email = $address = "";
$error_message = $success_message = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add Staff
    if (isset($_POST["add_staff"])) {
        $staff_id = sanitizeInput($_POST["staff_id"]);
        $name = sanitizeInput($_POST["name"]);
        $position = sanitizeInput($_POST["position"]);
        $contact_info = sanitizeInput($_POST["contact_info"]);
        $email = sanitizeInput($_POST["email"]);
        $address = sanitizeInput($_POST["address"]);
        
        // Validate input
        if (empty($staff_id) || empty($name) || empty($position) || empty($contact_info) || empty($email) || empty($address)) {
            $error_message = "All fields are required.";
        } else {
            // Check if staff ID already exists
            $check_sql = "SELECT id FROM staff WHERE staff_id = '$staff_id'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error_message = "Staff ID already exists.";
            } else {
                // Insert staff
                $sql = "INSERT INTO staff (staff_id, name, position, contact_info, email, address, status) 
                        VALUES ('$staff_id', '$name', '$position', '$contact_info', '$email', '$address', 'pending')";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Staff registration successful. Pending approval.";
                    $staff_id = $name = $position = $contact_info = $email = $address = "";
                } else {
                    $error_message = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Approve Staff
    elseif (isset($_POST["approve_staff"])) {
        $id = sanitizeInput($_POST["id"]);
        
        // Get staff details
        $sql_staff = "SELECT * FROM staff WHERE id = $id";
        $result_staff = mysqli_query($conn, $sql_staff);
        $staff_data = mysqli_fetch_assoc($result_staff);
        
        // Generate username and password
        $username = strtolower(str_replace(' ', '', $staff_data['name'])) . mt_rand(100, 999);
        $password = substr(md5(time()), 0, 8); // Generate a random 8-character password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user account
        $sql_user = "INSERT INTO users (username, password, user_type) VALUES ('$username', '$hashed_password', 'staff')";
        
        if (mysqli_query($conn, $sql_user)) {
            $user_id = mysqli_insert_id($conn);
            
            // Update staff status and link to user account
            $sql_update = "UPDATE staff SET status = 'approved', staff_id = $user_id WHERE id = $id";
            
            if (mysqli_query($conn, $sql_update)) {
                $success_message = "Staff approved. Username: $username, Password: $password";
            } else {
                $error_message = "Error updating staff: " . mysqli_error($conn);
                
                // Rollback the user creation if staff update fails
                mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
            }
        } else {
            $error_message = "Error creating user account: " . mysqli_error($conn);
        }
    }
    
    // Reject Staff
    elseif (isset($_POST["reject_staff"])) {
        $id = sanitizeInput($_POST["id"]);
        
        $sql = "DELETE FROM staff WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Staff registration rejected.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all staff
$sql_all = "SELECT * FROM staff ORDER BY status, name";
$result_all = mysqli_query($conn, $sql_all);

// Get pending staff
$sql_pending = "SELECT * FROM staff WHERE status = 'pending' ORDER BY name";
$result_pending = mysqli_query($conn, $sql_pending);

// Staff positions
$positions = array(
    "Administrative Assistant",
    "Librarian",
    "Lab Assistant",
    "IT Support",
    "Accountant",
    "Counselor",
    "Nurse",
    "Security Officer",
    "Maintenance Staff",
    "Cleaner"
);
?>

<h2>Manage Staff</h2>

<?php
if (!empty($success_message)) {
    echo displaySuccess($success_message);
} elseif (!empty($error_message)) {
    echo displayError($error_message);
}
?>

<!-- Staff Registration Form -->
<div class="card mb-20">
    <h3 class="card-title">Register New Staff</h3>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="staff_id">Staff ID</label>
            <input type="text" id="staff_id" name="staff_id" value="<?php echo $staff_id; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="position">Position</label>
            <select id="position" name="position" required>
                <option value="">Select Position</option>
                <?php
                foreach ($positions as $pos) {
                    $selected = ($position == $pos) ? "selected" : "";
                    echo "<option value=\"$pos\" $selected>$pos</option>";
                }
                ?>
                <option value="Other">Other</option>
            </select>
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
            <input type="submit" name="add_staff" value="Register Staff">
        </div>
    </form>
</div>

<!-- Pending Staff Approvals -->
<div class="card mb-20">
    <h3 class="card-title">Pending Staff Approvals</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Contact Info</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_pending) > 0) {
                    while ($row = mysqli_fetch_assoc($result_pending)) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["staff_id"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["position"] . "</td>";
                        echo "<td>" . $row["contact_info"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>";
                        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' style='display: inline;'>";
                        echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
                        echo "<input type='submit' name='approve_staff' value='Approve' class='button-success'> ";
                        echo "<input type='submit' name='reject_staff' value='Reject' class='button-danger' 
                              onclick='return confirm(\"Are you sure you want to reject this staff?\")'>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No pending staff approvals</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All Staff List -->
<div class="card">
    <h3 class="card-title">All Staff</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Contact Info</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_all) > 0) {
                    while ($row = mysqli_fetch_assoc($result_all)) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["staff_id"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["position"] . "</td>";
                        echo "<td>" . $row["contact_info"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . ucfirst($row["status"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No staff found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Include the footer
include_once '../includes/footer.php';
?>