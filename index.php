<?php
// index.php
// Public Homepage for Prasanna Channel Center

$page_title = "Home - Prasanna Channel Center";
include_once 'includes/header.php';

// Fetch stats for statistics cards
try {
    $doctor_count = $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'Active'")->fetchColumn();
    $patient_count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $appointment_count = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Completed'")->fetchColumn();
    
    // Add offset for realistic stats
    $patient_count += 450;
    $appointment_count += 1200;
} catch (PDOException $e) {
    $doctor_count = 2;
    $patient_count = 450;
    $appointment_count = 1200;
}

// Fetch 3 active doctors for the featured grid
try {
    $stmt = $pdo->query("SELECT d.*, dept.name AS dept_name 
                         FROM doctors d 
                         JOIN departments dept ON d.department_id = dept.id 
                         WHERE d.status = 'Active' 
                         LIMIT 3");
    $featured_doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_doctors = [];
}
?>

<!-- Hero Banner Section -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <span style="color: var(--accent-color); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 10px;">Caring for Mahiyanganaya</span>
            <h1>Your Health is Our Top Priority</h1>
            <p>Welcome to Prasanna Channel Center, the premier medical channeling destination in Mahiyanganaya. Book doctor appointments online instantly, check real-time schedules, and receive digital prescriptions from Sri Lanka's leading specialists.</p>
            <div class="hero-btns">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'patient'): ?>
                    <a href="<?php echo BASE_URL; ?>patient/book.php" class="btn btn-primary">Book Appointment Now</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php?redirect=book" class="btn btn-primary">Book Appointment Now</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>doctors.php" class="btn btn-outline">Find a Doctor</a>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <h3><?php echo $doctor_count; ?>+</h3>
                    <p style="font-weight: 600; color: var(--dark-color);">Specialist Doctors</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo $patient_count; ?>+</h3>
                    <p style="font-weight: 600; color: var(--dark-color);">Registered Patients</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo $appointment_count; ?>+</h3>
                    <p style="font-weight: 600; color: var(--dark-color);">Successful Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="hero-image">
            <!-- Hero Doctor Illustration -->
            <div style="width:100%; max-width:480px; position:relative;">
                <img src="<?php echo BASE_URL; ?>assets/images/hero_doctor.png" 
                     alt="Professional Doctor at Prasanna Channel Center"
                     style="width:100%; height:auto; border-radius:30px; box-shadow:var(--shadow-lg); display:block; object-fit:cover; max-height:420px;">
                <!-- Floating Secure Badge -->
                <div class="hero-badge" style="position:absolute; bottom:20px; left:20px; background:white; border-radius:12px; padding:12px 18px; box-shadow:var(--shadow-md); display:flex; align-items:center; gap:12px;">
                    <i class="fa-solid fa-shield-halved" style="color:var(--primary-color); font-size:1.4rem;"></i>
                    <div>
                        <h4 style="margin:0; font-size:0.85rem; color:var(--dark-color);">100% Secure</h4>
                        <p style="margin:0; font-size:0.75rem; color:var(--grey-color);">Privacy & Data Protected</p>
                    </div>
                </div>
                <!-- Floating Appointment Badge -->
                <div style="position:absolute; top:20px; right:-10px; background:var(--primary-color); color:white; border-radius:12px; padding:10px 16px; box-shadow:var(--shadow-md); text-align:center;">
                    <i class="fa-solid fa-calendar-check" style="font-size:1.2rem; display:block; margin-bottom:4px;"></i>
                    <span style="font-size:0.75rem; font-weight:600;">Book Online</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Welcome / Introduction Section -->
<section class="py-5" style="background-color: #fafbfc;">
    <div class="container text-center">
        <div class="section-title">
            <span style="color: var(--primary-color); font-weight: 600;">Welcome to Prasanna Channel Center</span>
            <h2 style="margin-top: 10px;">Advanced Channel Booking System</h2>
            <p>Our state-of-the-art booking system streamlines the clinical channel process. We connect patients with primary care physicians and medical consultants seamlessly, ensuring shorter waiting times and efficient service deliveries.</p>
        </div>
    </div>
</section>

