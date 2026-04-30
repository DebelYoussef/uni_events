<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

require_role(ROLE_STUDENT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/student/my-registrations.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/student/my-registrations.php', 'Token CSRF invalide.', ERROR);
}

$student_id = (int) get_current_user_id();
$event_id = (int) ($_POST['event_id'] ?? 0);

if ($event_id < 1) {
    redirect_with_message('pages/student/my-registrations.php', 'Evenement invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('
        UPDATE registrations
        SET status = "cancelled"
        WHERE student_id = ? AND event_id = ? AND status = "registered"
    ');
    $stmt->execute([$student_id, $event_id]);

    if ($stmt->rowCount() === 0) {
        redirect_with_message('pages/student/my-registrations.php', 'Inscription non trouvee.', WARNING);
    }

    redirect_with_message('pages/student/my-registrations.php', 'Inscription annulee.');
} catch (PDOException $e) {
    error_log('Cancel registration error: ' . $e->getMessage());
    redirect_with_message('pages/student/my-registrations.php', 'Erreur lors de l\'annulation.', ERROR);
}
