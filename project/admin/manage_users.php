<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect("/login.php");
}

// Initialize variables
$username = $password = $user_type = "";
$username_err = $password_err = $user_type_err = "";
$success_message = $error_message = "";

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add_user"])) {
        // Validate username
        $username = sanitizeInput($_POST["username"]);
        if (empty($username)) {
            $username_err = "Please enter a username.";
        } else {
            // Check if username exists
            $sql = "SELECT id FROM users WHERE username = '$username'";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                $username_err = "This username is already taken.";
            }
        }
        
        // Validate password
        $password = $_POST["password"];
        if (empty($password)) {
            $password_err = "Please enter a password.";
        } elseif (strlen($password) < 6) {
            $password_err = "Password must have at least 6 characters.";
        }
        
        // Validate user type
        $user_type = sanitizeInput($_POST["user_type"]);
        if (empty($user_type)) {
            $user_type_err = "Please select user type.";
        }
        
        // Check input errors before inserting into database
        if (empty($username_err) && empty($password_err) && empty($user_type_err)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $sql = "INSERT INTO users (username, password, user_type) VALUES ('$username', '$hashed_password', '$user_type')";
            
            if (mysqli_query($conn, $sql)) {
                $success_message = "User created successfully.";
                $username = $password = $user_type = "";
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST["delete_user"])) {
        $user_id = sanitizeInput($_POST["user_id"]);
        
        $sql = "DELETE FROM users WHERE id = $user_id";
        if (mysqli_query($conn, $sql)) {
            $success_message = "User deleted successfully.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all users
$sql = "SELECT * FROM users ORDER BY user_type, username";
$users_result = mysqli_query($conn, $sql);
?>

<h2>Manage Users</h2>

<?php
if (!empty($success_message)) {
    echo displaySuccess($success_message);
} elseif (!empty($error_message)) {
    echo displayError($error_message);
}
?>

<!-- Add New User Form -->
<div class="card mb-20">
    <h3 class="card-title">Add New User</h3>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
            <span class="error"><?php echo $username_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="user_type">User Type</label>
            <select id="user_type" name="user_type" required>
                <option value="">Select User Type</option>
                <option value="admin" <?php if ($user_type == "admin") echo "selected"; ?>>Admin</option>
                <option value="staff" <?php if ($user_type == "staff") echo "selected"; ?>>Staff</option>
                <option value="student" <?php if ($user_type == "student") echo "selected"; ?>>Student</option>
            </select>
            <span class="error"><?php echo $user_type_err; ?></span>
        </div>
        
        <div class="form-group">
            <input type="submit" name="add_user" value="Add User">
        </div>
    </form>
</div>

<!-- Users List -->
<div class="card">
    <h3 class="card-title">Users List</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>User Type</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($users_result) > 0) {
                    while ($row = mysqli_fetch_assoc($users_result)) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["username"] . "</td>";
                        echo "<td>" . ucfirst($row["user_type"]) . "</td>";
                        echo "<td>" . $row["created_at"] . "</td>";
                        echo "<td>";
                        // Don't allow deleting the current logged-in admin
                        if ($row["id"] != $_SESSION["user_id"]) {
                            echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' style='display: inline;'>";
                            echo "<input type='hidden' name='user_id' value='" . $row["id"] . "'>";
                            echo "<input type='submit' name='delete_user' value='Delete' class='button-danger' 
                                  onclick='return confirm(\"Are you sure you want to delete this user?\")'>";
                            echo "</form>";
                        } else {
                            echo "Current User";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No users found</td></tr>";
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