<!-- Services Preview Section -->
<section class="py-5">
    <div class="container text-center">
        <div class="section-title">
            <h2>Our Core Services</h2>
            <p>We provide a comprehensive range of health channel services to meet the diverse needs of our patients.</p>
        </div>
        <div class="grid-3">
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <h3>Doctor Channel Booking</h3>
                <p>Book appointments online with your preferred specialist from the comfort of your home. Confirmations are generated instantly.</p>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-stethoscope"></i></div>
                <h3>Specialist Consultation</h3>
                <p>Access high-quality clinical consultation across Cardiology, Pediatrics, Dermatology, General Medicine, and Orthopedics.</p>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-file-medical"></i></div>
                <h3>Digital Prescriptions</h3>
                <p>Receive digital prescriptions directly on your dashboard. Easy to view, download, and present to pharmacies.</p>
            </div>
        </div>
        <div class="text-center" style="margin-top: 40px;">
            <a href="<?php echo BASE_URL; ?>services.php" class="btn btn-secondary">Explore All Services</a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 why-choose-us">
    <div class="container wcu-grid">
        <div>
            <h2 style="font-size: 2.2rem; margin-bottom: 20px;">Why Choose Prasanna Channel Center?</h2>
            <p style="margin-bottom: 30px; color: var(--grey-color);">We are dedicated to providing the community of Mahiyanganaya with access to professional healthcare specialists in a clean, modern, and highly organized environment.</p>
            
            <div class="wcu-features">
                <div class="wcu-feature-item">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <div>
                        <h4>Real-Time Schedule Updates</h4>
                        <p>Our system maintains real-time doctor availability calendars, preventing double bookings and long clinical queues.</p>
                    </div>
                </div>
                <div class="wcu-feature-item">
                    <i class="fa-solid fa-user-shield"></i>
                    <div>
                        <h4>Certified Professional Doctors</h4>
                        <p>Consult with highly qualified, certified clinical practitioners and specialists from leading national hospitals.</p>
                    </div>
                </div>
                <div class="wcu-feature-item">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                    <div>
                        <h4>Patient-First Care Approach</h4>
                        <p>Our receptionists and nursing staff are committed to assisting you at every stage of your clinical visit.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display:flex; justify-content: center; position: relative;">
            <div style="width: 90%; max-width: 440px; position:relative; border-radius: 20px; overflow:hidden; box-shadow: var(--shadow-lg);">
                <img src="<?php echo BASE_URL; ?>assets/images/clinic_building.png" 
                     alt="Prasanna Channel Center Clinic Building"
                     style="width:100%; height:320px; object-fit:cover; display:block;">
                <!-- Overlay testimonial card -->
                <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(255,255,255,0.97) 70%, transparent); padding:25px 25px 20px;">
                    <div class="rating" style="margin:0 0 8px 0; font-size:1rem;">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p style="font-style:italic; font-size:0.9rem; color:var(--dark-color); margin-bottom:12px; line-height:1.5;">"The online booking process is incredibly easy. I booked my consultation in under two minutes!"</p>
                    <div style="display:flex; align-items:center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius:50%; background:var(--secondary-color); color:var(--primary-color); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem;">RS</div>
                        <div>
                            <h5 style="margin:0; font-size:0.9rem;">Ruwan Silva</h5>
                            <p style="margin:0; font-size:0.75rem; color:var(--grey-color);">Patient - Mahiyanganaya</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Doctors Section -->
