<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté.");
}

// Vérifie si contact_id est passé dans l'URL
if (!isset($_GET['contact_id']) || !is_numeric($_GET['contact_id'])) {
    die("❌ Contact invalide.");
}
$contact_id = (int)$_GET['contact_id'];

// Vérifie si le contact existe
function contactExiste($contact_id, $utilisateur_id, $pdo) {
    $sql = "SELECT COUNT(*) FROM contacts WHERE id = :contact_id AND utilisateur_id = :utilisateur_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':contact_id' => $contact_id, ':utilisateur_id' => $utilisateur_id]);
    return $stmt->fetchColumn() > 0;
}

// Fonction pour gérer l'envoi du message
function envoyerMessage($expediteur_id, $contact_id, $objet, $message, $fichier_extra, $audio_blob, $pdo) {
    if (!contactExiste($contact_id, $expediteur_id, $pdo)) {
        return "❌ Contact invalide.";
    }

    $dossier_base = __DIR__ . "/../fichier_vocal";
    if (!file_exists($dossier_base)) {
        mkdir($dossier_base, 0777, true) or die("❌ Impossible de créer le dossier de base.");
    }
    if (!is_writable($dossier_base)) {
        die("❌ Dossier $dossier_base non accessible en écriture.");
    }

    try {
        // Insérer le message
        $sql = "INSERT INTO messages (expediteur_id, contact_id, objet, message, date_envoi) 
                VALUES (:expediteur_id, :contact_id, :objet, :message, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':expediteur_id' => $expediteur_id,
            ':contact_id' => $contact_id,
            ':objet' => $objet,
            ':message' => $message
        ]);

        // Récupérer l'ID du message
        $message_id = $pdo->lastInsertId();
        $sous_dossier = "$dossier_base/$message_id";

        // Créer le sous-dossier
        if (!file_exists($sous_dossier)) {
            mkdir($sous_dossier, 0777, true) or die("❌ Impossible de créer le sous-dossier.");
        }

        // Gérer le fichier joint (colonne fichier)
        $chemin_fichier = null;
        if ($fichier_extra && isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $nom_fichier = basename($_FILES['fichier']['name']);
            $chemin_fichier = "$sous_dossier/$nom_fichier";
            if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin_fichier)) {
                return "❌ Échec du déplacement du fichier joint.";
            }
        }

        // Gérer l'audio (colonne audio)
        $chemin_audio = null;
        if ($audio_blob && isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            // Déterminer l'extension en fonction du type MIME
            $mime_type = $_FILES['audio']['type'];
            $extensions = [
                'audio/webm' => 'webm',
                'audio/mp4' => 'mp4',
                'audio/mpeg' => 'mp3',
                'audio/ogg' => 'ogg',
                'audio/wav' => 'wav'
            ];
            $extension = $extensions[$mime_type] ?? 'webm'; // Par défaut webm
            $nom_audio = "message_vocal_$message_id.$extension";
            $chemin_audio = "$sous_dossier/$nom_audio";
            if (!move_uploaded_file($_FILES['audio']['tmp_name'], $chemin_audio)) {
                return "❌ Échec du déplacement de l'audio. Vérifiez les permissions.";
            }
            if (!file_exists($chemin_audio) || filesize($chemin_audio) == 0) {
                return "❌ Le fichier audio n'a pas été correctement sauvegardé ou est vide.";
            }
        } else {
            if ($audio_blob && isset($_FILES['audio'])) {
                return "❌ Erreur d'upload audio : Code " . $_FILES['audio']['error'];
            }
        }

        // Mettre à jour les colonnes fichier et audio
        if ($chemin_fichier || $chemin_audio) {
            $sql_update = "UPDATE messages SET fichier = :fichier, audio = :audio WHERE id = :id";
            $stmt = $pdo->prepare($sql_update);
            $stmt->execute([
                ':fichier' => $chemin_fichier ?: null,
                ':audio' => $chemin_audio ?: null,
                ':id' => $message_id
            ]);
        }

        return "✅ Message envoyé (ID: $message_id)";
    } catch (Exception $e) {
        return "❌ Erreur : " . $e->getMessage();
    }
}

// Gestion de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expediteur_id = $_SESSION['user_id'];
    $objet = $_POST['objet'] ?? '';
    $message = $_POST['message'] ?? '';
    $fichier_extra = 'fichier';
    $audio_blob = 'audio';

    if ($message) {
        $resultat = envoyerMessage($expediteur_id, $contact_id, $objet, $message, $fichier_extra, $audio_blob, $pdo);
        echo $resultat;
    } else {
        echo "❌ Message requis.";
    }
    exit;
}

// Récupérer les infos du contact
$sql = "SELECT nom, prenom FROM contacts WHERE id = ? AND utilisateur_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$contact_id, $_SESSION['user_id']]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contact) {
    die("❌ Contact non trouvé.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 Envoyer un Message</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #2a1b3d, #44318d);
            color: #fff;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .message-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            text-align: center;
        }
        h2 {
            font-size: 32px;
            color: #f0a500;
            margin: 0 0 20px;
        }
        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #00d4b4;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            box-sizing: border-box;
        }
        input[type="submit"], button {
            padding: 12px 20px;
            background: #ffd60a;
            color: #2a1b3d;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }
        input[type="submit"]:hover, button:hover {
            background: #f0a500;
            transform: scale(1.05);
        }
        #recordButton.recording {
            background: #ff4040;
        }
        #stopButton:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2>💬 Message à <?php echo htmlspecialchars($contact['nom'] . ' ' . $contact['prenom']); ?></h2>
        <form method="POST" enctype="multipart/form-data" id="messageForm">
            <input type="text" name="objet" placeholder="Objet du message..." value="">
            <textarea name="message" placeholder="Ton message ici..." required></textarea>
            <label>Fichier joint: <input type="file" name="fichier"></label>
            <div>
                <button type="button" id="recordButton">🎤 Enregistrer Audio</button>
                <button type="button" id="stopButton" disabled>⏹ Arrêter</button>
            </div>
            <input type="submit" value="Envoyer">
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
                    // Pas de mimeType spécifié, laisse le navigateur choisir
                    mediaRecorder = new MediaRecorder(stream);

                    mediaRecorder.ondataavailable = event => {
                        audioChunks.push(event.data);
                    };

                    mediaRecorder.onstop = () => {
                        // Utilise le type MIME détecté par MediaRecorder ou un défaut
                        const mimeType = mediaRecorder.mimeType || 'audio/webm';
                        const audioBlob = new Blob(audioChunks, { type: mimeType });
                        const extension = mimeType.split('/')[1].split(';')[0]; // Ex: webm, mp4
                        const audioFile = new File([audioBlob], `message_vocal.${extension}`, { type: mimeType });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(audioFile);

                        const oldAudioInput = document.querySelector('input[name="audio"]');
                        if (oldAudioInput) oldAudioInput.remove();

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'file';
                        hiddenInput.name = 'audio';
                        hiddenInput.files = dataTransfer.files;
                        document.getElementById('messageForm').appendChild(hiddenInput);

                        audioChunks = [];
                    };
                })
                .catch(err => {
                    alert("❌ Erreur microphone : " + err.name + " - " + err.message + "\nVérifiez les permissions ou utilisez localhost.");
                });
        }

        const recordButton = document.getElementById('recordButton');
        const stopButton = document.getElementById('stopButton');
        const form = document.getElementById('messageForm');

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
                alert("❌ Arrêtez l'enregistrement avant d'envoyer.");
            }
        });
    </script>
</body>
</html>