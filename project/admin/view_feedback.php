<?php
// Include the header
include_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect("/login.php");
}

// Delete feedback if requested
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_feedback"])) {
    $feedback_id = sanitizeInput($_POST["feedback_id"]);
    
    $sql = "DELETE FROM feedbacks WHERE id = $feedback_id";
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Feedback deleted successfully.";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Get all feedback
$sql = "SELECT * FROM feedbacks ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<h2>View Feedback</h2>

<?php
if (isset($success_message)) {
    echo displaySuccess($success_message);
} elseif (isset($error_message)) {
    echo displayError($error_message);
}
?>

<div class="card">
    <h3 class="card-title">Feedback Submissions</h3>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo $row["name"]; ?></td>
                            <td><?php echo $row["email"]; ?></td>
                            <td><?php echo substr($row["message"], 0, 100) . (strlen($row["message"]) > 100 ? "..." : ""); ?></td>
                            <td><?php echo $row["created_at"]; ?></td>
                            <td>
                                <button type="button" onclick="viewFeedback(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['email']); ?>', '<?php echo addslashes($row['message']); ?>', '<?php echo $row['created_at']; ?>')">View</button>
                                
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                                    <input type="hidden" name="feedback_id" value="<?php echo $row["id"]; ?>">
                                    <input type="submit" name="delete_feedback" value="Delete" class="button-danger" 
                                           onclick="return confirm('Are you sure you want to delete this feedback?')">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No feedback submissions found.</p>
    <?php endif; ?>
</div>

<!-- Feedback Modal -->
<div id="feedback-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); overflow: auto;">
    <div style="background-color: white; margin: 10% auto; padding: 20px; border-radius: 8px; width: 80%; max-width: 600px;">
        <span id="close-modal" style="float: right; cursor: pointer; font-size: 24px;">&times;</span>
        <h3>Feedback Details</h3>
        <div id="feedback-details">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
// View feedback in a modal
function viewFeedback(id, name, email, message, date) {
    document.getElementById('feedback-details').innerHTML = `
        <p><strong>ID:</strong> ${id}</p>
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Date:</strong> ${date}</p>
        <p><strong>Message:</strong></p>
        <div style="background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
            ${message.replace(/\n/g, '<br>')}
        </div>
    `;
    
    document.getElementById('feedback-modal').style.display = 'block';
}

// Close the modal
document.getElementById('close-modal').addEventListener('click', function() {
    document.getElementById('feedback-modal').style.display = 'none';
});

// Close modal if clicking outside content
window.addEventListener('click', function(event) {
    var modal = document.getElementById('feedback-modal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php
// Include the footer
include_once '../includes/footer.php';
?>