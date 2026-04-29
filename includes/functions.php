<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

require_once __DIR__ . '/../config/constants.php';

/**
 * Sanitize input string
 * Removes dangerous characters and trims whitespace
 */
function sanitize_input($input)
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validate email format
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Requirements: Min 8 chars, uppercase, numbers, special chars
 */
function is_valid_password($password)
{
    $length_ok = strlen($password) >= MIN_PASSWORD_LENGTH;
    $uppercase_ok = REQUIRE_UPPERCASE ? preg_match('/[A-Z]/', $password) : true;
    $numbers_ok = REQUIRE_NUMBERS ? preg_match('/[0-9]/', $password) : true;
    $special_ok = REQUIRE_SPECIAL_CHARS ? preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\\\|`~]/', $password) : true;
    
    return $length_ok && $uppercase_ok && $numbers_ok && $special_ok;
}

/**
 * Get password strength feedback message
 */
function get_password_feedback($password)
{
    $feedback = [];
    
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $feedback[] = "Le mot de passe doit comporter au moins " . MIN_PASSWORD_LENGTH . " caractères";
    }
    if (REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $feedback[] = "Le mot de passe doit contenir au moins une lettre majuscule";
    }
    if (REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        $feedback[] = "Le mot de passe doit contenir au moins un chiffre";
    }
    if (REQUIRE_SPECIAL_CHARS && !preg_match('/[!@#$%^&*()_+\-=[\]{};:\'",.<>?\/\\|`~]/', $password)) {
        $feedback[] = "Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*)";
    }
    
    return $feedback;
}

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = SUCCESS)
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    redirect($url);
}

/**
 * Set flash message
 */
function set_message($message, $type = SUCCESS)
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Get and clear flash message
 */
function get_flash_message()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? SUCCESS;
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Display flash message HTML
 */
function display_flash_message()
{
    $flash = get_flash_message();
    if (!$flash) return '';
    
    $class = 'alert-' . $flash['type'];
    return sprintf(
        '<div class="alert %s alert-dismissible fade show" role="alert">
            %s
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>',
        htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Hash password using bcrypt
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_ROUNDS]);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Get current user ID
 */
function get_current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function get_current_role()
{
    return $_SESSION['role'] ?? null;
}

/**
 * Get current logged-in user data
 */
function get_logged_in_user()
{
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'name' => $_SESSION['name'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'is_approved' => $_SESSION['is_approved'] ?? false,
    ];
}

/**
 * Format datetime for display
 */
function format_date($date, $format = 'd/m/Y H:i')
{
    if (empty($date)) return '-';
    
    try {
        $datetime = new DateTime($date);
        return $datetime->format($format);
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Generate random token
 */
function generate_token($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check CSRF token
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_token();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Escape output for HTML
 */
function escape_output($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
