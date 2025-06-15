-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 08:13 AM
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
-- Database: `chainscholar`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_web_title_similarity_16850760acb98262becd6c454595b989', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037946),
('laravel_cache_web_title_similarity_1cdbf7ac81a44ce18c3f8fffb9e19b45', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037947),
('laravel_cache_web_title_similarity_24adf3e192df837e3adeada9621b2b76', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037947),
('laravel_cache_web_title_similarity_2744967bb9d4c771881e7207e09004b3', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037945),
('laravel_cache_web_title_similarity_35b535826515597cdbcac99d7954667e', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037944),
('laravel_cache_web_title_similarity_4a790b99282afea54c78f0447ccbc0c7', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037948),
('laravel_cache_web_title_similarity_53b3ad035f2dc6e9c7648e76972d41d2', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037945),
('laravel_cache_web_title_similarity_5d16f9739daf84c651618501d2ae1278', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037945),
('laravel_cache_web_title_similarity_60a5948d0b9e0d09d906342f4980d2f1', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037945),
('laravel_cache_web_title_similarity_6102824b6ed6157133d918b8ac91c6a9', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037948),
('laravel_cache_web_title_similarity_67eba6af44d971150d8db7d225f4d0cd', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037946),
('laravel_cache_web_title_similarity_712eccf23ee28ea6aa834278b7d158f4', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037947),
('laravel_cache_web_title_similarity_75371e7e4287a757bf721d999f0e75d5', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037942),
('laravel_cache_web_title_similarity_88305750ae9ca5ceb4febc1e20d3fe14', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037945),
('laravel_cache_web_title_similarity_8c9af9ce21a45d0a37c730405602ac57', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750038023),
('laravel_cache_web_title_similarity_a799ecd5a5df826e641374140112fbda', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750038052),
('laravel_cache_web_title_similarity_b3f80c677a911c352deab08f2faa36c4', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037945),
('laravel_cache_web_title_similarity_b56e41bd06cdb11f2c9d9a3a043ae4d7', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037943),
('laravel_cache_web_title_similarity_cdb07a79ec9e1745f0180bb9269a53f5', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037947),
('laravel_cache_web_title_similarity_eca9db22ffa7fdb2fc9d336917dbace0', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037941),
('laravel_cache_web_title_similarity_ed06cb6224629ba619741142ee4cf0bc', 'a:3:{s:14:\"max_similarity\";i:0;s:8:\"approved\";b:1;s:7:\"results\";a:0:{}}', 1750037944);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `content` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `title`, `file_path`, `status`, `content`, `created_at`, `updated_at`) VALUES
(13, 3, 'ChainScholar: A Blockchain-Powered System for Securing Academic Research Ownership with AI-Driven Plagiarism Detection', NULL, 'pending', '<p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>“ChainScholar: A Blockchain-Powered System for Securing Academic Research Ownership with AI-Driven Plagiarism Detection”</strong></span></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>A Research Presented to the Faculty of&nbsp;</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Institute of Computer Studies&nbsp;</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\">Holy Cross College</span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Sta. Ana, Pampanga</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong><img src=\"data:image/jpeg;base64,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\" alt=\"A yellow and blue logo\r\n\r\nAI-generated content may be incorrect.\" width=\"131\" height=\"131\" v:shapes=\"Picture_x0020_2\" uploadprocessed=\"true\"></strong></span></p><p style=\"text-align:center;\"><br><br>&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>In Partial Fulfillment of the Requirements for the degree&nbsp;</strong></span></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>Bachelor of Science in information Technology</strong></span></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>____________________</strong></span></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Submitted by:</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Dizon, Joel, N</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Dizon, John Carlo, V.</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Sebastian, Aron, P.</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\">&nbsp;</p>', '2025-06-14 18:01:26', '2025-06-14 18:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_09_101716_create_documents_table', 1),
(5, '2025_06_13_021842_create_templates_table', 1),
(6, '2025_06_14_035921_create_notifications_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'Document Submitted', 'Your document \"Hello\" has been submitted and is now pending approval.', 1, '2025-06-13 20:38:44', '2025-06-13 20:05:39', '2025-06-13 20:38:44'),
(2, 3, 'Document Submitted', 'Your document \"gdagda\" has been submitted and is now pending approval.', 1, '2025-06-13 20:47:54', '2025-06-13 20:10:49', '2025-06-13 20:47:54'),
(3, 3, 'Document Submitted', 'Your document \"This\" has been submitted and is now pending approval.', 0, NULL, '2025-06-14 02:25:22', '2025-06-14 02:25:22'),
(4, 3, 'Document Submitted', 'Your document \"ChainScholar: A Blockchain-Powered System for Securing Academic Research Ownership with AI-Driven Plagiarism Detection\" has been submitted and is now pending approval.', 0, NULL, '2025-06-14 18:01:26', '2025-06-14 18:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('AczqJqJe6gytJ4eFyFjtexEUBIHMooloEyfvfL4m', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieEJpWkFFZlZNbklaaEJDcGdFWGhOMW84VVZWeXJUOTdoakJXVUNvUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9jaGFpbnNjaG9sYXIudGVzdC9kb2N1bWVudHMvMTMvZWRpdCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1749962022),
('mQRGQgXqV6AOXgdRtZNBK2jsztwtCBCAi3TUYM9n', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoidGFNWnp4cnV6aWFGZ3ZBMzBNUm5Rb2FSa0hGek9zU2VyYkxrWVA5YSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749958643);

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `user_id`, `title`, `name`, `file_path`, `content`, `created_at`, `updated_at`) VALUES
(1, 2, '', 'Action Research', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>IMPROVING CASH FLOW MANAGEMENT PRACTICES IN FAMILY-OWNED RETAIL BUSINESSES THROUGH ACTION RESEARCH</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>An Action Research Presented to the Faculty of</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>College of Business and Accountancy</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Holy Cross College</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>In Partial Fulfillment of the Requirements for the Degree</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Bachelor of Science in Accountancy</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><!-- Abstract --><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This action research project aimed to enhance cash flow management practices among family-owned retail businesses through the introduction of structured budgeting tools and financial literacy workshops. The project followed a cycle of planning, action, observation, and reflection, leading to measurable improvements in cash flow tracking and decision-making.</p><!-- Acknowledgment --><p style=\"text-align:center;\"><strong>ACKNOWLEDGMENT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">The researchers wish to thank their adviser, participating business owners, and faculty members for their guidance and support during this action research project.</p><!-- Table of Contents --><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Acknowledgment ............ ii</p><p>Table of Contents ............ iii</p><p>Chapter I - Introduction ............ 1</p><p>Chapter II - Action Research Questions ............ 5</p><p>Chapter III - Intervention / Innovation ............ 12</p><p>Chapter IV - Results and Reflections ............ 18</p><p>Chapter V - Summary, Conclusion, and Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><!-- Chapter I --><p style=\"text-align:center;\"><strong>CHAPTER I</strong></p><p style=\"text-align:center;\"><strong>INTRODUCTION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Family-owned retail businesses in the local community face persistent challenges in managing cash flow effectively. Many lack formal budgeting systems and financial planning processes. This action research project was initiated to address this practical problem through direct intervention and capacity building.</p><!-- Chapter II --><p style=\"text-align:center;\"><strong>CHAPTER II</strong></p><p style=\"text-align:center;\"><strong>ACTION RESEARCH QUESTIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The following research questions guided this project: 1) How can structured budgeting tools improve cash flow management practices? 2) What is the effect of financial literacy workshops on the financial decision-making of business owners? 3) What challenges and barriers do family-owned businesses face in adopting new cash flow management practices?</p><!-- Chapter III --><p style=\"text-align:center;\"><strong>CHAPTER III</strong></p><p style=\"text-align:center;\"><strong>INTERVENTION / INNOVATION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The intervention involved the introduction of customized budgeting templates, a series of three financial literacy workshops, and one-on-one coaching sessions. The action cycle included phases of planning, implementation, observation, and reflection, allowing for iterative improvement based on participant feedback.</p><!-- Chapter IV --><p style=\"text-align:center;\"><strong>CHAPTER IV</strong></p><p style=\"text-align:center;\"><strong>RESULTS AND REFLECTIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Post-intervention evaluations indicated that participating businesses improved their ability to track cash inflows and outflows, forecast financial needs, and make more informed spending decisions. Reflection sessions revealed that peer learning and contextualized examples greatly enhanced understanding and application of cash flow concepts.</p><!-- Chapter V --><p style=\"text-align:center;\"><strong>CHAPTER V</strong></p><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The action research approach proved effective in addressing the identified problem. It is recommended that future efforts focus on scaling the intervention, providing continuous support, and fostering a community of practice among local business owners to sustain improvements in financial management.</p><!-- References --><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><!-- Appendices --><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Budgeting Template</p><p>Appendix B: Workshop Materials</p>', '2025-06-13 15:54:02', '2025-06-13 19:16:39'),
(2, 2, '', 'Capstone Project', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>IMPLEMENTATION OF A CLOUD-BASED ACCOUNTING SYSTEM FOR SMALL RETAIL BUSINESSES</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>A Capstone Project Presented to the Faculty of</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>College of Business and Accountancy</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Holy Cross College</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>In Partial Fulfillment of the Requirements for the Degree</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Bachelor of Science in Accountancy</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><!-- Abstract --><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This capstone project aims to design and implement a cloud-based accounting system tailored for small retail businesses. The project addresses operational inefficiencies and limited financial reporting capabilities by providing an affordable and scalable solution. Results show improved accuracy, faster report generation, and enhanced financial decision-making among pilot businesses.</p><!-- Acknowledgment --><p style=\"text-align:center;\"><strong>ACKNOWLEDGMENT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">We would like to express our sincere gratitude to our adviser, our professors, and the participating business owners who provided invaluable feedback and supported this project’s implementation.</p><!-- Table of Contents --><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Acknowledgment ............ ii</p><p>Table of Contents ............ iii</p><p>Chapter I - Project Background and Rationale ............ 1</p><p>Chapter II - Review of Related Systems and Literature ............ 5</p><p>Chapter III - System Design and Methodology ............ 12</p><p>Chapter IV - Results of Implementation and Evaluation ............ 18</p><p>Chapter V - Summary, Conclusion, and Future Enhancements ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><!-- Chapter I --><p style=\"text-align:center;\"><strong>CHAPTER I</strong></p><p style=\"text-align:center;\"><strong>PROJECT BACKGROUND AND RATIONALE</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Small retail businesses often face operational inefficiencies due to manual accounting practices. Cloud-based solutions offer opportunities for process improvement and greater financial control. This project seeks to address these challenges through the development of an accessible accounting system.</p><!-- Chapter II --><p style=\"text-align:center;\"><strong>CHAPTER II</strong></p><p style=\"text-align:center;\"><strong>REVIEW OF RELATED SYSTEMS AND LITERATURE</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Several cloud-based systems such as Xero and QuickBooks offer off-the-shelf solutions; however, these platforms often lack customization for small local retailers. Literature highlights the importance of tailoring financial software to specific operational workflows.</p><!-- Chapter III --><p style=\"text-align:center;\"><strong>CHAPTER III</strong></p><p style=\"text-align:center;\"><strong>SYSTEM DESIGN AND METHODOLOGY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The system was developed using a modular architecture with features for invoicing, expense tracking, payroll, and automated financial reports. User feedback was gathered through iterative prototyping cycles. The implementation followed an agile development model with continuous stakeholder engagement.</p><!-- Chapter IV --><p style=\"text-align:center;\"><strong>CHAPTER IV</strong></p><p style=\"text-align:center;\"><strong>RESULTS OF IMPLEMENTATION AND EVALUATION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pilot implementation across five small retail businesses demonstrated significant improvements in reporting speed and accuracy. User satisfaction surveys indicated positive perceptions of usability and system reliability. Some challenges were noted regarding initial user training and internet connectivity requirements.</p><!-- Chapter V --><p style=\"text-align:center;\"><strong>CHAPTER V</strong></p><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND FUTURE ENHANCEMENTS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The project successfully developed and implemented a cloud-based accounting solution that meets the needs of small retailers. Future enhancements include integration with inventory management systems and mobile access capabilities. Ongoing training and support will ensure sustained system adoption and usage.</p><!-- References --><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><!-- Appendices --><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: System User Guide</p><p>Appendix B: Evaluation Survey Instrument</p>', '2025-06-13 15:56:03', '2025-06-13 19:19:18'),
(3, 2, '', 'Business Feasibility Study', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>FEASIBILITY STUDY FOR THE ESTABLISHMENT OF AN ONLINE ACCOUNTING SERVICES FIRM</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>A Feasibility Study Presented to the Faculty of</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>College of Business and Accountancy</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Holy Cross College</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>In Partial Fulfillment of the Requirements for the Degree</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Bachelor of Science in Accountancy</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><!-- Executive Summary --><p style=\"text-align:center;\"><strong>EXECUTIVE SUMMARY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This study evaluates the feasibility of establishing an online accounting services firm targeting SMEs in the Philippines. The firm will provide affordable bookkeeping, payroll, and tax compliance services through a secure cloud-based platform. The study includes market research, technical and operational analysis, financial projections, and risk assessment. The results demonstrate strong market potential and financial viability, recommending the project\'s implementation.</p><!-- Acknowledgment --><p style=\"text-align:center;\"><strong>ACKNOWLEDGMENT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">We express our sincere gratitude to our adviser, faculty members, and industry experts whose insights greatly contributed to the development of this feasibility study.</p><!-- Table of Contents --><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Executive Summary ............ i</p><p>Acknowledgment ............ ii</p><p>Table of Contents ............ iii</p><p>Business Concept ............ 1</p><p>Market Analysis ............ 5</p><p>Technical / Operational Feasibility ............ 12</p><p>Financial Feasibility ............ 18</p><p>Conclusion and Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><!-- Business Concept --><p style=\"text-align:center;\"><strong>BUSINESS CONCEPT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The proposed online accounting services firm aims to provide SMEs with professional accounting support at competitive rates. By leveraging cloud technologies, the firm will offer on-demand access to financial reports, automated compliance tools, and expert consultation services, thereby filling a gap in the market for affordable and flexible accounting solutions.</p><!-- Market Analysis --><p style=\"text-align:center;\"><strong>MARKET ANALYSIS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The Philippine SME sector continues to expand rapidly, driving demand for affordable accounting services. Market research indicates that many SMEs lack internal accounting expertise and are highly receptive to cloud-based service models. Competitive analysis reveals opportunities to differentiate through superior user experience and service customization.</p><!-- Technical / Operational Feasibility --><p style=\"text-align:center;\"><strong>TECHNICAL / OPERATIONAL FEASIBILITY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The technical architecture will employ secure cloud hosting with multi-level user authentication. The operational workflow includes client onboarding, data import, automated transaction processing, and real-time reporting. Partnerships with IT providers and secure payment processors will support system reliability and scalability.</p><!-- Financial Feasibility --><p style=\"text-align:center;\"><strong>FINANCIAL FEASIBILITY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Projected startup costs amount to PHP 1.5 million, covering system development, marketing, and operational expenses. Financial forecasts indicate break-even within two years, with a projected net income margin of 20% by year three. Sensitivity analysis confirms the business model\'s resilience to market and operational risks.</p><!-- Conclusion and Recommendations --><p style=\"text-align:center;\"><strong>CONCLUSION AND RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The feasibility study confirms strong market demand and financial viability for the proposed online accounting services firm. It is recommended to proceed with project implementation, with particular emphasis on robust cybersecurity measures, user-friendly interface design, and comprehensive client support services.</p><!-- References --><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><!-- Appendices --><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Market Survey Questionnaire</p><p>Appendix B: Financial Projections Spreadsheet</p>', '2025-06-13 16:26:18', '2025-06-13 19:17:52'),
(5, 2, '', 'Thesis Template', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>THE IMPACT OF ACCOUNTING INFORMATION SYSTEMS ON BUSINESS PERFORMANCE</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>A Research Presented to the Faculty of</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>College of Business and Accountancy</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Holy Cross College</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>In Partial Fulfillment of the Requirements for the Degree</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Bachelor of Science in Accountancy</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><!-- Abstract --><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This study explores the relationship between the adoption of Accounting Information Systems (AIS) and overall business performance among SMEs. Quantitative data was gathered via surveys and analyzed statistically. Results indicate that effective AIS use contributes significantly to efficiency, accuracy, and financial performance.</p><!-- Acknowledgment --><p style=\"text-align:center;\"><strong>ACKNOWLEDGMENT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">The researchers wish to thank their adviser, faculty members, and family for their support during this research project.</p><!-- Table of Contents --><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Acknowledgment ............ ii</p><p>Table of Contents ............ iii</p><p>Chapter I - Introduction ............ 1</p><p>Chapter II - Review of Related Literature ............ 5</p><p>Chapter III - Methodology ............ 12</p><p>Chapter IV - Results and Discussion ............ 18</p><p>Chapter V - Summary, Conclusion, and Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><!-- Chapter I --><p style=\"text-align:center;\"><strong>CHAPTER I</strong></p><p style=\"text-align:center;\"><strong>INTRODUCTION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The increasing role of technology in accounting practices necessitates the use of AIS to improve efficiency and competitiveness among businesses.</p><!-- Chapter II --><p style=\"text-align:center;\"><strong>CHAPTER II</strong></p><p style=\"text-align:center;\"><strong>REVIEW OF RELATED LITERATURE</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Research shows that businesses utilizing AIS systems gain improved data accuracy and decision-making capabilities.</p><!-- Chapter III --><p style=\"text-align:center;\"><strong>CHAPTER III</strong></p><p style=\"text-align:center;\"><strong>RESEARCH METHODOLOGY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. A survey-based quantitative approach was used to gather data from SMEs.</p><!-- Chapter IV --><p style=\"text-align:center;\"><strong>CHAPTER IV</strong></p><p style=\"text-align:center;\"><strong>RESULTS AND DISCUSSION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Results indicate a strong positive relationship between AIS adoption and business performance metrics.</p><!-- Chapter V --><p style=\"text-align:center;\"><strong>CHAPTER V</strong></p><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. It is recommended that SMEs invest in robust AIS solutions and staff training programs.</p><!-- References --><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><!-- Appendices --><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Survey Questionnaire</p><p>Appendix B: Data Tables</p>', '2025-06-13 19:08:00', '2025-06-13 19:19:35'),
(6, 2, '', 'Case Study Research', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>CASE STUDY ON THE IMPLEMENTATION OF INTERNAL CONTROL SYSTEMS IN A MID-SIZED MANUFACTURING COMPANY</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>A Case Study Research Presented to the Faculty of</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>College of Business and Accountancy</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Holy Cross College</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>In Partial Fulfillment of the Requirements for the Degree</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Bachelor of Science in Accountancy</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><!-- Abstract --><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This case study explores the implementation of internal control systems at XYZ Manufacturing Company. The study examines the design, execution, and effectiveness of internal controls in mitigating risks and ensuring compliance with regulatory standards. Data was gathered through direct observation, document review, and interviews with key personnel. The findings highlight both strengths and areas for improvement in the current internal control framework.</p><!-- Acknowledgment --><p style=\"text-align:center;\"><strong>ACKNOWLEDGMENT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">The researchers would like to express their gratitude to their adviser, the management and staff of XYZ Manufacturing Company, and faculty members who provided guidance and support throughout this case study project.</p><!-- Table of Contents --><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Acknowledgment ............ ii</p><p>Table of Contents ............ iii</p><p>Introduction and Background of the Study ............ 1</p><p>Profile of the Case / Subject ............ 5</p><p>Analysis of Findings ............ 12</p><p>Discussion and Implications ............ 18</p><p>Summary, Conclusion, and Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><!-- Introduction and Background --><p style=\"text-align:center;\"><strong>INTRODUCTION AND BACKGROUND OF THE STUDY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Internal control systems play a critical role in organizational governance and risk management. This study focuses on the internal control practices of XYZ Manufacturing Company, which operates in a competitive and highly regulated industry. The purpose is to analyze how internal controls are implemented and their effectiveness in promoting operational efficiency and compliance.</p><!-- Profile of the Case / Subject --><p style=\"text-align:center;\"><strong>PROFILE OF THE CASE / SUBJECT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. XYZ Manufacturing Company is a mid-sized firm engaged in the production of consumer electronics. Established in 2005, the company has grown to employ over 200 staff members across its production, sales, and administrative departments. In response to increasing compliance requirements, the company launched an initiative to strengthen its internal control environment.</p><!-- Analysis of Findings --><p style=\"text-align:center;\"><strong>ANALYSIS OF FINDINGS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Data collected revealed that XYZ Manufacturing Company has established a comprehensive internal control framework that includes segregation of duties, authorization protocols, physical asset safeguards, and periodic reconciliation procedures. However, weaknesses were identified in areas such as documentation consistency and monitoring of control activities.</p><!-- Discussion and Implications --><p style=\"text-align:center;\"><strong>DISCUSSION AND IMPLICATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The findings suggest that while the company has made significant progress in implementing internal controls, further enhancements are necessary to fully mitigate risks. Strengthening training programs and adopting automated monitoring tools could enhance control effectiveness and ensure sustained compliance. The study also highlights the importance of fostering a culture of accountability and continuous improvement.</p><!-- Summary, Conclusion, and Recommendations --><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This case study concludes that internal control systems at XYZ Manufacturing Company provide a solid foundation for risk management, though opportunities for improvement remain. It is recommended that the company enhance documentation practices, implement continuous control monitoring, and promote cross-departmental collaboration to strengthen its control environment.</p><!-- References --><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><!-- Appendices --><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Interview Questions</p><p>Appendix B: Internal Control Assessment Checklist</p>', '2025-06-13 19:18:45', '2025-06-13 19:18:45'),
(7, 2, '', 'Program Evaluation Research', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>EVALUATION OF A FINANCIAL LITERACY PROGRAM FOR MICROENTREPRENEURS IN RURAL COMMUNITIES</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>A Program Evaluation Research Presented to the Faculty of</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>College of Business and Accountancy</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Holy Cross College</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>In Partial Fulfillment of the Requirements for the Degree</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;\"><strong>Bachelor of Science in Accountancy</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><!-- Abstract --><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This study evaluates the effectiveness of a financial literacy program designed for rural microentrepreneurs. The program aims to enhance knowledge and skills in budgeting, saving, and debt management. The evaluation employed pre- and post-intervention surveys and focus group discussions to assess changes in financial behavior and business outcomes.</p><!-- Table of Contents --><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Table of Contents ............ ii</p><p>Chapter I - Introduction and Program Background ............ 1</p><p>Chapter II - Evaluation Framework and Methodology ............ 5</p><p>Chapter III - Program Implementation ............ 12</p><p>Chapter IV - Results and Analysis ............ 18</p><p>Chapter V - Summary, Conclusion, and Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><!-- Chapter I --><p style=\"text-align:center;\"><strong>CHAPTER I</strong></p><p style=\"text-align:center;\"><strong>INTRODUCTION AND PROGRAM BACKGROUND</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Microentrepreneurs in rural communities often lack access to formal financial education. The financial literacy program evaluated in this study was developed to address this gap, with the goal of improving financial behaviors and strengthening business sustainability.</p><!-- Chapter II --><p style=\"text-align:center;\"><strong>CHAPTER II</strong></p><p style=\"text-align:center;\"><strong>EVALUATION FRAMEWORK AND METHODOLOGY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The study used a mixed-methods evaluation framework, incorporating quantitative pre- and post-program surveys and qualitative focus group discussions. The Kirkpatrick model of training evaluation guided the assessment of learning outcomes and behavioral changes.</p><!-- Chapter III --><p style=\"text-align:center;\"><strong>CHAPTER III</strong></p><p style=\"text-align:center;\"><strong>PROGRAM IMPLEMENTATION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The program was implemented over three months, with modules covering basic budgeting, savings strategies, debt management, and record-keeping. Participants engaged in interactive workshops and follow-up coaching sessions.</p><!-- Chapter IV --><p style=\"text-align:center;\"><strong>CHAPTER IV</strong></p><p style=\"text-align:center;\"><strong>RESULTS AND ANALYSIS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The evaluation found significant improvements in participants’ financial knowledge and behaviors. Increased savings rates, more consistent record-keeping, and improved debt management practices were observed. Qualitative feedback highlighted the importance of practical, context-specific training materials.</p><!-- Chapter V --><p style=\"text-align:center;\"><strong>CHAPTER V</strong></p><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The financial literacy program demonstrated positive impacts on microentrepreneurs\' financial behaviors. Continued support, periodic refresher training, and the integration of digital tools are recommended to sustain and expand the program’s impact.</p><!-- References --><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><!-- Appendices --><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Pre- and Post-Survey Questionnaire</p><p>Appendix B: Focus Group Discussion Guide</p>', '2025-06-13 19:20:44', '2025-06-13 19:20:44'),
(8, 2, '', 'Policy Research', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>POLICY RESEARCH ON THE EFFECTIVENESS OF TAX INCENTIVES FOR MSMES IN THE PHILIPPINES</strong></span></p><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This policy research examines the effectiveness of tax incentives for micro, small, and medium enterprises (MSMEs) in the Philippines. It analyzes the current policy framework, reviews international best practices, and provides recommendations for enhancing the impact of tax incentives on MSME growth and formalization.</p><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Table of Contents ............ ii</p><p>Chapter I - Introduction and Policy Context ............ 1</p><p>Chapter II - Review of Related Literature and Best Practices ............ 5</p><p>Chapter III - Policy Analysis and Evaluation ............ 12</p><p>Chapter IV - Findings and Policy Options ............ 18</p><p>Chapter V - Summary, Conclusion, and Policy Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><p style=\"text-align:center;\"><strong>CHAPTER I</strong></p><p style=\"text-align:center;\"><strong>INTRODUCTION AND POLICY CONTEXT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tax incentives are a common policy tool aimed at stimulating business growth and formalization among MSMEs. This research explores the policy landscape in the Philippines and evaluates whether existing tax incentives are achieving their intended outcomes.</p><p style=\"text-align:center;\"><strong>CHAPTER II</strong></p><p style=\"text-align:center;\"><strong>REVIEW OF RELATED LITERATURE AND BEST PRACTICES</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. International literature indicates that the design and implementation of tax incentives significantly influence their effectiveness. Lessons from countries such as Singapore and Malaysia highlight the importance of simplicity, targeting, and transparency.</p><p style=\"text-align:center;\"><strong>CHAPTER III</strong></p><p style=\"text-align:center;\"><strong>POLICY ANALYSIS AND EVALUATION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The analysis evaluates the impact of current tax incentives on MSME registration, investment levels, and tax compliance. Stakeholder interviews and secondary data analysis were conducted to assess the policy outcomes and identify gaps.</p><p style=\"text-align:center;\"><strong>CHAPTER IV</strong></p><p style=\"text-align:center;\"><strong>FINDINGS AND POLICY OPTIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The study finds that while tax incentives contribute to increased MSME registrations, challenges remain regarding awareness, administrative complexity, and equity of access. Several policy options are proposed to address these issues and enhance the incentives’ impact.</p><p style=\"text-align:center;\"><strong>CHAPTER V</strong></p><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND POLICY RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. To improve the effectiveness of tax incentives for MSMEs, this study recommends simplifying administrative processes, improving outreach and education, and establishing clear monitoring and evaluation mechanisms.</p><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Stakeholder Interview Guide</p><p>Appendix B: Tax Incentives Policy Matrix</p>', '2025-06-13 19:21:11', '2025-06-13 19:21:11'),
(9, 2, '', 'Training Needs Assessment', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:\'Times New Roman\',serif;font-size:16pt;\"><strong>TRAINING NEEDS ASSESSMENT OF ACCOUNTING STAFF IN LOCAL GOVERNMENT UNIT</strong></span></p><p style=\"text-align:center;\"><strong>ABSTRACT</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This study assesses the training needs of accounting staff in selected Local Government Units (LGUs). The assessment aims to identify competency gaps and recommend priority areas for future training programs to enhance financial reporting and accountability.</p><p style=\"text-align:center;\"><strong>TABLE OF CONTENTS</strong></p><p>Abstract ............ i</p><p>Table of Contents ............ ii</p><p>Chapter I - Introduction and Background ............ 1</p><p>Chapter II - Literature Review and Conceptual Framework ............ 5</p><p>Chapter III - Methodology ............ 12</p><p>Chapter IV - Results and Discussion ............ 18</p><p>Chapter V - Summary, Conclusion, and Training Recommendations ............ 25</p><p>References ............ 30</p><p>Appendices ............ 32</p><p style=\"text-align:center;\"><strong>CHAPTER I</strong></p><p style=\"text-align:center;\"><strong>INTRODUCTION AND BACKGROUND</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. LGU accounting staff face evolving demands for financial management and compliance. This study seeks to identify current competency levels and training needs to support capacity-building efforts and improve public financial management practices.</p><p style=\"text-align:center;\"><strong>CHAPTER II</strong></p><p style=\"text-align:center;\"><strong>LITERATURE REVIEW AND CONCEPTUAL FRAMEWORK</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Training needs assessment frameworks emphasize the importance of aligning training with organizational goals and individual competency requirements. The conceptual framework of this study integrates competency-based and results-based training approaches.</p><p style=\"text-align:center;\"><strong>CHAPTER III</strong></p><p style=\"text-align:center;\"><strong>METHODOLOGY</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. A mixed-methods approach was used, combining surveys, competency assessments, and focus group discussions. The sample included accounting staff from five LGUs, with data analyzed to identify competency gaps and training priorities.</p><p style=\"text-align:center;\"><strong>CHAPTER IV</strong></p><p style=\"text-align:center;\"><strong>RESULTS AND DISCUSSION</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The assessment revealed gaps in areas such as financial analysis, report writing, and compliance with updated accounting standards. Participants expressed strong interest in targeted training programs and on-the-job learning opportunities.</p><p style=\"text-align:center;\"><strong>CHAPTER V</strong></p><p style=\"text-align:center;\"><strong>SUMMARY, CONCLUSION, AND TRAINING RECOMMENDATIONS</strong></p><p style=\"text-align:justify;text-indent:.5in;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. The study concludes that a structured training program focusing on priority competency areas is needed. Recommendations include developing a comprehensive training plan, incorporating blended learning methods, and conducting periodic evaluations to monitor progress.</p><p style=\"text-align:center;\"><strong>REFERENCES</strong></p><p>Author A. (Year). Title of Book. Publisher.</p><p>Author B. (Year). Title of Article. Journal Name.</p><p style=\"text-align:center;\"><strong>APPENDICES</strong></p><p>Appendix A: Competency Assessment Tool</p><p>Appendix B: Focus Group Discussion Summary</p>', '2025-06-13 19:21:39', '2025-06-14 02:26:07');
INSERT INTO `templates` (`id`, `user_id`, `title`, `name`, `file_path`, `content`, `created_at`, `updated_at`) VALUES
(21, 3, '', 'Hcc', NULL, '<p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>NEURACARE: AI-INTEGRATED MENTAL HEALTH PLATFORMS</strong></span></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>A Research Presented to the Faculty of&nbsp;</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Institute of Computer Studies&nbsp;</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\">Holy Cross College</span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Sta. Ana, Pampangaa</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong><img src=\"data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAoHBwgHBgoICAgLCgoLDhgQDg0NDh0VFhEYIx8lJCIfIiEmKzcvJik0KSEiMEExNDk7Pj4+JS5ESUM8SDc9Pjv/2wBDAQoLCw4NDhwQEBw7KCIoOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozv/wAARCACDAIMDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD2aiiigAooooAKKKKACikzRmgBaKKTNAC0UUUAFFFFABRRRQAUUUUAFFFFABRRTZJEhjaSR1REUszMcAAdSaAFJwM1zlz4uFzcy2Xh2yfWLmI7ZJEcJbQn0aU8E+y5NZs1zP4yinu5ppbHwrApYlcpLqCqCWJPVYvpy30qm+vy6v4UvbPwpZ3OjtbQrJZOIkCXEfJ2oRkBiAeOox9a0hTlPbYTdjRvrPV1tWvPE3ixdMts4MWnIIVGeg8x8sT9MVnX9h4Fg0u3v7ua+1aK9m2QMLyedpX9AA3Xg1zOmHV/FPhaXTIxPd3Mcqalp9xLIXUENhoWdhww56nmtiHwfqS+FZbK+On2aNqRu47e8kHyRsOV3jOxs9Ctdbw1OnpUlqmRzN7Fu5sfh7YafbXzRXdouoK3k+TJcrIQv3jtByMd8itm20G8+yxXvh3xZe+TKgeJbsi6idTyPvYbH/Aq5y/8M61qWg6ZZWusafqd3YxuDP8AaSJIpCcqyuMkgL8pU9cVp+Pri9sPDWmac0skEE7pHf3sCkeWirlsBehYjA6enesvY05NRhLVtlXfU0z4l1PRDjxPpojtwedRscyQD3dfvJ9eR710ltcwXlulxbTJNDINySIwZWHqCK84svFa+FdIit1eTVn1CRri1tpWMRtbXH8ZfJA4PUnNaNlDJBp0PinwfBJ9ku1E1zo7DasoydzRjoknXpw2KzqUZQV3sNO53dFUdH1ez1vTor6wl8yGTPUYZSOqsOxB4Iq9WIwooooAKKKKACiiigArktcL+J9dHhqNiNPtVWfVWUkeZnlIMj+9jc3sMd66TUL2LTtPuL6Y4it4mlc+yjJ/lXn0l9ZaR4Nij1jUryw1LXWN9NPZxlpIixBBOOiqNq/hVQg5yUUJuxo6X4/sbrxLcaFeRLZ2rqFs/OiMROBho3VvXtjjHFVbrwjpPhW7TWLrVpItJs7n7Va2ITLLMRjYh6kE4+UDnApmgrqPiV449VvdI13QrbLm98oeYzDG1WU4KEfeJ7j1zUMk154luH15P9WjGPSYn+7GmcGbHdm7egox+LhgoXTtfTfr92nnpoXRpOrKw651XVb5BCGbQbNsmOxsVH2l+/zuBhCeuFGfeq0nhi0aQtHpdtcBkB829ZpZGPvuJrctrKK3LSbEM8nMkirjcfX2rnvEPiGS3uJrGKx1LKLhporcsrjqcHP6/WvjFjsRjK3LB/5fcer7OnRjexKnha1aVFn0TTFQyEs0CFGVccYIwc06zvtT0aVY9P1CaM4z/Z+qOZImHosn3kP4kVzXhLxBLp2n26yWeqXUW35RFAWXOSCQ2ef6c16FPBHdQ+XKoKnkex9adbEYjA1fed0+3+TbCMadaOqMq40vSPG9zdXzJeW2qQRKt9pHmKrThOUXcf4CcfMpweM8iqul64mk7PEniC8miupUa30/QbUEeWgO3aU7nI6nj69BPLpWpwqt5aTKdQ087rKVjlpV/ijk/wBlumOx5FT38wnFj408OadFNqGqCO0k+0jctsxO3eQBncpGwkEcV9dluYwxcORu9tP6b2T+/Sx5mIoOlLQ0JZ28P6nb+JEgltdN1coup20owbaVuEmI7HOFb8DXaDpXDaHp+rXaX+m+KtYtbttTgZHsjIu+EgkfIo427cH1zitrwVf3F54ejhvG3XlhI9ncHuXjO3P4jB/GtakeWVr3MlsdBRRRUDCiiigAooooA5r4gb5PCU9pHjN5NDbfexw8qqf0JrmfH15ov9sQ297Za5bz26iKC9sAAp3DO0DPzfTFdH4/VRolnO77Et9TtJGPt5qj+tU/Fnhvwg9xJq+tB0uJGRDIlwysDwq4APGPp2Nb4ecKc+ad7eQnGUtImN5NvpXwyvZrCe7M+sTrAZbuHypNzsIvujp8uee/WtuKGO2hjt4lCxxKEQDsAMCovFVpbaV4Jso7GQz2tlfWzb5Jd52+aMkt3+9T3uoUuxas4EpXeoPGRnHX1z2r5HiSU6lSFtVqz0MFaMXclqjq9jHfWb+YzARIXUD+8OQf0q9TLhGe2lRRktGwA9yK+Xo1JU6ilF2O+STVjmPAGnwp4cs74FvOlQhs8jAYgD/Pqa6qsfwnYXOl+GLKyvI/KniVg6ZBxlieo+tbFb46o6mJm731dvQmkrQQVnaNbySx+LNAimeASKLiF0O0x+ch3YPb5lJ/E1ckuYYpI43cb5GCqo5OTnH06Hn2o8NMsvivXmfAiitLaJ2JGM/vGP04YV6uQOccS+zX6nPi7OBxfhzTNFsNS0+5vNa0OzuYLtZfLtJTNJINoATeemWznGc59hXoWgYtvGXiWzDALI1vdqmeQXj2sfzSsbTvDngWS7k0u1eJ7iCKKMyeYuZCG3hlI6vngkduK1tJj3/EfxBOrKVSztI2GeQfnP8AKvvMRWjVldNv1PKUHHRo6miiiuYYUUUUAFFFFAGN4u0+TVPCmpWcOfOeAtFjrvX5l/UCsW/0C08f6RpWqCYW5e3DtJHGC53L93J7Bs12VcjoyR6PrGoeFLgFbS8El1p5BxmNz+9jB9VYk/Rvak0nuXCcoS5ouzLa6Lp934UuPDCT2xkS28mYQEDY5HDFc5BzzzXEk3N9YxXbApf2bfZr2LJzHKvGQBnr1GByCOa31sX8J3qSWx37WBury+kWGN4iABFGO7jaDnHUdfmxU+uaM18V8TeHhFdtcQgXNtuwl9FjjB6Bx2Pfoa4sZh3VgnDdf00VGd21LqZaeRraxs9xPDNGNhVG25PBPHrjGR2zinHQBlD/AGnfZXjPm9fT8eOtZkKw3e+4015GMRxPaMpWeE7gTHsAG3oMt396txeIGt49t2oMkcUjS5YcODlU3DjO0/mK+WqU68NKL+VlodkasVpU+8vWmkC0DgXtzJvUL8z9MHOfr/8AXqM2cemtHcTaldssQLeW75DcEHjqQM5/Co5vEVukgCYKpMUlOdxCBc7uOBkkDms5hPLbfatWuFtIYgPNmkbG4gEbkYZByD93HPvUU6eIb/euyflqypVYbQV2PVxcXkurX7eTa2q+Y7Z6KOgBHUe4JB9Oa67wTp8sWiS319DsutWma6ljcfcVgAiH6IBx7msTQ9Ak1+S3ubq0Npodu3nRwypskvnH3XdRjag6gHrx2qx4n1O11uV9FliR4WmhWCSGUGbe4BWZUPDxjOOM5w3TFfT4HDOn78lZvRLsjhqPTlLejeAbXR9cGqLOsjZkPlGIBE3HjZ6Y6VY8Ft9ufWdc5K6jft5JPeKMCNT9DtY/jVXVQ/h/wza6BpZA1bUsW8RVmb5yo8yXJOcKoJ+uPWun0vToNJ0y20+1GIbaJY0+gGMn3r0kktiZ1JTd5O5booopkBRRRQAUUUUAFY/iTQ/7bsEWGb7NfWz+dZ3IHMMo6H3B6Edwa2KKAORiks/GVjcaRrFotlrFoMTxYBeEn/lrC3XaccMPoas634i/4RfSYETTGmkWNVCRblgjH3VBkI4yeAMZPtV3XPDdrrPlXCyyWeoW3NtewYEkXt/tKe6ng1lNr97pEf2Txfp6mAMNupW0Rkt3wcgyLyYzkA85Ge9ADr6z8L+KNSlhlYLqdrKYPPgcxTK4UMQrD7wA+o4NUbvwJqUtqYLXxElxbuwYR3tmsmee7IVz+NWx4Z0nVUmvtE1LyxdwiFpreQSjYWLPtyeGbOCTnirulaTe6f4Zk0IYIgtmiguFfG7O7aMfw4G3+nSs50qc/iVxqTSsYln4C1eB5gutWVmkoAYWenAEgf7zEfpUp0bwv4cdb/ULiTVL6GMyxtcuJXVQQpKRjCqASOcDHc1GnhHxBb2lhDbaqFkgb7U7yM5RZgqKqAbslT85JOR8xOKsW/gS0tNUm1O4vNkDNIzW2B5SrJnzFyex3EHp0U9RShRpwd4x1HzO1iC+8RXutzrp9jZ3qWt5bl1ntXCXFuyyFGfO7aygheAeQ3Gau2kNn4H0M3eq3Tzys/yIAHPmt1SAYB+Y87fUn3pP+EptAI9N8L2b61cwxiJXib9xCAMDfMeOw4GSauaX4ZlGoLrGvXI1DU1z5W1dsNoD1ES9j6seT7VqSN8OaVey383iHXI1TUblPLhtw25bOHqIwe7E8se547V0lIBgUtABRRRQAUUUUAFFFFABUf2iHzDH5ibx1XcMj8Kkrnk8MSxNcSRag6SXF6bgnaCEXcTge/PckcdKAOgDBiQCCQcEelIVU5yM561ylp4T1W2eFxrTrtn82UIX/eHbGu4kkkk7G4PHz8dKv6VoF9a20Ud5rFzK0N156mNyN4xyjk53LnJxx2oAiuvBvh2+uZZ7eD7Hdg4km0+YwODjvsIGfqKjfwxqsG1bTxjqsY3Fts6RTcemSuahbwjfrYT2lvfW1uz/ACi5ihZJnXczEuwbk/MPxB9affeEr6+ik8zVn8yQvuJLkYITaB83ADJnjGc0ASHw3rszkz+Mr/YVxthtoY/xztNC+BdGeQSao95qzg8f2hdNKuf9zhf0qGbwvrcj3TJrrJ51ys8YBfEeN2B97kYK5H+zV/UdCvJnFxaak5nW8S6VLnLxJtQqUUDBAOc9etAGvBFbwRLBbxxxRoMLHGoUL+A6VKTgZPSuYm8MalNqEd4NTWKQSM5aMMCoZoyQBnBHyMMNkfN7VGnhLUJLGW1vdamuFeKZRl3A3OqqGPPOCGOOnzcUAdWDmlrkp/C2sTNdbdaaFZgnlpG74QqeOp6YwMd8dq6qMOsSLIwdwoDMBjJ9aAH0UUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFAH//2Q==\" alt=\"A yellow and blue logo\r\n\r\nAI-generated content may be incorrect.\" width=\"131\" height=\"131\" v:shapes=\"Picture_x0020_2\" uploadprocessed=\"true\"></strong></span></p><p style=\"text-align:center;\"><br><br>&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>In Partial Fulfillment of the Requirements for the degree&nbsp;</strong></span></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>Bachelor of Science in information Technology</strong></span></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:115%;\"><strong>____________________</strong></span></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Submitted by:</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Dizon, Joel, N</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Dizon, John Carlo, V.</strong></span></p><p style=\"text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Sebastian, Aron, P.</strong></span></p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\">&nbsp;</p><p style=\"text-align:center;\">&nbsp;</p><p style=\"line-height:200%;text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:200%;\"><strong>CHAPTER I</strong></span></span></p><p style=\"line-height:200%;text-align:center;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;font-size:14.0pt;\"><span style=\"line-height:200%;\"><strong>PROBLEMS AND ITS BACKGROUND</strong></span></span></p><p style=\"line-height:200%;text-align:justify;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\"><strong>Introduction</strong></span></p><p style=\"line-height:200%;text-align:justify;text-indent:.5in;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\">Mental health disorders remain a critical global challenge, with nearly 70% of affected individuals lacking access to treatment due to systemic barriers like stigma, cost, and limited care availability (WHO, 2022). The COVID-19 pandemic has intensified these issues, creating an urgent need for scalable solutions that can address rising rates of anxiety, depression, and digital burnout (Santomauro et al., 2021).</span></p><p style=\"line-height:200%;text-align:justify;text-indent:.5in;\"><span style=\"font-family:&quot;Times New Roman&quot;,serif;\">Digital mental health interventions, particularly AI-powered platforms, have emerged as promising tools to bridge this treatment gap. However, most existing solutions offer limited personalization or cultural adaptation, failing to meet diverse user needs. NeuraCare addresses these shortcomings through an AI-driven system that combines real-time mood tracking, adaptive emotional processing, and evidence-based coping strategies. By delivering proactive, culturally responsive support, the platform represents an innovative approach to making mental healthcare more accessible and effective worldwide.</span></p>', '2025-06-14 04:41:43', '2025-06-14 05:10:50'),
(22, 3, '', 'gdagda', NULL, '<p>gdagdgdagdagda</p>', '2025-06-14 05:11:03', '2025-06-14 05:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `position` enum('admin','user') NOT NULL DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `position`, `password`, `avatar`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Jay', 'admin@gmail.com', '2025-06-05 16:52:05', 'admin', '$2y$12$CwBPTw9GFt/.i145HDU.iOlDiymr1CkBFCeQT9uCKkybTaXO8SfWq', NULL, NULL, '2025-06-05 16:51:24', '2025-06-05 16:52:05'),
(3, 'Jc', 'johncarlovictoriadizon@gmail.com', '2025-06-05 16:55:06', 'user', '$2y$12$s1rY2bhXMZ.s/jIQ.2Ehrebo9vwaVAKvmw8Mg8ktxtUkgXdqZH9fa', '3_avatar_1749875043.jpg', NULL, '2025-06-05 16:54:16', '2025-06-13 20:24:03'),
(4, 'Jay Admin', 'admin1@gmail.com', '2025-06-05 16:52:05', 'user', '$2y$12$CwBPTw9GFt/.i145HDU.iOlDiymr1CkBFCeQT9uCKkybTaXO8SfWq', NULL, NULL, '2025-06-05 16:51:24', '2025-06-05 16:52:05'),
(5, 'John Carlo', 'johncarlovictoriadizons@gmail.com', '2025-06-05 16:55:06', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 16:54:16', '2025-06-05 16:55:06'),
(6, 'Alice Reyes', 'user4@example.com', '2025-06-05 17:00:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:00:00', '2025-06-05 17:00:00'),
(7, 'Brian Cruz', 'user5@example.com', '2025-06-05 17:01:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:01:00', '2025-06-05 17:01:00'),
(8, 'Cathy Mendoza', 'user6@example.com', '2025-06-05 17:02:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:02:00', '2025-06-05 17:02:00'),
(9, 'Daniel Santos', 'user7@example.com', '2025-06-05 17:03:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:03:00', '2025-06-05 17:03:00'),
(10, 'Ella Navarro', 'user8@example.com', '2025-06-05 17:04:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:04:00', '2025-06-05 17:04:00'),
(11, 'Francis Dela Cruz', 'user9@example.com', '2025-06-05 17:05:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:05:00', '2025-06-05 17:05:00'),
(12, 'Grace Lim', 'user10@example.com', '2025-06-05 17:06:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:06:00', '2025-06-05 17:06:00'),
(13, 'Harold Tan', 'user11@example.com', '2025-06-05 17:07:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:07:00', '2025-06-05 17:07:00'),
(14, 'Ivy Gomez', 'user12@example.com', '2025-06-05 17:08:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:08:00', '2025-06-05 17:08:00'),
(15, 'Jake Villanueva', 'user13@example.com', '2025-06-05 17:09:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:09:00', '2025-06-05 17:09:00'),
(16, 'Kyla Perez', 'user14@example.com', '2025-06-05 17:10:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:10:00', '2025-06-05 17:10:00'),
(17, 'Lance Uy', 'user15@example.com', '2025-06-05 17:11:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:11:00', '2025-06-05 17:11:00'),
(18, 'Mia Robles', 'user16@example.com', '2025-06-05 17:12:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:12:00', '2025-06-05 17:12:00'),
(19, 'Nathaniel Chua', 'user17@example.com', '2025-06-05 17:13:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:13:00', '2025-06-05 17:13:00'),
(20, 'Olivia Ramos', 'user18@example.com', '2025-06-05 17:14:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:14:00', '2025-06-05 17:14:00'),
(21, 'Patrick Cruz', 'user19@example.com', '2025-06-05 17:15:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:15:00', '2025-06-05 17:15:00'),
(22, 'Queenie Garcia', 'user20@example.com', '2025-06-05 17:16:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:16:00', '2025-06-05 17:16:00'),
(23, 'Ralph Dizon', 'user21@example.com', '2025-06-05 17:17:00', 'user', '$2y$12$kql8WVmE1nA8HTUyO0hXk.a45JV6i1yo60hLb5/P/ulaDLUzukW.i', NULL, NULL, '2025-06-05 17:17:00', '2025-06-05 17:17:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `templates_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
