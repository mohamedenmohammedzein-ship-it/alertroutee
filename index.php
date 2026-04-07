<?php require_once 'config.php';

// Statistiques
$stats = [];
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM signalement")->fetchColumn();
$stats['nouveau'] = $pdo->query("SELECT COUNT(*) FROM signalement WHERE etat='nouveau'")->fetchColumn();
$stats['en_cours'] = $pdo->query("SELECT COUNT(*) FROM signalement WHERE etat='en_cours'")->fetchColumn();
$stats['resolu'] = $pdo->query("SELECT COUNT(*) FROM signalement WHERE etat='resolu'")->fetchColumn();
$stats['eleve'] = $pdo->query("SELECT COUNT(*) FROM signalement WHERE niveau_de_gravite='eleve'")->fetchColumn();

// Derniers signalements
$derniers = $pdo->query("
    SELECT s.*, t.nom AS type_probleme, q.nom AS quartier, p.url AS photo_url
    FROM signalement s
    LEFT JOIN typeprobleme t ON t.id = s.type_probleme_id
    LEFT JOIN quartier q ON q.id = s.quartier_id
    LEFT JOIN photo p ON p.id = s.photo_id
    ORDER BY s.date_signalement DESC
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlertRoute — Signalement urbain</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="index.php" class="logo">
        <span>🚧</span> AlertRoute
    </a>
    <nav>
        <a href="index.php" class="active">Accueil</a>
        <a href="formulaire.php">Signaler</a>
        <a href="search.php">Rechercher</a>
        <a href="alertlocal.php">🗺️ Carte</a>
        <?php if (isModo()): ?>
            <a href="all_signalements.php">Admin</a>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php" class="btn-nav">Déconnexion</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Connexion</a>
        <?php endif; ?>
    </nav>
</div>

<!-- HERO -->
<div class="hero">
    <h1>🚧 Signalez les problèmes urbains</h1>
    <p>Aidez à améliorer nos villes en signalant les nids-de-poule, pannes d'éclairage, déchets et autres problèmes.</p>
    <a href="formulaire.php" class="btn btn-lg" style="background:white;color:var(--primary);">
        ➕ Faire un signalement
    </a>
</div>

<div class="container">

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">Total signalements</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🆕</div>
            <div class="stat-number"><?= $stats['nouveau'] ?></div>
            <div class="stat-label">Nouveaux</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔧</div>
            <div class="stat-number"><?= $stats['en_cours'] ?></div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?= $stats['resolu'] ?></div>
            <div class="stat-label">Résolus</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-number"><?= $stats['eleve'] ?></div>
            <div class="stat-label">Gravité élevée</div>
        </div>
    </div>

    <!-- DERNIERS SIGNALEMENTS -->
    <h2 style="margin:2rem 0 1rem;">📌 Derniers signalements</h2>
    <div class="sig-grid">
        <?php foreach ($derniers as $s): ?>
        <div class="sig-card">
            <?php if ($s['photo_url']): ?>
                <img src="<?= sanitize($s['photo_url']) ?>" alt="Photo signalement"
                     onerror="this.src='https://via.placeholder.com/400x200/e5e7eb/6b7280?text=Pas+de+photo'">
            <?php else: ?>
                <img src="https://via.placeholder.com/400x200/e5e7eb/6b7280?text=Pas+de+photo" alt="Pas de photo">
            <?php endif; ?>
            <div class="sig-body">
                <h3><?= sanitize($s['description']) ?></h3>
                <div class="sig-meta">
                    <span class="badge badge-<?= $s['etat'] ?>"><?= $s['etat'] ?></span>
                    <span class="badge badge-<?= $s['niveau_de_gravite'] ?>"><?= $s['niveau_de_gravite'] ?></span>
                </div>
                <p style="color:var(--gray-500);font-size:0.85rem;">
                    📍 <?= sanitize($s['quartier'] ?? 'Non précisé') ?>
                    &bull; 🏷️ <?= sanitize($s['type_probleme'] ?? 'Non précisé') ?>
                </p>
            </div>
            <div class="sig-footer">
                <span>📅 <?= $s['date_signalement'] ?></span>
                <a href="report_details.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">Détails →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<div class="footer">
    &copy; 2025 AlertRoute — Signalement de problèmes urbains en Mauritanie
</div>

</body>
</html>
