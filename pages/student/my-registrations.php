<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

require_role(ROLE_STUDENT);
$student_id = (int) get_current_user_id();
$flash_message = get_flash_message();

try {
    $stmt = $pdo->prepare('
        SELECT r.status AS registration_status, r.registered_at, e.id AS event_id, e.title, e.event_date, e.location, e.status AS event_status
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        WHERE r.student_id = ?
        ORDER BY r.registered_at DESC
    ');
    $stmt->execute([$student_id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes inscriptions</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container" style="padding-top:2rem; padding-bottom:2rem;">
        <div class="flex-between mb-2">
            <h1>Mes inscriptions</h1>
            <a href="events.php" class="btn btn-primary">Voir les evenements</a>
        </div>

        <?php if ($flash_message): ?>
            <div class="alert alert-<?php echo escape_output($flash_message['type']); ?>">
                <?php echo escape_output($flash_message['message']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <p>Aucune inscription.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Evenement</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Inscription</th>
                                <th>Event status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo escape_output($item['title']); ?></td>
                                    <td><?php echo format_date($item['event_date']); ?></td>
                                    <td><?php echo escape_output($item['location'] ?? '-'); ?></td>
                                    <td><?php echo escape_output($item['registration_status']); ?></td>
                                    <td><?php echo escape_output($item['event_status']); ?></td>
                                    <td>
                                        <?php if ($item['registration_status'] === 'registered'): ?>
                                            <form action="../../actions/cancel-registration.php" method="POST" onsubmit="return confirm('Annuler cette inscription ?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $item['event_id']; ?>">
                                                <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem;">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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
