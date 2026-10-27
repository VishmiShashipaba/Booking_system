-- Prasanna Channel Center Appointment Booking System Database Schema
-- Location: Mahiyanganaya, Sri Lanka

DROP DATABASE IF EXISTS `prasanna_channel_center`;
CREATE DATABASE `prasanna_channel_center` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `prasanna_channel_center`;

-- 1. Users Table (Core Auth Table)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'doctor', 'receptionist', 'patient') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`role`)
) ENGINE=InnoDB;

-- 2. Departments Table
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Patients Table
CREATE TABLE IF NOT EXISTS `patients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE NOT NULL,
    `patient_number` VARCHAR(20) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `nic` VARCHAR(15) UNIQUE,
    `dob` DATE NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `address` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Doctors Table
CREATE TABLE IF NOT EXISTS `doctors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE NOT NULL,
    `doctor_number` VARCHAR(20) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `department_id` INT NOT NULL,
    `specialization` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_doctor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_doctor_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Receptionists Table
CREATE TABLE IF NOT EXISTS `receptionists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE NOT NULL,
    `staff_number` VARCHAR(20) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_recep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Doctor Schedules Table
CREATE TABLE IF NOT EXISTS `doctor_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `doctor_id` INT NOT NULL,
    `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `max_patients` INT NOT NULL DEFAULT 15,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_schedule_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Appointments Table
CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_number` VARCHAR(20) NOT NULL UNIQUE,
    `patient_id` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `schedule_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `queue_number` INT NOT NULL,
    `status` ENUM('Pending', 'Checked-In', 'Completed', 'Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_appt_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appt_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appt_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `doctor_schedules` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 8. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT UNIQUE NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('Cash', 'Card') NOT NULL,
    `payment_status` ENUM('Pending', 'Paid', 'Refunded') DEFAULT 'Pending',
    `transaction_id` VARCHAR(100) UNIQUE NULL,
    `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_payment_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Prescriptions Table
CREATE TABLE IF NOT EXISTS `prescriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT UNIQUE NOT NULL,
    `doctor_id` INT NOT NULL,
    `patient_id` INT NOT NULL,
    `diagnosis` TEXT NOT NULL,
    `medicines` TEXT NOT NULL, -- Format: Text / details of drug name, strength, dosage, duration
    `instructions` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_presc_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_presc_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_presc_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Feedback Table
CREATE TABLE IF NOT EXISTS `feedback` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ==========================================
-- SEED DATA
-- ==========================================

-- 1. Departments
INSERT INTO `departments` (`id`, `name`, `description`) VALUES
(1, 'Cardiology', 'Heart health, cardiovascular diseases and ECG services.'),
(2, 'Pediatrics', 'Comprehensive child healthcare, immunizations and development monitoring.'),
(3, 'Dermatology', 'Skin, hair, nail treatments and cosmetic consultation.'),
(4, 'General Medicine', 'Primary healthcare, physical checkups, chronic disease management.'),
(5, 'Orthopedics', 'Bone, joint health, bone fractures, muscular and skeletal disorders.');

-- 2. Users (Passwords are admin123, doctor123, recep123, patient123 respectively)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@prasanna.com', '$2y$10$FXmz.6Y7wv6GULEfYfarzuCbmApULv4uzTgW7k.hRAO7R6.Tmg2gK', 'admin'),
(2, 'drprasanna', 'prasanna@prasanna.com', '$2y$10$nya18jMmXc8bhppdQZvSPuCPWXBQBUVO8qZOX8YxIgpDvEs3dPQIe', 'doctor'),
(3, 'drsanduni', 'sanduni@prasanna.com', '$2y$10$nya18jMmXc8bhppdQZvSPuCPWXBQBUVO8qZOX8YxIgpDvEs3dPQIe', 'doctor'),
(4, 'receptionist', 'recep@prasanna.com', '$2y$10$tnMoKWCkVOTZZ8ajX1pNvudrhYq6/dwsKrqmoPTO6MM/rPAOL/18q', 'receptionist'),
(5, 'patient', 'patient@gmail.com', '$2y$10$NCtJDfA3sMA5JMHdtxdduOCJEct7gX/j4Znl4fb3KZztMyEgkvo4G', 'patient'),
(6, 'vish', 'vish@prasanna.com', '$2y$10$NCtJDfA3sMA5JMHdtxdduOCJEct7gX/j4Znl4fb3KZztMyEgkvo4G', 'patient');

-- 3. Doctors
INSERT INTO `doctors` (`id`, `user_id`, `doctor_number`, `first_name`, `last_name`, `department_id`, `specialization`, `phone`, `charge`, `status`) VALUES
(1, 2, 'D-00001', 'Prasanna', 'Jayamaha', 1, 'Consultant Cardiologist', '0771234567', 1500.00, 'Active'),
(2, 3, 'D-00002', 'Sanduni', 'Silva', 2, 'Consultant Pediatrician', '0777654321', 1200.00, 'Active');

-- 4. Receptionists
INSERT INTO `receptionists` (`id`, `user_id`, `staff_number`, `first_name`, `last_name`, `phone`) VALUES
(1, 4, 'R-00001', 'Kavindi', 'Perera', '0711122334');

-- 5. Patients
INSERT INTO `patients` (`id`, `user_id`, `patient_number`, `first_name`, `last_name`, `nic`, `dob`, `gender`, `phone`, `address`) VALUES
(1, 5, 'P-00001', 'Chaminda', 'Bandara', '199012345678', '1990-05-15', 'Male', '0722233445', 'No. 45, Temple Road, Mahiyanganaya'),
(2, 6, 'P-00006', 'Vish', 'Prasanna', '199512345678', '1995-08-20', 'Male', '0779998887', 'No. 88, Padiyatalawa Road, Mahiyanganaya');

-- 6. Doctor Schedules
INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `max_patients`) VALUES
(1, 1, 'Monday', '09:00:00', '12:00:00', 15),
(2, 1, 'Wednesday', '14:00:00', '17:00:00', 15),
(3, 2, 'Tuesday', '10:00:00', '13:00:00', 20),
(4, 2, 'Friday', '15:00:00', '18:00:00', 20);

-- 7. Feedback
INSERT INTO `feedback` (`name`, `email`, `subject`, `message`, `rating`) VALUES
('Ruwan Silva', 'ruwan@gmail.com', 'Excellent Service', 'Highly satisfied with the booking process and doctor care. The staff was very supportive.', 5),
('Nimmi Perera', 'nimmi@gmail.com', 'Very Convenient', 'It is very easy to book channels online now. Saving a lot of time.', 5);
