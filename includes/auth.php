<?php
/**
 * Authentication Guards & Session Management
 * Middleware functions for protecting pages and checking user roles
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

/**
 * Start and configure session
 */
function init_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
        
        // Configure session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                // Session expired
                session_destroy();
                session_start();
                $_SESSION['message'] = 'Session expirée. Veuillez vous reconnecter.';
                $_SESSION['message_type'] = WARNING;
                header('Location: ' . BASE_URL . 'pages/auth/login.php');
                exit();
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Require user to be logged in
 * Redirects to login if not authenticated
 */
function require_login()
{
    init_session();
    
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('pages/auth/login.php');
    }
}

/**
 * Require specific role(s)
 * Pass single role or array of roles
 */
function require_role($roles)
{
    require_login();
    
    $roles = (array) $roles;
    $current_role = get_current_role();
    
    if (!in_array($current_role, $roles, true)) {
        http_response_code(403);
        die('Accès refusé. Vous n\'avez pas la permission d\'accéder à cette page.');
    }
}

/**
 * Check if organizer is approved
 */
function require_approved_organizer()
{
    require_role(ROLE_ORGANIZER);
    
    if (!($_SESSION['is_approved'] ?? false)) {
        http_response_code(403);
        die('Votre compte organisateur est en attente d\'approbation. Veuillez attendre la confirmation de l\'administrateur.');
    }
}

/**
 * Create user session after login
 */
function create_session($user_data)
{
    init_session();
    
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['name'] = $user_data['name'];
    $_SESSION['role'] = $user_data['role'];
    $_SESSION['is_approved'] = $user_data['is_approved'];
    $_SESSION['login_time'] = time();
    
    // Log login activity (optional)
    // log_activity($user_data['id'], 'login');
}

/**
 * Destroy user session
 */
function destroy_session()
{
    if (session_status() !== PHP_SESSION_NONE) {
        session_destroy();
    }
}

/**
 * Check if user already logged in
 * Redirect to appropriate dashboard
 */
function redirect_if_logged_in()
{
    init_session();
    
    if (is_logged_in()) {
        $role = get_current_role();
        
        switch ($role) {
            case ROLE_STUDENT:
                redirect('pages/student/dashboard.php');
                break;
            case ROLE_ORGANIZER:
                if ($_SESSION['is_approved']) {
                    redirect('pages/organizer/dashboard.php');
                } else {
                    redirect('pages/auth/pending-approval.php');
                }
                break;
            case ROLE_ADMIN:
                redirect('pages/admin/dashboard.php');
                break;
        }
    }
}

/**
 * Get user by email
 */
function get_user_by_email($pdo, $email)
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([sanitize_input($email)]);
    return $stmt->fetch();
}

/**
 * Check if email already exists
 */
function email_exists($pdo, $email)
{
    return get_user_by_email($pdo, $email) !== false;
}

/**
 * Create new user account
 */
function create_user($pdo, $name, $email, $password, $role = ROLE_STUDENT, $student_id = null)
{
    $email = sanitize_input($email);
    $name = sanitize_input($name);
    
    // Check if email already exists
    if (email_exists($pdo, $email)) {
        return ['success' => false, 'message' => 'Cet e-mail est déjà enregistré.'];
    }
    
    // Validate password
    if (!is_valid_password($password)) {
        return ['success' => false, 'message' => 'Le mot de passe ne répond pas aux exigences de sécurité.', 'feedback' => get_password_feedback($password)];
    }
    
    // Hash password
    $password_hash = hash_password($password);
    
    // Determine if approved based on role
    $is_approved = ($role !== ROLE_ORGANIZER) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO users (name, email, password, role, is_approved, student_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');
        
        $stmt->execute([
            $name,
            $email,
            $password_hash,
            $role,
            $is_approved,
            $student_id
        ]);
        
        return ['success' => true, 'message' => 'Compte créé avec succès.', 'user_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("User creation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la création du compte. Veuillez réessayer.'];
    }
}

/**
 * Authenticate user login
 */
function authenticate_user($pdo, $email, $password)
{
    $email = sanitize_input($email);
    
    // Get user by email
    $user = get_user_by_email($pdo, $email);
    
    if (!$user) {
        // Generic error - don't reveal email doesn't exist
        return ['success' => false, 'message' => 'E-mail ou mot de passe invalide.'];
    }
    
    // Verify password
    if (!verify_password($password, $user['password'])) {
        return ['success' => false, 'message' => 'E-mail ou mot de passe invalide.'];
    }
    
    // Check if organizer is approved
    if ($user['role'] === ROLE_ORGANIZER && !$user['is_approved']) {
        return ['success' => false, 'message' => 'Votre compte organisateur est en attente d\'approbation.', 'pending_approval' => true];
    }
    
    return ['success' => true, 'user' => $user];
}

/**
 * Log activity (for audit trail)
 */
function log_activity($pdo, $user_id, $action, $details = null)
{
    // Implementation depends on your activity_log table structure
    // This is optional but recommended for security and troubleshooting
}
