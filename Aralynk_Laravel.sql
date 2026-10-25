-- =====================================================================
-- ARALYNK (Laravel version): complete database
-- Import in phpMyAdmin: Import tab > choose this file > Import.
-- It creates the database "aralynk_laravel" with every table and the starter data.
-- For Laravel 11 or newer. After importing, create the admin account with:
--     php artisan db:seed --class=AdminSeeder
-- (Optional demo data: php artisan db:seed --class=DemoSeeder)
-- Do NOT run "php artisan migrate:fresh" after importing: it would erase this data.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `aralynk_laravel` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aralynk_laravel`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `ratings`, `activity_logs`, `job_reports`, `notifications`, `messages`, `applications`,
  `job_post_days`, `job_post_course`, `job_post_skill`, `job_posts`,
  `student_day_availabilities`, `student_availabilities`, `student_experiences`, `student_skills`,
  `business_profiles`, `student_profiles`, `skills`, `categories`, `courses`,
  `failed_jobs`, `job_batches`, `jobs`, `cache_locks`, `cache`, `sessions`, `password_reset_tokens`, `users`, `migrations`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Laravel's own tables ----------
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Aralynk lookup tables ----------
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `skills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skills_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Profiles ----------
CREATE TABLE `student_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `student_no` varchar(30) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `year_level` tinyint unsigned DEFAULT NULL,
  `about` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `student_profiles_student_no_unique` (`student_no`),
  CONSTRAINT `student_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_profiles_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `business_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `contact_person` varchar(120) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `business_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `business_profiles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Student details ----------
CREATE TABLE `student_skills` (
  `student_profile_id` bigint unsigned NOT NULL,
  `skill_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`student_profile_id`, `skill_id`),
  CONSTRAINT `student_skills_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_experiences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint unsigned NOT NULL,
  `title` varchar(120) NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `student_experiences_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_experiences_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_availabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint unsigned NOT NULL,
  `day_of_week` tinyint unsigned NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_availabilities_student_profile_id_day_of_week_unique` (`student_profile_id`, `day_of_week`),
  CONSTRAINT `student_availabilities_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_day_availabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint unsigned NOT NULL,
  `avail_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_day_availabilities_student_profile_id_avail_date_unique` (`student_profile_id`, `avail_date`),
  CONSTRAINT `student_day_availabilities_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Jobs ----------
CREATE TABLE `job_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `business_profile_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `job_type` varchar(20) NOT NULL,
  `work_location` varchar(255) NOT NULL,
  `work_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slots` tinyint unsigned NOT NULL DEFAULT 1,
  `required_experience` varchar(255) DEFAULT NULL,
  `additional_requirements` text,
  `compensation` varchar(100) DEFAULT NULL,
  `deadline` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `job_posts_business_profile_id_foreign` FOREIGN KEY (`business_profile_id`) REFERENCES `business_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_post_skill` (
  `job_post_id` bigint unsigned NOT NULL,
  `skill_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`job_post_id`, `skill_id`),
  CONSTRAINT `job_post_skill_job_post_id_foreign` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_post_skill_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_post_course` (
  `job_post_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`job_post_id`, `course_id`),
  CONSTRAINT `job_post_course_job_post_id_foreign` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_post_course_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_post_days` (
  `job_post_id` bigint unsigned NOT NULL,
  `day_of_week` tinyint unsigned NOT NULL,
  PRIMARY KEY (`job_post_id`, `day_of_week`),
  CONSTRAINT `job_post_days_job_post_id_foreign` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Applications, messages, notifications ----------
CREATE TABLE `applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint unsigned NOT NULL,
  `job_post_id` bigint unsigned NOT NULL,
  `message` text,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_student_profile_id_job_post_id_unique` (`student_profile_id`, `job_post_id`),
  CONSTRAINT `applications_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_job_post_id_foreign` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `receiver_id` bigint unsigned NOT NULL,
  `job_post_id` bigint unsigned DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_job_post_id_foreign` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Reports, history, ratings ----------
CREATE TABLE `job_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_post_id` bigint unsigned NOT NULL,
  `reporter_id` bigint unsigned NOT NULL,
  `reason` varchar(500) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `job_reports_job_post_id_foreign` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Two-way ratings: a business rates a student, or a student rates a business, after a job is Completed.
