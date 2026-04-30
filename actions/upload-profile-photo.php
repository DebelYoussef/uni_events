<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('index.php', 'Requete invalide.', ERROR);
}

$user_id = (int) get_current_user_id();
$role = get_current_role();
$redirect = 'pages/' . $role . '/profile.php';

try {
    $new_photo = upload_image('profile_photo', 'profiles');
    if (!$new_photo) {
        redirect_with_message($redirect, 'Aucune image selectionnee.', WARNING);
    }

    $stmt = $pdo->prepare('UPDATE users SET profile_photo = ? WHERE id = ?');
    $stmt->execute([$new_photo, $user_id]);
    $_SESSION['profile_photo'] = $new_photo;

    redirect_with_message($redirect, 'Photo de profil mise a jour.');
} catch (Throwable $e) {
    error_log('Upload profile photo error: ' . $e->getMessage());
    redirect_with_message($redirect, 'Erreur lors de l upload de la photo.', ERROR);
}
