<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_ADMIN);
$flash_message = get_flash_message();
try {
    $stmt = $pdo->query('SELECT id, name, email, created_at FROM users WHERE role="organizer" AND is_approved=0 ORDER BY created_at DESC');
    $pending = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending = [];
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Approbations</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body><div class="container" style="padding:2rem 1rem;">
<div class="flex-between mb-2"><h1>Approbation organisateurs</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
<?php if ($flash_message): ?><div class="alert alert-<?php echo escape_output($flash_message['type']); ?>"><?php echo escape_output($flash_message['message']); ?></div><?php endif; ?>
<div class="card"><div class="card-body">
<?php if(empty($pending)): ?><p>Aucune demande en attente.</p>
<?php else: ?><table><thead><tr><th>Nom</th><th>Email</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php foreach($pending as $org): ?><tr><td><?php echo escape_output($org['name']); ?></td><td><?php echo escape_output($org['email']); ?></td><td><?php echo format_date($org['created_at']); ?></td>
<td><a class="btn btn-success" style="padding:.3rem .6rem;" href="../../actions/admin-approve-organizer.php?id=<?php echo (int)$org['id']; ?>&action=approve">Approve</a>
<a class="btn btn-danger" style="padding:.3rem .6rem;" href="../../actions/admin-approve-organizer.php?id=<?php echo (int)$org['id']; ?>&action=reject">Reject</a></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>
</div></div></div></body></html>
