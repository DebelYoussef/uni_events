<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

require_role(ROLE_STUDENT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/student/events.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/student/events.php', 'Token CSRF invalide.', ERROR);
}

$student_id = (int) get_current_user_id();
$event_id = (int) ($_POST['event_id'] ?? 0);
if ($event_id < 1) {
    redirect_with_message('pages/student/events.php', 'Evenement invalide.', ERROR);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, capacity, status FROM events WHERE id = ? FOR UPDATE');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        $pdo->rollBack();
        redirect_with_message('pages/student/events.php', 'Evenement introuvable.', ERROR);
    }

    if ($event['status'] === 'cancelled' || $event['status'] === 'past') {
        $pdo->rollBack();
        redirect_with_message('pages/student/events.php', 'Inscriptions fermees pour cet evenement.', ERROR);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM registrations WHERE student_id = ? AND event_id = ? LIMIT 1');
    $stmt->execute([$student_id, $event_id]);
    $existing = $stmt->fetch();

    if ($existing && $existing['status'] === 'registered') {
        $pdo->rollBack();
        redirect_with_message('pages/student/events.php', 'Vous etes deja inscrit.', WARNING);
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM registrations WHERE event_id = ? AND status = "registered"');
    $stmt->execute([$event_id]);
    $current = (int) ($stmt->fetch()['total'] ?? 0);
    if ($current >= (int) $event['capacity']) {
        $pdo->rollBack();
        redirect_with_message('pages/student/events.php', 'Capacite atteinte.', ERROR);
    }

    if ($existing) {
        $stmt = $pdo->prepare('UPDATE registrations SET status = "registered", registered_at = NOW() WHERE id = ?');
        $stmt->execute([$existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO registrations (student_id, event_id, registered_at, status) VALUES (?, ?, NOW(), "registered")');
        $stmt->execute([$student_id, $event_id]);
    }

    $pdo->commit();
    redirect_with_message('pages/student/my-registrations.php', 'Inscription confirmee.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Register event error: ' . $e->getMessage());
    redirect_with_message('pages/student/events.php', 'Erreur pendant l\'inscription.', ERROR);
}
