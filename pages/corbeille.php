<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour voir la corbeille.");
}

$user_id = $_SESSION['user_id'];

// Récupérer les messages supprimés
$sql = "
    SELECT m.id, m.expediteur_id, m.contact_id, m.message, m.date_envoi, m.objet
    FROM messages m
    JOIN corbeille c ON m.id = c.message_id
    WHERE m.contact_id = ?
    ORDER BY c.date_ajout DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Corbeille</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>🗑️ Messages supprimés</h2>
    <?php if (!$messages): ?>
        <p>❌ Aucun message dans la corbeille.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($messages as $msg): ?>
                <li><strong><?= htmlspecialchars($msg['objet']) ?></strong> - <?= htmlspecialchars($msg['message']) ?> (<?= $msg['date_envoi'] ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
