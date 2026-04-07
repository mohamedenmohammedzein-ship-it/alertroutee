<?php
require_once 'config.php';

if (!isModo()) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $gravite = $_POST['niveau_de_gravite'] ?? '';

    if ($id > 0 && in_array($gravite, ['faible', 'moyen', 'eleve'])) {
        $stmt = $pdo->prepare("UPDATE signalement SET niveau_de_gravite = ? WHERE id = ?");
        $stmt->execute([$gravite, $id]);
        echo json_encode(['success' => true, 'message' => 'Gravité mise à jour']);
    } else {
        echo json_encode(['error' => 'Paramètres invalides']);
    }
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>
