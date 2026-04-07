<?php require_once 'config.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) redirect('index.php');

$stmt = $pdo->prepare("
    SELECT s.*, t.nom AS type_probleme, q.nom AS quartier, v.nom AS ville,
           p.url AS photo_url, p2.url AS photo2_url, p3.url AS photo3_url
    FROM signalement s
    LEFT JOIN typeprobleme t ON t.id = s.type_probleme_id
    LEFT JOIN quartier q ON q.id = s.quartier_id
    LEFT JOIN ville v ON v.id = q.ville_id
    LEFT JOIN photo p ON p.id = s.photo_id
    LEFT JOIN photo p2 ON p2.id = s.photo_id2
    LEFT JOIN photo p3 ON p3.id = s.photo_id3
    WHERE s.id = ?
");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) redirect('index.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signalement #<?= $s['id'] ?> — AlertRoute</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo"><span>🚧</span> AlertRoute</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="formulaire.php">Signaler</a>
        <a href="search.php">Rechercher</a>
        <a href="alertlocal.php">🗺️ Carte</a>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php" class="btn-nav">Déconnexion</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Connexion</a>
        <?php endif; ?>
    </nav>
</div>

<div class="container" style="max-width:800px;">
    <a href="javascript:history.back()" class="btn btn-secondary" style="margin-bottom:1rem;">← Retour</a>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:1rem;">
            <h2 style="margin:0;">Signalement #<?= $s['id'] ?></h2>
            <div style="display:flex;gap:8px;">
                <span class="badge badge-<?= $s['etat'] ?>"><?= $s['etat'] ?></span>
                <span class="badge badge-<?= $s['niveau_de_gravite'] ?>"><?= $s['niveau_de_gravite'] ?></span>
            </div>
        </div>

        <hr style="margin:1rem 0;border:none;border-top:1px solid var(--gray-200);">

        <p style="font-size:1.1rem;line-height:1.7;margin-bottom:1.5rem;">
            <?= sanitize($s['description']) ?>
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <strong style="color:var(--gray-500);font-size:0.85rem;">🏷️ TYPE</strong><br>
                <?= sanitize($s['type_probleme'] ?? 'Non précisé') ?>
            </div>
            <div>
                <strong style="color:var(--gray-500);font-size:0.85rem;">📍 QUARTIER</strong><br>
                <?= sanitize($s['quartier'] ?? 'Non précisé') ?>
                <?php if ($s['ville']): ?> — <?= sanitize($s['ville']) ?><?php endif; ?>
            </div>
            <div>
                <strong style="color:var(--gray-500);font-size:0.85rem;">📅 DATE</strong><br>
                <?= $s['date_signalement'] ?>
            </div>
            <div>
                <strong style="color:var(--gray-500);font-size:0.85rem;">📌 COORDONNÉES</strong><br>
                <?= $s['latitude'] ?>, <?= $s['longitude'] ?>
            </div>
        </div>

        <!-- Photos -->
        <?php
        $photos = array_filter([$s['photo_url'], $s['photo2_url'], $s['photo3_url']]);
        if ($photos):
        ?>
        <h3 style="margin-bottom:0.75rem;">📸 Photos</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
            <?php foreach ($photos as $photo): ?>
            <img src="<?= sanitize($photo) ?>" alt="Photo signalement"
                 style="width:100%;height:200px;object-fit:cover;border-radius:8px;cursor:pointer;"
                 onclick="window.open(this.src)"
                 onerror="this.style.display='none'">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Carte -->
        <?php if ($s['latitude'] && $s['longitude']): ?>
        <h3 style="margin-bottom:0.75rem;">🗺️ Localisation</h3>
        <div id="map" style="height:350px;"></div>
        <?php endif; ?>
    </div>
</div>

<?php if ($s['latitude'] && $s['longitude']): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([<?= $s['latitude'] ?>, <?= $s['longitude'] ?>], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    L.marker([<?= $s['latitude'] ?>, <?= $s['longitude'] ?>])
        .addTo(map)
        .bindPopup(`<strong><?= sanitize($s['description']) ?></strong>`)
        .openPopup();
</script>
<?php endif; ?>

</body>
</html>
