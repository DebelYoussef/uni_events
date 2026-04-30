<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

require_approved_organizer();

$user = get_logged_in_user();
$flash_message = get_flash_message();

try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creer un evenement</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 900px; padding-top: 2rem; padding-bottom: 2rem;">
        <div class="flex-between mb-2">
            <h1>Creer un evenement</h1>
            <div style="display:flex; gap:.5rem;">
                <button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button>
                <a href="my-events.php" class="btn btn-outline">Mes evenements</a>
            </div>
        </div>

        <?php if ($flash_message): ?>
            <div class="alert alert-<?php echo escape_output($flash_message['type']); ?>">
                <?php echo escape_output($flash_message['message']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="../../actions/create-event.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">

                    <div class="form-group">
                        <label for="title">Titre *</label>
                        <input type="text" id="title" name="title" required maxlength="200">
                    </div>

                    <div class="form-group">
                        <label for="event_date">Date *</label>
                        <input type="datetime-local" id="event_date" name="event_date" required>
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacite *</label>
                        <input type="number" id="capacity" name="capacity" min="1" max="100000" required value="100">
                    </div>

                    <div class="form-group">
                        <label for="category_id">Categorie *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Selectionner une categorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['id']; ?>">
                                    <?php echo escape_output($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Lieu</label>
                        <input type="text" id="location" name="location" maxlength="200">
                    </div>

                    <div class="form-group">
                        <label for="event_image">Image evenement</label>
                        <input type="file" id="event_image" name="event_image" accept="image/png,image/jpeg,image/webp">
                        <div class="form-help">Format: JPG/PNG/WEBP, max 5MB</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Creer</button>
                    <a href="dashboard.php" class="btn btn-outline">Retour</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
