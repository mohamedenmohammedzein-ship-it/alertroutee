<?php require_once 'config.php';

$signalements = $pdo->query("
    SELECT s.id, s.description, s.latitude, s.longitude, s.etat, s.niveau_de_gravite,
           s.date_signalement, t.nom AS type_probleme, q.nom AS quartier, p.url AS photo_url
    FROM signalement s
    LEFT JOIN typeprobleme t ON t.id = s.type_probleme_id
    LEFT JOIN quartier q ON q.id = s.quartier_id
    LEFT JOIN photo p ON p.id = s.photo_id
    WHERE s.latitude IS NOT NULL AND s.longitude IS NOT NULL
    ORDER BY s.date_signalement DESC
")->fetchAll();

// Traiter la souscription
$sub_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_email'])) {
    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (email, subscription_type, radius, latitude, longitude)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['sub_email'],
        $_POST['sub_type'] ?? 'all',
        floatval($_POST['sub_radius'] ?? 3.4),
        floatval($_POST['sub_lat'] ?? 18.0858),
        floatval($_POST['sub_lng'] ?? -15.9785)
    ]);
    $sub_success = "Vous recevrez des alertes pour votre zone !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte des signalements — AlertRoute</title>
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
        <a href="alertlocal.php" class="active">🗺️ Carte</a>
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
    <h2 style="margin-bottom:1rem;">🗺️ Carte des signalements (<?= count($signalements) ?>)</h2>

    <!-- Filtres carte -->
    <div style="display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap;">
        <button onclick="filterMap('all')" class="btn btn-sm btn-primary">Tous</button>
        <button onclick="filterMap('nouveau')" class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8;">Nouveaux</button>
        <button onclick="filterMap('en_cours')" class="btn btn-sm" style="background:#fef3c7;color:#b45309;">En cours</button>
        <button onclick="filterMap('resolu')" class="btn btn-sm" style="background:#dcfce7;color:#15803d;">Résolus</button>
        <button onclick="filterMap('eleve')" class="btn btn-sm btn-danger">Gravité élevée</button>
    </div>

    <div id="map"></div>

    <!-- Abonnement alertes -->
    <div class="card" style="margin-top:1.5rem;">
        <h2>🔔 S'abonner aux alertes locales</h2>

        <?php if ($sub_success): ?>
            <div class="alert alert-success"><?= sanitize($sub_success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="filters">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="sub_email" required placeholder="votre@email.mr">
                </div>
                <div class="form-group">
                    <label>Rayon (km)</label>
                    <input type="number" name="sub_radius" value="3.4" step="0.1" min="0.5" max="50">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="sub_type">
                        <option value="all">Tous les types</option>
                        <option value="danger">Danger uniquement</option>
                    </select>
                </div>
                <input type="hidden" name="sub_lat" id="sub_lat" value="18.0858">
                <input type="hidden" name="sub_lng" id="sub_lng" value="-15.9785">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">🔔 S'abonner</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([18.0858, -15.9785], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

const signalements = <?= json_encode($signalements) ?>;
let markers = [];

const colors = {
    'nouveau': '#2563eb',
    'en_cours': '#f59e0b',
    'resolu': '#16a34a',
    'rejete': '#dc2626'
};

function createMarkers(filter) {
    // Supprimer les anciens
    markers.forEach(m => map.removeLayer(m));
    markers = [];

    signalements.forEach(s => {
        if (filter !== 'all') {
            if (filter === 'eleve' && s.niveau_de_gravite !== 'eleve') return;
            else if (filter !== 'eleve' && s.etat !== filter) return;
        }

        const color = colors[s.etat] || '#6b7280';
        const icon = L.divIcon({
            className: '',
            html: `<div style="
                background:${color};
                width:14px;height:14px;
                border-radius:50%;
                border:3px solid white;
                box-shadow:0 2px 6px rgba(0,0,0,0.3);
            "></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        const photoHtml = s.photo_url
            ? `<img src="${s.photo_url}" style="width:100%;height:80px;object-fit:cover;border-radius:4px;margin-bottom:6px;" onerror="this.style.display='none'">`
            : '';

        const m = L.marker([s.latitude, s.longitude], {icon: icon})
            .addTo(map)
            .bindPopup(`
                ${photoHtml}
                <strong>${s.description.substring(0, 80)}...</strong><br>
                <span style="color:${color};font-weight:bold;">${s.etat}</span>
                &bull; ${s.niveau_de_gravite}<br>
                📍 ${s.quartier || 'N/A'} &bull; 📅 ${s.date_signalement}<br>
                <a href="report_details.php?id=${s.id}">Voir détails →</a>
            `);
        markers.push(m);
    });
}

function filterMap(filter) {
    createMarkers(filter);
}

createMarkers('all');

// Géolocalisation
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
        const {latitude, longitude} = pos.coords;
        document.getElementById('sub_lat').value = latitude;
        document.getElementById('sub_lng').value = longitude;
        L.circle([latitude, longitude], {radius: 200, color: '#2563eb', fillOpacity: 0.1}).addTo(map);
    });
}
</script>

</body>
</html>
