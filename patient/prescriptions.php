<?php
// patient/prescriptions.php
// View digital prescriptions written by consulting doctors

$page_title = "My Prescriptions";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['patient']);

$patient_id = $_SESSION['patient_id'];

$error = "";

// Fetch prescriptions
try {
    $stmt = $pdo->prepare("SELECT pr.*, d.first_name AS doc_first, d.last_name AS doc_last, d.specialization, 
                                  a.appointment_number, a.appointment_date 
                           FROM prescriptions pr 
                           JOIN doctors d ON pr.doctor_id = d.id 
                           JOIN appointments a ON pr.appointment_id = a.id 
                           WHERE pr.patient_id = ? 
                           ORDER BY pr.created_at DESC");
    $stmt->execute([$patient_id]);
    $prescriptions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load prescriptions: " . $e->getMessage();
    $prescriptions = [];
}
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="db-card">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-file-prescription text-primary"></i> Digital Prescriptions Issued</h3>
    </div>

    <div class="table-responsive">
        <table class="db-table">
            <thead>
                <tr>
                    <th>Presc ID</th>
                    <th>Appt No</th>
                    <th>Consulting Doctor</th>
                    <th>Specialization</th>
                    <th>Date Issued</th>
                    <th>Diagnosis</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prescriptions)): ?>
                    <?php foreach ($prescriptions as $pres): ?>
                        <tr>
                            <td><strong>#PR-<?php echo str_pad($pres['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo htmlspecialchars($pres['appointment_number']); ?></td>
                            <td>Dr. <?php echo htmlspecialchars($pres['doc_first'] . ' ' . $pres['doc_last']); ?></td>
                            <td><?php echo htmlspecialchars($pres['specialization']); ?></td>
                            <td><?php echo date('d-M-Y', strtotime($pres['created_at'])); ?></td>
                            <td>
                                <div style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?php echo htmlspecialchars($pres['diagnosis']); ?>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary" style="padding:6px 12px; font-size:0.75rem; border-radius:4px;" 
                                        onclick="showPrescription(<?php echo htmlspecialchars(json_encode($pres)); ?>)">
                                    <i class="fa-solid fa-eye"></i> View / Print
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--grey-color);">
                            No prescriptions have been written for you yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Prescription View & Print Modal -->
<div class="modal-overlay" id="prescModal">
    <div class="modal-container" style="max-width: 650px;">
        <div class="modal-header no-print">
            <h3>Digital Prescription Details</h3>
            <span class="modal-close" onclick="closeModal('prescModal')">&times;</span>
        </div>
        
        <div class="modal-body" id="printArea">
            <!-- Professional Medical Prescription Design -->
            <div style="border: 2px solid var(--primary-color); padding: 30px; border-radius: 8px; background: white; font-family: var(--font-sans);">
                <!-- Clinic Header Info -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid var(--primary-color); padding-bottom: 15px; margin-bottom: 20px;">
                    <div>
                        <h2 style="color:var(--primary-color); font-family:'Outfit'; margin:0; font-size:1.5rem;"><i class="fa-solid fa-heart-pulse"></i> Prasanna Channel Center</h2>
                        <p style="margin:5px 0 0 0; font-size:0.8rem; color:var(--grey-color);">No. 120, Padiyatalawa Road, Mahiyanganaya, Sri Lanka<br>Tel: +94 55 123 4567 | info@prasannachannel.com</p>
                    </div>
                    <div style="text-align:right;">
                        <h4 style="margin:0; font-size:0.9rem;">Prescription No: <span id="m_presc_id" style="color:var(--danger-color);"></span></h4>
                        <p style="margin:3px 0 0 0; font-size:0.8rem; color:var(--grey-color);">Date: <span id="m_date"></span></p>
                    </div>
                </div>
                
                <!-- Doctor & Patient Row -->
                <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:15px; margin-bottom: 25px; font-size:0.9rem; background:#f8fafc; padding:15px; border-radius:6px; border: 1px solid var(--light-grey);">
                    <div>
                        <span style="color:var(--grey-color); display:block; font-size:0.75rem; text-transform:uppercase; font-weight:600;">Consultant</span>
                        <strong style="font-size:1rem;" id="m_doctor_name"></strong>
                        <span style="display:block; color:var(--accent-color); font-size:0.8rem;" id="m_specialization"></span>
                    </div>
                    <div>
                        <span style="color:var(--grey-color); display:block; font-size:0.75rem; text-transform:uppercase; font-weight:600;">Patient Details</span>
                        <strong style="font-size:0.95rem;"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong>
                        <span style="display:block; color:var(--grey-color); font-size:0.8rem;">Patient ID: <?php echo htmlspecialchars($patient['patient_number']); ?></span>
                    </div>
                </div>
                
                <!-- Prescription Body -->
                <div style="margin-bottom: 25px;">
                    <h4 style="margin:0 0 8px 0; font-size:0.95rem; border-bottom:1px solid var(--light-grey); padding-bottom:5px; color:var(--primary-color);">Diagnosis / Clinical Notes</h4>
                    <p id="m_diagnosis" style="margin:0; font-size:0.95rem; line-height:1.5; color:#334155; font-style:italic;"></p>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom: 8px; border-bottom:1px solid var(--light-grey); padding-bottom:5px;">
                        <span style="font-size:1.3rem; font-weight:bold; color:var(--primary-color);">Rx</span>
                        <h4 style="margin:0; font-size:0.95rem; color:var(--primary-color);">Medicines & Dosage</h4>
                    </div>
                    <div id="m_medicines" style="white-space:pre-wrap; font-size:0.95rem; line-height:1.6; color:#1e293b; padding-left:15px; font-family: monospace;"></div>
                </div>

                <div style="margin-bottom: 30px;">
                    <h4 style="margin:0 0 8px 0; font-size:0.95rem; border-bottom:1px solid var(--light-grey); padding-bottom:5px; color:var(--primary-color);">Advice / Instructions</h4>
                    <p id="m_instructions" style="margin:0; font-size:0.95rem; line-height:1.5; color:#334155;"></p>
                </div>
                
                <!-- Footer Signature -->
                <div style="display:flex; justify-content:space-between; align-items:flex-end; border-top:1px solid var(--light-grey); padding-top:20px; font-size:0.8rem; color:var(--grey-color);">
                    <span>This is a secure system generated digital prescription. No physical signature required.</span>
                    <div style="text-align:center; min-width:150px;">
                        <div style="border-bottom:1px dashed var(--grey-color); height:30px; margin-bottom:5px;"></div>
                        <strong>Consultant Signature</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer no-print">
            <button class="btn btn-secondary" onclick="closeModal('prescModal')">Close</button>
            <button class="btn btn-accent" onclick="triggerPrint()"><i class="fa-solid fa-print"></i> Print Prescription</button>
        </div>
    </div>
</div>

<script>
function showPrescription(pres) {
    document.getElementById('m_presc_id').textContent = '#PR-' + String(pres.id).padStart(5, '0');
    document.getElementById('m_date').textContent = new Date(pres.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
    document.getElementById('m_doctor_name').textContent = 'Dr. ' + pres.doc_first + ' ' + pres.doc_last;
    document.getElementById('m_specialization').textContent = pres.specialization;
    document.getElementById('m_diagnosis').textContent = pres.diagnosis;
    document.getElementById('m_medicines').textContent = pres.medicines;
    document.getElementById('m_instructions').textContent = pres.instructions ? pres.instructions : 'No special advice given.';
    
    openModal('prescModal');
}

function triggerPrint() {
    window.print();
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
