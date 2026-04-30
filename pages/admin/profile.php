<?php
require_once '../../includes/auth.php';
require_role(ROLE_ADMIN);
$user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Profil admin</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body>
<div class="container" style="max-width:900px;padding:2rem 1rem;">
    <div class="flex-between mb-2"><h1>Profil administrateur</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
    <div class="card"><div class="card-body">
        <p><strong>Nom:</strong> <?php echo escape_output($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo escape_output($user['email']); ?></p>
        <p><strong>Role:</strong> Admin</p>
    </div></div>
</div>
</body>
</html>
