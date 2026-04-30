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

/**
 * Build application-relative asset URL from path stored in DB.
 */
function asset_url($relative_path)
{
    if (!$relative_path) {
        return null;
    }
    return BASE_URL . 'public/' . ltrim($relative_path, '/');
}

/**
 * Handle secure image upload and return relative path.
 */
function upload_image($field_name, $sub_dir)
{
    if (!isset($_FILES[$field_name]) || !is_array($_FILES[$field_name])) {
        return null;
    }

    $file = $_FILES[$field_name];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload image echoue.');
    }

    $max_size = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $max_size) {
        throw new RuntimeException('Image trop volumineuse (max 5MB).');
    }

    $tmp_path = $file['tmp_name'] ?? '';
    $mime = mime_content_type($tmp_path);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format image invalide. Utilisez JPG, PNG ou WEBP.');
    }

    $file_name = uniqid('img_', true) . '.' . $allowed[$mime];
    $relative_dir = 'uploads/' . trim($sub_dir, '/');
    $absolute_dir = dirname(__DIR__) . '/public/' . $relative_dir;
    if (!is_dir($absolute_dir) && !mkdir($absolute_dir, 0777, true) && !is_dir($absolute_dir)) {
        throw new RuntimeException('Impossible de creer le dossier de destination.');
    }

    $dest = $absolute_dir . '/' . $file_name;
    if (!move_uploaded_file($tmp_path, $dest)) {
        throw new RuntimeException('Impossible de sauvegarder l image.');
    }

    return $relative_dir . '/' . $file_name;
}
