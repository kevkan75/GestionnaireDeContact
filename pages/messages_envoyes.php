<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Initialiser les variables
$messages = [];
$error = "";

// Gestion de la suppression
if (isset($_GET['delete_message_id']) && is_numeric($_GET['delete_message_id'])) {
    $delete_message_id = $_GET['delete_message_id'];
    try {
        $sql_delete = "DELETE FROM messages WHERE id = ? AND expediteur_id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([$delete_message_id, $user_id]);
        
        $_SESSION['success_message'] = "✅ Message supprimé avec succès !";
        header("Location: messages_envoyes.php");
        exit();
    } catch (PDOException $e) {
        $error = "❌ Erreur lors de la suppression du message : " . $e->getMessage();
    }
}

// Récupérer les messages envoyés par l'utilisateur connecté
try {
    $sql = "SELECT m.id, m.message, m.objet, m.date_envoi, u.nom, u.prenom 
            FROM messages m 
            JOIN contacts c ON m.contact_id = c.id 
            JOIN utilisateurs u ON c.utilisateur_contact = u.id 
            WHERE m.expediteur_id = ? 
            ORDER BY m.date_envoi DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Erreur lors de la récupération des messages : " . $e->getMessage();
}

// Préparation des données pour affichage
$messages_data = [];
if (!empty($error)) {
    $messages_data['error'] = $error;
} elseif (!empty($messages)) {
    foreach ($messages as $message) {
        $messages_data[] = [
            'id' => $message['id'],
            'message' => htmlspecialchars($message['message']),
            'objet' => htmlspecialchars($message['objet']),
            'date_envoi' => $message['date_envoi'],
            'nom' => htmlspecialchars($message['nom']),
            'prenom' => htmlspecialchars($message['prenom'])
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📤 Messages Envoyés</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Comic Sans MS', 'Arial', sans-serif;
            background: linear-gradient(to bottom, #2a1b3d, #44318d);
            color: #fff;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Comètes de magma subtiles */
        .magma-comet {
            position: absolute;
            width: 3px;
            height: 3px;
            background: linear-gradient(45deg, #ff4500, #ffd60a);
            border-radius: 50%;
            box-shadow: 0 0 8px 3px rgba(255, 69, 0, 0.5);
            animation: cometFlow linear infinite;
            opacity: 0.6;
        }

        @keyframes cometFlow {
            0% { transform: translate(0, 0) rotate(-45deg); opacity: 0.6; }
            70% { opacity: 0.4; }
            100% { transform: translate(600px, 400px) rotate(-45deg); opacity: 0; }
        }

        .messages-container {
            width: 90%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        h2 {
            font-size: 28px;
            color: #f0a500;
            margin: 0 0 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            border-bottom: 2px solid #a663cc;
            padding-bottom: 10px;
        }

        .messages-list {
            list-style: none;
            padding: 0;
            max-height: 600px;
            overflow-y: auto;
        }

        .message-item {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #a663cc;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-item:hover {
            background: rgba(255, 214, 10, 0.2); /* Jaune subtil au hover */
            border-color: #f0a500;
            transform: translateY(-3px);
        }

        .message-info {
            flex-grow: 1;
            text-align: left;
        }

        .message-content {
            font-size: 16px;
            color: #fff;
            margin-bottom: 5px;
        }

        .message-destinataire {
            font-style: italic;
            color: #ffd60a;
            font-size: 14px;
        }

        .message-date {
            font-size: 12px;
            color: #00d4b4;
        }

        .message-actions {
            display: flex;
            gap: 10px;
        }

        .message-actions a {
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 10px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .message-actions a:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .btn-delete {
            border-color: #ff4040;
        }

        .btn-delete:hover {
            background: #ff4040;
            border-color: #f0a500;
        }

        .error {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        .no-messages {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        .success-message {
            background: rgba(0, 212, 180, 0.2);
            color: #00d4b4;
            padding: 15px;
            border-radius: 15px;
            margin: 20px auto;
            max-width: 400px;
            text-align: center;
            font-weight: bold;
            border: 2px dashed #f0a500;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 600px) {
            .messages-container {
                width: 100%;
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .message-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px;
            }

            .message-actions {
                margin-top: 10px;
                flex-direction: column;
                gap: 5px;
                width: 100%;
            }

            .message-actions a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Comètes de magma subtiles -->
    <div class="magma-comet" style="top: 15%; left: 10%; animation-duration: 1.5s;"></div>
    <div class="magma-comet" style="top: 35%; left: 80%; animation-duration: 2s;"></div>
    <div class="magma-comet" style="top: 65%; left: 20%; animation-duration: 1.7s;"></div>
    <div class="magma-comet" style="top: 85%; left: 70%; animation-duration: 2.2s;"></div>

    <div class="messages-container">
        <h2>📤 Messages Envoyés</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($messages_data['error'])): ?>
            <p class="error"><?php echo $messages_data['error']; ?></p>
        <?php elseif (empty($messages_data)): ?>
            <p class="no-messages">😢 Aucun message envoyé pour le moment.</p>
        <?php else: ?>
            <ul class="messages-list">
                <?php foreach ($messages_data as $message): ?>
                    <li class="message-item">
                        <div class="message-info">
                            <div class="message-content"><?php echo $message['objet'] ? $message['objet'] : '[Sans objet]'; ?> : <?php echo $message['message']; ?></div>
                            <div class="message-destinataire">À : <?php echo $message['nom'] . " " . $message['prenom']; ?></div>
                            <div class="message-date"><?php echo $message['date_envoi']; ?></div>
                        </div>
                        <div class="message-actions">
                            <a href="modifier_messages.php?message_id=<?php echo $message['id']; ?>">✏️ Modifier</a>
                            <a href="messages_envoyes.php?delete_message_id=<?php echo $message['id']; ?>" class="btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');">🗑️ Supprimer</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <script>
        // Ajouter dynamiquement plus de comètes de magma
        function createMagmaComet() {
            const comet = document.createElement('div');
            comet.className = 'magma-comet';
            comet.style.top = Math.random() * 80 + '%';
            comet.style.left = Math.random() * 80 + '%';
            comet.style.animationDuration = (Math.random() * 1 + 1.5) + 's';
            document.body.appendChild(comet);
            setTimeout(() => comet.remove(), 3000);
        }

        setInterval(createMagmaComet, 1500); // Nouvelle comète toutes les 1.5 secondes

        // Cacher le message de succès après 3 secondes
        setTimeout(() => {
            const message = document.querySelector('.success-message');
            if (message) message.style.display = 'none';
        }, 3000);
    </script>
</body>
</html>