CREATE TABLE `ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned NOT NULL,
  `rater_id` bigint unsigned NOT NULL,
  `ratee_id` bigint unsigned NOT NULL,
  `rater_role` varchar(20) NOT NULL,
  `stars` tinyint unsigned NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratings_application_id_rater_id_unique` (`application_id`, `rater_id`),
  CONSTRAINT `ratings_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_rater_id_foreign` FOREIGN KEY (`rater_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ratee_id_foreign` FOREIGN KEY (`ratee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Tell Laravel these migrations are already done ----------
INSERT INTO `migrations` (`migration`, `batch`) VALUES
 ('0001_01_01_000000_create_users_table', 1),
 ('0001_01_01_000001_create_cache_table', 1),
 ('0001_01_01_000002_create_jobs_table', 1),
 ('2026_10_25_000001_add_role_and_status_to_users_table', 1),
 ('2026_10_25_000002_create_lookup_tables', 1),
 ('2026_10_25_000003_create_profile_tables', 1),
 ('2026_10_25_000004_create_student_detail_tables', 1),
 ('2026_10_25_000005_create_job_post_tables', 1),
 ('2026_10_25_000006_create_application_message_notification_tables', 1),
 ('2026_10_25_000007_create_report_log_rating_tables', 1);

-- ---------- Starter data ----------
INSERT INTO `courses` (`code`, `name`) VALUES
 ('DVM','Doctor of Veterinary Medicine (DVM)'),
 ('BSA-AnSci','Bachelor of Science in Agriculture (BSA) - Animal Science'),
 ('BSA-AgExt','Bachelor of Science in Agriculture (BSA) - Agricultural Extension and Communication'),
 ('BSA-Hort','Bachelor of Science in Agriculture (BSA) - Crop Science, specializing in Horticulture'),
 ('BSA-Agro','Bachelor of Science in Agriculture (BSA) - Crop Science, specializing in Agronomy'),
 ('BSABE','Bachelor of Science in Agricultural and Biosystems Engineering'),
 ('BSGE','Bachelor of Science in Geodetic Engineering'),
 ('BSFT','Bachelor of Science in Food Technology'),
 ('BSIT','Bachelor of Science in Information Technology (BSIT)'),
 ('BSAgribiz','Bachelor of Science in Agribusiness'),
 ('BSBA-Mktg','Bachelor of Science in Business Administration (BSBA) - Marketing Management'),
 ('BSHM','Bachelor of Science in Hospitality Management (BSHM)'),
 ('BEEd','Bachelor of Elementary Education (BEEd)'),
 ('BSEd-Eng','Bachelor of Secondary Education (BSEd) - English'),
 ('BSEd-Sci','Bachelor of Secondary Education (BSEd) - Science'),
 ('BSEd-Math','Bachelor of Secondary Education (BSEd) - Mathematics'),
 ('BSDevCom','Bachelor of Science in Development Communication');

INSERT INTO `categories` (`name`) VALUES
 ('IT / Technology'),
 ('Food / Restaurant'),
 ('Sales / Business'),
 ('Social Media / Content'),
 ('Tutoring / Education'),
 ('Agriculture / Farm'),
 ('Events'),
 ('Retail / Store'),
 ('Customer Service / Call Center'),
 ('Administrative / Clerical'),
 ('Accounting / Bookkeeping'),
 ('Marketing / Advertising'),
 ('Graphic Design / Multimedia'),
 ('Writing / Translation'),
 ('Photography / Videography'),
 ('Delivery / Logistics'),
 ('Driving / Transportation'),
 ('Construction / Carpentry'),
 ('Electrical / Plumbing / Repair'),
 ('Manufacturing / Production'),
 ('Warehouse / Inventory'),
 ('Cleaning / Housekeeping'),
 ('Hotel / Tourism'),
 ('Health / Wellness'),
 ('Veterinary / Animal Care'),
 ('Fisheries / Aquaculture'),
 ('Food Processing / Technology'),
 ('Engineering / Surveying'),
 ('Research / Data Encoding'),
 ('Beauty / Salon'),
 ('Laundry'),
 ('Security'),
 ('Community / Government Service'),
 ('Arts / Crafts'),
 ('Entertainment / Music'),
 ('Sports / Fitness'),
 ('Gardening / Landscaping'),
 ('Agribusiness / Farm Marketing'),
 ('Childcare'),
 ('Other');

INSERT INTO `skills` (`name`) VALUES
 ('PHP'),
 ('HTML/CSS'),
 ('JavaScript'),
 ('MySQL'),
 ('MS Word'),
 ('MS Excel'),
 ('Graphic Design'),
 ('Photo/Video Editing'),
 ('Social Media Management'),
 ('Content Writing'),
 ('Customer Service'),
 ('Cashiering'),
 ('Cooking / Food Prep'),
 ('Tutoring'),
 ('Data Encoding'),
 ('Event Assistance'),
 ('Farm Work'),
 ('Computer Repair'),
 ('Communication'),
 ('Basic Bookkeeping'),
 ('Python'),
 ('Java'),
 ('C# / .NET'),
 ('Web Design'),
 ('Mobile App Development'),
 ('Networking'),
 ('Database Management'),
 ('Typing'),
 ('MS PowerPoint'),
 ('Canva'),
 ('Copywriting'),
 ('Translation'),
 ('Online Selling'),
 ('Sales / Selling'),
 ('Marketing'),
 ('Teamwork'),
 ('Time Management'),
 ('Leadership'),
 ('Public Speaking'),
 ('Problem Solving'),
 ('Baking'),
 ('Barista / Coffee Making'),
 ('Food Service / Waiting Tables'),
 ('Food Safety'),
 ('Teaching / Lesson Planning'),
 ('Child Care'),
 ('Event Hosting / Emcee'),
 ('Photography'),
 ('Videography'),
 ('Drawing / Arts'),
 ('Planting / Harvesting'),
 ('Livestock / Animal Handling'),
 ('Veterinary Assistance'),
 ('Crop Management'),
 ('Irrigation / Farm Machinery'),
 ('Pest Management'),
 ('Soil Sampling'),
 ('Surveying / Mapping'),
 ('AutoCAD / Drafting'),
 ('Food Processing'),
 ('Quality Control'),
 ('Packaging'),
 ('Inventory Management'),
 ('Warehouse Work'),
 ('Driving'),
 ('Motorcycle Delivery'),
 ('Carpentry'),
 ('Electrical Work'),
 ('Plumbing'),
 ('Welding'),
 ('Painting'),
 ('Cleaning / Housekeeping'),
 ('Laundry'),
 ('Sewing'),
 ('Hair / Beauty'),
 ('Massage'),
 ('First Aid'),
 ('Research / Data Gathering'),
 ('Survey / Interviewing'),
 ('Community Outreach'),
 ('Development Communication'),
 ('Radio / Broadcasting'),
 ('Writing / Editing'),
 ('Customer Chat Support'),
 ('Bookkeeping'),
 ('Gardening / Landscaping');
