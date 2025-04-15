<?php
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour voir la discussion.");
}

$user_id = $_SESSION['user_id'];
$groupe_id = $_GET['groupe_id'] ?? null;

if (!$groupe_id) {
    die("❌ Groupe non spécifié.");
}

// Vérifie si l'utilisateur appartient au groupe
$sql_check = "SELECT * FROM membres_groupe WHERE groupe_id = ? AND user_id = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$groupe_id, $user_id]);
if ($stmt_check->rowCount() == 0) {
    die("❌ Vous n'êtes pas membre de ce groupe.");
}

// Récupérer les messages
$sql_messages = "SELECT m.message, m.date_envoi, u.nom AS expediteur_nom 
                 FROM messages_groupe m
                 JOIN users u ON m.expediteur_id = u.id
                 WHERE m.groupe_id = ?
                 ORDER BY m.date_envoi ASC";
$stmt_messages = $pdo->prepare($sql_messages);
$stmt_messages->execute([$groupe_id]);
$messages = $stmt_messages->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion du groupe</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Messages du groupe</h2>

    <ul>
        <?php foreach ($messages as $message): ?>
            <li><strong><?= htmlspecialchars($message['expediteur_nom']) ?> :</strong> <?= htmlspecialchars($message['message']) ?> (<?= $message['date_envoi'] ?>)</li>
        <?php endforeach; ?>
    </ul>

    <br>
    <a href="message_groupe.php?groupe_id=<?= $groupe_id ?>">Envoyer un message</a>
    <br>
    <a href="mes_groupes.php">Retour aux groupes</a>
</body>
</html>
