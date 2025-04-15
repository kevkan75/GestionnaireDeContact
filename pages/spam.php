<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour voir les spams.");
}

$user_id = $_SESSION['user_id'];

// Récupérer les messages marqués comme spam
try {
    $sql = "
        SELECT s.message, u.nom AS expediteur_nom, u.prenom AS expediteur_prenom, s.date_envoi, s.id AS spam_id
        FROM spam s
        JOIN utilisateurs u ON s.expediteur_id = u.id
        WHERE s.contact_id = ?
        ORDER BY s.date_envoi DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);

    // Récupérer les résultats
    $messages = $stmt->fetchAll();

} catch (PDOException $e) {
    // Si une erreur survient, afficher l'erreur avec plus de détails
    echo "Erreur de requête SQL : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Spams</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>🚨 Messages marqués comme spam</h2>
    <?php if (!$messages): ?>
        <p>❌ Aucun spam.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($messages as $msg): ?>
                <li>
                    <strong><?= htmlspecialchars($msg['expediteur_prenom']) . ' ' . htmlspecialchars($msg['expediteur_nom']) ?></strong> - 
                    <?= htmlspecialchars($msg['message']) ?> (<?= $msg['date_envoi'] ?>)
                    <!-- Bouton Répondre -->
                    <a href="repondre.php?spam_id=<?= $msg['spam_id'] ?>" class="btn-repondre">Répondre</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
