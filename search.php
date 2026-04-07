<?php require_once 'config.php';

$types = $pdo->query("SELECT id, nom FROM typeprobleme ORDER BY nom")->fetchAll();
$quartiersList = $pdo->query("SELECT id, nom FROM quartier ORDER BY nom")->fetchAll();

// Construire la requête
$where = [];
$params = [];

if (!empty($_GET['q'])) {
    $where[] = "s.description LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}
if (!empty($_GET['type'])) {
    $where[] = "s.type_probleme_id = ?";
    $params[] = $_GET['type'];
}
if (!empty($_GET['etat'])) {
    $where[] = "s.etat = ?";
    $params[] = $_GET['etat'];
}
if (!empty($_GET['gravite'])) {
    $where[] = "s.niveau_de_gravite = ?";
    $params[] = $_GET['gravite'];
}
if (!empty($_GET['quartier'])) {
    $where[] = "s.quartier_id = ?";
    $params[] = $_GET['quartier'];
}

$sql = "
    SELECT s.*, t.nom AS type_probleme, q.nom AS quartier, p.url AS photo_url
    FROM signalement s
    LEFT JOIN typeprobleme t ON t.id = s.type_probleme_id
    LEFT JOIN quartier q ON q.id = s.quartier_id
    LEFT JOIN photo p ON p.id = s.photo_id
";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY s.date_signalement DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechercher — AlertRoute</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo"><span>🚧</span> AlertRoute</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="formulaire.php">Signaler</a>
        <a href="search.php" class="active">Rechercher</a>
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

<div class="container">
    <div class="card">
        <h2>🔍 Rechercher des signalements</h2>

        <form method="GET" action="">
            <div class="filters">
                <div class="form-group">
                    <label>Mots-clés</label>
                    <input type="text" name="q" placeholder="Rechercher..."
                           value="<?= sanitize($_GET['q'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="">Tous</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($_GET['type'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                <?= sanitize($t['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>État</label>
                    <select name="etat">
                        <option value="">Tous</option>
                        <option value="nouveau" <?= ($_GET['etat'] ?? '') === 'nouveau' ? 'selected' : '' ?>>Nouveau</option>
                        <option value="en_cours" <?= ($_GET['etat'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="resolu" <?= ($_GET['etat'] ?? '') === 'resolu' ? 'selected' : '' ?>>Résolu</option>
                        <option value="rejete" <?= ($_GET['etat'] ?? '') === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gravité</label>
                    <select name="gravite">
                        <option value="">Toutes</option>
                        <option value="faible" <?= ($_GET['gravite'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
                        <option value="moyen" <?= ($_GET['gravite'] ?? '') === 'moyen' ? 'selected' : '' ?>>Moyen</option>
                        <option value="eleve" <?= ($_GET['gravite'] ?? '') === 'eleve' ? 'selected' : '' ?>>Élevé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                </div>
            </div>
        </form>
    </div>

    <p style="color:var(--gray-500);margin-bottom:1rem;">
        <?= count($results) ?> résultat(s) trouvé(s)
    </p>

    <div class="sig-grid">
        <?php foreach ($results as $s): ?>
        <div class="sig-card">
            <?php if ($s['photo_url']): ?>
                <img src="<?= sanitize($s['photo_url']) ?>" alt="Photo"
                     onerror="this.src='https://via.placeholder.com/400x200/e5e7eb/6b7280?text=Photo'">
            <?php else: ?>
                <img src="https://via.placeholder.com/400x200/e5e7eb/6b7280?text=Pas+de+photo" alt="">
            <?php endif; ?>
            <div class="sig-body">
                <h3><?= sanitize($s['description']) ?></h3>
                <div class="sig-meta">
                    <span class="badge badge-<?= $s['etat'] ?>"><?= $s['etat'] ?></span>
                    <span class="badge badge-<?= $s['niveau_de_gravite'] ?>"><?= $s['niveau_de_gravite'] ?></span>
                </div>
                <p style="color:var(--gray-500);font-size:0.85rem;">
                    📍 <?= sanitize($s['quartier'] ?? 'N/A') ?>
                    &bull; 🏷️ <?= sanitize($s['type_probleme'] ?? 'N/A') ?>
                </p>
            </div>
            <div class="sig-footer">
                <span>📅 <?= $s['date_signalement'] ?></span>
                <a href="report_details.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">Détails →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($results)): ?>
        <div class="card" style="text-align:center;padding:3rem;">
            <p style="font-size:3rem;">🔍</p>
            <p style="color:var(--gray-500);">Aucun signalement trouvé avec ces critères.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
