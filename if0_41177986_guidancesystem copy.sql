-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.byetcluster.com
-- Generation Time: Mar 16, 2026 at 05:45 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41177986_guidancesystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `reference_type` enum('complaint','lost_item') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `reference_type`, `reference_id`, `action`, `description`, `performed_by`, `created_at`) VALUES
(1, 'complaint', 62, 'submitted', 'Complaint submitted: bullying', 3, '2025-09-19 02:46:45'),
(2, 'complaint', 63, 'submitted', 'Complaint submitted: family_problems', 3, '2025-09-19 02:50:19'),
(3, 'complaint', 64, 'submitted', 'Complaint submitted: family_problems', 3, '2025-09-21 07:11:56'),
(4, 'complaint', 65, 'submitted', 'Complaint submitted: mental_health', 3, '2025-09-17 07:12:12'),
(5, 'complaint', 66, 'submitted', 'Complaint submitted: mental_health', 3, '2024-11-10 07:12:25'),
(6, 'complaint', 67, 'submitted', 'Complaint submitted: family_problems', 3, '2025-09-21 07:39:54'),
(7, 'complaint', 68, 'submitted', 'Complaint submitted: family_problems', 3, '2025-09-21 07:40:13'),
(8, 'complaint', 80, 'submitted', 'Complaint submitted: financial', 6, '2026-02-16 21:51:43'),
(16, 'lost_item', 12, 'reported', 'Lost item reported: ffffffffff', 3, '2025-09-21 05:49:55'),
(17, 'lost_item', 13, 'reported', 'Lost item reported: asdsa', 3, '2025-09-21 07:24:51'),
(18, 'lost_item', 14, 'reported', 'Lost item reported: pink Phone', 4, '2025-09-24 04:55:13'),
(19, 'lost_item', 15, 'reported', 'Lost item reported: sample 2', 4, '2025-09-23 08:40:25'),
(20, 'lost_item', 17, 'reported', 'Lost item reported: wallet', 6, '2026-02-16 22:04:04'),
(23, 'lost_item', 18, 'reported', 'Lost item reported: walletasdasd (bag)', 14, '2026-02-17 06:51:53'),
(24, 'lost_item', 18, 'found', 'Item marked as found by admin', 8, '2026-02-17 07:06:22'),
(25, 'lost_item', 18, 'notified', 'Student notified via SMS and in-app notification', 8, '2026-02-17 07:06:22'),
(26, 'lost_item', 18, 'found', 'Item marked as found by admin', 8, '2026-02-17 07:13:21'),
(27, 'lost_item', 18, 'notified', 'Student notified via SMS and in-app notification', 8, '2026-02-17 07:13:21'),
(28, 'lost_item', 13, 'found', 'Item marked as found by admin', 8, '2026-02-17 07:42:55'),
(29, 'lost_item', 13, 'notified', 'Student notified via in-app notification only', 8, '2026-02-17 07:42:55'),
(30, 'lost_item', 19, 'reported', 'Lost item reported: umbrella (personal belongings)', 15, '2026-03-10 14:04:01'),
(31, 'lost_item', 19, 'found', 'Item marked as found by admin', 8, '2026-03-10 14:04:56'),
(32, 'lost_item', 19, 'notified', 'Student notified via in-app notification only', 8, '2026-03-10 14:04:56'),
(33, 'lost_item', 20, 'reported', 'Lost item reported: sample item (clothing)', 15, '2026-03-10 14:18:15'),
(34, 'lost_item', 20, 'found', 'Item marked as found by admin', 8, '2026-03-10 14:18:28'),
(35, 'lost_item', 20, 'notified', 'Student notified via SMS and in-app notification', 8, '2026-03-10 14:18:29'),
(36, 'lost_item', 21, 'reported', 'Lost item reported: another (clothing)', 15, '2026-03-10 14:24:16'),
(37, 'lost_item', 21, 'found', 'Item marked as found by admin', 8, '2026-03-10 14:24:45'),
(38, 'lost_item', 21, 'notified', 'Student notified via SMS and in-app notification', 8, '2026-03-10 14:24:47'),
(39, 'lost_item', 22, 'reported', 'Lost item reported: wallet (wallet)', 16, '2026-03-10 14:54:05'),
(40, 'lost_item', 22, 'found', 'Item marked as found by admin', 8, '2026-03-10 14:55:16'),
(41, 'lost_item', 22, 'notified', 'Student notified via SMS and in-app notification', 8, '2026-03-10 14:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `anonymous_suggestions`
--

CREATE TABLE `anonymous_suggestions` (
  `id` int(11) NOT NULL,
  `suggestion` text NOT NULL,
  `submitted_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anonymous_suggestions`
--

INSERT INTO `anonymous_suggestions` (`id`, `suggestion`, `submitted_at`, `is_read`) VALUES
(1, 'sample suggestion for the admin', '2026-02-17 15:51:41', 1),
(2, 'sample suggestion for the admin test2', '2026-02-17 00:26:45', 1),
(3, 'sample march 10', '2026-03-10 07:44:56', 1);

-- --------------------------------------------------------

--
-- Table structure for table `complaints_concerns`
--

CREATE TABLE `complaints_concerns` (
  `id` int(11) NOT NULL,
  `student_id` int(45) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `severity` enum('low','medium','high','urgent') DEFAULT 'medium',
  `description` varchar(255) DEFAULT NULL,
  `evidence` mediumblob DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `preferred_counseling_date` varchar(255) DEFAULT NULL,
  `scheduled_date` varchar(255) DEFAULT NULL,
  `scheduled_time` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date_created` varchar(255) DEFAULT NULL,
  `time_created` varchar(255) DEFAULT NULL,
  `updated_at` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `complaints_concerns`
--

INSERT INTO `complaints_concerns` (`id`, `student_id`, `type`, `severity`, `description`, `evidence`, `mime_type`, `preferred_counseling_date`, `scheduled_date`, `scheduled_time`, `status`, `date_created`, `time_created`, `updated_at`) VALUES
(64, 3, 'family_problems', 'medium', 'ssssssssss', NULL, NULL, '2025-09-24', '2025-09-24', '9:00', 'resolved', '2025-09-21', '15:11:56', NULL),
(67, 3, 'family_problems', 'high', 'daw ma suicide nako', NULL, NULL, '2025-09-22', '2025-09-23', '9:00', 'resolved', '2025-09-21', '15:39:54', NULL),
(68, 3, 'family_problems', 'high', 'daw ma suicide nagiddd ko yaaa', NULL, NULL, '2025-09-22', '2025-09-24', '8:00', 'resolved', '2025-09-21', '15:40:13', NULL),
(80, 6, 'financial', 'high', 'i have no money now, broke asf', NULL, NULL, '2026-02-18', '2026-02-17', '15:00', 'resolved', '2026-02-17', '05:51:43', NULL),
(82, 6, 'family_problems', 'urgent', 'asdasdasdasdasdas', NULL, NULL, '2026-02-20', '2026-02-17', '17:00', 'resolved', '2026-02-17', '07:32:16', NULL),
(83, 7, 'family_problems', 'high', 'im being bullied asdasdasdasdasd', NULL, NULL, '2026-03-17', NULL, NULL, 'pending', '2026-03-10', '21:54:28', NULL),
(84, 7, 'bullying', 'urgent', 'im beeeing bullied, please help me', NULL, NULL, '2026-03-11', '2026-03-13', '10:00', 'resolved', '2026-03-10', '21:56:43', NULL),
(85, 8, 'bullying', 'high', 'sample bully', NULL, NULL, '2026-03-13', '2026-03-13', '8:00', 'resolved', '2026-03-10', '22:46:51', NULL),
(86, 8, 'mental_health', 'low', 'sampleeeee', NULL, NULL, '2026-03-10', '2026-03-13', '09:00:00', 'scheduled', '2026-03-10', '22:58:03', NULL),
(87, 8, 'bullying', 'medium', 's;adwsjflk', NULL, NULL, '', NULL, NULL, 'pending', '2026-03-11', '10:58:10', NULL),
(88, 8, 'bullying', 'medium', 'ahascsilnlk', NULL, NULL, '2026-03-11', NULL, NULL, 'pending', '2026-03-11', '16:24:31', NULL),
(89, 8, 'romantic', 'medium', 'love gshkjrv vjb', NULL, NULL, '2026-03-20', '2026-03-20', '15:00', 'scheduled', '2026-03-12', '08:18:03', NULL),
(93, 6, 'career', 'low', 'sample desccc', NULL, NULL, '', NULL, NULL, 'pending', '2026-03-13', '13:58:50', NULL),
(94, 12, 'physical_health', 'medium', 'dvndvs,pdjsv dvkjdvsk', NULL, NULL, '', '2026-03-25', '13:00', 'scheduled', '2026-03-16', '17:13:40', NULL),
(95, 11, 'financial', 'medium', 'family problem sample', NULL, NULL, '', NULL, NULL, 'pending', '2026-03-16', '17:25:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lost_items`
--

CREATE TABLE `lost_items` (
  `id` int(11) NOT NULL,
  `student_id` int(45) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `date_lost` varchar(255) DEFAULT NULL,
  `time_lost` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `photo` mediumblob DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `claimant_photo` mediumblob DEFAULT NULL,
  `claimant_photo_mime_type` varchar(255) DEFAULT NULL,
  `receive_sms` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date` varchar(255) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `claimed_by_student_id` int(11) DEFAULT NULL,
  `claim_evidence` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lost_items`
--
-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `reference_type` enum('complaint','lost_item') NOT NULL,
  `type` enum('scheduled','resolved','found_item','new_complaint','item_claimed','reschedule_request','rescheduled','reschedule_rejected') NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `date_created` date NOT NULL,
  `time_created` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `reference_id`, `reference_type`, `type`, `priority`, `message`, `is_read`, `read_at`, `expires_at`, `date_created`, `time_created`) VALUES
(80, 7, 12, 'lost_item', 'found_item', 'medium', 'A matching item has been found: ffffffffff', 1, NULL, NULL, '2025-09-21', '13:50:03'),
(94, 7, 67, 'complaint', 'rescheduled', 'medium', 'Your counseling session has been rescheduled to September 23, 2025 at 9:00 AM. Reason: not at school', 0, NULL, NULL, '2025-09-23', '05:15:58'),
(139, 8, 80, 'complaint', 'new_complaint', 'medium', 'New complaint from sam ple: financial', 1, NULL, NULL, '2026-02-17', '05:51:43'),
(140, 14, 80, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for February 17, 2026 at 1:00 PM', 1, NULL, NULL, '2026-02-17', '05:53:19'),
(143, 14, 80, 'complaint', 'rescheduled', 'medium', 'Your counseling session has been rescheduled to February 17, 2026 at 3:00 PM. Reason: reschedule', 1, NULL, NULL, '2026-02-17', '05:56:18'),
(145, 8, 17, 'lost_item', '', 'medium', 'New lost item report from sam ple: wallet (clothing)', 1, NULL, NULL, '2026-02-17', '06:04:04'),
(146, 14, 17, 'lost_item', 'found_item', 'medium', 'A matching item has been found: wallet', 1, NULL, NULL, '2026-02-17', '06:08:52'),
(147, 8, 17, 'lost_item', 'item_claimed', 'medium', 'sam ple claimed the lost item: wallet', 1, NULL, NULL, '2026-02-17', '07:02:54'),
(150, 8, 82, 'complaint', 'new_complaint', 'medium', 'New complaint from sam ple: family_problems', 1, NULL, NULL, '2026-02-17', '07:32:16'),
(151, 8, 18, 'lost_item', '', 'medium', 'New lost item report from sam ple: walletasdasd (bag)', 1, NULL, NULL, '2026-02-17', '07:51:53'),
(152, 14, 18, 'lost_item', 'found_item', 'medium', 'A matching item has been found: walletasdasd', 1, NULL, NULL, '2026-02-17', '08:06:22'),
(153, 14, 18, 'lost_item', 'found_item', 'medium', 'A matching item has been found: walletasdasd', 1, NULL, NULL, '2026-02-17', '08:13:21'),
(154, 8, 18, 'lost_item', 'item_claimed', 'medium', 'sam ple claimed the lost item: walletasdasd', 1, NULL, NULL, '2026-02-17', '08:14:36'),
(156, 14, 82, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for February 17, 2026 at 10:00 AM', 1, NULL, NULL, '2026-02-17', '08:36:59'),
(157, 14, 82, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for February 17, 2026 at 10:00 AM', 1, NULL, NULL, '2026-02-17', '08:37:01'),
(158, 14, 82, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for February 17, 2026 at 5:00 PM', 1, NULL, NULL, '2026-02-17', '08:42:02'),
(160, 7, 13, 'lost_item', 'found_item', 'medium', 'A matching item has been found: asdsa', 0, NULL, NULL, '2026-02-17', '08:42:55'),
(161, 8, 13, 'lost_item', 'item_claimed', 'medium', 'fred fernandez claimed the lost item: asdsa', 1, NULL, NULL, '2026-02-17', '08:43:03'),
(163, 8, 83, 'complaint', 'new_complaint', 'medium', 'New complaint from sample student: family_problems', 1, NULL, NULL, '2026-03-10', '21:54:28'),
(164, 8, 84, 'complaint', 'new_complaint', 'medium', 'New complaint from sample student: bullying', 1, NULL, NULL, '2026-03-10', '21:56:43'),
(165, 15, 84, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for March 13, 2026 at 10:00 AM', 1, NULL, NULL, '2026-03-10', '22:00:26'),
(166, 7, 84, 'complaint', 'resolved', 'medium', 'Your complaint has been marked as resolved.', 0, NULL, NULL, '2026-03-10', '22:01:42'),
(167, 8, 19, 'lost_item', '', 'medium', 'New lost item report from sample student: umbrella (personal belongings)', 1, NULL, NULL, '2026-03-10', '22:04:01'),
(168, 15, 19, 'lost_item', 'found_item', 'medium', 'A matching item has been found: umbrella', 1, NULL, NULL, '2026-03-10', '22:04:56'),
(169, 8, 20, 'lost_item', '', 'medium', 'New lost item report from sample student: sample item (clothing)', 1, NULL, NULL, '2026-03-10', '22:18:15'),
(170, 15, 20, 'lost_item', 'found_item', 'medium', 'A matching item has been found: sample item', 1, NULL, NULL, '2026-03-10', '22:18:28'),
(171, 8, 21, 'lost_item', '', 'medium', 'New lost item report from sample student: another (clothing)', 1, NULL, NULL, '2026-03-10', '22:24:16'),
(172, 15, 21, 'lost_item', 'found_item', 'medium', 'A matching item has been found: another', 1, NULL, NULL, '2026-03-10', '22:24:45'),
(173, 8, 21, 'lost_item', 'item_claimed', 'medium', 'sample student claimed the lost item: another', 1, NULL, NULL, '2026-03-10', '22:25:37'),
(174, 7, 21, 'lost_item', 'item_claimed', 'medium', 'You have successfully claimed the item: another', 0, NULL, NULL, '2026-03-10', '22:25:37'),
(175, 8, 85, 'complaint', 'new_complaint', 'medium', 'New complaint from rj lastname: bullying', 1, NULL, NULL, '2026-03-10', '22:46:51'),
(176, 16, 85, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for March 13, 2026 at 8:00 AM', 1, NULL, NULL, '2026-03-10', '22:48:10'),
(177, 8, 85, 'complaint', 'resolved', 'medium', 'Your complaint has been marked as resolved.', 1, NULL, NULL, '2026-03-10', '22:50:36'),
(178, 8, 22, 'lost_item', '', 'medium', 'New lost item report from rj lastname: wallet (wallet)', 1, NULL, NULL, '2026-03-10', '22:54:05'),
(179, 16, 22, 'lost_item', 'found_item', 'medium', 'A matching item has been found: wallet', 1, NULL, NULL, '2026-03-10', '22:55:16'),
(180, 8, 22, 'lost_item', 'item_claimed', 'medium', 'rj lastname claimed the lost item: wallet', 1, NULL, NULL, '2026-03-10', '22:56:17'),
(181, 8, 22, 'lost_item', 'item_claimed', 'medium', 'You have successfully claimed the item: wallet', 1, NULL, NULL, '2026-03-10', '22:56:17'),
(182, 8, 86, 'complaint', 'new_complaint', 'medium', 'New complaint from rj lastname: mental_health', 1, NULL, NULL, '2026-03-10', '22:58:03'),
(183, 16, 86, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for March 11, 2026 at 10:00 AM', 1, NULL, NULL, '2026-03-10', '22:59:09'),
(184, 8, 86, 'complaint', 'reschedule_request', 'medium', 'Reschedule request from rj lastname for complaint ID: 86', 1, NULL, NULL, '2026-03-10', '22:59:52'),
(185, 16, 86, 'complaint', 'rescheduled', 'medium', 'Your reschedule request has been approved. New schedule: March 13, 2026 at 9:00 AM', 1, NULL, NULL, '2026-03-10', '23:00:14'),
(186, 8, 86, 'complaint', 'reschedule_request', 'medium', 'Reschedule request from rj lastname for complaint ID: 86', 1, NULL, NULL, '2026-03-10', '23:00:26'),
(187, 16, 86, 'complaint', 'rescheduled', 'medium', 'Your reschedule request has been approved. New schedule: March 13, 2026 at 9:00 AM', 1, NULL, NULL, '2026-03-11', '10:45:45'),
(188, 16, 86, 'complaint', 'rescheduled', 'medium', 'Your reschedule request has been approved. New schedule: March 13, 2026 at 9:00 AM', 1, NULL, NULL, '2026-03-11', '10:45:49'),
(189, 8, 87, 'complaint', 'new_complaint', 'medium', 'New complaint from rj lastname: bullying', 1, NULL, NULL, '2026-03-11', '10:58:10'),
(190, 8, 88, 'complaint', 'new_complaint', 'medium', 'New complaint from rj lastname: bullying', 1, NULL, NULL, '2026-03-11', '16:24:31'),
(191, 8, 89, 'complaint', 'new_complaint', 'medium', 'New complaint from rj lastname: romantic', 1, NULL, NULL, '2026-03-12', '08:18:03'),
(192, 16, 89, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for March 20, 2026 at 3:00 PM', 1, NULL, NULL, '2026-03-12', '08:24:13'),
(196, 8, 93, 'complaint', 'new_complaint', 'medium', 'New complaint from sam ple: career', 1, NULL, NULL, '2026-03-13', '13:58:50'),
(197, 17, 9, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 0, NULL, NULL, '2026-03-16', '17:05:41'),
(198, 19, 11, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 1, NULL, NULL, '2026-03-16', '17:08:01'),
(199, 18, 10, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 0, NULL, NULL, '2026-03-16', '17:08:50'),
(200, 16, 8, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 0, NULL, NULL, '2026-03-16', '17:12:43'),
(201, 14, 6, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 0, NULL, NULL, '2026-03-16', '17:12:51'),
(202, 10, 5, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 0, NULL, NULL, '2026-03-16', '17:12:57'),
(203, 21, 12, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 1, NULL, NULL, '2026-03-16', '17:13:05'),
(204, 8, 94, 'complaint', 'new_complaint', 'medium', 'New complaint from sample sample: physical_health', 1, NULL, NULL, '2026-03-16', '17:13:40'),
(205, 17, 9, '', '', 'medium', 'Your account has been verified! You can now submit complaints and report lost items.', 0, NULL, NULL, '2026-03-16', '17:15:15'),
(206, 21, 94, 'complaint', 'scheduled', 'medium', 'Your complaint has been scheduled for March 25, 2026 at 1:00 PM', 1, NULL, NULL, '2026-03-16', '17:19:40'),
(207, 8, 95, 'complaint', 'new_complaint', 'medium', 'New complaint from s s: financial', 1, NULL, NULL, '2026-03-16', '17:25:34');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `parent_name` varchar(45) DEFAULT NULL,
  `contact_number` varchar(45) DEFAULT NULL,
  `student_id` int(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `parent_name`, `contact_number`, `student_id`) VALUES
(8, 'mama fernandez', '09151018824', 3),
(9, 'CARL PARENT', '09319120634', 4),
(10, 'sample parent', '09319120634', 6),
(11, 'sample parent', '09319120634', 7),
(12, 'sample parents', '09949447487', 8),
(13, 'rose', '09073741369', 9),
(14, 'rose', '09073741369', 10),
(15, 's', '09319120634', 11),
(16, 'dcsdkj', '09073741369', 12);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `used`, `created_at`) VALUES
(4, 17, '5o91q1yfy5o5o233t2v5oy2g6i2r3m4801j434x214e514iv5s3fq3t', 0, '2026-03-16 09:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `reschedule_requests`
--

CREATE TABLE `reschedule_requests` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `date_requested` datetime NOT NULL,
  `date_processed` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reschedule_requests`
--

INSERT INTO `reschedule_requests` (`id`, `complaint_id`, `student_id`, `preferred_date`, `preferred_time`, `reason`, `status`, `admin_response`, `date_requested`, `date_processed`, `processed_by`) VALUES
(10, 86, 8, '2026-03-13', '09:00:00', 'emergency', 'approved', 'ok', '2026-03-10 07:59:52', '2026-03-10 08:00:14', 8),
(11, 86, 8, '2026-03-13', '09:00:00', 'emergency', 'approved', 'approved', '2026-03-10 08:00:25', '2026-03-10 19:45:49', 8);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `grade_level` varchar(255) DEFAULT NULL,
  `section` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(45) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `school_id_image` longblob DEFAULT NULL,
  `school_id_mime_type` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `grade_level`, `section`, `email`, `phone_number`, `password`, `address`, `school_id_image`, `school_id_mime_type`, `is_verified`) VALUES
(3, 7, 'fred', 'alvarado', 'fernandez', '7', 'A', NULL, '09151018824', NULL, 'bago city', NULL, NULL, 1),
(5, 10, 'Sigfredo', '', 'Fernandez', '12', 'ABM-B', 'sigfredofernandez25@gmail.com', '09123987654', NULL, '456 Another Street, City', NULL, NULL, 1),
(6, 14, 'sam', 'sad', 'ple', '7', 'asda', NULL, '09319120634', NULL, 'Mondia Street', NULL, NULL, 1),
(7, 15, 'sample', 'x', 'student', '7', 'A', NULL, '09319120634', NULL, 'sample address', NULL, NULL, 1),
(8, 16, 'rj', 'mname', 'lastname', '7', 'A', NULL, '09949447487', NULL, 'sample address', NULL, NULL, 1),
(9, 17, 'Rich', 'Tambanillo', 'Quiatchon', '12', 'abc', NULL, '09949447487', NULL, 'sitio pacol', NULL, NULL, 1),
(10, 18, 'rich', 'tambanillo', 'quiatchon', '10', 'v', NULL, '09949447487', NULL, 'saguabanua', NULL, NULL, 1);
-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `contact_number`) VALUES
(7, 'sigfredofernandez25@gmail.com', '$2y$10$3Wxeu7r5cXqIV6pfLrYUcuPbf5okxi4.iQX4Kw7QkWkD.ejwtEPla', 'student', NULL),
(8, 'admin@gmail.com', '$2y$10$fMpjwjEc3vgllX.6/6k0DuEC0vh9F8BUJJMc.PurOiSNZg4OwECS6', 'admin', NULL),
(10, 'sigfredofernandez25@gmail.com', '$2y$10$1M/yU.zAMffaLVFDKgxNguppNu7tBCSKJ5TyQl0rFKz.orC1v3p6u', 'student', NULL),
(14, 'cataancarlnavid@gmail.com', '$2y$12$k.Xx.83rqhKdGQ8iJQj3B.fiZ1B3seYkUwTVrpcjyfbsIrA4bzAaq', 'student', NULL),
(15, 'kang2x2k17@gmail.com', '$2y$10$5SlcJRJdJfte7q1Iv3dWBeT1L5e916dHqgDesdF70t5NjwbxtyX8m', 'student', NULL),
(16, 'rjaye.110803@gmail.com', '$2y$10$8vYR/y.4yS4esLyo.P5cF.qSji51GrQxFrpmPSAODQnyLyUV3TT3y', 'student', NULL),
(17, 'rj11.tq08.03@gmail.com', '$2y$10$c/PFe0jajK5OJlukV9UwXO4FPnp0sVV2DlthdH64vsJ.rYfs/fsha', 'student', NULL),
(18, 'rj11.tq08.03@gmail.com', '$2y$10$6WQDL0lXh91bjoLu7qB0o.Mmf0lJF/Wl/mb1B9Mj7s5VQ.mP9idN.', 'student', NULL),
(19, 'cataanluna@gmail.com', '$2y$10$9/e7IY/tBLgXUtLbysWUSOdLkcr1EUyfcqaD7YmqPrIoRgBE89eE.', 'student', NULL),
(21, 'osoriojub1@gmail.com', '$2y$10$uZA7n.rACBihwFFO.Lh/mOxoZm1wcAhbmn9C4XZAmlRxR97tmSWBi', 'student', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `anonymous_suggestions`
--
ALTER TABLE `anonymous_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_submitted_at` (`submitted_at`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `complaints_concerns`
--
ALTER TABLE `complaints_concerns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_claimed_at` (`claimed_at`),
  ADD KEY `idx_claimed_by_student_id` (`claimed_by_student_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reference_id` (`reference_id`),
  ADD KEY `idx_date_created` (`date_created`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reschedule_requests`
--
ALTER TABLE `reschedule_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_students_verified` (`is_verified`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `anonymous_suggestions`
--
ALTER TABLE `anonymous_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `complaints_concerns`
--
ALTER TABLE `complaints_concerns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `lost_items`
--
ALTER TABLE `lost_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reschedule_requests`
--
ALTER TABLE `reschedule_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD CONSTRAINT `fk_claimed_by_student` FOREIGN KEY (`claimed_by_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reschedule_requests`
--
ALTER TABLE `reschedule_requests`
  ADD CONSTRAINT `reschedule_requests_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints_concerns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reschedule_requests_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reschedule_requests_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
