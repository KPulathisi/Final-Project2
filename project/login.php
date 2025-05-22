<?php
// Include the header
include_once 'includes/header.php';

// Variable to store error message
$login_err = '';

// Process login form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from form
    $username = sanitizeInput($_POST["username"]);
    $password = $_POST["password"];
    
    // Validate if username and password are not empty
    if (empty($username) || empty($password)) {
        $login_err = "Please enter username and password.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id, username, password, user_type FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                
                // Verify the password
                if ($password === $row["password"]) {

                    // Password is correct, start a new session
                    session_start();
                    
                    // Store data in session variables
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["username"] = $row["username"];
                    $_SESSION["user_type"] = $row["user_type"];
                    
                    // Redirect user based on user_type
                    if ($row["user_type"] == "admin") {
                        redirect("admin/index.php");
                    } elseif ($row["user_type"] == "staff") {
                        redirect("staff/index.php");
                    } elseif ($row["user_type"] == "student") {
                        redirect("student/index.php");
                    }
                } else {
                    // Password is not valid
                    $login_err = "Invalid username or password.";
                }
            } else {
                // Username doesn't exist
                $login_err = "Invalid username or password.";
            }
        } else {
            $login_err = "Oops! Something went wrong. Please try again later.";
        }
    }
}
?>

<div class="form-container">
    <h2 class="form-title">Login</h2>
    
    <?php
    if (!empty($login_err)) {
        echo displayError($login_err);
    }
    ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Login">
        </div>
    </form>
</div>

<?php
// Include the footer
include_once 'includes/footer.php';
?>