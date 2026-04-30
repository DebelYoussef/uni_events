<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_STUDENT);

$student_id = (int) get_current_user_id();
try {
    $stmt = $pdo->prepare('
        SELECT e.title, e.event_date, e.status AS event_status, r.status AS registration_status, r.registered_at
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        WHERE r.student_id = ?
        ORDER BY r.registered_at DESC
    ');
    $stmt->execute([$student_id]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $history = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon historique</title><link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<div class="container" style="padding:2rem 1rem;">
    <div class="flex-between mb-2"><h1>Historique des inscriptions</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
    <div class="card"><div class="card-body">
        <?php if (empty($history)): ?><p>Aucun historique.</p>
        <?php else: ?>
            <table><thead><tr><th>Evenement</th><th>Date evenement</th><th>Statut evenement</th><th>Statut inscription</th><th>Inscrit le</th></tr></thead><tbody>
            <?php foreach ($history as $item): ?>
                <tr>
                    <td><?php echo escape_output($item['title']); ?></td>
                    <td><?php echo format_date($item['event_date']); ?></td>
                    <td><?php echo escape_output($item['event_status']); ?></td>
                    <td><?php echo escape_output($item['registration_status']); ?></td>
                    <td><?php echo format_date($item['registered_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php endif; ?>
    </div></div>
</div>
</body>
</html>
