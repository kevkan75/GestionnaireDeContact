<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour voir vos messages.");
}

// Requête pour récupérer les messages reçus par l'utilisateur connecté
$sql = "SELECT m.*, 
               u.email, 
               u.nom AS expediteur_nom, 
               u.prenom AS expediteur_prenom, 
               c.nom AS contact_nom, 
               c.prenom AS contact_prenom
        FROM messages m
        LEFT JOIN utilisateurs u ON m.expediteur_id = u.id
        LEFT JOIN contacts c ON m.contact_id = c.id
        WHERE m.contact_id IN 
            (SELECT id FROM contacts WHERE utilisateur_contact = :user_id)
        AND m.expediteur_id = c.utilisateur_id
        ORDER BY m.date_envoi DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Base URL pour accéder aux fichiers
$base_url = "http://localhost:8888/projet_php/"; // Ajuste selon ton serveur MAMP
$base_path = '/Applications/MAMP/htdocs/projet_php/'; // Chemin absolu de ton projet
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📥 Boîte de Réception</title>
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

        /* Particules flottantes violet-turquoise */
        .floating-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: radial-gradient(circle, rgba(166, 99, 204, 0.8), rgba(0, 212, 180, 0.5));
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0, 212, 180, 0.6);
            animation: floatParticle linear infinite;
            opacity: 0.7;
        }

        @keyframes floatParticle {
            0% { transform: translateY(0) scale(1); opacity: 0.7; }
            50% { opacity: 0.4; }
            100% { transform: translateY(-200px) scale(0.8); opacity: 0; }
        }

        .inbox-container {
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

        .message-list {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .message-item {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #a663cc;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            transition: all 0.3s ease;
            text-align: left;
        }

        .message-item:hover {
            background: rgba(255, 214, 10, 0.2); /* Jaune subtil */
            border-color: #f0a500;
            transform: translateY(-3px);
        }

        .message-item p {
            margin: 8px 0;
            font-size: 16px;
        }

        .message-item strong {
            color: #00d4b4;
        }

        .message-item a {
            color: #ffd60a;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .message-item a:hover {
            color: #f0a500;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 15px;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .no-messages {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        audio {
            width: 100%;
            margin-top: 10px;
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.5));
        }

        .actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        @media (max-width: 600px) {
            .inbox-container {
                width: 100%;
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .message-item {
                font-size: 14px;
                padding: 10px;
            }

            .btn {
                font-size: 14px;
                padding: 8px 15px;
            }

            .actions {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Particules flottantes -->
    <div class="floating-particle" style="top: 20%; left: 15%; animation-duration: 4s;"></div>
    <div class="floating-particle" style="top: 40%; left: 75%; animation-duration: 5s;"></div>
    <div class="floating-particle" style="top: 60%; left: 25%; animation-duration: 3.5s;"></div>
    <div class="floating-particle" style="top: 80%; left: 65%; animation-duration: 4.5s;"></div>

    <div class="inbox-container">
        <h2>📥 Boîte de Réception</h2>

        <?php if (empty($messages)): ?>
            <p class="no-messages">😢 Aucun message reçu pour l'instant.</p>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                        <p><strong>De :</strong> <?php echo htmlspecialchars($message['expediteur_prenom'] . " " . $message['expediteur_nom']); ?> (<?php echo htmlspecialchars($message['email']); ?>)</p>
                        <p><strong>À :</strong> <?php echo htmlspecialchars($message['contact_prenom'] . " " . $message['contact_nom']); ?></p>
                        <p><strong>Date :</strong> <?php echo htmlspecialchars($message['date_envoi']); ?></p>
                        <p><strong>Objet :</strong> <?php echo htmlspecialchars($message['objet'] ?? 'Sans objet'); ?></p>
                        <p><strong>Message texte :</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($message['message'] ?? 'Aucun message')); ?></p>

                        <!-- Affichage du fichier joint s'il existe -->
                        <?php if (!empty($message['fichier'])): ?>
                            <p><strong>Pièce jointe :</strong></p>
                            <?php
                            $message_id = $message['id'];
                            $file_path = $message['fichier'];
                            $file_relative = str_replace($base_path, '', $file_path);
                            $file_url = $base_url . $file_relative;
                            $file_name = basename($file_path);
                            ?>
                            <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank"><?php echo htmlspecialchars($file_name); ?></a>
                        <?php endif; ?>

                        <!-- Affichage du message vocal s'il existe -->
                        <?php if (!empty($message['audio'])): ?>
                            <p><strong>Message vocal :</strong></p>
                            <?php
                            $message_id = $message['id'];
                            $audio_path = $message['audio'];
                            $audio_relative = str_replace($base_path, '', $audio_path);
                            $audio_url = $base_url . $audio_relative;
                            $audio_extension = pathinfo($audio_path, PATHINFO_EXTENSION);
                            $mime_types = [
                                'webm' => 'audio/webm',
                                'mp3' => 'audio/mpeg',
                                'wav' => 'audio/wav',
                                'ogg' => 'audio/ogg'
                            ];
                            $mime_type = $mime_types[$audio_extension] ?? 'audio/webm';
                            ?>
                            <audio controls preload="auto">
                                <source src="<?php echo htmlspecialchars($audio_url); ?>" type="<?php echo $mime_type; ?>">
                                <p>Votre navigateur ne peut pas lire ce fichier audio. <a href="<?php echo htmlspecialchars($audio_url); ?>" target="_blank">Téléchargez-le ici</a>.</p>
                            </audio>
                            <p><a href="<?php echo htmlspecialchars($audio_url); ?>" target="_blank">Ouvrir le fichier audio brut</a></p>
                        <?php endif; ?>

                        <div class="actions">
                            <a href="repondre_message.php?message_id=<?php echo $message['id']; ?>" class="btn">📩 Répondre</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="envoyer_message.php" class="btn">✉️ Nouveau message</a>
        </div>
    </div>

    <script>
        // Ajouter dynamiquement plus de particules flottantes
        function createFloatingParticle() {
            const particle = document.createElement('div');
            particle.className = 'floating-particle';
            particle.style.top = Math.random() * 80 + 10 + '%';
            particle.style.left = Math.random() * 80 + 10 + '%';
            particle.style.animationDuration = (Math.random() * 2 + 3) + 's';
            document.body.appendChild(particle);
            setTimeout(() => particle.remove(), 5000);
        }

        setInterval(createFloatingParticle, 1000); // Nouvelle particule toutes les secondes
    </script>
</body>
</html>