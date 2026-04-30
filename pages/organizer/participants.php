<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_approved_organizer();
$organizer_id = (int) get_current_user_id();
$flash_message = get_flash_message();

try {
    $stmt = $pdo->prepare('
        SELECT r.id AS registration_id, r.registered_at, r.status AS registration_status,
               u.name AS student_name, u.email AS student_email,
               e.id AS event_id, e.title AS event_title, e.event_date,
               a.id AS attendance_id, a.validated_at
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        INNER JOIN users u ON u.id = r.student_id
        LEFT JOIN attendance a ON a.registration_id = r.id
        WHERE e.organizer_id = ?
        ORDER BY e.event_date DESC, r.registered_at DESC
    ');
    $stmt->execute([$organizer_id]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    $rows = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Participants</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body>
<div class="container" style="padding:2rem 1rem;">
    <div class="flex-between mb-2"><h1>Inscriptions recentes et participants</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
    <?php if ($flash_message): ?><div class="alert alert-<?php echo escape_output($flash_message['type']); ?>"><?php echo escape_output($flash_message['message']); ?></div><?php endif; ?>
    <div class="card"><div class="card-body">
        <?php if (empty($rows)): ?><p>Aucun participant.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Evenement</th><th>Etudiant</th><th>Inscription</th><th>Presence</th><th>Valider</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo escape_output($row['event_title']); ?><br><small><?php echo format_date($row['event_date']); ?></small></td>
                        <td><?php echo escape_output($row['student_name']); ?><br><small><?php echo escape_output($row['student_email']); ?></small></td>
                        <td><?php echo escape_output($row['registration_status']); ?></td>
                        <td><?php echo $row['attendance_id'] ? 'Validee' : 'Non'; ?></td>
                        <td>
                            <form action="../../actions/validate-attendance.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                <input type="hidden" name="registration_id" value="<?php echo (int) $row['registration_id']; ?>">
                                <input type="checkbox" name="is_present" value="1" <?php echo $row['attendance_id'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div></div>
</div>
</body>
</html>
