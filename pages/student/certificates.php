<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_STUDENT);
$student_id = (int) get_current_user_id();
$flash_message = get_flash_message();

try {
    $stmt = $pdo->prepare('
        SELECT r.id AS registration_id, e.title, e.event_date, c.id AS certificate_id, c.cert_code, c.issued_at
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        LEFT JOIN certificates c ON c.registration_id = r.id
        WHERE r.student_id = ? AND r.status = "registered"
        ORDER BY e.event_date DESC
    ');
    $stmt->execute([$student_id]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    $rows = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mes certificats</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body>
<div class="container" style="padding:2rem 1rem;">
    <div class="flex-between mb-2"><h1>Mes certificats</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
    <?php if ($flash_message): ?><div class="alert alert-<?php echo escape_output($flash_message['type']); ?>"><?php echo escape_output($flash_message['message']); ?></div><?php endif; ?>
    <div class="card"><div class="card-body">
        <table>
            <thead><tr><th>Evenement</th><th>Date</th><th>Code</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo escape_output($row['title']); ?></td>
                    <td><?php echo format_date($row['event_date']); ?></td>
                    <td><?php echo escape_output($row['cert_code'] ?? '-'); ?></td>
                    <td>
                        <?php if ($row['certificate_id']): ?>
                            <a class="btn btn-primary" style="padding:.3rem .6rem;" href="../../actions/download-certificate.php?id=<?php echo (int) $row['certificate_id']; ?>">Download PDF</a>
                        <?php else: ?>
                            <form method="POST" action="../../actions/generate-certificate.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                <input type="hidden" name="registration_id" value="<?php echo (int) $row['registration_id']; ?>">
                                <button class="btn btn-outline" style="padding:.3rem .6rem;">Generer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div>
</body>
</html>
