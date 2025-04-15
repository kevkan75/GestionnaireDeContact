<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET["id"])) {
    $contact_id = $_GET["id"];
    $user_id = $_SESSION["user_id"];

    // Vérifier que le contact appartient bien à l'utilisateur connecté
    $sql = "DELETE FROM Contacts WHERE id = ? AND utilisateur_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$contact_id, $user_id])) {
        echo "✅ Contact supprimé avec succès ! <a href='dashboard.php'>Retour</a>";
    } else {
        echo "❌ Erreur lors de la suppression.";
    }
}
?>
