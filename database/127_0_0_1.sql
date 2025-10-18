-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 04:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cs_db`
--
CREATE DATABASE IF NOT EXISTS `cs_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cs_db`;

-- --------------------------------------------------------

--
-- Table structure for table `tb_advisor`
--

CREATE TABLE `tb_advisor` (
  `Advisor_ID` varchar(20) NOT NULL,
  `Advisor_FName` varchar(50) NOT NULL,
  `Advisor_SName` varchar(50) NOT NULL,
  `Major_ID` int(11) NOT NULL,
  `UserName` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Password` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_advisor`
--

INSERT INTO `tb_advisor` (`Advisor_ID`, `Advisor_FName`, `Advisor_SName`, `Major_ID`, `UserName`, `Password`) VALUES
('0000000001', 'สุรินทร์', 'เพชรไทย', 1, '0000000001', '0ff89de99d4a8f4b04cb162bcb5740cf'),
('0000000002', 'ภูมินทร์', 'ตันอุตม์', 1, '0000000002', '639fc2398fd45606ada087e30168287b'),
('0000000003', 'กีรศักดิ์', 'พะยะ', 1, '0000000003', '4dde77cd192e5101fe0a317e00ba3827'),
('0000000004', 'จินดาพร', 'อ่อนเกตุ', 2, '0000000004', '7aa03c3c187aa873b6f20d687ff5c92a');

-- --------------------------------------------------------

--
-- Table structure for table `tb_enroll`
--

CREATE TABLE `tb_enroll` (
  `Row_ID` int(100) NOT NULL,
  `Student_ID` int(9) NOT NULL,
  `Subject_ID` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Schedule_ID` int(10) NOT NULL,
  `GPA` float NOT NULL,
  `Assessment_Score` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_enroll`
--

INSERT INTO `tb_enroll` (`Row_ID`, `Student_ID`, `Subject_ID`, `Schedule_ID`, `GPA`, `Assessment_Score`) VALUES
(1, 0, '', 0, 0, 0),
(2, 661320129, '4122204', 10, 0, 0),
(3, 661320129, '4122204', 10, 0, 0),
(4, 661320129, '4122204', 10, 0, 0),
(5, 661320129, '4122204', 10, 0, 0),
(6, 661320129, '4122204', 10, 0, 0),
(7, 661320129, '4123709 ', 9, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tb_major`
--

