<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_role(ROLE_STUDENT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_with_message('pages/student/certificates.php', 'Requete invalide.', ERROR);
}

$student_id = (int) get_current_user_id();
$registration_id = (int) ($_POST['registration_id'] ?? 0);
if ($registration_id < 1) {
    redirect_with_message('pages/student/certificates.php', 'Inscription invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('
        SELECT r.id
        FROM registrations r
        INNER JOIN attendance a ON a.registration_id = r.id
        WHERE r.id = ? AND r.student_id = ? AND r.status = "registered"
        LIMIT 1
    ');
    $stmt->execute([$registration_id, $student_id]);
    if (!$stmt->fetch()) {
        redirect_with_message('pages/student/certificates.php', 'Presence non validee pour cette inscription.', WARNING);
    }

    $stmt = $pdo->prepare('SELECT id FROM certificates WHERE registration_id = ?');
    $stmt->execute([$registration_id]);
    if ($stmt->fetch()) {
        redirect_with_message('pages/student/certificates.php', 'Certificat deja genere.', INFO);
    }

    $cert_code = 'CERT-' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $pdo->prepare('INSERT INTO certificates (registration_id, cert_code, issued_at) VALUES (?, ?, NOW())');
    $stmt->execute([$registration_id, $cert_code]);
    redirect_with_message('pages/student/certificates.php', 'Certificat genere avec succes.');
} catch (PDOException $e) {
    error_log('Generate certificate error: ' . $e->getMessage());
    redirect_with_message('pages/student/certificates.php', 'Erreur lors de la generation.', ERROR);
}
