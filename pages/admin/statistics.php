<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_role(ROLE_ADMIN);
try {
    $users = (int) $pdo->query('SELECT COUNT(*) c FROM users')->fetch()['c'];
    $events = (int) $pdo->query('SELECT COUNT(*) c FROM events')->fetch()['c'];
    $registrations = (int) $pdo->query('SELECT COUNT(*) c FROM registrations WHERE status="registered"')->fetch()['c'];
    $attendance = (int) $pdo->query('SELECT COUNT(*) c FROM attendance')->fetch()['c'];
    $certificates = (int) $pdo->query('SELECT COUNT(*) c FROM certificates')->fetch()['c'];
} catch (PDOException $e) {
    $users = $events = $registrations = $attendance = $certificates = 0;
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Statistiques</title><link rel="stylesheet" href="../../public/css/style.css"></head>
<body><div class="container" style="padding:2rem 1rem;">
<div class="flex-between mb-2"><h1>Statistiques systeme</h1><button type="button" class="btn btn-outline" onclick="history.back()">← Retour</button></div>
<div class="stats-grid">
<div class="stat-card"><div class="stat-info"><h3><?php echo $users; ?></h3><p>Utilisateurs</p></div></div>
<div class="stat-card"><div class="stat-info"><h3><?php echo $events; ?></h3><p>Evenements</p></div></div>
<div class="stat-card"><div class="stat-info"><h3><?php echo $registrations; ?></h3><p>Inscriptions actives</p></div></div>
<div class="stat-card"><div class="stat-info"><h3><?php echo $attendance; ?></h3><p>Presences validees</p></div></div>
<div class="stat-card"><div class="stat-info"><h3><?php echo $certificates; ?></h3><p>Certificats</p></div></div>
</div>
</div></body></html>
