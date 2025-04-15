<?php
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Connecte-toi pour rejoindre la party !");
}

$user_id = $_SESSION['user_id'];
$groupe_id = $_GET['groupe_id'] ?? null;

if (!$groupe_id) {
    die("❌ Woops, t’as pas choisi de groupe !");
}

// Vérifie si l'utilisateur appartient au groupe
$sql_check = "SELECT * FROM membres_groupe WHERE groupe_id = ? AND user_id = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$groupe_id, $user_id]);

if ($stmt_check->rowCount() == 0) {
    die("❌ T’es pas dans ce crew, désolé !");
}

// Récupérer le nom du groupe
$sql_groupe = "SELECT nom FROM groupe WHERE id = ?";
$stmt_groupe = $pdo->prepare($sql_groupe);
$stmt_groupe->execute([$groupe_id]);
$groupe_nom = $stmt_groupe->fetchColumn();

// Récupérer les messages du groupe
$sql_messages = "SELECT m.message, m.date_envoi, u.nom AS expediteur_nom, m.expediteur_id 
                 FROM messages_groupe m
                 JOIN utilisateurs u ON m.expediteur_id = u.id
                 WHERE m.groupe_id = ?
                 ORDER BY m.date_envoi ASC";
$stmt_messages = $pdo->prepare($sql_messages);
$stmt_messages->execute([$groupe_id]);
$messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

