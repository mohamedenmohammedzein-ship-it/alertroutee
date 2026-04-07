<?php require_once 'config.php';

$success = '';
$error = '';

// Charger les données pour les selects
$types = $pdo->query("SELECT id, nom FROM typeprobleme ORDER BY nom")->fetchAll();
$villes = $pdo->query("SELECT id, nom FROM ville ORDER BY nom")->fetchAll();
$quartiers = $pdo->query("SELECT id, nom, ville_id FROM quartier ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $type_id = intval($_POST['type_probleme_id'] ?? 0);
    $quartier_id = intval($_POST['quartier_id'] ?? 0);
    $gravite = $_POST['niveau_de_gravite'] ?? 'faible';
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    $date = date('Y-m-d');

    if (empty($description) || $type_id === 0) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $photo_id = null;
        $image_path = null;

        // Upload de photo
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $filename = uniqid('sig_') . '.' . $ext;
                $filepath = 'images/' . $filename;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $filepath)) {
                    // Insérer dans table photo
                    $stmt = $pdo->prepare("INSERT INTO photo (nom, url) VALUES (?, ?)");
                    $stmt->execute([$description, $filepath]);
                    $photo_id = $pdo->lastInsertId();
                    $image_path = $filepath;
                }
            } else {
                $error = "Format d'image non autorisé. Utilisez JPG, PNG, GIF ou WebP.";
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("
                INSERT INTO signalement
                    (description, date_signalement, type_probleme_id, photo_id, quartier_id,
                     etat, niveau_de_gravite, latitude, longitude, utilisateur_id, image_path)
                VALUES (?, ?, ?, ?, ?, 'nouveau', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $description, $date, $type_id, $photo_id,
                $quartier_id ?: null, $gravite,
                $latitude ?: null, $longitude ?: null,
                $_SESSION['user_id'] ?? null, $image_path
            ]);
            $success = "Signalement envoyé avec succès ! Merci pour votre contribution.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signaler un problème — AlertRoute</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo"><span>🚧</span> AlertRoute</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="formulaire.php" class="active">Signaler</a>
        <a href="search.php">Rechercher</a>
        <a href="alertlocal.php">🗺️ Carte</a>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php" class="btn-nav">Déconnexion</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Connexion</a>
        <?php endif; ?>
    </nav>
</div>

<div class="container-sm">
    <div class="card">
        <h2>➕ Signaler un problème</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="description">📝 Description *</label>
                <textarea id="description" name="description" required
                          placeholder="Décrivez le problème en détail..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="type_probleme_id">🏷️ Type de problème *</label>
                <select id="type_probleme_id" name="type_probleme_id" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['id'] ?>"
                            <?= (($_POST['type_probleme_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                            <?= sanitize($t['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="ville_id">🏙️ Ville</label>
                <select id="ville_id" name="ville_id" onchange="filterQuartiers()">
                    <option value="">— Choisir —</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= sanitize($v['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quartier_id">📍 Quartier</label>
                <select id="quartier_id" name="quartier_id">
                    <option value="">— Choisir une ville d'abord —</option>
                </select>
            </div>

            <div class="form-group">
                <label for="niveau_de_gravite">⚠️ Niveau de gravité</label>
                <select id="niveau_de_gravite" name="niveau_de_gravite">
                    <option value="faible">🟢 Faible</option>
                    <option value="moyen" selected>🟡 Moyen</option>
                    <option value="eleve">🔴 Élevé</option>
                </select>
            </div>

            <div class="form-group">
                <label>📸 Photo</label>
                <input type="file" name="photo" accept="image/*">
            </div>

            <div class="form-group">
                <label>📍 Localisation (cliquez sur la carte)</label>
                <div id="map" style="height:300px;border-radius:8px;margin-bottom:8px;"></div>
                <div style="display:flex;gap:1rem;">
                    <input type="text" id="latitude" name="latitude" placeholder="Latitude" readonly
                           style="flex:1;" value="<?= $_POST['latitude'] ?? '' ?>">
                    <input type="text" id="longitude" name="longitude" placeholder="Longitude" readonly
                           style="flex:1;" value="<?= $_POST['longitude'] ?? '' ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">
                📤 Envoyer le signalement
            </button>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Carte pour sélectionner la position
const map = L.map('map').setView([18.0858, -15.9785], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

let marker = null;
map.on('click', function(e) {
    const {lat, lng} = e.latlng;
    document.getElementById('latitude').value = lat.toFixed(7);
    document.getElementById('longitude').value = lng.toFixed(7);
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
});

// Filtrer les quartiers par ville
const quartiers = <?= json_encode($quartiers) ?>;

function filterQuartiers() {
    const villeId = document.getElementById('ville_id').value;
    const select = document.getElementById('quartier_id');
    select.innerHTML = '<option value="">— Choisir —</option>';
    quartiers
        .filter(q => q.ville_id == villeId)
        .forEach(q => {
            select.innerHTML += `<option value="${q.id}">${q.nom}</option>`;
        });
}
</script>

</body>
</html>