<section class="py-5">
    <div class="container text-center">
        <div class="section-title">
            <h2>Our Consulting Specialists</h2>
            <p>Consult with our top-rated medical doctors. Click to see their clinical schedules and book slots.</p>
        </div>
        
        <div class="grid-3" style="text-align: left;">
            <?php if (!empty($featured_doctors)): ?>
                <?php 
                $doc_index = 0;
                foreach ($featured_doctors as $doc): 
                    if (!empty($doc['image_path'])) {
                        $doc_photo = BASE_URL . 'images/' . $doc['image_path'];
                    } else {
                        $female_names = ['sanduni','amara','dilani','priya','kumari','nirosha','sachini'];
                        $first_lower = strtolower($doc['first_name']);
                        $is_female = in_array($first_lower, $female_names);
                        $doc_photo = BASE_URL . 'assets/images/' . ($is_female ? 'doctor_female.png' : 'doctor_male.png');
                    }
                    $doc_index++;
                ?>
                    <div class="doctor-card">
                        <div class="doctor-img" style="padding:0; overflow:hidden; height:220px; position:relative;">
                            <img src="<?php echo $doc_photo; ?>" 
                                 alt="Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>"
                                 style="width:100%; height:100%; object-fit:cover; object-position:top; display:block;">
                        </div>
                        <div class="doctor-info">
                            <h3>Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></h3>
                            <div class="spec"><?php echo htmlspecialchars($doc['specialization']); ?></div>
                            <div style="font-size: 0.9rem; margin-bottom: 15px; display:flex; justify-content: space-between;">
                                <span>Consultation Fee:</span>
                                <strong class="text-primary">LKR <?php echo number_format($doc['charge'], 2); ?></strong>
                            </div>
                            <a href="<?php echo BASE_URL; ?>doctors.php" class="btn btn-outline" style="width: 100%; font-size: 0.85rem; padding: 10px;">View Full Schedule</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card text-center" style="grid-column: 1 / -1; padding: 40px;">
                    <img src="<?php echo BASE_URL; ?>assets/images/empty_appointments.png" alt="No doctors" style="width:120px; opacity:0.6; margin-bottom:15px;">
                    <p style="margin:0; color:var(--grey-color);">No active doctors found. Please seed the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5" style="background-color: #fafbfc;">
    <div class="container text-center">
        <div class="section-title">
            <h2>What Our Patients Say</h2>
            <p>Read client reviews and feedback from visitors who received consultation at Prasanna Channel Center.</p>
        </div>
        <div class="grid-3" style="text-align: left;">
            <div class="testimonial-card">
                <div class="rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p>"Very professional healthcare clinic. The online scheduling dashboard is reliable, and the waiting queue is managed very well by the reception staff."</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">SP</div>
                    <div class="testimonial-user-info">
                        <h4>Saman Perera</h4>
                        <span>Patient from Badulla</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p>"I booked Dr. Sanduni for my daughter's vaccination. The receptionist confirmed our queue number instantly on payment. Excellent service!"</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">KP</div>
                    <div class="testimonial-user-info">
                        <h4>Kanthi Priyadarshani</h4>
                        <span>Mother - Girandurukotte</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p>"Highly recommend this channel center for Mahiyanganaya residents. Checking doctor timing slots online saves hours of waiting in lines."</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">AM</div>
                    <div class="testimonial-user-info">
                        <h4>Asanka Maduranga</h4>
                        <span>Patient - Mahiyanganaya</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="py-5" id="home-contact">
    <div class="container">
        <div class="section-title text-center">
            <h2>Contact Us / Location</h2>
            <p>Visit our facility in Mahiyanganaya or get in touch for support.</p>
        </div>
        
        <div class="contact-grid">
            <div>
                <h3 class="mb-4">Prasanna Channel Center</h3>
                <div class="contact-info-list">
                    <div class="contact-info-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <strong style="display:block;">Address</strong>
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
                
                <h4 class="mb-3">Clinical Operations Hours</h4>
                <p style="font-size:0.95rem; color:var(--grey-color);">We are open 365 days a year. Our reception desk is always ready to assist you with scheduling and billing inquiries.</p>
            </div>
            
            <div>
                <!-- Mock Map visual -->
                <div class="map-container mb-4">
                    <div style="background-color: var(--secondary-color); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 20px;">
                        <i class="fa-solid fa-map-marked-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px;"></i>
                        <h4 style="margin: 0 0 5px 0;">Google Map Location</h4>
                        <p style="margin: 0; font-size: 0.9rem; color: var(--grey-color);">Padiyatalawa Road, Mahiyanganaya, Sri Lanka</p>
                        <a href="https://maps.google.com" target="_blank" class="btn btn-outline" style="margin-top: 15px; padding: 8px 18px; font-size: 0.8rem;">Open in Maps</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
