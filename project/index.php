<?php
// Include the header
include_once 'includes/header.php';

// Handle feedback form submission
$feedbackMsg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $message = sanitizeInput($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $feedbackMsg = displayError("All fields are required.");
    } else {
        $sql = "INSERT INTO feedbacks (name, email, message) VALUES ('$name', '$email', '$message')";
        
        if (mysqli_query($conn, $sql)) {
            $feedbackMsg = displaySuccess("Thank you for your feedback!");
        } else {
            $feedbackMsg = displayError("Error: " . mysqli_error($conn));
        }
    }
}
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <h2>Welcome to Excellence High School</h2>
        <p>Building Future Leaders Through Quality Education</p>
    </div>
</div>

<!-- Main Home Sections -->
<div class="home-sections">
    <!-- Latest Achievements Section -->
    <div class="home-section">
        <h3>Latest Achievements</h3>
        <ul>
            <li>
                <h4>National Science Competition</h4>
                <p>Our students won first place in the National Science Competition 2023</p>
            </li>
            <li>
                <h4>Sports Championship</h4>
                <p>Excellence High School athletics team secured the regional championship</p>
            </li>
            <li>
                <h4>Academic Excellence</h4>
                <p>95% of our students passed with distinction in national examinations</p>
            </li>
        </ul>
    </div>
    
    <!-- Upcoming Events Section -->
    <div class="home-section">
        <h3>Upcoming Events</h3>
        <ul>
            <li>
                <h4>Annual Science Fair</h4>
                <p>Date: August 15, 2023</p>
                <p>Venue: School Auditorium</p>
            </li>
            <li>
                <h4>Parents-Teachers Meeting</h4>
                <p>Date: August 25, 2023</p>
                <p>Venue: School Hall</p>
            </li>
            <li>
                <h4>Sports Day</h4>
                <p>Date: September 10, 2023</p>
                <p>Venue: School Playground</p>
            </li>
        </ul>
    </div>
    
    <!-- School History Section -->
    <div class="home-section">
        <h3>School History and Milestones</h3>
        <p>Founded in 1980, Excellence High School has been a pillar of quality education for over four decades.</p>
        <ul>
            <li><strong>1980:</strong> School established with 100 students</li>
            <li><strong>1990:</strong> Expanded to include advanced level classes</li>
            <li><strong>2000:</strong> New campus constructed with modern facilities</li>
            <li><strong>2010:</strong> Introduced specialized science and technology programs</li>
            <li><strong>2020:</strong> Celebrated 40 years of academic excellence</li>
        </ul>
    </div>
</div>

<!-- Contact and Feedback Section -->
<div class="contact-feedback">
    <!-- Contact Information -->
    <div class="card">
        <h3 class="card-title">Contact Information</h3>
        <p><strong>Address:</strong> 123 Education Street, Academic City</p>
        <p><strong>Phone:</strong> (123) 456-7890</p>
        <p><strong>Email:</strong> info@excellencehigh.edu</p>
        <p><strong>Office Hours:</strong> Monday to Friday, 8:00 AM to 4:00 PM</p>
    </div>
    
    <!-- Feedback Form -->
    <div class="card">
        <h3 class="card-title">Feedback Corner</h3>
        <p>We value your feedback! Please share your thoughts with us.</p>
        
        <?php echo $feedbackMsg; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="message">Your Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            
            <div class="form-group">
                <input type="submit" name="submit_feedback" value="Submit Feedback">
            </div>
        </form>
    </div>
</div>

<?php
// Include the footer
include_once 'includes/footer.php';
?>