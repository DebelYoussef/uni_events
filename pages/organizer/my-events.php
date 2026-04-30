<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

require_approved_organizer();
$user = get_logged_in_user();
$flash_message = get_flash_message();

try {
    $stmt = $pdo->prepare('
        SELECT e.*, c.name AS category_name,
               (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = "registered") AS registered_count
        FROM events e
        LEFT JOIN categories c ON c.id = e.category_id
        WHERE e.organizer_id = ?
        ORDER BY e.event_date DESC
    ');
    $stmt->execute([$user['id']]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes evenements</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="flex-between mb-2">
            <h1>Mes evenements</h1>
            <div style="display:flex; gap:.5rem;">
                <button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button>
                <a href="create-event.php" class="btn btn-primary">+ Creer</a>
            </div>
        </div>

        <?php if ($flash_message): ?>
            <div class="alert alert-<?php echo escape_output($flash_message['type']); ?>">
                <?php echo escape_output($flash_message['message']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($events)): ?>
                    <p>Aucun evenement pour le moment.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Categorie</th>
                                <th>Date</th>
                                <th>Inscrits</th>
                                <th>Capacite</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo escape_output($event['title']); ?></td>
                                    <td><?php echo escape_output($event['category_name'] ?? '-'); ?></td>
                                    <td><?php echo format_date($event['event_date']); ?></td>
                                    <td><?php echo (int) $event['registered_count']; ?></td>
                                    <td><?php echo (int) $event['capacity']; ?></td>
                                    <td><?php echo escape_output($event['status']); ?></td>
                                    <td>
                                        <a class="btn btn-outline" style="padding: .3rem .6rem;" href="edit-event.php?id=<?php echo (int) $event['id']; ?>">Edit</a>
                                        <form action="../../actions/delete-event.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet evenement ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: .3rem .6rem;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
