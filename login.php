<?php require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM connexion WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['mot_de_passe'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        redirect('index.php');
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — AlertRoute</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo"><span>🚧</span> AlertRoute</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="register.php">Inscription</a>
    </nav>
</div>

<div class="container-sm" style="margin-top:3rem;">
    <div class="card">
        <h2>🔐 Connexion</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
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
                       placeholder="Votre mot de passe">
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">
                Se connecter
            </button>
        </form>

        <p style="text-align:center;margin-top:1rem;color:var(--gray-500);">
            Pas encore de compte ? <a href="register.php" style="color:var(--primary);">S'inscrire</a>
        </p>
    </div>
</div>

</body>
</html>
