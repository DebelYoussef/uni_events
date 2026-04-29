<?php
/**
 * Application Constants
 * Centralized configuration for the University Events Management System
 */

// Base URL - Update based on your environment
define('BASE_URL', 'http://localhost/unievents/');

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('SESSION_NAME', 'uni_events_session');

// User Roles
define('ROLE_STUDENT', 'student');
define('ROLE_ORGANIZER', 'organizer');
define('ROLE_ADMIN', 'admin');

// Admin credentials (for first-time setup only - can be moved to config)
define('DEFAULT_ADMIN_EMAIL', 'admin@unievents.local');
define('DEFAULT_ADMIN_PASSWORD', 'Admin@123');

// Upload paths
define('UPLOAD_DIR', '/uploads/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');

// Security headers
define('BCRYPT_ROUNDS', 12);

// Status codes and messages
define('SUCCESS', 'success');
define('ERROR', 'error');
define('INFO', 'info');
define('WARNING', 'warning');

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_UPPERCASE', true);
define('REQUIRE_NUMBERS', true);
define('REQUIRE_SPECIAL_CHARS', true);
