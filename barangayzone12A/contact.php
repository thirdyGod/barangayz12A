<?php
/**
 * Contact Page - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$page_title = 'Contact Us';
$active_page = 'contact';

$success = false;
$errors = [];
$name = '';
$email = '';
$message = '';

// Server-side validation and insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate Name
    if (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }
    
    // Validate Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    
    // Validate Message
    if (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters long.';
    }
    
    // If no validation errors, proceed to insert
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message, date_sent) VALUES (:name, :email, :message, NOW())");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'message' => $message
            ]);
            $success = true;
            // Clear inputs on success
            $name = $email = $message = '';
        } catch (PDOException $e) {
            $errors['db'] = 'Failed to submit message: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Contact <span>Barangay Hall</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Have queries or need assistance? Reach out to the Barangay Council directly.</p>
        </div>
    </div>
</section>

<!-- Contact Form and Details -->
<section class="section">
    <div class="container">
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> Thank you! Your message has been sent successfully. The Barangay Council will get back to you shortly.
            </div>
        <?php elseif (!empty($errors['db'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-x-circle-fill"></i> Error: <?php echo htmlspecialchars($errors['db']); ?>
            </div>
        <?php endif; ?>

        <div class="grid-2 contact-grid">
            <!-- Contact Info and Map -->
            <div>
                <h2 style="margin-bottom: 1.5rem;">Get in Touch</h2>
                <p style="margin-bottom: 2rem; color: var(--color-text-muted);">
                    Visit us at the Barangay Hall during office hours or contact us through our official phone line or email. You can also send a direct message using the form.
                </p>
                
                <div class="contact-info-list" style="margin-bottom: 2.5rem;">
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="contact-info-text">
                            <h4>Office Address</h4>
                            <p>Barangay Hall, Zone 12-A, Talisay City, Negros Occidental</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div class="contact-info-text">
                            <h4>Phone Number</h4>
                            <p>(034) 441-1774 (Talisay Water Utility Office)</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div class="contact-info-text">
                            <h4>Email Address</h4>
                            <p>contact@zone12a-talisay.gov.ph</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="bi bi-person-badge-fill"></i></div>
                        <div class="contact-info-text">
                            <h4>Contact Person</h4>
                            <p><a href="https://www.facebook.com/jessa.penafiel.9" target="_blank" rel="noopener noreferrer" class="contact-person-link">Jessa Odecpa Penafel</a></p>
                        </div>
                    </div>
                </div>

                <!-- OpenStreetMap Embed -->
                <div class="about-map-placeholder">
                    <iframe 
                        width="100%" 
                        height="350" 
                        frameborder="0" 
                        scrolling="no" 
                        marginheight="0" 
                        marginwidth="0" 
                        src="https://www.openstreetmap.org/export/embed.html?bbox=122.9700%2C10.7350%2C122.9820%2C10.7480&amp;layer=mapnik&amp;marker=10.7419%2C122.9764" 
                        style="border: 0;">
                    </iframe>
                </div>
            </div>
            
            <!-- Contact Message Form -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">Send a Message</h3>
                <form action="contact.php" method="POST" id="contact-form" novalidate>
                    <div class="form-group <?php echo isset($errors['name']) ? 'invalid' : ''; ?>">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required>
                        <span class="form-error"><?php echo isset($errors['name']) ? htmlspecialchars($errors['name']) : 'Name must be at least 2 characters.'; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['email']) ? 'invalid' : ''; ?>">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="example@email.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        <span class="form-error"><?php echo isset($errors['email']) ? htmlspecialchars($errors['email']) : 'Please enter a valid email address.'; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['message']) ? 'invalid' : ''; ?>">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea name="message" id="message" class="form-control" placeholder="Type your concern or inquiry here..." required><?php echo htmlspecialchars($message); ?></textarea>
                        <span class="form-error"><?php echo isset($errors['message']) ? htmlspecialchars($errors['message']) : 'Message must be at least 10 characters.'; ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; border: none;">Submit Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
