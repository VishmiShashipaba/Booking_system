-- Create Additional Departments
INSERT IGNORE INTO departments (id, name) VALUES 
(4, 'Neurology'),
(5, 'Orthopedics'),
(6, 'Dermatology');

-- Create Users for Doctors
INSERT INTO users (username, password, email, role) VALUES 
('drnimal', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'drnimal@prasanna.com', 'doctor'), -- password: doctor123
('dramara', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'dramara@prasanna.com', 'doctor'),
('drkamal', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'drkamal@prasanna.com', 'doctor');

-- Get User IDs and Insert Doctors
-- Note: Manually setting IDs for predictable referencing in schedules
INSERT INTO doctors (user_id, department_id, first_name, last_name, specialization, qualification, phone, charge, status) VALUES 
((SELECT id FROM users WHERE username = 'drnimal'), 4, 'Nimal', 'Perera', 'Neurologist', 'MBBS, MD', '0711122334', 2500.00, 'Active'),
((SELECT id FROM users WHERE username = 'dramara'), 5, 'Amara', 'Silva', 'Orthopedic Surgeon', 'MBBS, MS', '0778899001', 3000.00, 'Active'),
((SELECT id FROM users WHERE username = 'drkamal'), 6, 'Kamal', 'Fernando', 'Dermatologist', 'MBBS, Dip Derm', '0723344556', 1500.00, 'Active');

-- Add Schedules for New Doctors
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, max_patients) VALUES 
-- Dr Nimal (id=3 in doctors table if it auto increments, but we can use subquery)
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
