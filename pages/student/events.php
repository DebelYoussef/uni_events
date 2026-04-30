<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

require_role(ROLE_STUDENT);
$user = get_logged_in_user();
$flash_message = get_flash_message();

$search = trim($_GET['q'] ?? '');
$category_filter = (int) ($_GET['category_id'] ?? 0);
$date_filter = trim($_GET['date'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$allowed_statuses = ['upcoming', 'ongoing', 'past', 'cancelled'];

try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(e.title LIKE ? OR e.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category_filter > 0) {
    $where[] = 'e.category_id = ?';
    $params[] = $category_filter;
}
if ($date_filter !== '') {
    $where[] = 'DATE(e.event_date) = ?';
    $params[] = $date_filter;
}
if (in_array($status_filter, $allowed_statuses, true)) {
    $where[] = 'e.status = ?';
    $params[] = $status_filter;
}

$sql = '
    SELECT e.*, c.name AS category_name, u.name AS organizer_name,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = "registered") AS registered_count,
           (SELECT status FROM registrations r2 WHERE r2.event_id = e.id AND r2.student_id = ? LIMIT 1) AS my_registration_status
    FROM events e
    LEFT JOIN categories c ON c.id = e.category_id
    INNER JOIN users u ON u.id = e.organizer_id
';
$params = array_merge([$user['id']], $params);
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY e.event_date ASC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
    <title>Parcourir les evenements</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container" style="padding-top:2rem; padding-bottom:2rem;">
        <h1>Parcourir les evenements</h1>

        <?php if ($flash_message): ?>
            <div class="alert alert-<?php echo escape_output($flash_message['type']); ?>">
                <?php echo escape_output($flash_message['message']); ?>
            </div>
        <?php endif; ?>

        <div class="card mb-2">
            <div class="card-body">
                <form method="GET" class="flex gap-2" style="flex-wrap:wrap; align-items:end;">
                    <div style="min-width:220px; flex:1;">
                        <label for="q">Search</label>
                        <input type="text" id="q" name="q" value="<?php echo escape_output($search); ?>" placeholder="Titre ou description">
                    </div>
                    <div>
                        <label for="category_id">Categorie</label>
                        <select id="category_id" name="category_id">
                            <option value="0">Toutes</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['id']; ?>" <?php echo ($category_filter === (int) $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape_output($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" value="<?php echo escape_output($date_filter); ?>">
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Tous</option>
                            <?php foreach ($allowed_statuses as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($status_filter === $status) ? 'selected' : ''; ?>>
                                    <?php echo escape_output($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Filtrer</button>
                    <a href="events.php" class="btn btn-outline">Reset</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (empty($events)): ?>
                    <p>Aucun evenement trouve.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Categorie</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Places</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <?php $remaining = (int) $event['capacity'] - (int) $event['registered_count']; ?>
                                <tr>
                                    <td>
                                        <strong><?php echo escape_output($event['title']); ?></strong><br>
                                        <small class="text-muted"><?php echo escape_output($event['organizer_name']); ?></small>
                                    </td>
                                    <td><?php echo escape_output($event['category_name'] ?? '-'); ?></td>
                                    <td><?php echo format_date($event['event_date']); ?></td>
                                    <td><?php echo escape_output($event['status']); ?></td>
                                    <td><?php echo (int) $event['registered_count']; ?> / <?php echo (int) $event['capacity']; ?></td>
                                    <td>
                                        <?php if ($event['my_registration_status'] === 'registered'): ?>
                                            <span class="text-muted">Deja inscrit</span>
                                        <?php elseif ($event['status'] === 'cancelled' || $remaining <= 0): ?>
                                            <span class="text-muted">Complet / indisponible</span>
                                        <?php else: ?>
                                            <form action="../../actions/register-event.php" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">
                                                <button class="btn btn-success" type="submit" style="padding:.3rem .6rem;">Register</button>
                                            </form>
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
