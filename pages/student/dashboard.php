<?php
/**
 * Student Dashboard
 * Main page for students after login - displays upcoming events and quick actions
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';

// Require student role
require_role(ROLE_STUDENT);

// Get current user data
$user = get_logged_in_user();

// Get flash message
$flash_message = get_flash_message();

// Fetch upcoming events (next 5 events)
try {
    $stmt = $pdo->prepare('
        SELECT e.*, 
               (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = "registered") as registration_count
        FROM events e 
        WHERE e.event_date >= CURDATE()
        AND e.status IN ("upcoming", "ongoing")
        ORDER BY e.event_date ASC 
        LIMIT 5
    ');
    $stmt->execute();
    $upcoming_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $upcoming_events = [];
}

// Fetch student's registrations count
try {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count 
        FROM registrations 
        WHERE student_id = ? AND status = "registered"
    ');
    $stmt->execute([$user['id']]);
    $my_registrations_count = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $my_registrations_count = 0;
}

// Fetch student's upcoming registered events
try {
    $stmt = $pdo->prepare('
        SELECT e.*, r.registered_at
        FROM events e 
        INNER JOIN registrations r ON e.id = r.event_id
        WHERE r.student_id = ? AND r.status = "registered"
        AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC 
        LIMIT 3
    ');
    $stmt->execute([$user['id']]);
    $my_upcoming_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $my_upcoming_events = [];
}

// Fetch certificates count
try {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count
        FROM certificates c
        INNER JOIN registrations r ON r.id = c.registration_id
        WHERE r.student_id = ?
    ');
    $stmt->execute([$user['id']]);
    $certificates_count = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $certificates_count = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Espace Etudiant</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .dashboard-container {
            min-height: 100vh;
            background-color: var(--light-bg);
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: calc(var(--spacing-unit) * 6) 0;
        }
        
        .dashboard-header h1 {
            color: white;
            margin-bottom: calc(var(--spacing-unit) * 1);
        }
        
        .dashboard-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .dashboard-content {
            padding: calc(var(--spacing-unit) * 6) 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: calc(var(--spacing-unit) * 4);
            margin-bottom: calc(var(--spacing-unit) * 6);
        }
        
        .stat-card {
            background: white;
            border-radius: 0.5rem;
            padding: calc(var(--spacing-unit) * 4);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: calc(var(--spacing-unit) * 3);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.primary { background-color: rgba(37, 99, 235, 0.1); color: var(--primary-color); }
        .stat-icon.success { background-color: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .stat-icon.warning { background-color: rgba(245, 158, 11, 0.1); color: var(--secondary-color); }
        
        .stat-info h3 {
            font-size: 1.5rem;
            margin-bottom: 0;
        }
        
        .stat-info p {
            color: var(--text-light);
            margin-bottom: 0;
            font-size: 0.875rem;
        }
        
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: calc(var(--spacing-unit) * 4);
        }
        
        .section-title h2 {
            margin-bottom: 0;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: calc(var(--spacing-unit) * 4);
        }
        
        .event-card {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .event-card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: calc(var(--spacing-unit) * 3);
        }
        
        .event-card-header h4 {
            color: white;
            margin-bottom: calc(var(--spacing-unit) * 1);
            font-size: 1.1rem;
        }
        
        .event-card-header .event-date {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .event-card-body {
            padding: calc(var(--spacing-unit) * 3);
        }
        
        .event-card-body p {
            color: var(--text-light);
            font-size: 0.875rem;
            margin-bottom: calc(var(--spacing-unit) * 2);
        }
        
        .event-meta {
            display: flex;
            gap: calc(var(--spacing-unit) * 3);
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .event-meta span {
            display: flex;
            align-items: center;
            gap: calc(var(--spacing-unit) * 1);
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: calc(var(--spacing-unit) * 3);
            margin-top: calc(var(--spacing-unit) * 6);
        }
        
        .action-card {
            background: white;
            border-radius: 0.5rem;
            padding: calc(var(--spacing-unit) * 4);
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            text-decoration: none;
            color: var(--text-dark);
        }
        
        .action-card:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: var(--primary-color);
        }
        
        .action-card .action-icon {
            font-size: 2rem;
            margin-bottom: calc(var(--spacing-unit) * 2);
        }
        
        .action-card h5 {
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        
        .empty-state {
            text-align: center;
            padding: calc(var(--spacing-unit) * 8);
            color: var(--text-light);
        }
        
        .empty-state p {
            margin-bottom: calc(var(--spacing-unit) * 4);
        }
        
        .two-columns {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: calc(var(--spacing-unit) * 6);
        }
        
        @media (max-width: 900px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
        }
        
        .sidebar-card {
            background: white;
            border-radius: 0.5rem;
            padding: calc(var(--spacing-unit) * 4);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: calc(var(--spacing-unit) * 4);
        }
        
        .sidebar-card h3 {
            font-size: 1rem;
            margin-bottom: calc(var(--spacing-unit) * 3);
            padding-bottom: calc(var(--spacing-unit) * 2);
            border-bottom: 1px solid var(--border-color);
        }
        
        .my-event-item {
            display: flex;
            gap: calc(var(--spacing-unit) * 2);
            padding: calc(var(--spacing-unit) * 2) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .my-event-item:last-child {
            border-bottom: none;
        }
        
        .my-event-date {
            background: var(--primary-color);
            color: white;
            border-radius: 0.25rem;
            padding: calc(var(--spacing-unit) * 1);
            text-align: center;
            min-width: 45px;
        }
        
        .my-event-date .day {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .my-event-date .month {
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        
        .my-event-info h5 {
            margin-bottom: calc(var(--spacing-unit) * 0.5);
            font-size: 0.9rem;
        }
        
        .my-event-info p {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navigation -->
        <nav>
            <div class="container">
                <div class="navbar">
                    <a href="../../index.php" class="navbar-brand">UniEvents</a>
                    <ul class="navbar-nav">
                        <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                        <li><a href="events.php">Evenements</a></li>
                        <li><a href="upcoming-events.php">A venir</a></li>
                        <li><a href="my-registrations.php">Mes inscriptions</a></li>
                        <li><a href="certificates.php">Certificats</a></li>
                        <li class="nav-item">
                            <a href="#"><?php echo escape_output($user['name']); ?></a>
                            <ul class="dropdown-menu">
                                <li><a href="profile.php">Mon profil</a></li>
                                <li><a href="../auth/logout.php">Deconnexion</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Header -->
        <header class="dashboard-header">
            <div class="container">
                <h1>Bienvenue, <?php echo escape_output($user['name']); ?></h1>
                <p>Decouvrez les evenements universitaires et inscrivez-vous facilement</p>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="container">
                <?php if ($flash_message): ?>
                    <div class="alert alert-<?php echo escape_output($flash_message['type']); ?> alert-dismissible">
                        <?php echo escape_output($flash_message['message']); ?>
                        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none';">x</button>
                    </div>
                <?php endif; ?>
                
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <span>&#128197;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($upcoming_events); ?></h3>
                            <p>Evenements a venir</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <span>&#9989;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $my_registrations_count; ?></h3>
                            <p>Mes inscriptions</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <span>&#127942;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $certificates_count; ?></h3>
                            <p>Certificats obtenus</p>
                        </div>
                    </div>
                </div>
                
                <div class="two-columns">
                    <!-- Main Section: Upcoming Events -->
                    <section>
                        <div class="section-title">
                            <h2>Evenements a venir</h2>
                            <a href="events.php" class="btn btn-outline">Voir tous</a>
                        </div>
                        
                        <?php if (empty($upcoming_events)): ?>
                            <div class="empty-state card">
                                <p>Aucun evenement prevu pour le moment.</p>
                                <p>Revenez bientot pour decouvrir de nouveaux evenements!</p>
                            </div>
                        <?php else: ?>
                            <div class="events-grid">
                                <?php foreach ($upcoming_events as $event): ?>
                                    <div class="event-card">
                                        <div class="event-card-header">
                                            <h4><?php echo escape_output($event['title']); ?></h4>
                                            <span class="event-date">
                                                <?php echo format_date($event['event_date'], 'd M Y - H:i'); ?>
                                            </span>
                                        </div>
                                        <div class="event-card-body">
                                            <p><?php echo escape_output(substr($event['description'] ?? '', 0, 100)); ?>...</p>
                                            <div class="event-meta">
                                                <span>
                                                    <span>&#128205;</span>
                                                    <?php echo escape_output($event['location'] ?? 'Non specifie'); ?>
                                                </span>
                                                <span>
                                                    <span>&#128101;</span>
                                                    <?php echo $event['registration_count']; ?> inscrits
                                                </span>
                                            </div>
                                            <a href="events.php" class="btn btn-primary btn-block" style="margin-top: calc(var(--spacing-unit) * 3);">
                                                S'inscrire
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                    
                    <!-- Sidebar -->
                    <aside>
                        <!-- My Upcoming Events -->
                        <div class="sidebar-card">
                            <h3>Mes prochains evenements</h3>
                            <?php if (empty($my_upcoming_events)): ?>
                                <p style="color: var(--text-light); font-size: 0.875rem;">
                                    Vous n'etes inscrit a aucun evenement.
                                </p>
                                <a href="events.php" class="btn btn-primary btn-block">Explorer les evenements</a>
                            <?php else: ?>
                                <?php foreach ($my_upcoming_events as $event): 
                                    $date = new DateTime($event['event_date']);
                                ?>
                                    <div class="my-event-item">
                                        <div class="my-event-date">
                                            <div class="day"><?php echo $date->format('d'); ?></div>
                                            <div class="month"><?php echo $date->format('M'); ?></div>
                                        </div>
                                        <div class="my-event-info">
                                            <h5><?php echo escape_output($event['title']); ?></h5>
                                            <p><?php echo $date->format('H:i'); ?> - <?php echo escape_output($event['location'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="my-registrations.php" class="btn btn-outline btn-block" style="margin-top: calc(var(--spacing-unit) * 3);">
                                    Voir toutes mes inscriptions
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="sidebar-card">
                            <h3>Actions rapides</h3>
                            <div style="display: flex; flex-direction: column; gap: calc(var(--spacing-unit) * 2);">
                                <a href="events.php" class="btn btn-primary btn-block">Parcourir les evenements</a>
                                <a href="my-registrations.php" class="btn btn-outline btn-block">Mes inscriptions</a>
                                <a href="certificates.php" class="btn btn-outline btn-block">Mes certificats</a>
                                <a href="profile.php" class="btn btn-outline btn-block">Mon profil</a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer>
            <div class="container">
                <p style="text-align: center; margin-bottom: 0;">
                    &copy; <?php echo date('Y'); ?> UniEvents - Systeme de gestion des evenements universitaires
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
