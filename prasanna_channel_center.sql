-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 01:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prasanna_channel_center`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `appointment_number` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `queue_number` int(11) NOT NULL,
  `status` enum('Pending','Checked-In','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `appointment_number`, `patient_id`, `doctor_id`, `schedule_id`, `appointment_date`, `queue_number`, `status`, `created_at`) VALUES
(1, 'A-00001', 2, 1, 1, '2026-07-20', 1, 'Cancelled', '2026-07-08 05:34:14'),
(2, 'A-00002', 2, 1, 1, '2026-07-20', 2, 'Pending', '2026-07-08 05:37:30'),
(3, 'A-00003', 2, 1, 1, '2026-07-27', 1, 'Pending', '2026-07-08 05:39:02'),
(4, 'A-00004', 2, 5, 19, '2026-07-10', 1, 'Pending', '2026-07-08 07:05:36'),
(5, 'A-00005', 4, 1, 1, '2026-07-20', 2, 'Cancelled', '2026-07-15 14:35:31'),
(6, 'A-00006', 5, 1, 1, '2026-07-20', 3, 'Pending', '2026-07-15 14:42:20'),
(7, 'A-00007', 5, 1, 1, '2026-07-27', 2, 'Pending', '2026-07-15 14:45:43'),
(8, 'A-00008', 6, 1, 1, '2026-07-27', 3, 'Pending', '2026-07-15 15:08:52'),
(9, 'A-00009', 7, 1, 1, '2026-07-20', 4, 'Pending', '2026-07-15 15:16:27'),
(10, 'A-00010', 8, 5, 19, '2026-07-24', 1, 'Pending', '2026-07-16 05:45:23'),
(11, 'A-00011', 4, 7, 23, '2026-07-21', 1, 'Cancelled', '2026-07-16 06:13:20'),
(12, 'A-00012', 4, 7, 23, '2026-07-21', 2, 'Cancelled', '2026-07-16 06:14:11'),
(13, 'A-00013', 4, 7, 23, '2026-07-21', 3, 'Cancelled', '2026-07-16 06:14:35'),
(14, 'A-00014', 4, 7, 23, '2026-07-21', 4, 'Cancelled', '2026-07-16 06:14:46'),
(15, 'A-00015', 4, 7, 23, '2026-07-21', 5, 'Pending', '2026-07-16 06:14:53'),
(16, 'A-00016', 4, 7, 23, '2026-07-21', 6, 'Pending', '2026-07-16 06:15:01'),
(17, 'A-00017', 4, 7, 23, '2026-07-21', 7, 'Pending', '2026-07-16 06:15:10'),
(18, 'A-00018', 4, 7, 23, '2026-07-21', 8, 'Pending', '2026-07-16 06:15:16'),
(19, 'A-00019', 4, 7, 23, '2026-07-21', 9, 'Pending', '2026-07-16 06:15:24'),
(20, 'A-00020', 4, 7, 23, '2026-07-21', 10, 'Pending', '2026-07-16 06:15:24'),
(21, 'A-00021', 4, 7, 23, '2026-07-21', 11, 'Pending', '2026-07-16 06:15:24'),
(22, 'A-00022', 4, 7, 23, '2026-07-21', 12, 'Pending', '2026-07-16 06:15:25'),
(23, 'A-00023', 4, 7, 23, '2026-07-21', 13, 'Pending', '2026-07-16 06:15:25'),
(24, 'A-00024', 4, 7, 23, '2026-07-21', 14, 'Pending', '2026-07-16 06:15:25'),
(25, 'A-00025', 4, 7, 23, '2026-07-21', 15, 'Pending', '2026-07-16 06:21:48'),
(26, 'A-00026', 9, 5, 20, '2026-07-18', 1, 'Pending', '2026-07-16 07:03:21'),
(27, 'A-00027', 9, 1, 1, '2026-07-27', 4, 'Pending', '2026-07-23 10:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Cardiology', 'Heart health, cardiovascular diseases and ECG services.', '2026-07-07 17:04:37'),
(2, 'Pediatrics', 'Comprehensive child healthcare, immunizations and development monitoring.', '2026-07-07 17:04:37'),
(3, 'Dermatology', 'Skin, hair, nail treatments and cosmetic consultation.', '2026-07-07 17:04:37'),
(4, 'General Medicine', 'Primary healthcare, physical checkups, chronic disease management.', '2026-07-07 17:04:37'),
(5, 'Orthopedics', 'Bone, joint health, bone fractures, muscular and skeletal disorders.', '2026-07-07 17:04:37'),
(6, 'Dermatology', NULL, '2026-07-08 06:50:49'),
(7, 'Ophthalmology', NULL, '2026-07-08 07:35:45'),
(8, 'ENT', NULL, '2026-07-08 07:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doctor_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `department_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `doctor_number`, `first_name`, `last_name`, `department_id`, `specialization`, `phone`, `charge`, `status`, `image_path`, `created_at`) VALUES
(1, 2, 'D-00001', 'Prasanna', 'Jayamaha', 1, 'Consultant Cardiologist', '0771234567', 1500.00, 'Active', '4.jpeg', '2026-07-07 17:04:37'),
(2, 3, 'D-00002', 'Sanduni', 'Silva', 2, 'Consultant Pediatrician', '0777654321', 1200.00, 'Active', '8.jpeg', '2026-07-07 17:04:37'),
(3, 8, 'D-00003', 'Nimal', 'Perera', 4, 'Neurologist', '0711122334', 2500.00, 'Active', '6.jpeg', '2026-07-08 06:52:32'),
(4, 9, 'D-00004', 'Amara', 'Silva', 5, 'Orthopedic Surgeon', '0778899001', 3000.00, 'Active', '5.jpeg', '2026-07-08 06:52:32'),
(5, 10, 'D-00005', 'Kamal', 'Fernando', 6, 'Dermatologist', '0723344556', 1500.00, 'Active', '11.jpeg', '2026-07-08 06:52:32'),
(6, 11, 'D-00006', 'Sunil', 'Perera', 7, 'Eye Surgeon', '0711122335', 2000.00, 'Active', '10.jpeg', '2026-07-08 07:35:45'),
(7, 12, 'D-00007', 'Chathuri', 'Silva', 8, 'ENT Specialist', '0778899002', 1800.00, 'Active', '7.jpeg', '2026-07-08 07:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_patients` int(11) NOT NULL DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_schedules`
--

INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `max_patients`, `created_at`) VALUES
(1, 1, 'Monday', '09:00:00', '12:00:00', 15, '2026-07-07 17:04:37'),
(2, 1, 'Wednesday', '14:00:00', '17:00:00', 15, '2026-07-07 17:04:37'),
(3, 2, 'Tuesday', '10:00:00', '13:00:00', 20, '2026-07-07 17:04:37'),
(4, 2, 'Friday', '15:00:00', '18:00:00', 20, '2026-07-07 17:04:37'),
(13, 3, 'Tuesday', '16:00:00', '19:00:00', 12, '2026-07-08 06:52:32'),
(14, 3, 'Thursday', '16:00:00', '19:00:00', 12, '2026-07-08 06:52:32'),
(15, 3, 'Saturday', '09:00:00', '13:00:00', 20, '2026-07-08 06:52:32'),
(16, 4, 'Monday', '17:00:00', '20:00:00', 15, '2026-07-08 06:52:32'),
(17, 4, 'Wednesday', '17:00:00', '20:00:00', 15, '2026-07-08 06:52:32'),
(18, 4, 'Sunday', '10:00:00', '14:00:00', 25, '2026-07-08 06:52:32'),
(19, 5, 'Friday', '14:00:00', '18:00:00', 20, '2026-07-08 06:52:32'),
(20, 5, 'Saturday', '14:00:00', '18:00:00', 20, '2026-07-08 06:52:32'),
(21, 6, 'Monday', '16:00:00', '19:00:00', 15, '2026-07-08 07:35:45'),
(22, 6, 'Wednesday', '16:00:00', '19:00:00', 15, '2026-07-08 07:35:45'),
(23, 7, 'Tuesday', '17:00:00', '20:00:00', 20, '2026-07-08 07:35:45'),
(24, 7, 'Friday', '17:00:00', '20:00:00', 20, '2026-07-08 07:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `subject`, `message`, `rating`, `created_at`) VALUES
(1, 'Ruwan Silva', 'ruwan@gmail.com', 'Excellent Service', 'Highly satisfied with the booking process and doctor care. The staff was very supportive.', 5, '2026-07-07 17:04:37'),
(2, 'Nimmi Perera', 'nimmi@gmail.com', 'Very Convenient', 'It is very easy to book channels online now. Saving a lot of time.', 5, '2026-07-07 17:04:37');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 7, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-08 05:01:06'),
(2, 6, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-20, Queue Number: 1. Appt No: A-00001. Payment Method: Cash (Pending).', 1, '2026-07-08 05:34:14'),
(3, 2, 'New appointment booked for 2026-07-20. Queue No: 1. Patient: Vish Prasanna.', 0, '2026-07-08 05:34:14'),
(4, 6, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-20, Queue Number: 2. Appt No: A-00002. Payment Method: Cash (Pending).', 1, '2026-07-08 05:37:30'),
(5, 2, 'New appointment booked for 2026-07-20. Queue No: 2. Patient: Vish Prasanna.', 0, '2026-07-08 05:37:30'),
(6, 6, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-27, Queue Number: 1. Appt No: A-00003. Payment Method: Cash (Pending).', 1, '2026-07-08 05:39:02'),
(7, 2, 'New appointment booked for 2026-07-27. Queue No: 1. Patient: Vish Prasanna.', 0, '2026-07-08 05:39:02'),
(8, 6, 'Appointment A-00001 for date 2026-07-20 was Cancelled.', 1, '2026-07-08 05:39:38'),
(9, 2, 'Appointment queue 1 for 2026-07-20 was cancelled by patient.', 0, '2026-07-08 05:39:38'),
(10, 6, 'Appointment booked successfully! Doctor: Dr. Kamal Fernando, Date: 2026-07-10, Queue Number: 1. Appt No: A-00004. Payment Method: Cash (Pending).', 1, '2026-07-08 07:05:36'),
(11, 10, 'New appointment booked for 2026-07-10. Queue No: 1. Patient: Vish Prasanna.', 0, '2026-07-08 07:05:36'),
(12, 13, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-13 08:19:50'),
(13, 13, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-20, Queue Number: 2. Appt No: A-00005. Payment Method: Cash (Pending).', 1, '2026-07-15 14:35:31'),
(14, 2, 'New appointment booked for 2026-07-20. Queue No: 2. Patient: kalhara Perera.', 0, '2026-07-15 14:35:31'),
(15, 14, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-15 14:41:43'),
(16, 14, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-20, Queue Number: 3. Appt No: A-00006. Payment Method: Card (Paid).', 1, '2026-07-15 14:42:20'),
(17, 2, 'New appointment booked for 2026-07-20. Queue No: 3. Patient: sewmini rasangika.', 0, '2026-07-15 14:42:20'),
(18, 14, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-27, Queue Number: 2. Appt No: A-00007. Payment Method: Card (Paid).', 0, '2026-07-15 14:45:43'),
(19, 2, 'New appointment booked for 2026-07-27. Queue No: 2. Patient: sewmini rasangika.', 0, '2026-07-15 14:45:43'),
(20, 15, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-15 15:08:16'),
(21, 15, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-27, Queue Number: 3. Appt No: A-00008. Payment Method: Card (Paid).', 1, '2026-07-15 15:08:52'),
(22, 2, 'New appointment booked for 2026-07-27. Queue No: 3. Patient: sewmini rasangika.', 0, '2026-07-15 15:08:52'),
(23, 16, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-15 15:15:12'),
(24, 16, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-20, Queue Number: 4. Appt No: A-00009. Payment Method: Cash (Pending).', 1, '2026-07-15 15:16:27'),
(25, 2, 'New appointment booked for 2026-07-20. Queue No: 4. Patient: Imesha Madhumali.', 0, '2026-07-15 15:16:27'),
(26, 17, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-16 05:40:03'),
(27, 17, 'Appointment booked successfully! Doctor: Dr. Kamal Fernando, Date: 2026-07-24, Queue Number: 1. Appt No: A-00010. Payment Method: Cash (Pending).', 0, '2026-07-16 05:45:23'),
(28, 10, 'New appointment booked for 2026-07-24. Queue No: 1. Patient: samudini nisansala.', 0, '2026-07-16 05:45:23'),
(29, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 1. Appt No: A-00011. Payment Method: Cash (Pending).', 1, '2026-07-16 06:13:20'),
(30, 12, 'New appointment booked for 2026-07-21. Queue No: 1. Patient: kalhara Perera.', 0, '2026-07-16 06:13:20'),
(31, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 2. Appt No: A-00012. Payment Method: Cash (Pending).', 1, '2026-07-16 06:14:11'),
(32, 12, 'New appointment booked for 2026-07-21. Queue No: 2. Patient: kalhara Perera.', 0, '2026-07-16 06:14:11'),
(33, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 3. Appt No: A-00013. Payment Method: Cash (Pending).', 1, '2026-07-16 06:14:35'),
(34, 12, 'New appointment booked for 2026-07-21. Queue No: 3. Patient: kalhara Perera.', 0, '2026-07-16 06:14:35'),
(35, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 4. Appt No: A-00014. Payment Method: Cash (Pending).', 1, '2026-07-16 06:14:46'),
(36, 12, 'New appointment booked for 2026-07-21. Queue No: 4. Patient: kalhara Perera.', 0, '2026-07-16 06:14:46'),
(37, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 5. Appt No: A-00015. Payment Method: Cash (Pending).', 1, '2026-07-16 06:14:53'),
(38, 12, 'New appointment booked for 2026-07-21. Queue No: 5. Patient: kalhara Perera.', 0, '2026-07-16 06:14:53'),
(39, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 6. Appt No: A-00016. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:01'),
(40, 12, 'New appointment booked for 2026-07-21. Queue No: 6. Patient: kalhara Perera.', 0, '2026-07-16 06:15:01'),
(41, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 7. Appt No: A-00017. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:10'),
(42, 12, 'New appointment booked for 2026-07-21. Queue No: 7. Patient: kalhara Perera.', 0, '2026-07-16 06:15:10'),
(43, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 8. Appt No: A-00018. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:16'),
(44, 12, 'New appointment booked for 2026-07-21. Queue No: 8. Patient: kalhara Perera.', 0, '2026-07-16 06:15:16'),
(45, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 9. Appt No: A-00019. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:24'),
(46, 12, 'New appointment booked for 2026-07-21. Queue No: 9. Patient: kalhara Perera.', 0, '2026-07-16 06:15:24'),
(47, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 10. Appt No: A-00020. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:24'),
(48, 12, 'New appointment booked for 2026-07-21. Queue No: 10. Patient: kalhara Perera.', 0, '2026-07-16 06:15:24'),
(49, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 11. Appt No: A-00021. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:24'),
(50, 12, 'New appointment booked for 2026-07-21. Queue No: 11. Patient: kalhara Perera.', 0, '2026-07-16 06:15:24'),
(51, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 12. Appt No: A-00022. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:25'),
(52, 12, 'New appointment booked for 2026-07-21. Queue No: 12. Patient: kalhara Perera.', 0, '2026-07-16 06:15:25'),
(53, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 13. Appt No: A-00023. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:25'),
(54, 12, 'New appointment booked for 2026-07-21. Queue No: 13. Patient: kalhara Perera.', 0, '2026-07-16 06:15:25'),
(55, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 14. Appt No: A-00024. Payment Method: Cash (Pending).', 1, '2026-07-16 06:15:25'),
(56, 12, 'New appointment booked for 2026-07-21. Queue No: 14. Patient: kalhara Perera.', 0, '2026-07-16 06:15:25'),
(57, 13, 'Appointment booked successfully! Doctor: Dr. Chathuri Silva, Date: 2026-07-21, Queue Number: 15. Appt No: A-00025. Payment Method: Cash (Pending).', 1, '2026-07-16 06:21:48'),
(58, 12, 'New appointment booked for 2026-07-21. Queue No: 15. Patient: kalhara Perera.', 0, '2026-07-16 06:21:48'),
(59, 13, 'Appointment A-00005 for date 2026-07-20 was Cancelled.', 0, '2026-07-16 06:32:56'),
(60, 2, 'Appointment queue 2 for 2026-07-20 was cancelled by patient.', 0, '2026-07-16 06:32:56'),
(61, 13, 'Appointment A-00011 for date 2026-07-21 was Cancelled.', 0, '2026-07-16 06:33:01'),
(62, 12, 'Appointment queue 1 for 2026-07-21 was cancelled by patient.', 0, '2026-07-16 06:33:01'),
(63, 13, 'Appointment A-00012 for date 2026-07-21 was Cancelled.', 0, '2026-07-16 06:33:06'),
(64, 12, 'Appointment queue 2 for 2026-07-21 was cancelled by patient.', 0, '2026-07-16 06:33:06'),
(65, 13, 'Appointment A-00013 for date 2026-07-21 was Cancelled.', 0, '2026-07-16 06:33:10'),
(66, 12, 'Appointment queue 3 for 2026-07-21 was cancelled by patient.', 0, '2026-07-16 06:33:10'),
(67, 13, 'Appointment A-00014 for date 2026-07-21 was Cancelled.', 0, '2026-07-16 06:33:15'),
(68, 12, 'Appointment queue 4 for 2026-07-21 was cancelled by patient.', 0, '2026-07-16 06:33:15'),
(69, 18, 'Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online.', 1, '2026-07-16 07:02:35'),
(70, 18, 'Appointment booked successfully! Doctor: Dr. Kamal Fernando, Date: 2026-07-18, Queue Number: 1. Appt No: A-00026. Payment Method: Card (Paid).', 1, '2026-07-16 07:03:21'),
(71, 10, 'New appointment booked for 2026-07-18. Queue No: 1. Patient: melani nimeshika.', 0, '2026-07-16 07:03:21'),
(72, 18, 'Appointment booked successfully! Doctor: Dr. Prasanna Jayamaha, Date: 2026-07-27, Queue Number: 4. Appt No: A-00027. Payment Method: Cash (Pending).', 1, '2026-07-23 10:45:03'),
(73, 2, 'New appointment booked for 2026-07-27. Queue No: 4. Patient: melani nimeshika.', 0, '2026-07-23 10:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `nic` varchar(15) DEFAULT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `patient_number`, `first_name`, `last_name`, `nic`, `dob`, `gender`, `phone`, `address`, `created_at`) VALUES
(1, 5, 'P-00001', 'Chaminda', 'Bandara', '199012345678', '1990-05-15', 'Male', '0722233445', 'No. 45, Temple Road, Mahiyanganaya', '2026-07-07 17:04:37'),
(2, 6, 'P-00006', 'Vish', 'Prasanna', '199512345678', '1995-08-20', 'Male', '0779998887', 'No. 88, Padiyatalawa Road, Mahiyanganaya', '2026-07-07 17:45:42'),
(3, 7, 'P-00007', 'melani', 'nimeshika', '200474902075', '2004-01-28', 'Female', '0742589323', 'keppetipola,walimda', '2026-07-08 05:01:06'),
(4, 13, 'P-00013', 'kalhara', 'Perera', '200034567898', '2026-02-07', 'Male', '0768845633', 'No 3, Pannalawela,Badulla', '2026-07-13 08:19:50'),
(5, 14, 'P-00014', 'sewmini', 'rasangika', '200034567897', '2000-04-04', 'Female', '0760013586', 'No 3, Ambagahapathana,Uduhawara', '2026-07-15 14:41:43'),
(6, 15, 'P-00015', 'sewmini', 'rasangika', '200043564523', '2000-04-04', 'Female', '0760013586', 'No5,welimada,uduhawara', '2026-07-15 15:08:16'),
(7, 16, 'P-00016', 'Imesha', 'Madhumali', '200123457654', '2004-02-29', 'Female', '0773374527', 'Buthtala,Monaragala', '2026-07-15 15:15:12'),
(8, 17, 'P-00017', 'samudini', 'nisansala', '3679801674', '2002-05-04', 'Female', '76893456', 'kotawara,welimada', '2026-07-16 05:40:03'),
(9, 18, 'P-00018', 'melani', 'nimeshika', '123546987', '2027-01-28', 'Female', '0745236987', 'haputhle walimda', '2026-07-16 07:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card') NOT NULL,
  `payment_status` enum('Pending','Paid','Refunded') DEFAULT 'Pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `appointment_id`, `amount`, `payment_method`, `payment_status`, `transaction_id`, `payment_date`) VALUES
(1, 1, 2000.00, 'Cash', 'Pending', NULL, '2026-07-08 05:34:14'),
(2, 2, 2000.00, 'Cash', 'Pending', NULL, '2026-07-08 05:37:30'),
(3, 3, 2000.00, 'Cash', 'Pending', NULL, '2026-07-08 05:39:02'),
(4, 4, 2000.00, 'Cash', 'Pending', NULL, '2026-07-08 07:05:36'),
(5, 5, 2000.00, 'Cash', 'Pending', NULL, '2026-07-15 14:35:31'),
(6, 6, 2000.00, 'Card', 'Paid', 'CARD-6A579C4C643C5', '2026-07-15 14:42:20'),
(7, 7, 2000.00, 'Card', 'Paid', 'CARD-6A579D17655C3', '2026-07-15 14:45:43'),
(8, 8, 2000.00, 'Card', 'Paid', 'CARD-6A57A284F083D', '2026-07-15 15:08:52'),
(9, 9, 2000.00, 'Cash', 'Pending', NULL, '2026-07-15 15:16:27'),
(10, 10, 2000.00, 'Cash', 'Pending', NULL, '2026-07-16 05:45:23'),
(11, 11, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:13:20'),
(12, 12, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:14:11'),
(13, 13, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:14:35'),
(14, 14, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:14:46'),
(15, 15, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:14:53'),
(16, 16, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:01'),
(17, 17, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:10'),
(18, 18, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:16'),
(19, 19, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:24'),
(20, 20, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:24'),
(21, 21, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:24'),
(22, 22, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:25'),
(23, 23, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:25'),
(24, 24, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:15:25'),
(25, 25, 2300.00, 'Cash', 'Pending', NULL, '2026-07-16 06:21:48'),
(26, 26, 2000.00, 'Card', 'Paid', 'CARD-6A5882392695C', '2026-07-16 07:03:21'),
(27, 27, 2000.00, 'Cash', 'Pending', NULL, '2026-07-23 10:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `diagnosis` text NOT NULL,
  `medicines` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receptionists`
--

CREATE TABLE `receptionists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receptionists`
--

INSERT INTO `receptionists` (`id`, `user_id`, `staff_number`, `first_name`, `last_name`, `phone`, `created_at`) VALUES
(1, 4, 'R-00001', 'Kavindi', 'Perera', '0711122334', '2026-07-07 17:04:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','receptionist','patient') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@prasanna.com', '$2y$10$FXmz.6Y7wv6GULEfYfarzuCbmApULv4uzTgW7k.hRAO7R6.Tmg2gK', 'admin', '2026-07-07 17:04:37'),
(2, 'drprasanna', 'prasanna@prasanna.com', '$2y$10$nya18jMmXc8bhppdQZvSPuCPWXBQBUVO8qZOX8YxIgpDvEs3dPQIe', 'doctor', '2026-07-07 17:04:37'),
(3, 'drsanduni', 'sanduni@prasanna.com', '$2y$10$nya18jMmXc8bhppdQZvSPuCPWXBQBUVO8qZOX8YxIgpDvEs3dPQIe', 'doctor', '2026-07-07 17:04:37'),
(4, 'receptionist', 'recep@prasanna.com', '$2y$10$tnMoKWCkVOTZZ8ajX1pNvudrhYq6/dwsKrqmoPTO6MM/rPAOL/18q', 'receptionist', '2026-07-07 17:04:37'),
(5, 'patient', 'patient@gmail.com', '$2y$10$NCtJDfA3sMA5JMHdtxdduOCJEct7gX/j4Znl4fb3KZztMyEgkvo4G', 'patient', '2026-07-07 17:04:37'),
(6, 'vish', 'vish@prasanna.com', '$2y$10$NUm0B3UsS/BVeLrX0Rj6VujEDxix/5l9SWXF1ECrZbLj49p1xyY4y', 'patient', '2026-07-07 17:45:42'),
(7, 'melani', 'sewminirasangika0@gmail.com', '$2y$10$iI5eOLLc2ppude2WU1Pr2.P3tXFI.PZCh3BVFiNDBdia5Xc4hMgq.', 'patient', '2026-07-08 05:01:06'),
(8, 'drnimal', 'drnimal@prasanna.com', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'doctor', '2026-07-08 06:50:49'),
(9, 'dramara', 'dramara@prasanna.com', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'doctor', '2026-07-08 06:50:49'),
(10, 'drkamal', 'drkamal@prasanna.com', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'doctor', '2026-07-08 06:50:49'),
(11, 'drsunil', 'drsunil@prasanna.com', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'doctor', '2026-07-08 07:35:45'),
(12, 'drchathuri', 'drchathuri@prasanna.com', '$2y$10$wE3/oQ5s2FvA9oK5uYq8qOfzL1w3kP2m4xW9pZ2r9T1bO.X5JvE9K', 'doctor', '2026-07-08 07:35:45'),
(13, 'Kalhara', 'kalhara@gmail.com', '$2y$10$v5zgiBznelhm.ESUnfuk9OqZAvFmd.PKtWoaPhI/c4jTSZlXzTSQW', 'patient', '2026-07-13 08:19:50'),
(14, 'sewmini', 'shshsipabavishmi@gmail.com', '$2y$10$60a5slXwdXIqSMz0z8eIdukJgpf9cA8gtVN43HmR27gmbFaOWa.6u', 'patient', '2026-07-15 14:41:43'),
(15, 'rasa', 'shashsipabavishmi@gmail.com', '$2y$10$NHtrPR.YkDkBr465EooQZ.BqzNUbTrQ/U4bMVQNExmEHEvavzXdLW', 'patient', '2026-07-15 15:08:16'),
(16, 'Imesha', 'imeshaima2004@gmail.com', '$2y$10$DzJ1s8vqet3407/F2auiV.U7be30YiXU2ZvOcK9WJPLQR6pxMIS0e', 'patient', '2026-07-15 15:15:12'),
(17, 'samudini', 'hansimarasingha20022@gmail.com', '$2y$10$sD1HAs5huvP/iPVucSW/UuFliLjQq32.RPqYBi9j7H/.bmwnpGaly', 'patient', '2026-07-16 05:40:03'),
(18, 'samu', 'samudaniyel@gmail.com', '$2y$10$rEI1YjW1l/sJXDA7rLV4OeBh6/KlcmgVYCNp8HXrUCohxVDtHghAO', 'patient', '2026-07-16 07:02:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_number` (`appointment_number`),
  ADD KEY `fk_appt_patient` (`patient_id`),
  ADD KEY `fk_appt_doctor` (`doctor_id`),
  ADD KEY `fk_appt_schedule` (`schedule_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `doctor_number` (`doctor_number`),
  ADD KEY `fk_doctor_department` (`department_id`);

--
-- Indexes for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedule_doctor` (`doctor_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_user` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `patient_number` (`patient_number`),
  ADD UNIQUE KEY `nic` (`nic`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD KEY `fk_presc_doctor` (`doctor_id`),
  ADD KEY `fk_presc_patient` (`patient_id`);

--
-- Indexes for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `staff_number` (`staff_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receptionists`
--
ALTER TABLE `receptionists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `doctor_schedules` (`id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctor_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_doctor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD CONSTRAINT `fk_schedule_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_presc_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_presc_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_presc_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD CONSTRAINT `fk_recep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
