<?php
/**
 * Navigation Header
 * Role-aware navigation bar included in all pages
 */

// Make sure session is initialized
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/auth.php';
    init_session();
}

$user = is_logged_in() ? get_logged_in_user() : null;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape_output($page_title) : 'Système de gestion des événements universitaires'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <nav>
        <div class="container">
            <div class="navbar">
                <div class="navbar-brand">
                    <a href="<?php echo BASE_URL; ?>index.php">UniEvents</a>
                </div>

                <ul class="navbar-nav">
                    <?php if (!$user): ?>
                        <!-- Public Navigation -->
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>pages/auth/login.php" <?php echo $current_page === 'login.php' ? 'class="active"' : ''; ?>>
                                Se connecter
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>pages/auth/register.php" <?php echo $current_page === 'register.php' ? 'class="active"' : ''; ?>>
                                S'inscrire
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Student Navigation -->
                        <?php if ($user['role'] === ROLE_STUDENT): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/student/dashboard.php" <?php echo $current_page === 'dashboard.php' ? 'class="active"' : ''; ?>>
                                    Tableau de bord
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/student/events.php" <?php echo $current_page === 'events.php' ? 'class="active"' : ''; ?>>
                                    Événements
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/student/upcoming-events.php" <?php echo $current_page === 'upcoming-events.php' ? 'class="active"' : ''; ?>>
                                    A venir
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/student/my-registrations.php" <?php echo $current_page === 'my-registrations.php' ? 'class="active"' : ''; ?>>
                                    Mes inscriptions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/student/history.php" <?php echo $current_page === 'history.php' ? 'class="active"' : ''; ?>>
                                    Mon historique
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/student/certificates.php" <?php echo $current_page === 'certificates.php' ? 'class="active"' : ''; ?>>
                                    Certificats
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Organizer Navigation -->
                        <?php if ($user['role'] === ROLE_ORGANIZER && $user['is_approved']): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/organizer/dashboard.php" <?php echo $current_page === 'dashboard.php' ? 'class="active"' : ''; ?>>
                                    Tableau de bord
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/organizer/create-event.php" <?php echo $current_page === 'create-event.php' ? 'class="active"' : ''; ?>>
                                    Créer un événement
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/organizer/my-events.php" <?php echo $current_page === 'my-events.php' ? 'class="active"' : ''; ?>>
                                    Mes événements
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/organizer/upcoming-events.php" <?php echo $current_page === 'upcoming-events.php' ? 'class="active"' : ''; ?>>
                                    A venir
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/organizer/participants.php" <?php echo $current_page === 'participants.php' ? 'class="active"' : ''; ?>>
                                    Participants
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Admin Navigation -->
                        <?php if ($user['role'] === ROLE_ADMIN): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php" <?php echo $current_page === 'dashboard.php' ? 'class="active"' : ''; ?>>
                                    Tableau de bord
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/admin/users.php" <?php echo $current_page === 'users.php' ? 'class="active"' : ''; ?>>
                                    Utilisateurs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/admin/approvals.php" <?php echo $current_page === 'approvals.php' ? 'class="active"' : ''; ?>>
                                    Approuver les organisateurs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/admin/events.php" <?php echo $current_page === 'events.php' ? 'class="active"' : ''; ?>>
                                    Événements
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/admin/upcoming-events.php" <?php echo $current_page === 'upcoming-events.php' ? 'class="active"' : ''; ?>>
                                    A venir
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>pages/admin/statistics.php" <?php echo $current_page === 'statistics.php' ? 'class="active"' : ''; ?>>
                                    Statistiques
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- User Profile Dropdown -->
                        <li class="nav-item">
                            <a href="#user-menu" style="font-weight: 500;">
                                <?php echo escape_output(substr($user['name'], 0, 20)); ?> <span style="font-size: 0.75rem;">▼</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo BASE_URL; ?>pages/<?php echo escape_output($user['role']); ?>/profile.php">
                                        Rôle : <?php echo $user['role'] === ROLE_STUDENT ? 'Étudiant' : ($user['role'] === ROLE_ORGANIZER ? 'Organisateur' : 'Administrateur'); ?>
                                    </a>
                                </li>
                                <?php if ($user['role'] === ROLE_ORGANIZER && !$user['is_approved']): ?>
                                    <li>
                                        <a href="<?php echo BASE_URL; ?>pages/auth/pending-approval.php">
                                            Approbation en attente
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>pages/auth/logout.php" style="color: var(--danger-color);">
                                        Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?php
        // Display flash messages at the top of the page
        if (isset($show_flash) && $show_flash) {
            echo display_flash_message();
        }
        ?>
