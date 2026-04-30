<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_ADMIN);
$flash_message = get_flash_message();

try {
    $stmt = $pdo->query('SELECT id, name, email, role, is_approved, created_at FROM users ORDER BY created_at DESC');
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Gestion utilisateurs</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body><div class="container" style="padding:2rem 1rem;">
<div class="flex-between mb-2"><h1>Gestion des utilisateurs</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
<?php if ($flash_message): ?><div class="alert alert-<?php echo escape_output($flash_message['type']); ?>"><?php echo escape_output($flash_message['message']); ?></div><?php endif; ?>
<div class="card"><div class="card-body"><table><thead><tr><th>Nom</th><th>Email</th><th>Role</th><th>Etat</th><th>Action</th></tr></thead><tbody>
<?php foreach ($users as $u): ?><tr>
<td><?php echo escape_output($u['name']); ?></td><td><?php echo escape_output($u['email']); ?></td><td><?php echo escape_output($u['role']); ?></td>
<td><?php echo ($u['role']==='organizer' && !(int)$u['is_approved']) ? 'Pending' : 'OK'; ?></td>
<td>
<?php if ($u['role']==='organizer' && !(int)$u['is_approved']): ?>
<a class="btn btn-success" style="padding:.3rem .6rem;" href="../../actions/admin-approve-organizer.php?id=<?php echo (int)$u['id']; ?>&action=approve">Approve</a>
<?php endif; ?>
</td></tr><?php endforeach; ?>
</tbody></table></div></div></div></body></html>
