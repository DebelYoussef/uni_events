<?php
/**
 * User Registration Handler
 * Processes registration form submission
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
    redirect('pages/auth/register.php');
}

// Get and validate input
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';
$student_id = $_POST['student_id'] ?? null;

// Sanitize inputs
$name = sanitize_input($name);
$email = sanitize_input($email);
$role = sanitize_input($role);
$student_id = !empty($student_id) ? sanitize_input($student_id) : null;

// Validation errors array
$errors = [];

// Validate name
if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
    $errors['name'] = 'Le nom doit comporter entre 2 et 100 caractères';
}

// Validate email
if (empty($email) || !is_valid_email($email)) {
    $errors['email'] = 'Veuillez entrer une adresse e-mail valide';
}

// Validate role
if (empty($role) || !in_array($role, [ROLE_STUDENT, ROLE_ORGANIZER], true)) {
    $errors['role'] = 'Type de compte invalide';
}

// Validate password
if (empty($password) || !is_valid_password($password)) {
    $feedback = get_password_feedback($password);
    $errors['password'] = !empty($feedback) ? implode(', ', $feedback) : 'Le mot de passe ne répond pas aux exigences de sécurité';
}

// Validate confirm password
if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
}

// Check for validation errors
if (!empty($errors)) {
    // Store errors in session and redirect with form data
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_form_data'] = [
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'student_id' => $student_id
    ];
    
    set_message('Veuillez corriger les erreurs ci-dessous avant de vous inscrire.', ERROR);
    redirect('pages/auth/register.php');
}

// Check if email already exists
if (email_exists($pdo, $email)) {
    $_SESSION['register_errors'] = ['email' => 'Cet e-mail est déjà enregistré'];
    $_SESSION['register_form_data'] = [
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'student_id' => $student_id
    ];
    
    set_message('Cet e-mail est déjà enregistré. Veuillez vous connecter ou utiliser un autre e-mail.', ERROR);
    redirect('pages/auth/register.php');
}

// Validate student_id for student role
if ($role === ROLE_STUDENT && empty($student_id)) {
    $_SESSION['register_errors'] = ['student_id' => 'L\'ID étudiant est requis pour les comptes étudiants'];
    $_SESSION['register_form_data'] = [
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'student_id' => $student_id
    ];
    
    set_message('L\'ID étudiant est requis pour les comptes étudiants.', ERROR);
    redirect('pages/auth/register.php');
}

// Create user account
$result = create_user($pdo, $name, $email, $password, $role, $student_id);

if ($result['success']) {
    // Clear any previous form data
    unset($_SESSION['register_form_data']);
    unset($_SESSION['register_errors']);
    
    // Show success message
    if ($role === ROLE_ORGANIZER) {
        set_message(
            'Compte créé avec succès ! Votre compte organisateur est en attente d\'approbation par l\'administrateur. Vous recevrez un e-mail une fois approuvé.',
            SUCCESS
        );
    } else {
        set_message(
            'Compte créé avec succès ! Vous pouvez maintenant vous connecter avec vos identifiants.',
            SUCCESS
        );
    }
    
    redirect('pages/auth/login.php');
} else {
    // Registration failed
    $_SESSION['register_errors'] = ['general' => $result['message']];
    $_SESSION['register_form_data'] = [
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'student_id' => $student_id
    ];
    
    set_message($result['message'], ERROR);
    redirect('pages/auth/register.php');
}
