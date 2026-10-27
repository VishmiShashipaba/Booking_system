<?php
// about.php
// Public About Us page

$page_title = "About Us - Prasanna Channel Center";
include_once 'includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #ffffff 100%);">
    <div class="container text-center">
        <h1 style="font-size: 2.8rem; margin-bottom: 15px;">About Our Channel Center</h1>
        <p style="font-size: 1.1rem; color: var(--grey-color); max-width: 800px; margin: 0 auto;">Prasanna Channel Center is a leading medical consultation clinic serving the healthcare needs of the Mahiyanganaya community with integrity, compassion, and innovation.</p>
    </div>
</section>

<section class="py-5">
    <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center;">
        <div>
            <h2 style="font-size: 2rem; margin-bottom: 20px; color: var(--primary-color);">A Legacy of Trusted Care</h2>
            <p style="margin-bottom: 20px; font-size: 1rem; color: #475569;">Established to bridge the gap in specialist healthcare access in the Uva Province, Prasanna Channel Center has grown to become the most trusted clinical channeling center in Mahiyanganaya, Sri Lanka.</p>
            <p style="margin-bottom: 20px; font-size: 1rem; color: #475569;">We connect local patients with top-tier consultants from national and regional general hospitals, eliminating the need to travel long distances for specialized clinical opinions. Our modern facility provides a comfortable, sanitized, and professional setting for doctor channel consultations, walk-in registrations, and medical checkups.</p>
        </div>
        <div>
            <!-- Visual Card mockup -->
            <div style="background-color: var(--secondary-color); padding: 40px; border-radius: 20px; border: 1px solid var(--light-grey); text-align: center; box-shadow: var(--shadow-md);">
                <i class="fa-solid fa-clinic-medical" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 10px;">Clinical Excellence</h3>
                <p style="color: var(--grey-color); font-size: 0.95rem; line-height: 1.6;">Serving patients from Mahiyanganaya, Girandurukotte, Padiyatalawa, Aluththarama, and surrounding healthcare zones with dedicated specialist appointments.</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="py-5" style="background-color: var(--secondary-color);">
    <div class="container grid-3">
        <div class="card text-center" style="background: white; border: none; padding: 40px;">
            <div class="card-icon" style="margin: 0 auto 20px auto; background-color: #fee2e2; color: var(--danger-color);"><i class="fa-solid fa-eye"></i></div>
            <h3 style="font-size:1.4rem;">Our Vision</h3>
            <p style="line-height:1.6; font-size:0.95rem;">To be the leading, most trusted private channel clinic in the region, recognized for bringing clinical innovations, transparency, and superior patient experiences to rural and suburban Sri Lanka.</p>
        </div>
        
        <div class="card text-center" style="background: white; border: none; padding: 40px;">
            <div class="card-icon" style="margin: 0 auto 20px auto; background-color: #d1fae5; color: var(--success-color);"><i class="fa-solid fa-bullseye"></i></div>
            <h3 style="font-size:1.4rem;">Our Mission</h3>
            <p style="line-height:1.6; font-size:0.95rem;">To provide convenient, secure, and rapid medical consultation services through a state-of-the-art booking portal, connecting patients with skilled medical specialists while upholding the highest ethics of healthcare delivery.</p>
        </div>

        <div class="card text-center" style="background: white; border: none; padding: 40px;">
            <div class="card-icon" style="margin: 0 auto 20px auto; background-color: #e0f2fe; color: var(--primary-color);"><i class="fa-solid fa-heart"></i></div>
            <h3 style="font-size:1.4rem;">Our Commitment</h3>
            <p style="line-height:1.6; font-size:0.95rem;">We pledge to treat every patient with dignity, provide full clarity on specialist charges, minimize clinic waiting queues, and deliver precise electronic records and prescriptions for every consultation.</p>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5">
    <div class="container text-center">
        <div class="section-title">
            <h2>Our Core Values</h2>
            <p>The principles that guide our everyday decisions and patient care processes.</p>
        </div>
        
        <div class="grid-4" style="text-align: left;">
            <div class="card" style="padding: 25px; text-align: center;">
                <h4 style="font-size: 1.2rem; color: var(--primary-color); margin-bottom: 10px;">Integrity</h4>
                <p style="font-size: 0.9rem;">We maintain absolute transparency in consultation fees, payment collections, and scheduling queues.</p>
            </div>
            
            <div class="card" style="padding: 25px; text-align: center;">
                <h4 style="font-size: 1.2rem; color: var(--primary-color); margin-bottom: 10px;">Compassion</h4>
                <p style="font-size: 0.9rem;">We understand clinical anxieties and strive to create a warm, welcoming environment for patients.</p>
            </div>
            
            <div class="card" style="padding: 25px; text-align: center;">
                <h4 style="font-size: 1.2rem; color: var(--primary-color); margin-bottom: 10px;">Innovation</h4>
                <p style="font-size: 0.9rem;">We leverage modern digital dashboards and electronic prescriptions to optimize patient-doctor interactions.</p>
            </div>
            
            <div class="card" style="padding: 25px; text-align: center;">
                <h4 style="font-size: 1.2rem; color: var(--primary-color); margin-bottom: 10px;">Accountability</h4>
                <p style="font-size: 0.9rem;">We are responsible for maintaining secure database backups and protecting patient health information.</p>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
