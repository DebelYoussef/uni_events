<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_STUDENT);

$user = get_logged_in_user();
$flash_message = get_flash_message();
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
    <div class="card"><div class="card-body">
        <p><strong>Nom:</strong> <?php echo escape_output($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo escape_output($user['email']); ?></p>
        <p><strong>Role:</strong> Etudiant</p>
    </div></div>
</div>
</body>
</html>
