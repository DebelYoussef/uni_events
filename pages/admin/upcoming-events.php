<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_ADMIN);

try {
    $stmt = $pdo->query('
        SELECT e.*, u.name AS organizer_name
        FROM events e
        LEFT JOIN users u ON u.id = e.organizer_id
        WHERE e.status IN ("upcoming","ongoing")
        ORDER BY e.event_date ASC
    ');
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Evenements a venir</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body>
<div class="container" style="padding:2rem 1rem;">
    <div class="flex-between mb-2"><h1>Evenements a venir (admin)</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
    <div class="card"><div class="card-body">
        <table>
            <thead><tr><th>Titre</th><th>Organisateur</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo escape_output($event['title']); ?></td>
                    <td><?php echo escape_output($event['organizer_name'] ?? '-'); ?></td>
                    <td><?php echo format_date($event['event_date']); ?></td>
                    <td><?php echo escape_output($event['status']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div>
</body>
</html>
