<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_role(ROLE_ADMIN);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/admin/events.php', 'Requete invalide.', ERROR);
}
$event_id = (int) ($_POST['event_id'] ?? 0);
if ($event_id < 1) {
    redirect_with_message('pages/admin/events.php', 'Evenement invalide.', ERROR);
}
try {
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([$event_id]);
    redirect_with_message('pages/admin/events.php', 'Evenement supprime.');
} catch (PDOException $e) {
    error_log('Admin delete event error: ' . $e->getMessage());
    redirect_with_message('pages/admin/events.php', 'Erreur de suppression.', ERROR);
}
