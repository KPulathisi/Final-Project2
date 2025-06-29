<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user_type = $_SESSION['user_type'];
    header("Location: {$user_type}_dashboard.php");
    exit();
}

$error_message = '';

if ($_POST && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error_message = 'All fields are required.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Get user from database (no password hashing)
        $query = "SELECT u.*, 
                  CASE 
                    WHEN u.user_type = 'teacher' THEN t.full_name
                    WHEN u.user_type = 'student' THEN s.full_name
                    WHEN u.user_type = 'other_staff' THEN os.full_name
                    ELSE u.username
                  END as full_name
                  FROM users u
                  LEFT JOIN teachers t ON u.id = t.user_id
                  LEFT JOIN students s ON u.id = s.user_id
                  LEFT JOIN other_staff os ON u.id = os.user_id
                  WHERE u.username = ? AND u.user_type = ? AND u.is_active = 1";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $user_type]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['password'] === $password) {
            // Update last login
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->execute([$user['id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Remember me functionality
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
            }
            
            // Redirect to appropriate dashboard
            header("Location: {$user_type}_dashboard.php");
            exit();
        } else {
            $error_message = 'Invalid username, password, or user type.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Leeds International</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">GA</div>
                <div class="school-info">
                    <h1>Leeds International</h1>
                    <p>Login to Your Account</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <div class="form-container fade-in">
            <h2 style="text-align: center; color: var(--primary-dark); margin-bottom: 2rem;">Login to Your Account</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="user_type">User Type *</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="">Select User Type</option>
                        <option value="admin" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="teacher" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        <option value="student" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="other_staff" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'other_staff') ? 'selected' : ''; ?>>Other Staff</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember_me"> Remember me for 30 days
                    </label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem;">
                <p>Don't have an account? Contact the administrator for registration.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Leeds International. All rights reserved.</p>
    </footer>

    <script>
        // Add fade-in animation
        document.addEventListener('DOMContentLoaded', function() {
            const formContainer = document.querySelector('.form-container');
            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateY(20px)';
            formContainer.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            
            setTimeout(() => {
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>