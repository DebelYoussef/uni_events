<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

require_approved_organizer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/organizer/create-event.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/organizer/create-event.php', 'Token CSRF invalide.', ERROR);
}

$user = get_logged_in_user();
$title = trim($_POST['title'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$capacity = (int) ($_POST['capacity'] ?? 0);
$category_id = (int) ($_POST['category_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');

if ($title === '' || $event_date === '' || $capacity < 1 || $category_id < 1) {
    redirect_with_message('pages/organizer/create-event.php', 'Veuillez remplir tous les champs obligatoires.', ERROR);
}

if (strtotime($event_date) === false) {
    redirect_with_message('pages/organizer/create-event.php', 'Date invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);
    if (!$stmt->fetch()) {
        redirect_with_message('pages/organizer/create-event.php', 'Categorie invalide.', ERROR);
    }

    $stmt = $pdo->prepare('
        INSERT INTO events (organizer_id, category_id, title, description, location, event_date, capacity, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, "upcoming", NOW(), NOW())
    ');
    $stmt->execute([
        $user['id'],
        $category_id,
        $title,
        $description !== '' ? $description : null,
        $location !== '' ? $location : null,
        date('Y-m-d H:i:s', strtotime($event_date)),
        $capacity
    ]);

    redirect_with_message('pages/organizer/my-events.php', 'Evenement cree avec succes.');
} catch (PDOException $e) {
    error_log('Create event error: ' . $e->getMessage());
    redirect_with_message('pages/organizer/create-event.php', 'Erreur lors de la creation de l\'evenement.', ERROR);
}
