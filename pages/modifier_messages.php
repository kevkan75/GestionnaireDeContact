<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'ID du message est passé dans l'URL
if (!isset($_GET['message_id']) || !is_numeric($_GET['message_id'])) {
    header("Location: messages_envoyes.php?error=message_id_manquant");
    exit();
}

$message_id = $_GET['message_id'];

// Récupérer le message à modifier avec fichier et audio
try {
    $sql = "SELECT m.id, m.message, m.objet, m.fichier, m.audio, u.nom, u.prenom 
            FROM messages m 
            JOIN contacts c ON m.contact_id = c.id 
            JOIN utilisateurs u ON c.utilisateur_contact = u.id 
            WHERE m.id = ? AND m.expediteur_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$message_id, $user_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        header("Location: messages_envoyes.php?error=message_introuvable");
        exit();
    }
} catch (PDOException $e) {
    $error = "❌ Erreur lors de la récupération du message : " . $e->getMessage();
}

// Gestion de la modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_message = trim($_POST['message']);
    $new_objet = trim($_POST['objet']);
    $new_fichier = $message['fichier']; // Garder l'ancien fichier par défaut
    $new_audio = $message['audio'];     // Garder l'ancien audio par défaut

    $dossier_base = __DIR__ . "/../fichier_vocal/$message_id";
    if (!file_exists($dossier_base)) {
        mkdir($dossier_base, 0777, true) or die("❌ Impossible de créer le dossier de base.");
    }

    // Gestion du nouveau fichier joint
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $nom_fichier = basename($_FILES['fichier']['name']);
        $new_fichier = "$dossier_base/$nom_fichier";
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $new_fichier)) {
            if ($message['fichier'] && $message['fichier'] !== $new_fichier && file_exists($message['fichier'])) {
                unlink($message['fichier']);
            }
        } else {
            $error = "❌ Échec de l'upload du fichier joint.";
        }
    }

    // Gestion du nouvel audio
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $mime_type = $_FILES['audio']['type'];
        $extensions = [
            'audio/webm' => 'webm',
            'audio/mp4' => 'mp4',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav'
        ];
        $extension = $extensions[$mime_type] ?? 'webm';
        $nom_audio = "message_vocal_$message_id.$extension";
        $new_audio = "$dossier_base/$nom_audio";
        if (move_uploaded_file($_FILES['audio']['tmp_name'], $new_audio)) {
            if ($message['audio'] && $message['audio'] !== $new_audio && file_exists($message['audio'])) {
                unlink($message['audio']);
            }
        } else {
            $error = "❌ Échec de l'upload de l'audio.";
        }
    }

    if (!isset($error)) {
        try {
            $sql_update = "UPDATE messages SET message = ?, objet = ?, fichier = ?, audio = ? WHERE id = ? AND expediteur_id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$new_message, $new_objet, $new_fichier, $new_audio, $message_id, $user_id]);
            
            $_SESSION['success_message'] = "✅ Message modifié avec succès !";
            header("Location: messages_envoyes.php");
            exit();
        } catch (PDOException $e) {
            $error = "❌ Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Modifier un Message</title>
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

        /* Comètes violet-turquoise */
        .cosmic-comet {
            position: absolute;
            width: 3px;
            height: 3px;
            background: linear-gradient(45deg, #a663cc, #00d4b4);
            border-radius: 50%;
            box-shadow: 0 0 8px 3px rgba(0, 212, 180, 0.5);
            animation: cometFlow linear infinite;
            opacity: 0.6;
        }

        @keyframes cometFlow {
            0% { transform: translate(0, 0) rotate(-45deg); opacity: 0.6; }
            70% { opacity: 0.4; }
            100% { transform: translate(600px, 400px) rotate(-45deg); opacity: 0; }
        }

        .modify-container {
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

        .input-group input[type="text"],
        .input-group textarea,
        .input-group input[type="file"] {
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

        .input-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #f0a500;
            outline: none;
        }

        .existing-file, .existing-audio {
            color: #ffd60a;
            font-size: 14px;
            margin-top: 5px;
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
            margin-top: 15px;
        }

        .btn:hover {
            background: #f0a500;
        }

        .record-controls {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        #recordButton, #stopButton {
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 15px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #recordButton:hover, #stopButton:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        #recordButton.recording {
            background: #ff4040;
            border-color: #ff4040;
        }

        #stopButton:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .error {
            color: #a663cc;
            font-size: 18px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .modify-container {
                width: 100%;
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .input-group label {
                font-size: 16px;
            }

            .btn, #recordButton, #stopButton {
                font-size: 14px;
                padding: 10px 15px;
            }

            .record-controls {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Comètes violet-turquoise -->
    <div class="cosmic-comet" style="top: 15%; left: 10%; animation-duration: 1.5s;"></div>
    <div class="cosmic-comet" style="top: 35%; left: 80%; animation-duration: 2s;"></div>
    <div class="cosmic-comet" style="top: 65%; left: 20%; animation-duration: 1.7s;"></div>
    <div class="cosmic-comet" style="top: 85%; left: 70%; animation-duration: 2.2s;"></div>

    <div class="modify-container">
        <h2>✏️ Modifier le Message</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="modifier_messages.php?message_id=<?php echo $message_id; ?>" method="POST" enctype="multipart/form-data" id="modifyForm">
            <div class="input-group">
                <label for="objet">Objet :</label>
                <input type="text" id="objet" name="objet" value="<?php echo htmlspecialchars($message['objet']); ?>">
            </div>

            <div class="input-group">
                <label for="message">Message :</label>
                <textarea id="message" name="message" required><?php echo htmlspecialchars($message['message']); ?></textarea>
            </div>

            <div class="input-group">
                <label for="fichier">Fichier joint :</label>
                <input type="file" id="fichier" name="fichier">
                <?php if ($message['fichier']): ?>
                    <p class="existing-file">Fichier actuel : <?php echo htmlspecialchars(basename($message['fichier'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="audio">Message vocal :</label>
                <div class="record-controls">
                    <button type="button" id="recordButton">🎤 Enregistrer Audio</button>
                    <button type="button" id="stopButton" disabled>⏹ Arrêter</button>
                </div>
                <?php if ($message['audio']): ?>
                    <p class="existing-audio">Audio actuel : <?php echo htmlspecialchars(basename($message['audio'])); ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn">✅ Sauvegarder</button>
        </form>
    </div>

    <script>
        let mediaRecorder;
        let audioChunks = [];

        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            alert("❌ Utilisez HTTPS ou localhost pour l'audio.");
        } else {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    mediaRecorder = new MediaRecorder(stream);

                    mediaRecorder.ondataavailable = event => {
                        audioChunks.push(event.data);
                    };

                    mediaRecorder.onstop = () => {
                        const mimeType = mediaRecorder.mimeType || 'audio/webm';
                        const audioBlob = new Blob(audioChunks, { type: mimeType });
                        const extension = mimeType.split('/')[1].split(';')[0];
                        const audioFile = new File([audioBlob], `message_vocal.${extension}`, { type: mimeType });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(audioFile);

                        const oldAudioInput = document.querySelector('input[name="audio"]');
                        if (oldAudioInput) oldAudioInput.remove();

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'file';
                        hiddenInput.name = 'audio';
                        hiddenInput.files = dataTransfer.files;
                        document.getElementById('modifyForm').appendChild(hiddenInput);

                        audioChunks = [];
                    };
                })
                .catch(err => {
                    alert("❌ Erreur microphone : " + err.name + " - " + err.message + "\nVérifiez les permissions ou utilisez localhost.");
                });
        }

        const recordButton = document.getElementById('recordButton');
        const stopButton = document.getElementById('stopButton');
        const form = document.getElementById('modifyForm');

        recordButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state === 'inactive') {
                audioChunks = [];
                mediaRecorder.start();
                recordButton.classList.add('recording');
                recordButton.textContent = '🎤 En cours...';
                stopButton.disabled = false;
                recordButton.disabled = true;
            }
        });

        stopButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                recordButton.classList.remove('recording');
                recordButton.textContent = '🎤 Enregistrer Audio';
                stopButton.disabled = true;
                recordButton.disabled = false;
            }
        });

        form.addEventListener('submit', (e) => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                e.preventDefault();
                alert("❌ Arrêtez l'enregistrement avant de sauvegarder.");
            }
        });
    </script>
</body>
</html>