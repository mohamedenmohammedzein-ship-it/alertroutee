<?php require_once 'config.php';

// Protection admin/modo
if (!isModo()) {
    redirect('login.php');
}

$message = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_etat':
                $stmt = $pdo->prepare("UPDATE signalement SET etat = ? WHERE id = ?");
                $stmt->execute([$_POST['etat'], $_POST['id']]);
                $message = "État mis à jour avec succès.";
                break;

            case 'update_gravite':
                $stmt = $pdo->prepare("UPDATE signalement SET niveau_de_gravite = ? WHERE id = ?");
                $stmt->execute([$_POST['gravite'], $_POST['id']]);
                $message = "Niveau de gravité mis à jour.";
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM signalement WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = "Signalement supprimé.";
                break;
        }
    }
}

// Récupérer tous les signalements
$signalements = $pdo->query("
    SELECT s.*, t.nom AS type_probleme, q.nom AS quartier, p.url AS photo_url
    FROM signalement s
    LEFT JOIN typeprobleme t ON t.id = s.type_probleme_id
    LEFT JOIN quartier q ON q.id = s.quartier_id
    LEFT JOIN photo p ON p.id = s.photo_id
    ORDER BY s.date_signalement DESC
")->fetchAll();

// Stats rapides
$total = count($signalements);
$byEtat = ['nouveau' => 0, 'en_cours' => 0, 'resolu' => 0, 'rejete' => 0];
foreach ($signalements as $s) { $byEtat[$s['etat']]++; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — AlertRoute</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo"><span>🚧</span> AlertRoute</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="formulaire.php">Signaler</a>
        <a href="search.php">Rechercher</a>
        <a href="alertlocal.php">🗺️ Carte</a>
        <a href="all_signalements.php" class="active">Admin</a>
        <a href="logout.php" class="btn-nav">Déconnexion (<?= sanitize($_SESSION['email']) ?>)</a>
    </nav>
</div>

<div class="container">
    <h2>⚙️ Administration des signalements</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= sanitize($message) ?></div>
    <?php endif; ?>

    <!-- Mini stats -->
    <div class="stats-grid" style="margin-bottom:2rem;">
        <div class="stat-card">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #2563eb;">
            <div class="stat-number"><?= $byEtat['nouveau'] ?></div>
            <div class="stat-label">Nouveaux</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-number"><?= $byEtat['en_cours'] ?></div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #16a34a;">
            <div class="stat-number"><?= $byEtat['resolu'] ?></div>
            <div class="stat-label">Résolus</div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Quartier</th>
                        <th>Date</th>
                        <th>État</th>
                        <th>Gravité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($signalements as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td>
                            <?php if ($s['photo_url']): ?>
                                <img src="<?= sanitize($s['photo_url']) ?>"
                                     onerror="this.src='https://via.placeholder.com/60/e5e7eb/6b7280?text=—'">
                            <?php else: ?>
                                <span style="color:var(--gray-300);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:200px;">
                            <a href="report_details.php?id=<?= $s['id'] ?>" style="color:var(--primary);text-decoration:none;">
                                <?= sanitize(mb_substr($s['description'], 0, 60)) ?>…
                            </a>
                        </td>
                        <td style="font-size:0.8rem;"><?= sanitize($s['type_probleme'] ?? '—') ?></td>
                        <td style="font-size:0.8rem;"><?= sanitize($s['quartier'] ?? '—') ?></td>
                        <td style="font-size:0.8rem;white-space:nowrap;"><?= $s['date_signalement'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="update_etat">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <select name="etat" onchange="this.form.submit()" style="padding:4px;border-radius:6px;border:1px solid var(--gray-200);font-size:0.8rem;">
                                    <?php foreach (['nouveau','en_cours','resolu','rejete'] as $e): ?>
                                        <option value="<?= $e ?>" <?= $s['etat'] === $e ? 'selected' : '' ?>><?= $e ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="update_gravite">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <select name="gravite" onchange="this.form.submit()" style="padding:4px;border-radius:6px;border:1px solid var(--gray-200);font-size:0.8rem;">
                                    <?php foreach (['faible','moyen','eleve'] as $g): ?>
                                        <option value="<?= $g ?>" <?= $s['niveau_de_gravite'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Supprimer ce signalement ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