// Envoi d'un message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $sql_insert = "INSERT INTO messages_groupe (groupe_id, expediteur_id, message, date_envoi) VALUES (?, ?, ?, NOW())";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$groupe_id, $user_id, $message]);
        header("Location: message_groupe.php?groupe_id=" . $groupe_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 <?= htmlspecialchars($groupe_nom) ?> - Discussion de groupe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #2a1b3d 0%, #44318d 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Étoiles filantes blanches */
        .star {
            position: absolute;
            width: 3px;
            height: 3px;
            background: linear-gradient(90deg, #ffffff, #a3e4ff);
            border-radius: 50%;
            box-shadow: 0 0 8px 2px rgba(255, 255, 255, 0.5);
            animation: starFlow linear infinite;
        }

        @keyframes starFlow {
            0% {
                transform: translate(0, 0) rotate(-60deg);
                opacity: 1;
            }
            70% {
                opacity: 0.7;
            }
            100% {
                transform: translate(600px, 400px) rotate(-60deg);
                opacity: 0;
            }
        }

        .chat-container {
            width: 90%;
            max-width: 800px;
            height: 85vh;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(90deg, #a663cc, #f0a500);
            color: #fff;
            padding: 16px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            border-radius: 16px 16px 0 0;
            position: relative;
        }

        .chat-header h2 {
            margin: 0;
            font-size: 1.5em;
        }

        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fb;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .message {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 15px;
            line-height: 1.5;
            position: relative;
        }

        .sent {
            align-self: flex-end;
            background: #00d4b4;
            color: #fff;
            border: 1px solid #00b89c;
        }

        .received {
            align-self: flex-start;
            background: #ffffff;
            color: #2a1b3d;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .message strong {
            font-size: 13px;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }

        .message-time {
            font-size: 11px;
            color: #888;
            margin-top: 6px;
            text-align: right;
        }

        .no-messages {
            color: #a663cc;
            font-size: 16px;
            text-align: center;
            margin: auto;
            font-style: italic;
        }

        .chat-footer {
            display: flex;
            padding: 12px;
            background: #ffffff;
            border-top: 1px solid #e0e0e0;
        }

        .chat-footer textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #d0d0d0;
            border-radius: 12px;
            background: #f8f9fb;
            color: #333;
            font-size: 15px;
            resize: none;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .chat-footer textarea:focus {
            border-color: #f0a500;
        }

        .chat-footer button {
            margin-left: 12px;
            padding: 12px 20px;
            background: #ffd60a;
            color: #2a1b3d;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .chat-footer button:hover {
            background: #f0a500;
            transform: translateY(-1px);
        }

        .chat-actions {
            padding: 12px;
            display: flex;
            justify-content: center;
            gap: 12px;
            background: #f8f9fb;
            border-bottom: 1px solid #e0e0e0;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease, transform 0.2s ease;
            border: none;
        }

        .add-btn {
            background: #00d4b4;
            color: #fff;
        }

        .add-btn:hover {
            background: #00b89c;
            transform: translateY(-1px);
        }

        .remove-btn {
            background: #a663cc;
            color: #fff;
        }

        .remove-btn:hover {
            background: #8e4cb6;
            transform: translateY(-1px);
        }

        .quit-btn {
            background: #ff3399;
            color: #fff;
        }

        .quit-btn:hover {
            background: #e02e87;
            transform: translateY(-1px);
        }

        .back-btn {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            padding: 8px;
            background: #ffffff;
            color: #2a1b3d;
            border-radius: 50%;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.2s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .back-btn:hover {
            background: #ffd60a;
            transform: translateY(-50%) scale(1.1);
        }

        @media (max-width: 600px) {
            .chat-container {
                width: 100%;
                height: 90vh;
                border-radius: 12px;
            }

            .chat-header h2 {
                font-size: 18px;
            }

            .message {
                font-size: 14px;
                padding: 10px 14px;
            }

            .chat-footer textarea, .chat-footer button {
                font-size: 14px;
            }

            .action-btn {
                font-size: 13px;
                padding: 8px 16px;
            }

            .chat-actions {
                flex-wrap: wrap;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Étoiles filantes -->
    <div class="star" style="top: 10%; left: 15%; animation-duration: 1.5s;"></div>
    <div class="star" style="top: 30%; left: 60%; animation-duration: 2.0s;"></div>
    <div class="star" style="top: 50%; left: 25%; animation-duration: 1.7s;"></div>
    <div class="star" style="top: 70%; left: 70%; animation-duration: 2.2s;"></div>

    <div class="chat-container">
        <div class="chat-header">
            <a href="mes_groupes.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>💬 <?= htmlspecialchars($groupe_nom) ?></h2>
        </div>

        <div class="chat-actions">
            <a href="ajout_contact_groupe.php?groupe_id=<?= $groupe_id ?>" class="action-btn add-btn">
                <i class="fas fa-user-plus"></i> Ajouter un membre
            </a>
            <a href="supprimer_membre.php?groupe_id=<?= $groupe_id ?>" class="action-btn remove-btn">
                <i class="fas fa-user-minus"></i> Supprimer un membre
            </a>
            <a href="quitter_groupe.php?groupe_id=<?= $groupe_id ?>" class="action-btn quit-btn">
                <i class="fas fa-sign-out-alt"></i> Quitter le groupe
            </a>
        </div>

        <div class="chat-box">
            <?php if (empty($messages)): ?>
                <p class="no-messages">🎤 Lancez la conversation !</p>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= $message['expediteur_id'] == $user_id ? 'sent' : 'received' ?>">
                        <strong><?= htmlspecialchars($message['expediteur_nom']) ?> :</strong><br>
                        <?= nl2br(htmlspecialchars($message['message'])) ?><br>
                        <small class="message-time"><?= date('H:i', strtotime($message['date_envoi'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form class="chat-footer" method="POST">
            <textarea name="message" placeholder="Tapez votre message..." rows="1" required></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>

    <script>
        // Ajouter dynamiquement plus d'étoiles filantes
        function createStar() {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.top = Math.random() * 80 + '%';
            star.style.left = Math.random() * 80 + '%';
            star.style.animationDuration = (Math.random() * 0.7 + 1.5) + 's';
            document.body.appendChild(star);
            setTimeout(() => star.remove(), 2500);
        }

        setInterval(createStar, 1000);

        // Auto-scroll vers le bas du chat
        const chatBox = document.querySelector('.chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>