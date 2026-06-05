-- Student Record Management System Database
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `student_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `student_db`;

-- Table structure for admin users
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin
INSERT INTO `admins` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- Table structure for classes
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_name_section` (`class_name`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default classes
INSERT INTO `classes` (`class_name`, `section`) VALUES
('1st Year', 'A'),
('1st Year', 'B'),
('2nd Year', 'A'),
('2nd Year', 'B'),
('3rd Year', 'A'),
('3rd Year', 'B');

-- Table structure for students
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roll_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `roll_number` (`roll_number`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for subjects
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `max_marks` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subjects
INSERT INTO `subjects` (`subject_name`, `subject_code`, `class_id`, `max_marks`) VALUES
('Mathematics', 'MATH101', 1, 100),
('Physics', 'PHY101', 1, 100),
('Chemistry', 'CHEM101', 1, 100),
('Computer Science', 'CS101', 1, 100),
('English', 'ENG101', 1, 100),
('Mathematics', 'MATH201', 2, 100),
('Physics', 'PHY201', 2, 100),
('Chemistry', 'CHEM201', 2, 100),
('Computer Science', 'CS201', 2, 100),
('English', 'ENG201', 2, 100),
('Mathematics', 'MATH301', 3, 100),
('Physics', 'PHY301', 3, 100),
('Chemistry', 'CHEM301', 3, 100),
('Computer Science', 'CS301', 3, 100),
('English', 'ENG301', 3, 100);

-- Table structure for exams
CREATE TABLE `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_name` varchar(100) NOT NULL,
  `exam_type` enum('Mid Term','Final Term','Unit Test','Assignment') NOT NULL,
  `class_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Upcoming','Ongoing','Completed') NOT NULL DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default exams
INSERT INTO `exams` (`exam_name`, `exam_type`, `class_id`, `start_date`, `end_date`, `status`) VALUES
('Mid Term Examination', 'Mid Term', 1, '2024-02-01', '2024-02-15', 'Completed'),
('Final Term Examination', 'Final Term', 1, '2024-05-01', '2024-05-20', 'Completed'),
('Mid Term Examination', 'Mid Term', 2, '2024-02-01', '2024-02-15', 'Completed'),
('Final Term Examination', 'Final Term', 2, '2024-05-01', '2024-05-20', 'Completed'),
('Mid Term Examination', 'Mid Term', 3, '2024-02-01', '2024-02-15', 'Completed'),
('Final Term Examination', 'Final Term', 3, '2024-05-01', '2024-05-20', 'Completed');

-- Table structure for results
CREATE TABLE `results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) NOT NULL,
  `grade` varchar(2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_result` (`student_id`, `subject_id`, `exam_id`),
  KEY `subject_id` (`subject_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `results_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `results_ibfk_3` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_students_name ON students(name);
CREATE INDEX idx_students_roll ON students(roll_number);
CREATE INDEX idx_results_student ON results(student_id);
CREATE INDEX idx_results_exam ON results(exam_id);
