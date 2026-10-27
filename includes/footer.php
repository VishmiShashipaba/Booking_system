<?php
// includes/footer.php
// Common footer for public website pages
?>
    <footer>
        <div class="container footer-grid">
            <div class="footer-brand">
                <h3 style="font-family: 'Outfit', sans-serif;">Prasanna Channel Center</h3>
                <p>Providing high-quality and reliable specialist medical consultations to the community of Mahiyanganaya and surrounding areas. We prioritize your health and time.</p>
                <div class="social-links">
                    <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about.php">About Us</a></li>
                    <li><a href="<?php echo BASE_URL; ?>services.php">Services</a></li>
                    <li><a href="<?php echo BASE_URL; ?>doctors.php">Doctors</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php">Contact Us</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Our Services</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>services.php">Doctor Channel Booking</a></li>
                    <li><a href="<?php echo BASE_URL; ?>services.php">Specialist consultations</a></li>
                    <li><a href="<?php echo BASE_URL; ?>services.php">Walk-in Registrations</a></li>
                    <li><a href="<?php echo BASE_URL; ?>services.php">Digital Prescriptions</a></li>
                    <li><a href="<?php echo BASE_URL; ?>services.php">Instant Reminders</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Working Hours</h4>
                <ul style="font-size: 0.9rem; line-height: 1.8;">
                    <li><i class="fa-solid fa-clock"></i> Monday - Friday:<br>08:00 AM - 09:00 PM</li>
                    <li><i class="fa-solid fa-clock"></i> Saturday - Sunday:<br>08:00 AM - 06:00 PM</li>
                    <li style="margin-top: 10px;"><i class="fa-solid fa-phone"></i> Contact Hotline:<br><strong style="color: var(--accent-color);">+94 55 123 4567</strong></li>
                </ul>
            </div>
        </div>
        
        <div class="container footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Prasanna Channel Center. All Rights Reserved. Mahiyanganaya, Sri Lanka.</p>
            <div style="display: flex; gap: 15px;">
                <a href="<?php echo BASE_URL; ?>login.php?role=doctor">Doctor Login</a> | 
                <a href="<?php echo BASE_URL; ?>login.php?role=receptionist">Receptionist Login</a> | 
                <a href="<?php echo BASE_URL; ?>login.php?role=admin">Admin Login</a>
            </div>
        </div>
    </footer>

    <!-- Main JavaScript -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
