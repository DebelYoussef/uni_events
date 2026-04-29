<?php
/**
 * User Login Handler
 * Processes login form submission and creates user session
 * POST handler - no HTML output
 */

session_start();

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    set_message('Requête invalide. Veuillez réessayer.', ERROR);
    redirect('pages/auth/login.php');
}

// Get input
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Sanitize email
$email = sanitize_input($email);

// Validation
$errors = [];

if (empty($email)) {
    $errors['email'] = 'L\'e-mail est requis';
}

if (empty($password)) {
    $errors['password'] = 'Le mot de passe est requis';
}

// If validation errors, redirect back
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_form_data'] = ['email' => $email];
    
    set_message('Veuillez entrer votre e-mail et votre mot de passe.', ERROR);
    redirect('pages/auth/login.php');
}

// Authenticate user
$auth_result = authenticate_user($pdo, $email, $password);

if (!$auth_result['success']) {
    // Log failed login attempt (optional, for security)
    // log_activity($pdo, null, 'failed_login', $email);
    
    // If pending approval, show specific message
    if (isset($auth_result['pending_approval']) && $auth_result['pending_approval']) {
        set_message(
            'Votre compte organisateur est en attente d\'approbation par l\'administrateur. Veuillez attendre l\'e-mail de confirmation.',
            WARNING
        );
    } else {
        // Generic error message for failed login
        set_message('E-mail ou mot de passe invalide. Veuillez réessayer.', ERROR);
    }
    
    redirect('pages/auth/login.php');
}

// Login successful - create session
$user = $auth_result['user'];
create_session($user);

// Log successful login (optional)
// log_activity($pdo, $user['id'], 'login', $_SERVER['REMOTE_ADDR']);

// Clear any previous error messages
unset($_SESSION['login_errors']);
unset($_SESSION['login_form_data']);

// Determine redirect URL based on role
$redirect_url = 'index.php';

switch ($user['role']) {
    case ROLE_STUDENT:
        $redirect_url = 'pages/student/dashboard.php';
        break;
    
    case ROLE_ORGANIZER:
        if ($user['is_approved']) {
            $redirect_url = 'pages/organizer/dashboard.php';
        } else {
            // This shouldn't happen if authenticate_user works correctly
            $redirect_url = 'pages/auth/pending-approval.php';
        }
        break;
    
    case ROLE_ADMIN:
        $redirect_url = 'pages/admin/dashboard.php';
        break;
}

// Check if there was a redirect_after_login URL
if (isset($_SESSION['redirect_after_login'])) {
    $redirect_url = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']);
}

// Success message
set_message('Connexion réussie ! Bienvenue de nouveau, ' . escape_output($user['name']) . '.', SUCCESS);

// Redirect to appropriate dashboard
redirect($redirect_url);
