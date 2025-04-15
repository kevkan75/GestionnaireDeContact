<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Connecte-toi pour voir tes messages, yo !");
}

$user_id = $_SESSION['user_id'];

// Requête pour récupérer uniquement les messages reçus par l'utilisateur connecté
$sql = "SELECT m.*, u.email, u.nom, u.prenom 
        FROM messages m
        LEFT JOIN utilisateurs u ON m.expediteur_id = u.id
        WHERE m.contact_id = ?
        ORDER BY m.date_envoi DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📩 Ta Boîte de Réception</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Comic Sans MS', 'Arial', sans-serif;
            background: linear-gradient(to bottom, #2a1b3d, #44318d); /* Fond violet-bleu */
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            border-right: 3px dashed #f0a500; /* Bordure orange */
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.5);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            animation: slideInLeft 0.5s ease;
        }

        @keyframes slideInLeft {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(0); }
        }

        .sidebar h2 {
            font-size: 28px;
            color: #f0a500; /* Orange vif */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
            margin: 0 0 20px;
            text-align: center;
        }

        .sidebar a {
            text-decoration: none;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid #a663cc; /* Violet clair */
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background: #ffd60a; /* Jaune vif */
            color: #2a1b3d; /* Violet sombre */
            border-color: #f0a500; /* Orange */
            transform: scale(1.05);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
        }

        .sidebar i {
            margin-right: 10px;
            font-size: 20px;
            color: #00d4b4; /* Turquoise */
        }

        .sidebar a:hover i {
            color: #2a1b3d; /* Violet sombre */
        }

        .content {
            flex: 1;
            margin-left: 270px; /* Espace pour la sidebar */
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow-y: auto;
        }

        .messages-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            border: 2px dashed #f0a500; /* Bordure orange */
            width: 90%;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .messages-section h3 {
            font-size: 28px;
            color: #f0a500; /* Orange vif */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
            margin: 0 0 20px;
            text-align: center;
            border-bottom: 2px dashed #00d4b4; /* Turquoise */
        }

        .messages-list {
            list-style-type: none;
            padding: 0;
        }

        .message-item {
            background: #ffd60a; /* Jaune vif comme Snapchat */
            color: #2a1b3d; /* Violet sombre */
            margin: 15px 0;
            padding: 15px;
            border-radius: 20px;
            border: 2px solid #a663cc; /* Violet clair */
            max-width: 70%;
            align-self: flex-start; /* Aligné à gauche */
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            0% { transform: translateX(-20px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }

        .message-item:hover {
            background: #00d4b4; /* Turquoise au survol */
            border-color: #f0a500; /* Orange */
            transform: scale(1.02);
        }

        .message-header {
            font-weight: bold;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .message-subject {
            font-size: 14px;
            color: #2a1b3d; /* Violet sombre */
        }

        .message-header span.date {
            font-size: 12px;
            color: rgba(42, 27, 61, 0.8); /* Violet sombre léger */
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .file-link {
            margin-top: 10px;
            font-size: 14px;
            color: #f0a500; /* Orange */
            display: block;
        }

        .file-link:hover {
            color: #ff3399; /* Rose vif */
        }

        .no-messages {
            color: #a663cc; /* Violet clair */
            font-size: 18px;
            text-align: center;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 220px;
            }

            .messages-section {
                width: 100%;
            }
        }

        @media (max-width: 500px) {
            .sidebar {
                width: 150px;
                padding: 15px;
            }

            .sidebar h2 {
                font-size: 24px;
            }

            .sidebar a {
                font-size: 16px;
                padding: 10px;
            }

            .content {
                margin-left: 170px;
            }

            .message-item {
                max-width: 85%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<!-- MENU LATÉRAL -->
<div class="sidebar">
    <h2>💌 Messagerie</h2>
    <a href="message_detail.php" class="menu-item"><i class="fas fa-inbox"></i> Boîte de Réception</a>
    <a href="spam.php" class="menu-item"><i class="fas fa-exclamation-triangle"></i> Spams</a>
    <a href="favoris.php" class="menu-item"><i class="fas fa-star"></i> Favoris</a>
    <a href="corbeille.php" class="menu-item"><i class="fas fa-trash"></i> Corbeille</a>
    <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i> Retour au QG</a>
</div>

<!-- CONTENU PRINCIPAL -->
<div class="content">
    <div class="messages-section">
        <h3>📩 Tes Messages Reçus</h3>
        <ul class="messages-list">
            <?php if (empty($messages)): ?>
                <li class="no-messages">😢 Rien dans ta boîte pour l’instant, t’es trop discret !</li>
            <?php else: ?>
                <?php foreach ($messages as $row): ?>
                    <?php
                    $email = isset($row['email']) ? htmlspecialchars($row['email']) : "Inconnu";
                    $nom = isset($row['nom']) ? htmlspecialchars($row['nom']) : "Nom inconnu";
                    $prenom = isset($row['prenom']) ? htmlspecialchars($row['prenom']) : "Prénom inconnu";
                    $date = isset($row['date_envoi']) ? date('H:i - d/m', strtotime($row['date_envoi'])) : "Date inconnue";
                    $message_id = $row['id'];
                    $objet = isset($row['objet']) ? htmlspecialchars($row['objet']) : "Pas d’objet";
                    $fichier = isset($row['fichier']) ? htmlspecialchars($row['fichier']) : null;
                    ?>
                    <li class="message-item">
                        <a href="message_detail.php?id=<?= $message_id ?>">
                            <div class="message-header">
                                <span>De : <?= "$prenom $nom ($email)" ?></span>
                                <span class="date"><?= $date ?></span>
                            </div>
                            <div class="message-subject">Objet : <?= $objet ?></div>
                            <?php if ($fichier): ?>
                                <div class="file-link">
                                    Fichier : <a href="<?= $fichier ?>" target="_blank">Check ça !</a>
                                </div>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

</body>
</html>