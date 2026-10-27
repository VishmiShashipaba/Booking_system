/* 
   assets/js/main.js
   Prasanna Channel Center - Main JS
*/

document.addEventListener('DOMContentLoaded', function() {
    // 1. Mobile Menu Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Toggle hamburger icon if font-awesome is used
            const icon = menuToggle.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }

    // 2. Active Link Highlighting
    const currentPath = window.location.pathname;
    const navAnchors = document.querySelectorAll('.nav-links a');
    
    navAnchors.forEach(anchor => {
        const href = anchor.getAttribute('href');
        if (href && currentPath.endsWith(href)) {
            anchor.classList.add('active');
        }
    });

    // 3. Contact Form Submission Alert
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            // If contact form is standard form submission, let it be, 
            // but we can add nice client-side validation
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!name || !email || !subject || !message) {
                e.preventDefault();
                alert('Please fill out all fields before submitting.');
            }
        });
    }
});
