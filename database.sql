-- =====================================================
-- University Events Management System
-- Database Schema & Sample Data
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS uni_events;
USE uni_events;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'organizer', 'admin') DEFAULT 'student',
    is_approved TINYINT(1) DEFAULT 0 COMMENT 'For organizers: 0=pending, 1=approved',
    student_id VARCHAR(20) NULL,
    profile_photo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#2563eb' COMMENT 'Hex color code',
    icon VARCHAR(50) NULL COMMENT 'Icon class or emoji',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. EVENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    category_id INT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    location VARCHAR(200) NULL,
    event_date DATETIME NOT NULL,
    capacity INT UNSIGNED DEFAULT 100,
    status ENUM('upcoming', 'ongoing', 'past', 'cancelled') DEFAULT 'upcoming',
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_organizer_id (organizer_id),
    INDEX idx_category_id (category_id),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. REGISTRATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_event (student_id, event_id),
    INDEX idx_student_id (student_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ATTENDANCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    validated_by INT NOT NULL,
    validated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes VARCHAR(255) NULL,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_registration (registration_id),
    INDEX idx_validated_by (validated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. CERTIFICATES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    cert_code VARCHAR(50) NOT NULL UNIQUE,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(255) NULL,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (registration_id),
    INDEX idx_cert_code (cert_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert default admin user
-- Email: admin@unievents.local
-- Password: Admin@123 (bcrypt hash)
INSERT IGNORE INTO users (name, email, password, role, is_approved, created_at) VALUES
('Admin User', 'admin@unievents.local', '$2y$12$v0hErdJhxvnGhN1jCKLiiORN95i9hmbJI6/tVUz654Vh1AoTRMi5a', 'admin', 1, NOW());

-- Insert sample categories
INSERT IGNORE INTO categories (name, color, icon) VALUES
('Tech & Innovation', '#2563eb', '💻'),
('Sports & Fitness', '#ef4444', '⚽'),
('Arts & Culture', '#f59e0b', '🎨'),
('Social & Networking', '#10b981', '👥'),
('Academic Seminars', '#8b5cf6', '📚'),
('Career Development', '#06b6d4', '💼');

-- =====================================================
-- END OF SCHEMA
-- =====================================================
