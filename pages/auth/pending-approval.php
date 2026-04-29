<?php
/**
 * Pending Approval Page
 * Shown to organizers waiting for admin approval
 */

require_once '../../includes/auth.php';


// Require login and organizer role
require_login();
require_role(ROLE_ORGANIZER);

// Check if already approved
if (get_logged_in_user()['is_approved']) {
    redirect('pages/organizer/dashboard.php');
}

// Get flash message
$flash_message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approbation en attente - Système de gestion des événements universitaires</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: var(--light-bg);">
        <div class="card" style="max-width: 500px;">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">⏳</div>
                
                <h2 style="margin-bottom: 1rem; color: var(--warning-color);">
                    Approbation en attente
                </h2>

                <?php if ($flash_message): ?>
                    <div class="alert alert-<?php echo escape_output($flash_message['type']); ?> alert-dismissible fade show">
                        <?php echo escape_output($flash_message['message']); ?>
                    </div>
                <?php endif; ?>

                <p style="font-size: 1.125rem; color: var(--text-light); margin-bottom: 2rem;">
                    Votre compte organisateur est actuellement en attente d'approbation par notre équipe administrative.
                </p>

                <div style="background-color: var(--light-bg); padding: 1.5rem; border-radius: 0.375rem; margin-bottom: 2rem; text-align: left;">
                    <h4 style="margin-bottom: 1rem;">Et ensuite ?</h4>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative;">
                            <span style="position: absolute; left: 0;">✓</span>
                            Notre équipe examinera votre demande
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative;">
                            <span style="position: absolute; left: 0;">✓</span>
                            Vous recevrez un e-mail dès l'approbation
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative;">
                            <span style="position: absolute; left: 0;">✓</span>
                            Vous pourrez ensuite créer et gérer des événements
                        </li>
                        <li style="padding-left: 1.5rem; position: relative;">
                            <span style="position: absolute; left: 0;">✓</span>
                            L'approbation prend généralement 24 à 48 heures
                        </li>
                    </ul>
                </div>

                <p style="color: var(--text-light); font-size: 0.875rem; margin-bottom: 2rem;">
                    Des questions ? Contactez notre équipe de support à <strong>support@unievents.local</strong>
                </p>

                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="../../index.php" class="btn btn-outline">
                        Aller à l'accueil
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
