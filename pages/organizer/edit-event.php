<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

require_approved_organizer();
$user = get_logged_in_user();
$flash_message = get_flash_message();
$event_id = (int) ($_GET['id'] ?? 0);

if ($event_id < 1) {
    redirect_with_message('pages/organizer/my-events.php', 'Evenement invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? AND organizer_id = ?');
    $stmt->execute([$event_id, $user['id']]);
    $event = $stmt->fetch();

    if (!$event) {
        redirect_with_message('pages/organizer/my-events.php', 'Evenement introuvable ou non autorise.', ERROR);
    }

    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    redirect_with_message('pages/organizer/my-events.php', 'Erreur de chargement.', ERROR);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier evenement</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 900px; padding-top: 2rem; padding-bottom: 2rem;">
        <h1>Modifier evenement</h1>

        <?php if ($flash_message): ?>
            <div class="alert alert-<?php echo escape_output($flash_message['type']); ?>">
                <?php echo escape_output($flash_message['message']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="../../actions/update-event.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                    <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">

                    <div class="form-group">
                        <label for="title">Titre *</label>
                        <input type="text" id="title" name="title" required maxlength="200" value="<?php echo escape_output($event['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="event_date">Date *</label>
                        <input type="datetime-local" id="event_date" name="event_date" required value="<?php echo escape_output((new DateTime($event['event_date']))->format('Y-m-d\TH:i')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacite *</label>
                        <input type="number" id="capacity" name="capacity" min="1" required value="<?php echo (int) $event['capacity']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category_id">Categorie *</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['id']; ?>" <?php echo ((int) $event['category_id'] === (int) $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape_output($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Statut *</label>
                        <select id="status" name="status" required>
                            <?php foreach (['upcoming', 'ongoing', 'past', 'cancelled'] as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($event['status'] === $status) ? 'selected' : ''; ?>>
                                    <?php echo escape_output($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo escape_output($event['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Lieu</label>
                        <input type="text" id="location" name="location" maxlength="200" value="<?php echo escape_output($event['location'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="my-events.php" class="btn btn-outline">Retour</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
