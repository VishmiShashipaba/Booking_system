/* 
   assets/js/dashboard.js
   Prasanna Channel Center - Dashboard JS
*/

document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.db-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar on click outside in mobile view
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // 2. Modal Management (Open/Close actions)
    const modalCloseBtns = document.querySelectorAll('.modal-close, [data-modal-dismiss]');
    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const openModals = document.querySelectorAll('.modal-overlay.active');
            openModals.forEach(modal => modal.classList.remove('active'));
        });
    });

    // Close modal when clicking outside content area
    const modalOverlays = document.querySelectorAll('.modal-overlay');
    modalOverlays.forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
});

// Helper functions for modal loading
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// 3. Dynamic Doctors and Schedules Loader (AJAX Helpers)
function initBookingSelectors(base_url, deptSelectorId, doctorSelectorId, dateSelectorId, scheduleSelectorId) {
    const deptSelect = document.getElementById(deptSelectorId);
    const doctorSelect = document.getElementById(doctorSelectorId);
    const dateInput = document.getElementById(dateSelectorId);
    const scheduleSelect = document.getElementById(scheduleSelectorId);
    const bookingDetailsDiv = document.getElementById('bookingDetailsSummary');

    if (!deptSelect || !doctorSelect) return;

    // A. Department Selection Change
    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
        if (scheduleSelect) scheduleSelect.innerHTML = '<option value="">Select Schedule Slot</option>';
        if (dateInput) dateInput.value = '';
        if (bookingDetailsDiv) bookingDetailsDiv.innerHTML = '';
        
        const hintEl = document.getElementById('doctor_days_hint');
        if (hintEl) hintEl.innerHTML = '';

        if (!deptId) return;

        // Fetch doctors
        fetch(`${base_url}ajax/get_doctors.php?department_id=${deptId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(doc => {
                    const option = document.createElement('option');
                    option.value = doc.id;
                    option.textContent = `Dr. ${doc.first_name} ${doc.last_name} (Fee: LKR ${doc.charge})`;
                    option.dataset.charge = doc.charge;
                    doctorSelect.appendChild(option);
                });
            })
            .catch(err => console.error('Error fetching doctors:', err));
    });

    // B. Doctor Selection Change
    if (doctorSelect && dateInput) {
        doctorSelect.addEventListener('change', function() {
            if (dateInput) dateInput.value = '';
            if (scheduleSelect) scheduleSelect.innerHTML = '<option value="">Select Schedule Slot</option>';
            if (bookingDetailsDiv) bookingDetailsDiv.innerHTML = '';
            
            const hintEl = document.getElementById('doctor_days_hint');
            if (hintEl) hintEl.innerHTML = '';
            
            const doctorId = this.value;
            if (doctorId && hintEl) {
                fetch(`${base_url}ajax/get_doctor_days.php?doctor_id=${doctorId}`)
                    .then(res => res.json())
                    .then(days => {
                        if (days && days.length > 0) {
                            hintEl.innerHTML = `<i class="fa-solid fa-calendar-day"></i> Available Days: ${days.join(', ')}`;
                        } else {
                            hintEl.innerHTML = 'No schedule defined for this doctor.';
                        }
                    })
                    .catch(err => console.error(err));
            }
        });
        
        // Also trigger on load if pre-selected
        if (doctorSelect.value) {
            doctorSelect.dispatchEvent(new Event('change'));
        }
        
        dateInput.addEventListener('change', function() {
            const doctorId = doctorSelect.value;
            const chosenDate = this.value;

            if (scheduleSelect) scheduleSelect.innerHTML = '<option value="">Select Schedule Slot</option>';
            if (bookingDetailsDiv) bookingDetailsDiv.innerHTML = '';

            if (!doctorId || !chosenDate) return;

            // Fetch schedules for the selected doctor and date
            fetch(`${base_url}ajax/get_schedules.php?doctor_id=${doctorId}&date=${chosenDate}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No schedule available for this day';
                        scheduleSelect.appendChild(option);
                    } else {
                        data.forEach(sched => {
                            const option = document.createElement('option');
                            option.value = sched.id;
                            option.textContent = `${sched.day_of_week} [${sched.start_time} - ${sched.end_time}] (Max: ${sched.max_patients} Patients)`;
                            option.dataset.max = sched.max_patients;
                            scheduleSelect.appendChild(option);
                        });
                    }
                })
                .catch(err => console.error('Error fetching schedules:', err));
        });
    }

    if (scheduleSelect && bookingDetailsDiv) {
        scheduleSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            const doctorOpt = doctorSelect.options[doctorSelect.selectedIndex];
            if (!this.value || !doctorOpt.value) {
                bookingDetailsDiv.innerHTML = '';
                return;
            }
            
            const doctorName = doctorOpt.text;
            const charge = doctorOpt.dataset.charge;
            const slotDetails = selectedOpt.text;

            bookingDetailsDiv.innerHTML = `
                <div class="alert alert-success" style="margin-top: 15px;">
                    <h5 style="margin-bottom: 5px; font-weight:600;">Booking Details Summary</h5>
                    <p style="margin: 0; font-size: 0.9rem;">
                        <strong>Doctor:</strong> ${doctorName}<br>
                        <strong>Time:</strong> ${slotDetails}<br>
                        <strong>Date:</strong> ${dateInput.value}<br>
                        <strong>Doctor Fee:</strong> LKR ${charge} <span style="font-size:0.8rem; color:#666;">(Excluding channel center charge of LKR 500)</span><br>
                        <strong>Total Payable:</strong> <strong>LKR ${parseFloat(charge) + 500.00}</strong>
                    </p>
                </div>
            `;
        });
    }
}
