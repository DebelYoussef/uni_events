<?php
/**
 * Admin Dashboard
 * Main page for administrators - system overview and management
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';

// Require admin role
require_role(ROLE_ADMIN);

// Get current user data
$user = get_logged_in_user();

// Get flash message
$flash_message = get_flash_message();

// Fetch system statistics
try {
    // Total users by role
    $stmt = $pdo->query('
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN role = "student" THEN 1 ELSE 0 END) as students,
            SUM(CASE WHEN role = "organizer" THEN 1 ELSE 0 END) as organizers,
            SUM(CASE WHEN role = "organizer" AND is_approved = 0 THEN 1 ELSE 0 END) as pending_organizers
        FROM users
    ');
    $user_stats = $stmt->fetch();
} catch (PDOException $e) {
    $user_stats = ['total_users' => 0, 'students' => 0, 'organizers' => 0, 'pending_organizers' => 0];
}

// Total events
try {
    $stmt = $pdo->query('
        SELECT 
            COUNT(*) as total_events,
            SUM(CASE WHEN status IN ("upcoming","ongoing") THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN event_date >= CURDATE() AND status IN ("upcoming","ongoing") THEN 1 ELSE 0 END) as upcoming
        FROM events
    ');
    $event_stats = $stmt->fetch();
} catch (PDOException $e) {
    $event_stats = ['total_events' => 0, 'published' => 0, 'upcoming' => 0];
}

// Total registrations
try {
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM registrations');
    $total_registrations = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $total_registrations = 0;
}

// Pending organizer approvals
try {
    $stmt = $pdo->query('
        SELECT * FROM users 
        WHERE role = "organizer" AND is_approved = 0 
        ORDER BY created_at DESC 
        LIMIT 5
    ');
    $pending_organizers = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_organizers = [];
}

// Recent events
try {
    $stmt = $pdo->query('
        SELECT e.*, u.name as organizer_name,
               (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as registration_count
        FROM events e
        LEFT JOIN users u ON e.organizer_id = u.id
        ORDER BY e.created_at DESC 
        LIMIT 5
    ');
    $recent_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_events = [];
}

// Recent users
try {
    $stmt = $pdo->query('
        SELECT * FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ');
    $recent_users = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_users = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .dashboard-container {
            min-height: 100vh;
            background-color: var(--light-bg);
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
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
        .stat-icon.danger { background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
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
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
        
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: calc(var(--spacing-unit) * 2) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .list-item:last-child {
            border-bottom: none;
        }
        
        .list-item-info h4 {
            margin-bottom: calc(var(--spacing-unit) * 0.5);
            font-size: 0.9rem;
        }
        
        .list-item-info p {
            margin-bottom: 0;
            font-size: 0.8rem;
            color: var(--text-light);
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
        .badge-primary { background: #eff6ff; color: #1e40af; }
        
        .empty-state {
            text-align: center;
            padding: calc(var(--spacing-unit) * 4);
            color: var(--text-light);
        }
        
        .pending-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: calc(var(--spacing-unit) * 3);
            background: #fffbeb;
            border-radius: 0.375rem;
            margin-bottom: calc(var(--spacing-unit) * 2);
        }
        
        .pending-item:last-child {
            margin-bottom: 0;
        }
        
        .pending-actions {
            display: flex;
            gap: calc(var(--spacing-unit) * 1);
        }
        
        .btn-sm {
            padding: calc(var(--spacing-unit) * 1) calc(var(--spacing-unit) * 2);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navigation -->
        <nav>
            <div class="container">
                <div class="navbar">
                    <a href="../../index.php" class="navbar-brand">UniEvents Admin</a>
                    <ul class="navbar-nav">
                        <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                        <li><a href="users.php">Utilisateurs</a></li>
                        <li><a href="events.php">Evenements</a></li>
                        <li><a href="approvals.php">Approbations</a></li>
                        <li class="nav-item">
                            <a href="#"><?php echo escape_output($user['name']); ?></a>
                            <ul class="dropdown-menu">
                                <li><a href="settings.php">Parametres</a></li>
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
                <h1>Administration UniEvents</h1>
                <p>Gerez les utilisateurs, evenements et parametres du systeme</p>
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
                            <span>&#128101;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $user_stats['total_users'] ?? 0; ?></h3>
                            <p>Total utilisateurs</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <span>&#127891;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $user_stats['students'] ?? 0; ?></h3>
                            <p>Etudiants</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <span>&#128197;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $event_stats['total_events'] ?? 0; ?></h3>
                            <p>Total evenements</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <span>&#9889;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $user_stats['pending_organizers'] ?? 0; ?></h3>
                            <p>En attente</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <span>&#128203;</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_registrations; ?></h3>
                            <p>Inscriptions</p>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Approvals Alert -->
                <?php if (!empty($pending_organizers)): ?>
                    <div class="alert alert-warning" style="margin-bottom: calc(var(--spacing-unit) * 6);">
                        <strong>Attention:</strong> <?php echo count($pending_organizers); ?> organisateur(s) en attente d'approbation.
                        <a href="approvals.php" style="margin-left: 1rem;">Voir les demandes</a>
                    </div>
                <?php endif; ?>
                
                <div class="two-columns">
                    <!-- Recent Events -->
                    <div class="data-card">
                        <div class="data-card-header">
                            <h3>Evenements recents</h3>
                            <a href="events.php" class="btn btn-outline btn-sm">Voir tous</a>
                        </div>
                        <div class="data-card-body">
                            <?php if (empty($recent_events)): ?>
                                <div class="empty-state">
                                    <p>Aucun evenement.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_events as $event): ?>
                                    <div class="list-item">
                                        <div class="list-item-info">
                                            <h4><?php echo escape_output($event['title']); ?></h4>
                                            <p>
                                                Par <?php echo escape_output($event['organizer_name'] ?? 'N/A'); ?>
                                                - <?php echo format_date($event['event_date'], 'd/m/Y'); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <?php 
                                            $status_class = in_array($event['status'], ['upcoming', 'ongoing'], true) ? 'success' : 'warning';
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo $event['registration_count']; ?> inscrit(s)
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Users -->
                    <div class="data-card">
                        <div class="data-card-header">
                            <h3>Utilisateurs recents</h3>
                            <a href="users.php" class="btn btn-outline btn-sm">Voir tous</a>
                        </div>
                        <div class="data-card-body">
                            <?php if (empty($recent_users)): ?>
                                <div class="empty-state">
                                    <p>Aucun utilisateur.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_users as $u): ?>
                                    <div class="list-item">
                                        <div class="list-item-info">
                                            <h4><?php echo escape_output($u['name']); ?></h4>
                                            <p><?php echo escape_output($u['email']); ?></p>
                                        </div>
                                        <div>
                                            <?php 
                                            $role_class = $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'organizer' ? 'info' : 'primary');
                                            $role_text = $u['role'] === 'admin' ? 'Admin' : ($u['role'] === 'organizer' ? 'Organisateur' : 'Etudiant');
                                            ?>
                                            <span class="badge badge-<?php echo $role_class; ?>">
                                                <?php echo $role_text; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Organizers -->
                <?php if (!empty($pending_organizers)): ?>
                    <div class="data-card">
                        <div class="data-card-header">
                            <h3>Organisateurs en attente d'approbation</h3>
                            <a href="approvals.php" class="btn btn-outline btn-sm">Gerer</a>
                        </div>
                        <div class="data-card-body">
                            <?php foreach ($pending_organizers as $org): ?>
                                <div class="pending-item">
                                    <div class="list-item-info">
                                        <h4><?php echo escape_output($org['name']); ?></h4>
                                        <p><?php echo escape_output($org['email']); ?> - Inscrit le <?php echo format_date($org['created_at'], 'd/m/Y'); ?></p>
                                    </div>
                                    <div class="pending-actions">
                            <a href="../../actions/admin-approve-organizer.php?id=<?php echo $org['id']; ?>&action=approve" class="btn btn-success btn-sm">Approuver</a>
                            <a href="../../actions/admin-approve-organizer.php?id=<?php echo $org['id']; ?>&action=reject" class="btn btn-danger btn-sm">Rejeter</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <!-- Footer -->
        <footer>
            <div class="container">
                <p style="text-align: center; margin-bottom: 0;">
                    &copy; <?php echo date('Y'); ?> UniEvents - Panneau d'administration
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
