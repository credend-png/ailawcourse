-- LexLearnAI Database Schema
-- Created for cPanel / phpMyAdmin deployment
-- Run this SQL in phpMyAdmin after creating your database

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin@lexlearnai.com / Admin@12345
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@lexlearnai.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- --------------------------------------------------------
-- Table: students
-- --------------------------------------------------------
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `law_college` varchar(200) DEFAULT NULL,
  `year_semester` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `profession` enum('Student','Advocate','Associate','Other') DEFAULT 'Student',
  `profile_photo` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: courses
-- --------------------------------------------------------
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL UNIQUE,
  `short_description` text,
  `description` longtext,
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `original_fee` decimal(10,2) DEFAULT NULL,
  `duration_weeks` int(11) DEFAULT NULL,
  `total_classes` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `syllabus` longtext,
  `what_you_learn` longtext,
  `requirements` text,
  `level` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner',
  `language` varchar(50) DEFAULT 'English',
  `certificate_provided` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Course
INSERT INTO `courses` (`title`, `slug`, `short_description`, `description`, `fee`, `original_fee`, `duration_weeks`, `total_classes`, `start_date`, `end_date`, `level`, `certificate_provided`, `status`, `featured`) VALUES
('AI in Legal Practice: A Comprehensive Certification Program', 'ai-legal-practice-certification', 'Master the intersection of Artificial Intelligence and Law. Learn how AI tools are transforming legal research, contract analysis, and litigation strategy.', '<p>This comprehensive program is designed for law students, advocates, and legal professionals who want to stay ahead of the rapidly evolving landscape of Legal AI.</p><p>Over 8 weeks, you will explore how artificial intelligence is transforming every facet of legal practice—from automated contract review to AI-assisted legal research and predictive analytics in litigation.</p>', 4999.00, 7999.00, 8, 16, '2026-04-11', '2026-06-06', 'Intermediate', 1, 'active', 1),
('Legal Research with AI Tools', 'legal-research-ai-tools', 'Learn to supercharge your legal research using cutting-edge AI platforms. A practical, hands-on program for modern legal professionals.', '<p>This focused 4-week program teaches you exactly how to use AI-powered legal research tools to find case laws, statutes, and legal precedents faster and more accurately than ever before.</p>', 2999.00, 4999.00, 4, 8, '2026-05-02', '2026-05-30', 'Beginner', 1, 'active', 1);

-- --------------------------------------------------------
-- Table: enrollments
-- --------------------------------------------------------
CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','completed','suspended') DEFAULT 'active',
  `progress_percentage` int(3) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `access_granted_by` enum('payment','admin') DEFAULT 'payment',
  UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `txnid` varchar(100) NOT NULL UNIQUE,
  `payu_txnid` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `payment_mode` varchar(50) DEFAULT NULL,
  `bank_ref_num` varchar(100) DEFAULT NULL,
  `card_type` varchar(50) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `payu_response` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: coupons
-- --------------------------------------------------------
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL COMMENT 'NULL means all courses',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `status`) VALUES
('LAUNCH50', 'percentage', 50.00, 'active'),
('EARLYBIRD', 'fixed', 1000.00, 'active');

-- --------------------------------------------------------
-- Table: class_schedules
-- --------------------------------------------------------
CREATE TABLE `class_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `class_date` date NOT NULL,
  `class_time` time NOT NULL,
  `duration_minutes` int(11) DEFAULT 90,
  `meeting_link` varchar(500) DEFAULT NULL,
  `meeting_platform` enum('Zoom','Google Meet','Microsoft Teams','Other') DEFAULT 'Zoom',
  `recording_link` varchar(500) DEFAULT NULL,
  `status` enum('upcoming','live','completed','cancelled') DEFAULT 'upcoming',
  `week_number` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: study_materials
-- --------------------------------------------------------
CREATE TABLE `study_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('pdf','video','link','doc','other') DEFAULT 'pdf',
  `file_path` varchar(500) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_free_preview` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: certificates
-- --------------------------------------------------------
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `verification_id` varchar(50) NOT NULL UNIQUE,
  `certificate_file` varchar(255) NOT NULL,
  `issued_date` date NOT NULL,
  `issued_by` varchar(100) DEFAULT 'LexLearnAI',
  `student_name_on_cert` varchar(150) DEFAULT NULL,
  `course_name_on_cert` varchar(200) DEFAULT NULL,
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: announcements
-- --------------------------------------------------------
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `course_id` int(11) DEFAULT NULL COMMENT 'NULL = all students',
  `type` enum('info','warning','success','urgent') DEFAULT 'info',
  `is_pinned` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `announcements` (`title`, `content`, `type`, `is_pinned`, `status`) VALUES
('Welcome to LexLearnAI!', 'Welcome to the future of legal education. We are excited to have you on this journey to master AI in law. Classes begin soon — stay tuned for your schedule and materials.', 'success', 1, 'active');

-- --------------------------------------------------------
-- Table: quizzes
-- --------------------------------------------------------
CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `total_marks` int(11) DEFAULT 100,
  `passing_marks` int(11) DEFAULT 50,
  `time_limit_minutes` int(11) DEFAULT 30,
  `week_number` int(11) DEFAULT NULL,
  `attempts_allowed` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: quiz_questions
-- --------------------------------------------------------
CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(500) NOT NULL,
  `option_b` varchar(500) NOT NULL,
  `option_c` varchar(500) DEFAULT NULL,
  `option_d` varchar(500) DEFAULT NULL,
  `correct_answer` enum('a','b','c','d') NOT NULL,
  `marks` int(11) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: quiz_attempts
-- --------------------------------------------------------
CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `score` int(11) DEFAULT 0,
  `total_marks` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `passed` tinyint(1) DEFAULT 0,
  `answers` longtext DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: password_resets
-- --------------------------------------------------------
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: contact_messages
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: attendance
-- --------------------------------------------------------
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('present','absent','excused') DEFAULT 'present',
  `marked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_attendance` (`student_id`, `class_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'LexLearnAI', 'general'),
('site_tagline', 'Legal AI Education for the Next Generation of Lawyers', 'general'),
('site_email', 'info@lexlearnai.com', 'general'),
('site_phone', '+91-XXXXXXXXXX', 'general'),
('site_address', 'New Delhi, India', 'general'),
('payu_merchant_key', 'YOUR_PAYU_KEY', 'payment'),
('payu_merchant_salt', 'YOUR_PAYU_SALT', 'payment'),
('payu_mode', 'test', 'payment'),
('smtp_host', 'smtp.gmail.com', 'email'),
('smtp_port', '587', 'email'),
('smtp_user', '', 'email'),
('smtp_pass', '', 'email'),
('smtp_from_name', 'LexLearnAI', 'email'),
('smtp_from_email', 'noreply@lexlearnai.com', 'email'),
('meta_title', 'LexLearnAI - Legal AI Education for Lawyers', 'seo'),
('meta_description', 'Master AI in law with LexLearnAI. Online certificate courses for law students and legal professionals in India.', 'seo'),
('google_analytics', '', 'analytics'),
('maintenance_mode', '0', 'general'),
('registration_open', '1', 'general');
