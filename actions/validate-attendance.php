<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_approved_organizer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/organizer/participants.php', 'Requete invalide.', ERROR);
}

$organizer_id = (int) get_current_user_id();
$registration_id = (int) ($_POST['registration_id'] ?? 0);
$is_present = isset($_POST['is_present']) ? 1 : 0;
if ($registration_id < 1) {
    redirect_with_message('pages/organizer/participants.php', 'Inscription invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('
        SELECT r.id
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        WHERE r.id = ? AND e.organizer_id = ?
    ');
    $stmt->execute([$registration_id, $organizer_id]);
    if (!$stmt->fetch()) {
        redirect_with_message('pages/organizer/participants.php', 'Action non autorisee.', ERROR);
    }

    if ($is_present) {
        $stmt = $pdo->prepare('INSERT INTO attendance (registration_id, validated_by, validated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE validated_by = VALUES(validated_by), validated_at = NOW()');
        $stmt->execute([$registration_id, $organizer_id]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM attendance WHERE registration_id = ? AND validated_by = ?');
        $stmt->execute([$registration_id, $organizer_id]);
    }
    redirect_with_message('pages/organizer/participants.php', 'Presence mise a jour.');
} catch (PDOException $e) {
    error_log('Validate attendance error: ' . $e->getMessage());
    redirect_with_message('pages/organizer/participants.php', 'Erreur de validation.', ERROR);
}
