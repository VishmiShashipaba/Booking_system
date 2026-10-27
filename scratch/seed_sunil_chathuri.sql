-- 1. Add image_path column to doctors table
ALTER TABLE doctors ADD COLUMN image_path VARCHAR(255) NULL AFTER status;

-- 2. Create Additional Departments
INSERT IGNORE INTO departments (name) VALUES 
('Ophthalmology'),
('ENT');

-- 3. Create Users for Doctors
INSERT INTO users (username, password, email, role) VALUES 
('drsunil', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'drsunil@prasanna.com', 'doctor'), -- password: doctor123
('drchathuri', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'drchathuri@prasanna.com', 'doctor');

-- 4. Get User IDs and Insert Doctors
INSERT INTO doctors (user_id, doctor_number, department_id, first_name, last_name, specialization, phone, charge, status, image_path) VALUES 
((SELECT id FROM users WHERE username = 'drsunil'), 'D-00006', (SELECT id FROM departments WHERE name = 'Ophthalmology'), 'Sunil', 'Perera', 'Eye Surgeon', '0711122335', 2000.00, 'Active', 'doctor_sunil.png'),
((SELECT id FROM users WHERE username = 'drchathuri'), 'D-00007', (SELECT id FROM departments WHERE name = 'ENT'), 'Chathuri', 'Silva', 'ENT Specialist', '0778899002', 1800.00, 'Active', 'doctor_chathuri.png');

-- 5. Add Schedules for New Doctors
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, max_patients) VALUES 
-- Dr Sunil
((SELECT id FROM doctors WHERE first_name = 'Sunil' AND last_name = 'Perera'), 'Monday', '16:00:00', '19:00:00', 15),
((SELECT id FROM doctors WHERE first_name = 'Sunil' AND last_name = 'Perera'), 'Wednesday', '16:00:00', '19:00:00', 15),

-- Dr Chathuri
((SELECT id FROM doctors WHERE first_name = 'Chathuri' AND last_name = 'Silva'), 'Tuesday', '17:00:00', '20:00:00', 20),
((SELECT id FROM doctors WHERE first_name = 'Chathuri' AND last_name = 'Silva'), 'Friday', '17:00:00', '20:00:00', 20);
