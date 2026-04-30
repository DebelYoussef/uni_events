<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

require_approved_organizer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/organizer/my-events.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/organizer/my-events.php', 'Token CSRF invalide.', ERROR);
}

$user = get_logged_in_user();
$event_id = (int) ($_POST['event_id'] ?? 0);

if ($event_id < 1) {
    redirect_with_message('pages/organizer/my-events.php', 'Evenement invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ? AND organizer_id = ?');
    $stmt->execute([$event_id, $user['id']]);

    if ($stmt->rowCount() === 0) {
        redirect_with_message('pages/organizer/my-events.php', 'Evenement introuvable ou non autorise.', ERROR);
    }

    redirect_with_message('pages/organizer/my-events.php', 'Evenement supprime.');
} catch (PDOException $e) {
    error_log('Delete event error: ' . $e->getMessage());
    redirect_with_message('pages/organizer/my-events.php', 'Erreur lors de la suppression.', ERROR);
}
