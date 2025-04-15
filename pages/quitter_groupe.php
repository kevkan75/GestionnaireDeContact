<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour quitter ce groupe.");
}

$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté
$groupe_id = $_GET['groupe_id'] ?? null;

// Vérifie si le groupe_id est spécifié
if (!$groupe_id) {
    die("❌ Groupe non spécifié.");
}

// Vérifie si l'utilisateur est membre du groupe
$stmt_check = $pdo->prepare("SELECT * FROM membres_groupe WHERE groupe_id = ? AND user_id = ?");
$stmt_check->execute([$groupe_id, $user_id]);

if ($stmt_check->rowCount() == 0) {
    die("❌ Vous n'êtes pas membre de ce groupe.");
}

// Supprime l'utilisateur du groupe
$sql_delete_member = "DELETE FROM membres_groupe WHERE groupe_id = ? AND user_id = ?";
$stmt_delete_member = $pdo->prepare($sql_delete_member);
$stmt_delete_member->execute([$groupe_id, $user_id]);

// Message de succès et redirection vers le dashboard
$_SESSION['success_message'] = "Vous avez quitté le groupe avec succès.";
header("Location: dashboard.php"); // Redirige vers le tableau de bord ou une autre page
exit();
