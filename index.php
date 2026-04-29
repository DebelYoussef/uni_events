<?php
/**
 * Home / Landing Page
 * Redirect authenticated users to dashboard
 * Show landing page for unauthenticated users
 */

require_once 'includes/auth.php';


init_session();

// If user is logged in, redirect to their dashboard
if (is_logged_in()) {
    $role = get_current_role();
    
    switch ($role) {
        case ROLE_STUDENT:
            redirect('pages/student/dashboard.php');
            break;
        case ROLE_ORGANIZER:
            if (get_logged_in_user()['is_approved']) {
                redirect('pages/organizer/dashboard.php');
            } else {
                redirect('pages/auth/pending-approval.php');
            }
            break;
        case ROLE_ADMIN:
            redirect('pages/admin/dashboard.php');
            break;
    }
}

// Set page variables for header
$page_title = 'Accueil - Système de gestion des événements universitaires';
$show_flash = true;

require_once 'includes/header.php';
?>

<div class="container" style="padding-top: 3rem; padding-bottom: 3rem;">
    <!-- Hero Section -->
    <section style="text-align: center; margin-bottom: 4rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; color: var(--primary-color);">
            Système de gestion des événements universitaires
        </h1>
        <p style="font-size: 1.25rem; color: var(--text-light); margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            Découvrez, inscrivez-vous et gérez les événements universitaires. Connectez-vous avec la communauté du campus et restez informé de tous les événements.
        </p>
        
        <?php if (!is_logged_in()): ?>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="pages/auth/register.php" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1.125rem;">
                    Commencer
                </a>
                <a href="pages/auth/login.php" class="btn btn-outline" style="padding: 0.75rem 2rem; font-size: 1.125rem;">
                    Se connecter
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Features Section -->
    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
        <!-- Feature 1 -->
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <h3>Découverte d'événements</h3>
                <p>
                    Parcourez et découvrez tous les événements universitaires à venir. Filtrez par catégorie, date et lieu pour trouver des événements qui vous intéressent.
                </p>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h3>Inscription facile</h3>
                <p>
                    Inscrivez-vous aux événements en un clic. Gérez vos inscriptions et suivez votre participation tout au long de l'année.
                </p>
            </div>
        </div>

        <!-- Feature 3 -->
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
                <h3>Certificats numériques</h3>
                <p>
                    Obtenez et téléchargez des certificats pour les événements auxquels vous avez assisté. Constituez votre portfolio de participation sur le campus.
                </p>
            </div>
        </div>

        <!-- Feature 4 -->
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                <h3>Gestion d'événements</h3>
                <p>
                    Les organisateurs peuvent créer et gérer des événements facilement. Suivez les inscriptions et validez la présence en temps réel.
                </p>
            </div>
        </div>

        <!-- Feature 5 -->
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                <h3>Statistiques</h3>
                <p>
                    Tableau de bord administrateur avec des statistiques détaillées sur les événements, les inscriptions et l'utilisation du système pour une meilleure gestion.
                </p>
            </div>
        </div>

        <!-- Feature 6 -->
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
                <h3>Sécurisé et fiable</h3>
                <p>
                    Construit selon les meilleures pratiques de sécurité. Vos données sont protégées par un chiffrement et une validation conformes aux normes de l'industrie.
                </p>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section style="background-color: var(--light-bg); padding: 3rem; border-radius: 0.5rem; margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 2rem;">Comment ça marche</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <!-- Student Role -->
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎓</div>
                <h4>Pour les étudiants</h4>
                <p style="color: var(--text-light);">
                    Créez un compte, explorez les événements, inscrivez-vous à ceux qui vous intéressent et obtenez des certificats après votre participation.
                </p>
            </div>

            <!-- Organizer Role -->
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
                <h4>Pour les organisateurs</h4>
                <p style="color: var(--text-light);">
                    Planifiez et créez des événements, gérez les inscriptions des participants, validez la participation et générez des rapports.
                </p>
            </div>

            <!-- Admin Role -->
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">⚙️</div>
                <h4>Pour les administrateurs</h4>
                <p style="color: var(--text-light);">
                    Supervisez l'ensemble du système, approuvez les organisateurs, gérez les utilisateurs, consultez les statistiques et assurez l'intégrité du système.
                </p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <?php if (!is_logged_in()): ?>
        <section style="text-align: center; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; padding: 3rem; border-radius: 0.5rem;">
            <h2 style="margin-bottom: 1rem;">Prêt à commencer ?</h2>
            <p style="margin-bottom: 2rem; font-size: 1.125rem;">
                Rejoignez la communauté et commencez à découvrir des événements universitaires incroyables dès aujourd'hui.
            </p>
            <a href="pages/auth/register.php" class="btn btn-primary" style="background-color: white; color: var(--primary-color); padding: 0.75rem 2rem; font-size: 1.125rem;">
                Créez votre compte maintenant
            </a>
        </section>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
