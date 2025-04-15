<?php
$host = "localhost"; 
$dbname = "ad"; // NOM DE LA BASE DE DONNÉES
$username = "KARIM"; // NOM D'UTILISATEUR
$password = "test"; // MOT DE PASSE

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "✅ Connexion réussie à la base de données !"; // Test de connexion
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}
?>

