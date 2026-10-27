<?php
// services.php
// Public Services page

$page_title = "Our Services - Prasanna Channel Center";
include_once 'includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #ffffff 100%);">
    <div class="container text-center">
        <h1 style="font-size: 2.8rem; margin-bottom: 15px;">Our Services</h1>
        <p style="font-size: 1.1rem; color: var(--grey-color); max-width: 800px; margin: 0 auto;">We offer a fully integrated healthcare management portal and physical facilities for seamless specialist channeling services.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="grid-3">
            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #e0f2fe; color: var(--primary-color);"><i class="fa-solid fa-calendar-alt"></i></div>
                <h3>Doctor Channel Booking</h3>
                <p>Browse through lists of consulting specialists, check their weekly schedules, and book slots instantly online with your patient account.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>patient/book.php" class="btn btn-primary" style="width: 100%; text-align: center;">Book Appointment</a>
                </div>
            </div>
            
            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #fee2e2; color: var(--danger-color);"><i class="fa-solid fa-user-md"></i></div>
                <h3>Specialist Doctor Consultation</h3>
                <p>Consult with certified specialists in Cardiology, Pediatrics, Dermatology, General Medicine, and Orthopedics in a modern environment.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>doctors.php" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid var(--danger-color); color: var(--danger-color);">View Specialists</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #d1fae5; color: var(--success-color);"><i class="fa-solid fa-clipboard-list"></i></div>
                <h3>Doctor Schedule Management</h3>
                <p>Real-time slot tracking and weekly calendar synchronizations allow doctors to adjust their availability, avoiding slot conflicts.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>login.php?role=doctor" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid var(--success-color); color: var(--success-color);">Doctor Portal</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #fef3c7; color: var(--warning-color);"><i class="fa-solid fa-user-plus"></i></div>
                <h3>Patient Registration</h3>
                <p>Create a secure patient profile online to save your personal details, NIC, phone numbers, and past visit summaries for fast checkouts.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>login.php?role=patient" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid var(--warning-color); color: var(--warning-color);">Register Account</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #e0e7ff; color: #4f46e5;"><i class="fa-solid fa-tasks"></i></div>
                <h3>Appointment Management</h3>
                <p>Cancel or reschedule your clinical channel appointments from your private portal. Receptionists can easily oversee and update daily queues.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>patient/history.php" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid #4f46e5; color: #4f46e5;">Manage Bookings</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #fae8ff; color: #d946ef;"><i class="fa-solid fa-credit-card"></i></div>
                <h3>Payment Management</h3>
                <p>Choose cash or card payment methods. All clinical bills are securely recorded in our MySQL registry and downloadable instantly.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>patient/history.php" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid #d946ef; color: #d946ef;">View Payments</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #d1fae5; color: var(--success-color);"><i class="fa-solid fa-file-prescription"></i></div>
                <h3>Digital Prescription</h3>
                <p>Consulting doctors enter diagnoses, dosages, and drug lists directly into our database. Patients access and print prescriptions from home.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>patient/history.php" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid var(--success-color); color: var(--success-color);">My Prescriptions</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #fee2e2; color: var(--danger-color);"><i class="fa-solid fa-history"></i></div>
                <h3>Patient History Management</h3>
                <p>A consolidated clinical log records all your past doctor visits, diagnoses, payment invoices, and prescription data securely.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>patient/history.php" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid var(--danger-color); color: var(--danger-color);">View History</a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-icon" style="background-color: #e0f2fe; color: var(--primary-color);"><i class="fa-solid fa-bell"></i></div>
                <h3>Appointment Reminders</h3>
                <p>Receive notifications regarding your checked-in status, queue updates, or cancellations directly inside your patient dashboard.</p>
                <div style="margin-top: auto; padding-top: 15px;">
                    <a href="<?php echo BASE_URL; ?>patient/index.php" class="btn btn-secondary" style="width: 100%; text-align: center; border: 1px solid var(--primary-color); color: var(--primary-color);">Alerts Hub</a>
                </div>
            </div>
        </div>

        <div class="card text-center" style="margin-top: 50px; background-color: var(--secondary-color); border: 1px solid var(--light-grey); padding: 40px;">
            <i class="fa-solid fa-headset" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 20px; display: block;"></i>
            <h3>Need Help? 24/7 Customer Support</h3>
            <p style="max-width: 600px; margin: 0 auto 20px auto; color: var(--grey-color);">Our support helpdesk is ready to assist you with channel cancellations, patient profile resets, or general queries about doctor arrival timings.</p>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-primary">Contact Support Desk</a>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
