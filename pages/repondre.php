<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "❌ Vous devez être connecté pour répondre à un message.";
    exit;
}

$expediteur_id = $_SESSION['user_id']; // L'utilisateur connecté
$contact_id = null; // Le destinataire, à récupérer à partir de spam
$objet = "";
$destinataire_nom = "";
$destinataire_prenom = "";

// Vérifier si un message est sélectionné pour répondre
if (isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    // Récupérer l'expéditeur et le contact_id de la table spam
    $stmt = $pdo->prepare("SELECT s.expediteur_id, s.contact_id, m.objet, u.nom, u.prenom
                           FROM spam s
                           JOIN messages m ON m.id = s.message_id
                           JOIN utilisateurs u ON u.id = s.expediteur_id
                           WHERE s.message_id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($message) {
        // Préparer les variables pour la réponse
        $objet = "Re: " . htmlspecialchars($message['objet']); // Préfixer l'objet avec "Re:"
        $contact_id = $message['contact_id']; // Le contact_id dans spam est l'expéditeur pour la réponse
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
    $nouveau_message = $_POST['message'];

    // Insérer la réponse dans la table messages
    $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, contact_id, message, objet, date_envoi)
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$expediteur_id, $contact_id, $nouveau_message, $objet]);

    echo "✅ Réponse envoyée avec succès.";

    // Redirection vers la boîte de réception
    header("Location: boite_reception.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répondre au message</title>
</head>
<body>
    <h2>Répondre à <?= $destinataire_nom ?> <?= $destinataire_prenom ?> </h2>
    <form method="POST">
        <label for="message">Votre réponse :</label><br>
        <textarea name="message" id="message" rows="4" cols="50" required></textarea><br><br>
        <button type="submit">Envoyer la réponse</button>
    </form>
</body>
</html>
