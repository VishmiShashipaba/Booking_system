<?php
// contact.php
// Public Contact Us page with feedback submission

$page_title = "Contact Us - Prasanna Channel Center";
include_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message, $rating]);
            $success_msg = "Thank you for contacting us! Your message has been received successfully.";
        } catch (PDOException $e) {
            $error_msg = "An error occurred while saving your feedback. Please try again.";
        }
    } else {
        $error_msg = "Please fill out all fields before submitting.";
    }
}
?>

<section class="py-5" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #ffffff 100%);">
    <div class="container text-center">
        <h1 style="font-size: 2.8rem; margin-bottom: 15px;">Contact Us</h1>
        <p style="font-size: 1.1rem; color: var(--grey-color); max-width: 800px; margin: 0 auto;">Have any questions or feedback? Reach out to us through the form below or visit our clinical facilities.</p>
    </div>
</section>

<section class="py-5">
    <div class="container contact-grid">
        <!-- Contact details -->
        <div>
            <h2 class="mb-4">Get In Touch</h2>
            <p class="mb-4" style="color: var(--grey-color);">We welcome feedback from our patients to help us improve clinic operations and channeling systems.</p>
            
            <div class="contact-info-list">
                <div class="contact-info-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <strong style="display:block;">Clinic Address</strong>
                        <span>No. 120, Padiyatalawa Road, Mahiyanganaya, Sri Lanka</span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fa-solid fa-phone"></i>
                    <div>
                        <strong style="display:block;">Call Us</strong>
                        <span>+94 55 123 4567 / +94 55 765 4321</span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fa-solid fa-envelope"></i>
                    <div>
                        <strong style="display:block;">Email Address</strong>
                        <span>info@prasannachannel.com</span>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 25px; background-color: var(--secondary-color); border:none;">
                <h4 style="margin-bottom:10px;"><i class="fa-solid fa-clock"></i> Clinic Timing Details</h4>
                <p style="font-size:0.9rem; margin-bottom:5px; color:#475569;"><strong>Monday - Friday:</strong> 08:00 AM - 09:00 PM</p>
                <p style="font-size:0.9rem; margin-bottom:5px; color:#475569;"><strong>Saturday - Sunday:</strong> 08:00 AM - 06:00 PM</p>
                <p style="font-size:0.8rem; font-style:italic; color:var(--grey-color); margin-top:10px;">Note: Channeling consultation starts depending on the doctor's specific arrival hours.</p>
            </div>
        </div>

        <!-- Feedback & Inquiry Form -->
        <div>
            <div class="card">
                <h3 class="mb-4">Send Us a Message</h3>
                
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="contact.php" method="POST" id="contactForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" name="name" id="name" required class="form-control" placeholder="e.g. Ruwan Silva">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" required class="form-control" placeholder="e.g. ruwan@gmail.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" required class="form-control" placeholder="e.g. Consultation Inquiry">
                    </div>

                    <div class="form-group">
                        <label for="rating">Rate Our Service</label>
                        <select name="rating" id="rating" class="form-control">
                            <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                            <option value="4">⭐⭐⭐⭐ (Very Good)</option>
                            <option value="3">⭐⭐⭐ (Average)</option>
                            <option value="2">⭐⭐ (Fair)</option>
                            <option value="1">⭐ (Poor)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message / Feedback</label>
                        <textarea name="message" id="message" rows="5" required class="form-control" placeholder="Type your message details here..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Feedback</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Location Map Section -->
<section style="height: 400px; width: 100%; border-top: 1px solid var(--light-grey);">
    <div style="background-color: #f1f5f9; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 20px;">
        <i class="fa-solid fa-map-location-dot" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 15px;"></i>
        <h3 style="margin-bottom: 5px;">Our Location on Map</h3>
        <p style="color: var(--grey-color); font-size: 0.95rem; max-width: 500px; margin-bottom: 20px;">Located on Padiyatalawa Road, Mahiyanganaya. Conveniently accessible with parking facilities.</p>
        <a href="https://maps.google.com" target="_blank" class="btn btn-outline" style="padding: 10px 24px;">Get Directions</a>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
