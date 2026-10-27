-- Get User IDs and Insert Doctors
INSERT INTO doctors (user_id, doctor_number, department_id, first_name, last_name, specialization, phone, charge, status) VALUES 
((SELECT id FROM users WHERE username = 'drnimal'), 'D-00003', 4, 'Nimal', 'Perera', 'Neurologist', '0711122334', 2500.00, 'Active'),
((SELECT id FROM users WHERE username = 'dramara'), 'D-00004', 5, 'Amara', 'Silva', 'Orthopedic Surgeon', '0778899001', 3000.00, 'Active'),
((SELECT id FROM users WHERE username = 'drkamal'), 'D-00005', 6, 'Kamal', 'Fernando', 'Dermatologist', '0723344556', 1500.00, 'Active');

-- Add Schedules for New Doctors
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, max_patients) VALUES 
-- Dr Nimal 
((SELECT id FROM doctors WHERE first_name = 'Nimal' AND last_name = 'Perera'), 'Tuesday', '16:00:00', '19:00:00', 12),
((SELECT id FROM doctors WHERE first_name = 'Nimal' AND last_name = 'Perera'), 'Thursday', '16:00:00', '19:00:00', 12),
((SELECT id FROM doctors WHERE first_name = 'Nimal' AND last_name = 'Perera'), 'Saturday', '09:00:00', '13:00:00', 20),

-- Dr Amara
((SELECT id FROM doctors WHERE first_name = 'Amara' AND last_name = 'Silva'), 'Monday', '17:00:00', '20:00:00', 15),
((SELECT id FROM doctors WHERE first_name = 'Amara' AND last_name = 'Silva'), 'Wednesday', '17:00:00', '20:00:00', 15),
((SELECT id FROM doctors WHERE first_name = 'Amara' AND last_name = 'Silva'), 'Sunday', '10:00:00', '14:00:00', 25),

-- Dr Kamal
((SELECT id FROM doctors WHERE first_name = 'Kamal' AND last_name = 'Fernando'), 'Friday', '14:00:00', '18:00:00', 20),
((SELECT id FROM doctors WHERE first_name = 'Kamal' AND last_name = 'Fernando'), 'Saturday', '14:00:00', '18:00:00', 20);
