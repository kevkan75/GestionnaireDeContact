<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "❌ Vous devez être connecté pour répondre à un message.";
    exit;
}

$expediteur_id = $_SESSION['user_id']; // L'utilisateur connecté
$destinataire_id = null;
$objet = "";
$destinataire_nom = "";
$destinataire_prenom = "";

// Vérifier si un message est sélectionné pour répondre
if (isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    // Récupérer les informations du message d'origine et du destinataire
    $stmt = $pdo->prepare("SELECT m.objet, u.id as destinataire_id, u.nom, u.prenom 
                           FROM messages m
                           JOIN utilisateurs u ON m.expediteur_id = u.id
                           WHERE m.id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($message) {
        $objet = "Re: " . htmlspecialchars($message['objet']);
        $destinataire_id = $message['destinataire_id'];
        $destinataire_nom = htmlspecialchars($message['nom']);
        $destinataire_prenom = htmlspecialchars($message['prenom']);
    } else {
        echo "❌ Message introuvable.";
        exit;
    }
} else {
    echo "❌ Aucun message sélectionné.";
    exit;
}

// Traitement du formulaire d'envoi de réponse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nouveau_message = $_POST['message'] ?? '';
    $audio_file = null;
    $attachment_file = null;

    // Gestion du fichier audio
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] == UPLOAD_ERR_OK) {
        $audio_dir = __DIR__ . '/../uploads/audio/';
        if (!is_dir($audio_dir)) mkdir($audio_dir, 0777, true);
        $audio_file = $audio_dir . uniqid() . '.' . pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['audio']['tmp_name'], $audio_file);
    }

    // Gestion du fichier joint
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == UPLOAD_ERR_OK) {
        $file_dir = __DIR__ . '/../uploads/files/';
        if (!is_dir($file_dir)) mkdir($file_dir, 0777, true);
        $attachment_file = $file_dir . uniqid() . '.' . pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['fichier']['tmp_name'], $attachment_file);
    }

    // Insérer la réponse dans la base de données
    $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, contact_id, message, objet, date_envoi, audio, fichier)
                           VALUES (?, ?, ?, ?, NOW(), ?, ?)");
    $stmt->execute([$expediteur_id, $destinataire_id, $nouveau_message, $objet, $audio_file, $attachment_file]);

    $_SESSION['success_message'] = "✅ Réponse envoyée avec succès.";
    header("Location: boite_reception.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✉️ Répondre à un Message</title>
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

        .reply-container {
            width: 90%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            position: relative;
            z-index: 1;
            text-align: left;
        }

        h2 {
            font-size: 28px;
            color: #f0a500;
            margin: 0 0 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            border-bottom: 2px solid #a663cc;
            padding-bottom: 10px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            font-size: 18px;
            color: #00d4b4;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #a663cc;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .input-group input[readonly] {
            background: rgba(255, 255, 255, 0.1);
            color: #ffd60a;
            cursor: not-allowed;
        }

        .input-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #f0a500;
            outline: none;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #ffd60a;
            color: #2a1b3d;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #f0a500;
        }

        .back-link {
            color: #a663cc;
            text-decoration: none;
            font-size: 16px;
            margin-top: 20px;
            display: inline-block;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #f0a500;
        }

        @media (max-width: 600px) {
            .reply-container {
                width: 100%;
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .input-group label {
                font-size: 16px;
            }

            .btn {
                font-size: 14px;
                padding: 10px 15px;
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

    <div class="reply-container">
        <h2>✉️ Répondre à <?php echo "$destinataire_prenom $destinataire_nom"; ?></h2>
        
        <form action="repondre_message.php?message_id=<?php echo $message_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="objet">Objet :</label>
                <input type="text" id="objet" name="objet" value="<?php echo $objet; ?>" readonly>
            </div>
            <div class="input-group">
                <label for="message">Votre message :</label>
                <textarea name="message" id="message" required></textarea>
            </div>
            <button type="submit" class="btn">📤 Envoyer la réponse</button>
            <a href="message_detail.php" class="back-link">⬅ Retour à la boîte de réception</a>
        </form>
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
    </script>
</body>
</html>