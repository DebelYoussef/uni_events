<?php
/**
 * Organizer Dashboard
 * Main page for organizers after login - displays event management and statistics
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';

// Require approved organizer
require_approved_organizer();

// Get current user data
$user = get_logged_in_user();

// Get flash message
$flash_message = get_flash_message();

// Fetch organizer's events count
try {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as total,
               SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published,
               SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
               SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming
        FROM events 
        WHERE organizer_id = ?
    ');
    $stmt->execute([$user['id']]);
    $event_stats = $stmt->fetch();
} catch (PDOException $e) {
    $event_stats = ['total' => 0, 'published' => 0, 'draft' => 0, 'upcoming' => 0];
}

// Fetch total participants across all organizer's events
try {
    $stmt = $pdo->prepare('
        SELECT COUNT(r.id) as total_participants
        FROM registrations r
        INNER JOIN events e ON r.event_id = e.id
        WHERE e.organizer_id = ?
    ');
    $stmt->execute([$user['id']]);
    $total_participants = $stmt->fetch()['total_participants'] ?? 0;
} catch (PDOException $e) {
    $total_participants = 0;
}

// Fetch organizer's recent events
try {
    $stmt = $pdo->prepare('
        SELECT e.*, 
               (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as registration_count
        FROM events e 
        WHERE e.organizer_id = ?
        ORDER BY e.created_at DESC 
        LIMIT 5
    ');
    $stmt->execute([$user['id']]);
    $recent_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_events = [];
}

// Fetch upcoming events (next 3)
try {
    $stmt = $pdo->prepare('
        SELECT e.*, 
               (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as registration_count
        FROM events e 
        WHERE e.organizer_id = ? 
        AND e.event_date >= CURDATE()
        AND e.status = "published"
        ORDER BY e.event_date ASC 
        LIMIT 3
    ');
    $stmt->execute([$user['id']]);
    $upcoming_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $upcoming_events = [];
}

// Fetch recent registrations for organizer's events
try {
    $stmt = $pdo->prepare('
        SELECT r.*, u.name as user_name, u.email as user_email, e.title as event_title
        FROM registrations r
        INNER JOIN users u ON r.user_id = u.id
        INNER JOIN events e ON r.event_id = e.id
        WHERE e.organizer_id = ?
        ORDER BY r.registered_at DESC
        LIMIT 5
    ');
    $stmt->execute([$user['id']]);
    $recent_registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_registrations = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Espace Organisateur</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .dashboard-container {
            min-height: 100vh;
            background-color: var(--light-bg);
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
        
        .header-actions {
            display: flex;
            gap: calc(var(--spacing-unit) * 2);
            margin-top: calc(var(--spacing-unit) * 3);
        }
        
        .btn-light {
            background: white;
            color: #059669;
            font-weight: 600;
        }
        
        .btn-light:hover {
            background: #f0fdf4;
        }
        
        .dashboard-content {
            padding: calc(var(--spacing-unit) * 6) 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
        .stat-icon.info { background-color: rgba(14, 165, 233, 0.1); color: var(--info-color); }
        
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
        
        .two-columns {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: calc(var(--spacing-unit) * 6);
        }
        
        @media (max-width: 900px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
        }
        
        .data-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: calc(var(--spacing-unit) * 4);
        }
        
        .data-card-header {
            padding: calc(var(--spacing-unit) * 3) calc(var(--spacing-unit) * 4);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .data-card-header h3 {
            margin-bottom: 0;
            font-size: 1rem;
        }
        
        .data-card-body {
            padding: calc(var(--spacing-unit) * 4);
        }
        
        .event-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: calc(var(--spacing-unit) * 3) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .event-list-item:last-child {
            border-bottom: none;
        }
        
        .event-list-info h4 {
            margin-bottom: calc(var(--spacing-unit) * 0.5);
            font-size: 0.95rem;
        }
        
        .event-list-info p {
            margin-bottom: 0;
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .event-list-meta {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: calc(var(--spacing-unit) * 0.5) calc(var(--spacing-unit) * 1.5);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: #ecfdf5; color: #065f46; }
        .badge-warning { background: #fffbeb; color: #78350f; }
        .badge-info { background: #f0f9ff; color: #0c2d6b; }
        .badge-danger { background: #fef2f2; color: #7f1d1d; }
        
        .participant-count {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .registration-item {
            display: flex;
            gap: calc(var(--spacing-unit) * 2);
            padding: calc(var(--spacing-unit) * 2) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .registration-item:last-child {
            border-bottom: none;
        }
        
        .registration-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .registration-info h5 {
            margin-bottom: calc(var(--spacing-unit) * 0.5);
            font-size: 0.9rem;
        }
        
        .registration-info p {
            margin-bottom: 0;
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .upcoming-event-card {
            background: white;
            border-radius: 0.5rem;
            padding: calc(var(--spacing-unit) * 3);
            margin-bottom: calc(var(--spacing-unit) * 3);
            border-left: 4px solid var(--success-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .upcoming-event-card h4 {
            margin-bottom: calc(var(--spacing-unit) * 1);
            font-size: 0.95rem;
        }
        
        .upcoming-event-card p {
            margin-bottom: calc(var(--spacing-unit) * 1);
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .upcoming-event-card .event-stats {
            display: flex;
            gap: calc(var(--spacing-unit) * 3);
            font-size: 0.8rem;
        }
        
        .empty-state {
            text-align: center;
            padding: calc(var(--spacing-unit) * 6);
            color: var(--text-light);
        }
        
        .empty-state p {
            margin-bottom: calc(var(--spacing-unit) * 3);
        }
        
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: calc(var(--spacing-unit) * 2);
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: calc(var(--spacing-unit) * 3);
            background: var(--light-bg);
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }
        
        .quick-action-btn:hover {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
        }
        
        .quick-action-btn span {
            font-size: 1.5rem;
            margin-bottom: calc(var(--spacing-unit) * 1);
        }
        
        .quick-action-btn small {
            font-size: 0.8rem;
            font-weight: 500;
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
                        <li><a href="my-events.php">Mes evenements</a></li>
                        <li><a href="create-event.php">Creer un evenement</a></li>
                        <li><a href="participants.php">Participants</a></li>
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
                <div class="flex-between">
                    <div>
                        <h1>Bienvenue, <?php echo escape_output($user['name']); ?></h1>
                        <p>Gerez vos evenements et suivez les inscriptions</p>
                    </div>
                    <div class="header-actions">
                        <a href="create-event.php" class="btn btn-light">+ Creer un evenement</a>
                    </div>
                </div>
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
                            <h3><?php echo $event_stats['total'] ?? 0; ?></h3>
                            <p>Total evenements</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <span>&#9989;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $event_stats['published'] ?? 0; ?></h3>
                            <p>Publies</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <span>&#128221;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $event_stats['draft'] ?? 0; ?></h3>
                            <p>Brouillons</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <span>&#128101;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_participants; ?></h3>
                            <p>Total participants</p>
                        </div>
                    </div>
                </div>
                
                <div class="two-columns">
                    <!-- Main Section -->
                    <div>
                        <!-- Recent Events -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <h3>Mes evenements recents</h3>
                                <a href="my-events.php" class="btn btn-outline" style="padding: calc(var(--spacing-unit) * 1) calc(var(--spacing-unit) * 2); font-size: 0.875rem;">
                                    Voir tous
                                </a>
                            </div>
                            <div class="data-card-body">
                                <?php if (empty($recent_events)): ?>
                                    <div class="empty-state">
                                        <p>Vous n'avez pas encore cree d'evenement.</p>
                                        <a href="create-event.php" class="btn btn-primary">Creer mon premier evenement</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_events as $event): ?>
                                        <div class="event-list-item">
                                            <div class="event-list-info">
                                                <h4><?php echo escape_output($event['title']); ?></h4>
                                                <p>
                                                    <?php echo format_date($event['event_date'], 'd/m/Y H:i'); ?> 
                                                    - <?php echo escape_output($event['location'] ?? 'Non specifie'); ?>
                                                </p>
                                            </div>
                                            <div class="event-list-meta">
                                                <?php 
                                                $status_class = $event['status'] === 'published' ? 'success' : 'warning';
                                                $status_text = $event['status'] === 'published' ? 'Publie' : 'Brouillon';
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                                <p class="participant-count">
                                                    <?php echo $event['registration_count']; ?> inscrit(s)
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Recent Registrations -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <h3>Inscriptions recentes</h3>
                                <a href="participants.php" class="btn btn-outline" style="padding: calc(var(--spacing-unit) * 1) calc(var(--spacing-unit) * 2); font-size: 0.875rem;">
                                    Voir tous
                                </a>
                            </div>
                            <div class="data-card-body">
                                <?php if (empty($recent_registrations)): ?>
                                    <div class="empty-state">
                                        <p>Aucune inscription pour le moment.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_registrations as $reg): ?>
                                        <div class="registration-item">
                                            <div class="registration-avatar">
                                                <?php echo strtoupper(substr($reg['user_name'], 0, 1)); ?>
                                            </div>
                                            <div class="registration-info">
                                                <h5><?php echo escape_output($reg['user_name']); ?></h5>
                                                <p>
                                                    S'est inscrit a "<?php echo escape_output($reg['event_title']); ?>"
                                                    - <?php echo format_date($reg['registered_at'], 'd/m/Y H:i'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div>
                        <!-- Upcoming Events -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <h3>Prochains evenements</h3>
                            </div>
                            <div class="data-card-body">
                                <?php if (empty($upcoming_events)): ?>
                                    <div class="empty-state">
                                        <p>Aucun evenement a venir.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($upcoming_events as $event): ?>
                                        <div class="upcoming-event-card">
                                            <h4><?php echo escape_output($event['title']); ?></h4>
                                            <p><?php echo format_date($event['event_date'], 'd M Y - H:i'); ?></p>
                                            <div class="event-stats">
                                                <span>&#128205; <?php echo escape_output($event['location'] ?? 'N/A'); ?></span>
                                                <span>&#128101; <?php echo $event['registration_count']; ?> inscrit(s)</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <h3>Actions rapides</h3>
                            </div>
                            <div class="data-card-body">
                                <div class="quick-actions-grid">
                                    <a href="create-event.php" class="quick-action-btn">
                                        <span>&#10133;</span>
                                        <small>Nouvel evenement</small>
                                    </a>
                                    <a href="my-events.php" class="quick-action-btn">
                                        <span>&#128197;</span>
                                        <small>Mes evenements</small>
                                    </a>
                                    <a href="participants.php" class="quick-action-btn">
                                        <span>&#128101;</span>
                                        <small>Participants</small>
                                    </a>
                                    <a href="profile.php" class="quick-action-btn">
                                        <span>&#128100;</span>
                                        <small>Mon profil</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
