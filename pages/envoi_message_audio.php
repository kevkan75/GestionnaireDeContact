<?php
include __DIR__ . '/../config/database.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour envoyer un message.");
}

// Vérifier si un fichier audio a été envoyé
if (!isset($_FILES['audio']) || $_FILES['audio']['error'] === UPLOAD_ERR_NO_FILE) {
    die("❌ Aucun fichier audio trouvé.");
}

// Gestion des erreurs d'upload
$audioFile = $_FILES['audio'];
if ($audioFile['error'] !== UPLOAD_ERR_OK) {
    die("❌ Erreur lors du téléchargement du fichier : " . $audioFile['error']);
}

// Vérifier le type de fichier
if ($audioFile['type'] !== 'audio/wav') {
    die("❌ Le fichier doit être un audio au format WAV.");
}

// Vérifier la taille du fichier (exemple : limite à 10 Mo)
$maxFileSize = 10 * 1024 * 1024; // 10 Mo en octets
if ($audioFile['size'] > $maxFileSize) {
    die("❌ Le fichier audio est trop volumineux (max 10 Mo).");
}

// Définir un chemin unique pour le fichier
$audioFilePath = __DIR__ . '/../uploads/audio/' . time() . '_' . uniqid() . '.wav';

// Déplacer le fichier téléchargé
if (move_uploaded_file($audioFile['tmp_name'], $audioFilePath)) {
    // Vérifier si groupe_id est présent
    if (!isset($_POST['groupe_id']) || empty($_POST['groupe_id'])) {
        die("❌ ID du groupe manquant.");
    }

    // Insérer le message audio dans la base de données
    $sql = "INSERT INTO messages_groupe (groupe_id, expediteur_id, message, audio_path, date_envoi) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['groupe_id'], $_SESSION['user_id'], '', $audioFilePath]);

    echo "✅ Message audio envoyé avec succès.";
} else {
    echo "❌ Erreur lors de l'envoi du fichier audio.";
}
?>