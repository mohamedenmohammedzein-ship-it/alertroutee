<?php require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['mot_de_passe'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères.";
    } else {
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id FROM connexion WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO connexion (email, mot_de_passe, role) VALUES (?, ?, 'citoyen')");
            $stmt->execute([$email, $password]);
            $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — AlertRoute</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo"><span>🚧</span> AlertRoute</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="login.php">Connexion</a>
    </nav>
</div>

<div class="container-sm" style="margin-top:3rem;">
    <div class="card">
        <h2>📝 Inscription</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">📧 Email</label>
                <input type="email" id="email" name="email" required
                       placeholder="votre@email.mr"
                       value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">🔑 Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required
                       placeholder="Minimum 6 caractères">
            </div>
            <div class="form-group">
                <label for="confirm">🔑 Confirmer le mot de passe</label>
                <input type="password" id="confirm" name="confirm" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">
                Créer mon compte
            </button>
        </form>
    </div>
</div>

</body>
</html>
