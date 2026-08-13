-- MySQL Database Dump for Barangay Zone 12-A, Talisay City, Negros Occidental
-- Barangay Information System Database Setup
-- Generated: 2026-07-18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zone12a_db`
--
CREATE DATABASE IF NOT EXISTS `zone12a_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `zone12a_db`;

--
-- Drop existing tables in correct order (child tables first to satisfy foreign keys)
--
DROP TABLE IF EXISTS `age_groups`;
DROP TABLE IF EXISTS `demographics`;
DROP TABLE IF EXISTS `officials`;
DROP TABLE IF EXISTS `facilities`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `admin_users`;

-- --------------------------------------------------------

--
-- Table structure for table `demographics`
--

CREATE TABLE `demographics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year` INT NOT NULL UNIQUE,
  `population` INT NOT NULL,
  `households` INT DEFAULT NULL,
  `avg_household_size` DECIMAL(5,2) DEFAULT NULL,
  `growth_rate` DECIMAL(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Seeding data for table `demographics`
--

INSERT INTO `demographics` (`year`, `population`, `growth_rate`) VALUES
(1990, 1025, NULL),
(1995, 4620, 32.59),
(2000, 5748, 4.80),
(2007, 8104, 4.85),
(2010, 9033, 4.03),
(2015, 10423, 2.76),
(2020, 12419, 3.76);

-- --------------------------------------------------------

--
-- Table structure for table `age_groups`
--

CREATE TABLE `age_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `age_range` VARCHAR(50) NOT NULL,
  `population` INT NOT NULL,
  `percentage` DECIMAL(5,2) NOT NULL,
  `census_year` INT NOT NULL,
  CONSTRAINT `fk_age_groups_census_year` FOREIGN KEY (`census_year`) REFERENCES `demographics` (`year`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Seeding data for table `age_groups`
--

INSERT INTO `age_groups` (`age_range`, `population`, `percentage`, `census_year`) VALUES
('Under 1', 113, 1.08, 2015),
('1-4', 649, 6.23, 2015),
('5-9', 850, 8.16, 2015),
('10-14', 938, 9.00, 2015),
('15-19', 1019, 9.78, 2015),
('20-24', 953, 9.14, 2015),
('25-29', 803, 7.70, 2015),
('30-34', 747, 7.17, 2015),
('35-39', 715, 6.86, 2015),
('40-44', 672, 6.45, 2015),
('45-49', 642, 6.16, 2015),
('50-54', 642, 6.16, 2015),
('55-59', 546, 5.24, 2015),
('60-64', 439, 4.21, 2015),
('65-69', 258, 2.48, 2015),
('70-74', 184, 1.77, 2015),
('75-79', 121, 1.16, 2015),
('80+', 132, 1.27, 2015);

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `term_start` INT NOT NULL,
  `term_end` INT NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `order_display` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Seeding data for table `officials`
--

INSERT INTO `officials` (`name`, `position`, `term_start`, `term_end`, `photo`, `order_display`) VALUES
('Edwin Lester Iledan', 'Barangay Captain', 2023, 2026, NULL, 1),
('Ana Trisha D. Hellera', 'SK Chairperson', 2023, 2026, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `contact_number` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Seeding data for table `facilities`
--

INSERT INTO `facilities` (`name`, `type`, `address`, `contact_number`, `description`, `image`) VALUES
('Zone 12-A (Pob.) Barangay Health Station', 'Health', 'Zone 12-A, Talisay City', NULL, 'Government/LGU-owned health facility, DOH Code DOH000000000011249', NULL),
('Talisay Water District', 'Utility', '5 Star Building, Pueblo San Antonio, Brgy. Zone 12-A', '(034) 441-1774', 'Main office of the city\'s water utility', NULL),
('Rafael B. Lacson National High School', 'Education', 'Near Zone 12-A', NULL, 'Nearby public high school', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `date_posted` DATE NOT NULL,
  `author` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Seeding data for table `news`
--

INSERT INTO `news` (`title`, `content`, `image`, `date_posted`, `author`) VALUES
('Barangay Tambo, Parañaque City Visits Zone 12-A for Benchmarking', 'Zone 12-A hosted a benchmarking activity with the Barangay Council of Barangay Tambo, Parañaque City, sharing best practices and community innovations.', NULL, '2025-08-21', 'Barangay Zone 12-A');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `date_sent` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Seeding data for table `admin_users`
--
-- Password hash generated using PHP's password_hash('admin123', PASSWORD_DEFAULT)
-- The password is "admin123"
-- This must be changed to a secure, unique password in production!
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('admin', '$2y$10$e0MYzXy55.k1BJBdeEZ2F.u5QjS8168K3s3y/XW2hWn5E11lCqyGq');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'General',
  `image` TEXT DEFAULT NULL,
  `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blotter_records`
--

CREATE TABLE `blotter_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `case_no` VARCHAR(50) NOT NULL UNIQUE,
  `date_filed` DATE NOT NULL,
  `incident_type` VARCHAR(100) NOT NULL,
  `complainant` VARCHAR(255) NOT NULL,
  `respondent` VARCHAR(255) DEFAULT NULL,
  `incident_location` VARCHAR(255) DEFAULT NULL,
  `narrative` TEXT NOT NULL,
  `status` ENUM('Open','Under Mediation','Resolved','Referred to Higher Authority') DEFAULT 'Open',
  `date_resolved` DATE DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reference_no` VARCHAR(20) NOT NULL UNIQUE,
  `document_type` VARCHAR(100) NOT NULL,
  `requester_name` VARCHAR(255) NOT NULL,
  `requester_address` TEXT NOT NULL,
  `requester_contact` VARCHAR(50) DEFAULT NULL,
  `purpose` TEXT NOT NULL,
  `status` ENUM('Pending','Processing','Ready for Pickup','Released','Cancelled') DEFAULT 'Pending',
  `admin_notes` TEXT DEFAULT NULL,
  `date_requested` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_entries`
--

CREATE TABLE `budget_entries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year` INT NOT NULL,
  `type` ENUM('income','expense') NOT NULL,
  `sector` VARCHAR(150) NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
