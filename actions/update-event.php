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
$title = trim($_POST['title'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$capacity = (int) ($_POST['capacity'] ?? 0);
$category_id = (int) ($_POST['category_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');

if ($event_id < 1 || $title === '' || $event_date === '' || $capacity < 1 || $category_id < 1) {
    redirect_with_message('pages/organizer/my-events.php', 'Donnees invalides.', ERROR);
}

$allowed_statuses = ['upcoming', 'ongoing', 'past', 'cancelled'];
if (!in_array($status, $allowed_statuses, true)) {
    redirect_with_message('pages/organizer/my-events.php', 'Statut invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ? AND organizer_id = ?');
    $stmt->execute([$event_id, $user['id']]);
    if (!$stmt->fetch()) {
        redirect_with_message('pages/organizer/my-events.php', 'Evenement introuvable ou non autorise.', ERROR);
    }

    $stmt = $pdo->prepare('
        UPDATE events
        SET title = ?, event_date = ?, capacity = ?, category_id = ?, status = ?, description = ?, location = ?, updated_at = NOW()
        WHERE id = ? AND organizer_id = ?
    ');
    $stmt->execute([
        $title,
        date('Y-m-d H:i:s', strtotime($event_date)),
        $capacity,
        $category_id,
        $status,
        $description !== '' ? $description : null,
        $location !== '' ? $location : null,
        $event_id,
        $user['id']
    ]);

    redirect_with_message('pages/organizer/my-events.php', 'Evenement mis a jour.');
} catch (PDOException $e) {
    error_log('Update event error: ' . $e->getMessage());
    redirect_with_message('pages/organizer/my-events.php', 'Erreur lors de la mise a jour.', ERROR);
}