CREATE TABLE `tb_major` (
  `Major_ID` int(11) NOT NULL,
  `Major_Name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_major`
--

INSERT INTO `tb_major` (`Major_ID`, `Major_Name`) VALUES
(1, 'วิทยาการคอมพิวเตอร์'),
(2, 'เทคโนโลยีสารสนเทศ');

-- --------------------------------------------------------

--
-- Table structure for table `tb_room`
--

CREATE TABLE `tb_room` (
  `Room_ID` int(5) NOT NULL,
  `Room_Name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_room`
--

INSERT INTO `tb_room` (`Room_ID`, `Room_Name`) VALUES
(48232, 'ห้องปฏิบัติการปัญญาประดิษฐ์2');

-- --------------------------------------------------------

--
-- Table structure for table `tb_schedule`
--

CREATE TABLE `tb_schedule` (
  `Schedule_ID` int(10) NOT NULL,
  `Subject_ID` varchar(10) NOT NULL,
  `Advisor_ID` varchar(20) NOT NULL,
  `Room_ID` int(5) NOT NULL,
  `Schedule_Semester` int(1) NOT NULL,
  `Schedule_Year` varchar(4) NOT NULL,
  `Schedule_Day` varchar(20) NOT NULL,
  `Schedule_Start` varchar(10) NOT NULL,
  `Schedule_End` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_schedule`
--

INSERT INTO `tb_schedule` (`Schedule_ID`, `Subject_ID`, `Advisor_ID`, `Room_ID`, `Schedule_Semester`, `Schedule_Year`, `Schedule_Day`, `Schedule_Start`, `Schedule_End`) VALUES
(9, '4123709', '0000000001', 48232, 1, '2568', 'จันทร์', '11', '16'),
(10, '4122204', '0000000001', 48232, 1, '2568', 'จันทร์', '8', '9');

-- --------------------------------------------------------

--
-- Table structure for table `tb_student`
--

CREATE TABLE `tb_student` (
  `Student_ID` int(9) NOT NULL,
  `Student_FName` varchar(30) NOT NULL,
  `Student_SName` varchar(32) NOT NULL,
  `Major_ID` int(2) NOT NULL,
  `Advisor_ID` int(11) DEFAULT NULL,
  `UserName` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Password` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_student`
--

INSERT INTO `tb_student` (`Student_ID`, `Student_FName`, `Student_SName`, `Major_ID`, `Advisor_ID`, `UserName`, `Password`) VALUES
(661320112, 'นางสาวอนุธิดา', 'มาอินทร์', 1, 2000000001, '661320112', 'e890df20233475bc5747485dc68119c2'),
(661320114, 'นางสาวเฉลิมภรณ์', 'พ่วงอาสา', 1, 2000000001, '661320114', 'ad6b429f6e4137e61bc5b6ddef1780fb'),
(661320121, 'นายกฤษฎา', 'หมื่นฤทธิ์', 1, 2000000001, '661320121', 'a61b5027f16c11f43c345f1e17327846'),
(661320122, 'นายภาณุวัฒน์', 'โออ่อน', 1, 2000000001, '661320122', 'b61157bad271332ae1ac67d680ff22ed'),
(661320127, 'นายสัณหณัฐ', 'แดงใหม่', 1, 2000000001, '661320127', '6a3b92150f6e0752e74de73dd0533f8e'),
(661320128, 'นางสาวฑิตยา', 'แปงคำมา', 1, 2000000001, '661320128', '299e163a799b2ab5de8969750aa61e56'),
(661320129, 'นายรัชชานนท์', 'แก้วเอี่ยม', 1, 2000000001, '661320129', '4934b7b3af492abab3d1a01ed415a29c');

-- --------------------------------------------------------

--
-- Table structure for table `tb_subject`
--

CREATE TABLE `tb_subject` (
  `Subject_ID` varchar(10) NOT NULL,
  `Subject_Name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Subject_Credit` int(2) NOT NULL,
  `Subject_Description` varchar(500) NOT NULL,
  `Major_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_subject`
--

INSERT INTO `tb_subject` (`Subject_ID`, `Subject_Name`, `Subject_Credit`, `Subject_Description`, `Major_ID`) VALUES
('4122204', 'การออกแบบและบริหารฐานข้อมูล ', 3, 'การออกแบบและบริหารฐานข้อมูล ', 2),
('4123302', 'วิศวกรรมซอฟต์แวร์ ', 3, 'วิศวกรรมซอฟต์แวร์ ', 1),
('4123709 ', 'เทคโนโลยีไร้สายและการเชื่อมต่อทุกสรรพสิ่งผ่านอินเท', 3, 'เทคโนโลยีไร้สายและการเชื่อมต่อทุกสรรพสิ่งผ่านอินเทอร์เน็ต ', 1),
('4123724', 'การพัฒนาฐานข้อมูลบนเว็บ ', 3, 'การพัฒนาฐานข้อมูลบนเว็บ ', 1),
('4123801', 'จริยธรรมและกฎหมายทางคอมพิวเตอร์ ', 3, 'จริยธรรมและกฎหมายทางคอมพิวเตอร์ ', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_advisor`
--
ALTER TABLE `tb_advisor`
  ADD PRIMARY KEY (`Advisor_ID`),
  ADD KEY `Major_ID` (`Major_ID`);

--
-- Indexes for table `tb_enroll`
--
ALTER TABLE `tb_enroll`
  ADD PRIMARY KEY (`Row_ID`);

--
-- Indexes for table `tb_major`
--
ALTER TABLE `tb_major`
  ADD PRIMARY KEY (`Major_ID`);

--
-- Indexes for table `tb_room`
--
ALTER TABLE `tb_room`
  ADD PRIMARY KEY (`Room_ID`);

--
-- Indexes for table `tb_schedule`
--
ALTER TABLE `tb_schedule`
  ADD PRIMARY KEY (`Schedule_ID`);

--
-- Indexes for table `tb_student`
--
ALTER TABLE `tb_student`
  ADD PRIMARY KEY (`Student_ID`);

--
-- Indexes for table `tb_subject`
--
ALTER TABLE `tb_subject`
  ADD PRIMARY KEY (`Subject_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_enroll`
--
ALTER TABLE `tb_enroll`
  MODIFY `Row_ID` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_major`
--
ALTER TABLE `tb_major`
  MODIFY `Major_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_schedule`
--
ALTER TABLE `tb_schedule`
  MODIFY `Schedule_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
--
-- Database: `database design`
--
CREATE DATABASE IF NOT EXISTS `database design` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `database design`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `alumni_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `gender` enum('ชาย','หญิง') NOT NULL,
  `birthdate` date NOT NULL,
  `graduate_year` year(4) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `occupation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum`
--

CREATE TABLE `forum` (
  `forum_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_reply`
--

CREATE TABLE `forum_reply` (
  `reply_id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  `reply_content` text NOT NULL,
  `replied_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `news_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`alumni_id`);

--
-- Indexes for table `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`forum_id`);

--
-- Indexes for table `forum_reply`
--
ALTER TABLE `forum_reply`
  ADD PRIMARY KEY (`reply_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`news_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`);
--
-- Database: `equipment_management`
--
CREATE DATABASE IF NOT EXISTS `equipment_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `equipment_management`;

-- --------------------------------------------------------

--
-- Table structure for table `borrowing`
--

CREATE TABLE `borrowing` (
  `borrow_id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `borrow_date` datetime NOT NULL,
  `expected_return_date` datetime NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `return_request_date` datetime DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('borrowed','pending_return','returned','overdue') DEFAULT 'borrowed',
  `notes` text DEFAULT NULL,
  `return_notes` text DEFAULT NULL,
  `condition_on_return` enum('good','damaged','need_repair') DEFAULT 'good',
  `approved_by` int(11) DEFAULT NULL,
  `checked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `borrowing`
--

INSERT INTO `borrowing` (`borrow_id`, `equipment_id`, `user_id`, `borrow_date`, `expected_return_date`, `actual_return_date`, `return_request_date`, `quantity`, `status`, `notes`, `return_notes`, `condition_on_return`, `approved_by`, `checked_by`, `created_at`) VALUES
(1, 1, 3, '2025-10-17 06:58:12', '2025-10-19 23:59:59', '2025-10-17 07:00:54', '2025-10-17 06:58:19', 1, 'returned', '', '', 'good', NULL, 2, '2025-10-17 04:58:12'),
(2, 1, 2, '2025-10-17 07:00:24', '2025-10-18 23:59:59', '2025-10-17 07:01:12', '2025-10-17 07:01:06', 1, 'returned', '', '', 'need_repair', NULL, 2, '2025-10-17 05:00:24'),
(3, 1, 2, '2025-10-17 07:00:39', '2025-10-18 23:59:59', '2025-10-17 07:15:48', '2025-10-17 07:03:09', 1, 'returned', '', '', 'need_repair', NULL, 1, '2025-10-17 05:00:39'),
(4, 1, 1, '2025-10-17 07:12:46', '2025-10-18 23:59:59', NULL, NULL, 1, 'borrowed', '', NULL, 'good', NULL, NULL, '2025-10-17 05:12:46'),
(5, 2, 3, '2025-10-17 07:18:21', '2025-10-25 23:59:59', '2025-10-17 07:44:58', '2025-10-17 07:44:26', 1, 'returned', '', '', 'damaged', NULL, 2, '2025-10-17 05:18:21'),
(6, 2, 3, '2025-10-17 07:50:14', '2025-10-18 23:59:59', '2025-10-17 07:51:07', '2025-10-17 07:50:43', 1, 'returned', '', '', 'need_repair', NULL, 1, '2025-10-17 05:50:14'),
(7, 1, 3, '2025-10-17 07:54:28', '2025-10-18 23:59:59', '2025-10-17 08:31:12', '2025-10-17 07:54:40', 1, 'returned', '', '', 'good', NULL, 1, '2025-10-17 05:54:28'),
(8, 18, 1, '2025-10-17 07:56:33', '2025-10-18 23:59:59', NULL, NULL, 1, 'borrowed', '', NULL, 'good', NULL, NULL, '2025-10-17 05:56:33'),
(9, 1, 1, '2025-10-17 08:31:32', '2025-10-25 23:59:59', NULL, NULL, 1, 'borrowed', '', NULL, 'good', NULL, NULL, '2025-10-17 06:31:32');

-- --------------------------------------------------------

--
-- Table structure for table `equipments`
--

CREATE TABLE `equipments` (
  `equipment_id` int(11) NOT NULL,
  `equipment_code` varchar(50) NOT NULL,
  `equipment_name` varchar(200) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `available_quantity` int(11) DEFAULT 1,
  `status` enum('available','borrowed','maintenance','damaged') DEFAULT 'available',
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipments`
--

INSERT INTO `equipments` (`equipment_id`, `equipment_code`, `equipment_name`, `type_id`, `brand`, `model`, `quantity`, `available_quantity`, `status`, `description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'NB001', 'Computer Notebook', 2, '', '', 5, 4, 'maintenance', '', 'uploads/equipments/68f1f832a8003.jpg', '2025-10-17 04:20:06', '2025-10-17 08:02:58'),
(2, 'ARM001', 'Robotic Arm', 3, 'Hiwonder', 'AiArm', 2, 2, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 05:51:07'),
(3, 'LIMO001', 'Mobile Robot', 4, 'AGILE X', 'Limo', 1, 1, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(4, 'SORT001', 'Autonomous AI Sorting System', 5, 'Hiwonder', 'Autonomous Al Sorting System', 1, 1, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(5, 'GRAV001', 'Electronics Board', 6, 'Gravitech', '', 3, 3, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(6, 'DISP001', 'Display 75 inches', 7, 'PULIN', '75 inches', 1, 1, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(7, 'CROW001', 'Crow Pi 2', 6, 'Elecrow', 'Crow Pi 2', 2, 2, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(8, 'SPEAK001', 'Speaker System', 8, 'Behringer', 'MPA40BT-PRO', 2, 2, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(9, 'ENC001', 'Enclosure SATA', 9, '', 'Nvem-SATA', 5, 5, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(10, 'ENC002', 'Enclosure M.2', 9, 'ORICO', 'TCM2-10G-C3-BP-HW Blue', 3, 3, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(11, 'HDMI001', 'Cable HDMI 5M', 10, 'UGREEN', 'V.1.4 M/M 5M', 10, 10, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(12, 'HDMI002', 'Cable HDMI 10M', 10, 'UGREEN', 'V.1.4 M/M 10M', 5, 5, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(13, 'PLUG001', 'ปลั๊กแยก 4 ทาง', 11, '', 'หัวเทียบทองเหลือง', 8, 8, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(14, 'PLUG002', 'ปลั๊กไฟ 5 เมตร', 11, '', '', 10, 10, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(15, 'IKON001', 'อุปกรณ์อิเล็กทรอนิกส์', 6, 'IKON', '2931', 2, 2, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(16, 'AMP001', 'เครื่องขยายเสียง', 8, '', '', 3, 3, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(17, 'NET001', 'Network Equipment', 12, '', '', 5, 5, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(18, 'IOT001', 'IOT Device', 13, '', '', 4, 3, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 05:56:33'),
(19, 'CAM001', 'กล้อง', 14, '', '', 3, 3, 'available', NULL, NULL, '2025-10-17 04:20:06', '2025-10-17 04:20:06'),
(20, 'NF101', 'กล้วย', 5, 'adf', 'dsfaf', 5, 4, 'available', '', 'uploads/equipments/68f2fe4639947.png', '2025-10-17 06:39:00', '2025-10-18 02:41:10'),
(21, 'N011', 'dsfdf', 2, 'asdf', 'dasf', 4, 4, 'available', 'sdafasdf', 'uploads/equipments/68f1fa777406d.jpg', '2025-10-17 08:12:39', '2025-10-17 08:12:39');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_types`
--

CREATE TABLE `equipment_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_types`
--

INSERT INTO `equipment_types` (`type_id`, `type_name`, `description`, `created_at`) VALUES
(1, 'ทุกประเภท', 'อุปกรณ์ทั่วไป', '2025-10-17 04:20:06'),
(2, 'Computer Notebook', 'เครื่องคอมพิวเตอร์โน้ตบุ๊ค', '2025-10-17 04:20:06'),
(3, 'Robotic Arm', 'แขนกลหุ่นยนต์', '2025-10-17 04:20:06'),
(4, 'Mobile Robot', 'หุ่นยนต์เคลื่อนที่', '2025-10-17 04:20:06'),
(5, 'AI System', 'ระบบปัญญาประดิษฐ์', '2025-10-17 04:20:06'),
(6, 'Electronics', 'อุปกรณ์อิเล็กทรอนิกส์', '2025-10-17 04:20:06'),
(7, 'Display', 'อุปกรณ์แสดงผล', '2025-10-17 04:20:06'),
(8, 'Audio', 'อุปกรณ์เสียง', '2025-10-17 04:20:06'),
(9, 'Storage', 'อุปกรณ์จัดเก็บข้อมูล', '2025-10-17 04:20:06'),
(10, 'Cable', 'สายเคเบิล', '2025-10-17 04:20:06'),
(11, 'Power', 'อุปกรณ์ไฟฟ้า', '2025-10-17 04:20:06'),
(12, 'Network', 'อุปกรณ์เครือข่าย', '2025-10-17 04:20:06'),
(13, 'IOT', 'อุปกรณ์ IOT', '2025-10-17 04:20:06'),
(14, 'Camera', 'กล้อง', '2025-10-17 04:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `material_code` varchar(50) NOT NULL,
  `material_name` varchar(200) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `min_quantity` int(11) DEFAULT 10,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `material_code`, `material_name`, `type_id`, `unit`, `quantity`, `min_quantity`, `description`, `created_at`, `updated_at`, `image_url`) VALUES
(1, 'N001', 'กล้วย1', 5, 'ชิ้น', 29, 1, '0', '2025-10-17 06:18:40', '2025-10-18 02:39:38', 'uploads/materials/mat_68f2fdea8f6f5_1760755178.png');

-- --------------------------------------------------------

--
-- Table structure for table `material_requisition`
--

CREATE TABLE `material_requisition` (
  `requisition_id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `requisition_date` datetime NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_requisition`
--

INSERT INTO `material_requisition` (`requisition_id`, `material_id`, `user_id`, `quantity`, `requisition_date`, `status`, `purpose`, `approved_by`, `notes`, `created_at`) VALUES
(1, 1, 1, 2, '2025-10-17 08:19:26', 'rejected', 'กิน', 1, NULL, '2025-10-17 06:19:26'),
(2, 1, 1, 1, '2025-10-17 08:26:50', 'approved', 'eat', 1, NULL, '2025-10-17 06:26:50'),
(3, 1, 9, 2, '2025-10-18 04:43:51', 'pending', 'กิน', NULL, NULL, '2025-10-18 02:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','staff','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin', 'ผู้ดูแลระบบ', 'admin@system.com', NULL, 'admin', '2025-10-17 04:20:06'),
(2, 'staff', 'staff', 'เจ้าหน้าที่', 'staff@system.com', NULL, 'staff', '2025-10-17 04:20:06'),
(3, 'user1', 'user1', 'นายภาณุวัฒน์  โออ่อน', 'panuwataoon@gmail.com', '091266626', 'user', '2025-10-17 04:47:53'),
(5, 'wuttichai', '', 'kk', 'kk@gmail.com', '0123456789', 'user', '2025-10-17 06:13:18'),
(8, 'wuttichai1', '$2y$10$MpKKCJQ2EZy95JyA8mXtDOP1XRNRRpE56JjOvRFQRvXRd9Y8lGO0G', '่าา', 'N@gmail.com', '01234654123', 'user', '2025-10-17 06:17:27'),
(9, 'zxy_abcd', '123', 'นายภาณุวัฒน์  โออ่อน', 'mumos678@gmail.com', '0695223122', 'user', '2025-10-18 02:42:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowing`
--
ALTER TABLE `borrowing`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `checked_by` (`checked_by`);

--
-- Indexes for table `equipments`
--
ALTER TABLE `equipments`
  ADD PRIMARY KEY (`equipment_id`),
  ADD UNIQUE KEY `equipment_code` (`equipment_code`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `equipment_types`
--
ALTER TABLE `equipment_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD UNIQUE KEY `material_code` (`material_code`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `material_requisition`
--
ALTER TABLE `material_requisition`
  ADD PRIMARY KEY (`requisition_id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowing`
--
ALTER TABLE `borrowing`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `equipment_types`
--
ALTER TABLE `equipment_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `material_requisition`
--
ALTER TABLE `material_requisition`
  MODIFY `requisition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowing`
--
ALTER TABLE `borrowing`
  ADD CONSTRAINT `borrowing_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipments` (`equipment_id`),
  ADD CONSTRAINT `borrowing_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowing_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowing_ibfk_4` FOREIGN KEY (`checked_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `equipments`
--
ALTER TABLE `equipments`
  ADD CONSTRAINT `equipments_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `equipment_types` (`type_id`);

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `equipment_types` (`type_id`);

--
-- Constraints for table `material_requisition`
--
ALTER TABLE `material_requisition`
  ADD CONSTRAINT `material_requisition_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`),
  ADD CONSTRAINT `material_requisition_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `material_requisition_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"cs_db\",\"table\":\"tb_enroll\"},{\"db\":\"cs_db\",\"table\":\"tb_advisor\"},{\"db\":\"database design\",\"table\":\"forum_reply\"},{\"db\":\"database design\",\"table\":\"forum\"},{\"db\":\"database design\",\"table\":\"news\"},{\"db\":\"database design\",\"table\":\"admins\"},{\"db\":\"database design\",\"table\":\"teachers\"},{\"db\":\"database design\",\"table\":\"alumni\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-10-18 02:33:41', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
