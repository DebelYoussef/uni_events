<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_approved_organizer();
$organizer_id = (int) get_current_user_id();

try {
    $stmt = $pdo->prepare('
        SELECT e.*, c.name AS category_name
        FROM events e
        LEFT JOIN categories c ON c.id = e.category_id
        WHERE e.organizer_id = ? AND e.status IN ("upcoming","ongoing")
        ORDER BY e.event_date ASC
    ');
    $stmt->execute([$organizer_id]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mes evenements a venir</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body>
<div class="container" style="padding:2rem 1rem;">
    <div class="flex-between mb-2"><h1>Mes evenements a venir</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1rem;">
    <?php foreach ($events as $event): ?>
        <div class="card"><div class="card-body">
            <?php if (!empty($event['image_path'])): ?><img src="<?php echo escape_output(asset_url($event['image_path'])); ?>" alt="event" style="width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:.7rem;"><?php endif; ?>
            <h4><?php echo escape_output($event['title']); ?></h4>
            <p><?php echo escape_output($event['category_name'] ?? '-'); ?> - <?php echo format_date($event['event_date']); ?></p>
        </div></div>
    <?php endforeach; ?>
    </div>
</div>
</body>
</html>
