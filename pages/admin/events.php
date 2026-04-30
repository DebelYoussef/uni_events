<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_ADMIN);
$flash_message = get_flash_message();
try {
    $stmt = $pdo->query('
        SELECT e.id, e.title, e.event_date, e.status, e.capacity, u.name AS organizer_name
        FROM events e
        LEFT JOIN users u ON u.id = e.organizer_id
        ORDER BY e.created_at DESC
    ');
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin evenements</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body><div class="container" style="padding:2rem 1rem;">
<div class="flex-between mb-2"><h1>Gestion des evenements</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
<?php if ($flash_message): ?><div class="alert alert-<?php echo escape_output($flash_message['type']); ?>"><?php echo escape_output($flash_message['message']); ?></div><?php endif; ?>
<div class="card"><div class="card-body"><table><thead><tr><th>Titre</th><th>Organisateur</th><th>Date</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($events as $event): ?><tr><td><?php echo escape_output($event['title']); ?></td><td><?php echo escape_output($event['organizer_name'] ?? '-'); ?></td><td><?php echo format_date($event['event_date']); ?></td><td><?php echo escape_output($event['status']); ?></td>
<td><form method="POST" action="../../actions/admin-delete-event.php" onsubmit="return confirm('Supprimer cet evenement ?');">
<input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>"><input type="hidden" name="event_id" value="<?php echo (int)$event['id']; ?>">
<button class="btn btn-danger" style="padding:.3rem .6rem;">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></body></html>
