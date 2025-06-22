<?php
require_once 'config/database.php';

// Handle feedback form submission
$feedback_message = '';
if ($_POST && isset($_POST['submit_feedback'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    $query = "INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$name, $email, $message])) {
        $feedback_message = '<div class="alert alert-success">Thank you for your feedback! We will get back to you soon.</div>';
    } else {
        $feedback_message = '<div class="alert alert-danger">Sorry, there was an error submitting your feedback. Please try again.</div>';
    }
}

// Get recent events and achievements
$database = new Database();
$db = $database->getConnection();

// Get recent events
$events_query = "SELECT * FROM events WHERE start_date >= CURDATE() ORDER BY start_date LIMIT 3";
$events_stmt = $db->prepare($events_query);
$events_stmt->execute();
$recent_events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent achievements
$achievements_query = "SELECT * FROM achievements ORDER BY date DESC LIMIT 3";
$achievements_stmt = $db->prepare($achievements_query);
$achievements_stmt->execute();
$recent_achievements = $achievements_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenwood Academy - Excellence in Education</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">
                     <image src = "logo.jpeg" height ="60px" weight = "60px">
                </div>
                <div class="school-info">
                    <h1>Greenwood Academy</h1>
                    <p>Excellence in Education Since 1985</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <ul class="nav-links">
                <li><a href="#about">About Us</a></li>
                <li><a href="#achievements">Achievements</a></li>
                <li><a href="#events">Events</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Section -->
        <section class="hero-section fade-in">
            <h2>Welcome to Greenwood Academy</h2>
            <p>Nurturing young minds for a brighter tomorrow. Our commitment to academic excellence, character development, and holistic education has made us a leading institution in the region.</p>
        </section>

        <!-- About Us Section -->
        <section id="about" class="fade-in">
            <div class="cards-grid">
                <div class="card">
                    <h3>Our History</h3>
                    <p>Founded in 1985, Greenwood Academy has been serving the community for over 35 years. We started with just 50 students and have grown to accommodate over 1,000 students from kindergarten through grade 12.</p>
                </div>
                <div class="card">
                    <h3>Our Mission</h3>
                    <p>To provide quality education that develops critical thinking, creativity, and character in our students, preparing them to be responsible global citizens and future leaders.</p>
                </div>
                <div class="card">
                    <h3>Our Vision</h3>
                    <p>To be recognized as the premier educational institution that inspires excellence, innovation, and integrity in all aspects of learning and personal development.</p>
                </div>
            </div>
        </section>

        <!-- Achievements Section -->
        <section id="achievements" class="fade-in">
            <h2 style="text-align: center; color: var(--primary-dark); margin: 2rem 0;">Recent Achievements</h2>
            <div class="cards-grid">
                <?php if (empty($recent_achievements)): ?>
                    <div class="card">
                        <h3>Academic Excellence Award 2024</h3>
                        <p>Our school received the Regional Academic Excellence Award for outstanding performance in standardized tests and overall student achievement.</p>
                    </div>
                    <div class="card">
                        <h3>Science Fair Champions</h3>
                        <p>Our students won first place in the National Science Fair with their innovative project on renewable energy solutions.</p>
                    </div>
                    <div class="card">
                        <h3>Sports Championship</h3>
                        <p>Our basketball team secured the state championship title, bringing pride and recognition to our school community.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_achievements as $achievement): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($achievement['title']); ?></h3>
                            <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <small>Date: <?php echo date('F j, Y', strtotime($achievement['date'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Events Section -->
        <section id="events" class="fade-in">
            <h2 style="text-align: center; color: var(--primary-dark); margin: 2rem 0;">Upcoming Events</h2>
            <div class="cards-grid">
                <?php if (empty($recent_events)): ?>
                    <div class="card">
                        <h3>Annual Sports Day</h3>
                        <p>Join us for our annual sports day featuring various competitions, team sports, and fun activities for all age groups.</p>
                        <small>Date: March 15, 2024</small>
                    </div>
                    <div class="card">
                        <h3>Science Exhibition</h3>
                        <p>Students will showcase their innovative science projects and experiments in our annual science exhibition.</p>
                        <small>Date: March 22, 2024</small>
                    </div>
                    <div class="card">
                        <h3>Parent-Teacher Conference</h3>
                        <p>An opportunity for parents to meet with teachers and discuss their child's academic progress and development.</p>
                        <small>Date: March 30, 2024</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_events as $event): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            <small>Date: <?php echo date('F j, Y', strtotime($event['start_date'])); ?></small>
                            <?php if ($event['location']): ?>
                                <br><small>Location: <?php echo htmlspecialchars($event['location']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="fade-in">
            <h2 style="text-align: center; color: var(--primary-dark); margin: 2rem 0;">Contact Information</h2>
            <div class="cards-grid">
                <div class="card">
                    <h3>Address</h3>
                    <p>123 Education Street<br>Greenwood City, GW 12345<br>United States</p>
                </div>
                <div class="card">
                    <h3>Phone & Email</h3>
                    <p>Phone: (555) 123-4567<br>Email: info@greenwoodacademy.edu<br>Fax: (555) 123-4568</p>
                </div>
                <div class="card">
                    <h3>Office Hours</h3>
                    <p>Monday - Friday: 8:00 AM - 4:00 PM<br>Saturday: 9:00 AM - 12:00 PM<br>Sunday: Closed</p>
                </div>
            </div>

            <!-- Feedback Form -->
            <div class="form-container fade-in">
                <h3 style="text-align: center; color: var(--primary-dark); margin-bottom: 1.5rem;">Send us your feedback</h3>
                <?php echo $feedback_message; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="submit_feedback" class="btn btn-primary" style="width: 100%;">Send Feedback</button>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Greenwood Academy. All rights reserved. | Excellence in Education Since 1985</p>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add fade-in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>