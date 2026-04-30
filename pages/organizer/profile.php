<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_approved_organizer();
$user = get_logged_in_user();

try {
    $stmt = $pdo->prepare('SELECT id, name, email, profile_photo FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $me = $stmt->fetch() ?: $user;
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM events WHERE organizer_id = ?');
    $stmt->execute([$user['id']]);
    $events_count = (int) (($stmt->fetch()['c'] ?? 0));
} catch (PDOException $e) {
    $me = $user;
    $events_count = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil organisateur</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<div class="container" style="max-width:900px;padding:2rem 1rem;">
    <div class="flex-between mb-2">
        <h1>Mon profil organisateur</h1>
        <button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button>
    </div>
    <div class="card"><div class="card-body">
        <div class="flex gap-3 align-center" style="align-items:center;">
            <?php if (!empty($me['profile_photo'])): ?>
                <img src="<?php echo escape_output(asset_url($me['profile_photo'])); ?>" alt="Profile" style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:3px solid #e5e7eb;">
            <?php else: ?>
                <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#e5e7eb,#cbd5e1);border:3px solid #e5e7eb;"></div>
            <?php endif; ?>
            <div>
                <p><strong>Nom:</strong> <?php echo escape_output($me['name']); ?></p>
                <p><strong>Email:</strong> <?php echo escape_output($me['email']); ?></p>
                <p><strong>Statut:</strong> Approuve</p>
                <p><strong>Evenements crees:</strong> <?php echo $events_count; ?></p>
            </div>
        </div>
        <form action="../../actions/upload-profile-photo.php" method="POST" enctype="multipart/form-data" style="margin-top:1rem;">
            <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
            <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp" required>
            <button class="btn btn-primary" type="submit">Mettre a jour photo</button>
        </form>
    </div></div>
</div>
</body>
</html>
