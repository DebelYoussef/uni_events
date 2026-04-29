<?php
/**
 * User Logout Handler
 * Destroys user session and clears authentication data
 */

require_once '../../includes/auth.php';


// Initialize session if not already started
init_session();

// Get user info before destroying session (for logging if needed)
// $user_id = get_current_user_id();

// Destroy the session
destroy_session();

// Start a new session with proper configuration to set the logout message
init_session();
set_message('Vous avez été déconnecté avec succès.', SUCCESS);

// Redirect to login page
header('Location: ' . BASE_URL . 'pages/auth/login.php');
exit();
