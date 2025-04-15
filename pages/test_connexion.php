<?php
// Inclure la configuration de la base de données (en remontant d'un niveau)
include __DIR__ . '/../config/database.php';

try {
    // Tester la connexion à la base de données
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Vérifier si la connexion fonctionne
    echo "✅ Connexion à la base de données réussie.";
} catch (PDOException $e) {
    // Afficher l'erreur si la connexion échoue
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage();
}
?>

