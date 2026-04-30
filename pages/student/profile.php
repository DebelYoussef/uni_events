<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_STUDENT);

$user = get_logged_in_user();
$flash_message = get_flash_message();

try {
    $stmt = $pdo->prepare('SELECT id, name, email, student_id, profile_photo FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $me = $stmt->fetch() ?: $user;

    $stmt = $pdo->prepare('
        SELECT c.id, c.cert_code, c.issued_at, e.title
        FROM certificates c
        INNER JOIN registrations r ON r.id = c.registration_id
        INNER JOIN events e ON e.id = r.event_id
        WHERE r.student_id = ?
        ORDER BY c.issued_at DESC
    ');
    $stmt->execute([$user['id']]);
    $certificates = $stmt->fetchAll();
} catch (PDOException $e) {
    $me = $user;
    $certificates = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<div class="container" style="max-width:900px;padding:2rem 1rem;">
    <div class="flex-between mb-2">
        <h1>Mon profil etudiant</h1>
        <button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button>
    </div>
    <?php if ($flash_message): ?><div class="alert alert-<?php echo escape_output($flash_message['type']); ?>"><?php echo escape_output($flash_message['message']); ?></div><?php endif; ?>
    <div class="card mb-2"><div class="card-body">
        <div class="flex gap-3 align-center" style="align-items:center;">
            <?php if (!empty($me['profile_photo'])): ?>
                <img src="<?php echo escape_output(asset_url($me['profile_photo'])); ?>" alt="Profile" style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:3px solid #e5e7eb;">
            <?php else: ?>
                <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#e5e7eb,#cbd5e1);border:3px solid #e5e7eb;"></div>
            <?php endif; ?>
            <div>
                <p><strong>Nom:</strong> <?php echo escape_output($me['name']); ?></p>
                <p><strong>Email:</strong> <?php echo escape_output($me['email']); ?></p>
                <p><strong>ID etudiant:</strong> <?php echo escape_output($me['student_id'] ?? '-'); ?></p>
                <p><strong>Certificats obtenus:</strong> <?php echo count($certificates); ?></p>
            </div>
        </div>
        <form action="../../actions/upload-profile-photo.php" method="POST" enctype="multipart/form-data" style="margin-top:1rem;">
            <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
            <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp" required>
            <button class="btn btn-primary" type="submit">Mettre a jour photo</button>
        </form>
    </div></div>

    <div class="card"><div class="card-body">
        <h3>Liste des certificats</h3>
        <?php if (empty($certificates)): ?>
            <p>Aucun certificat pour le moment.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Code</th><th>Evenement</th><th>Date emission</th></tr></thead>
                <tbody>
                <?php foreach ($certificates as $cert): ?>
                    <tr>
                        <td><?php echo escape_output($cert['cert_code']); ?></td>
                        <td><?php echo escape_output($cert['title']); ?></td>
                        <td><?php echo format_date($cert['issued_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div></div>
</div>
</body>
</html>
