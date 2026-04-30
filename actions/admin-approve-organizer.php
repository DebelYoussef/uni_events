<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_role(ROLE_ADMIN);

$id = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($id < 1 || !in_array($action, ['approve', 'reject'], true)) {
    redirect_with_message('pages/admin/approvals.php', 'Parametres invalides.', ERROR);
}

try {
    if ($action === 'approve') {
        $stmt = $pdo->prepare('UPDATE users SET is_approved = 1 WHERE id = ? AND role = "organizer"');
        $stmt->execute([$id]);
        redirect_with_message('pages/admin/approvals.php', 'Organisateur approuve.');
    }
    $stmt = $pdo->prepare('UPDATE users SET role = "student", is_approved = 1 WHERE id = ? AND role = "organizer"');
    $stmt->execute([$id]);
    redirect_with_message('pages/admin/approvals.php', 'Demande rejetee.');
} catch (PDOException $e) {
    error_log('Admin approve organizer error: ' . $e->getMessage());
    redirect_with_message('pages/admin/approvals.php', 'Erreur.', ERROR);
}
