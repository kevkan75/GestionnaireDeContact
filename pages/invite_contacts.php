<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "❌ Vous devez être connecté pour ajouter des contacts au groupe.";
    exit;
}

$createur_id = $_SESSION['user_id']; // L'ID de l'utilisateur qui ajoute les contacts

// Récupérer l'ID du groupe depuis l'URL
if (isset($_GET['groupe_id'])) {
    $groupe_id = $_GET['groupe_id'];
} else {
    echo "❌ Groupe introuvable.";
    exit;
}

// Récupérer les contacts de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE utilisateur_id = ?");
$stmt->execute([$createur_id]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un contact au groupe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_id'])) {
    $contact_id = $_POST['contact_id'];

    // Afficher l'ID du contact pour vérifier sa valeur
    var_dump($contact_id);

    // Vérifier si le contact existe dans la table 'utilisateurs'
    $stmt_check_user = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ?");
    $stmt_check_user->execute([$contact_id]);

    // Vérification si l'utilisateur existe
    if ($stmt_check_user->rowCount() == 0) {
        $message = "❌ Ce contact n'existe pas dans la table des utilisateurs.";
        echo $message;  // Affichage de l'erreur pour déboguer
    } else {
        // Vérifier si le contact est déjà membre du groupe
        $stmt_check_member = $pdo->prepare("SELECT * FROM membres_groupe WHERE groupe_id = ? AND user_id = ?");
        $stmt_check_member->execute([$groupe_id, $contact_id]);

        if ($stmt_check_member->rowCount() > 0) {
            $message = "❌ Ce contact est déjà membre du groupe.";
        } else {
            // Ajouter le contact au groupe dans la table membres_groupe
            $stmt_add_member = $pdo->prepare("INSERT INTO membres_groupe (groupe_id, user_id, role, date_ajout) VALUES (?, ?, 'membre', NOW())");
            $stmt_add_member->execute([$groupe_id, $contact_id]);

            $message = "✅ Le contact a été ajouté au groupe avec succès.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter des contacts au groupe</title>
    <style>
        body {
            background: linear-gradient(to bottom, #0a0f2c, #1b1f3a);
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .add-member-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            width: 50%;
            text-align: center;
        }
        .btn {
            margin-top: 10px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="add-member-section">
    <h2>Ajouter un contact au groupe</h2>

    <!-- Affiche le message d'erreur ou de succès -->
    <?php if (isset($message)) echo "<p>$message</p>"; ?>

    <h3>Contacts disponibles :</h3>
    <ul>
        <?php foreach ($contacts as $contact): ?>
            <li>
                <?php echo htmlspecialchars($contact['nom'] . ' ' . $contact['prenom']); ?>
                <form action="invite_contacts.php?groupe_id=<?php echo $groupe_id; ?>" method="POST" style="display:inline;">
                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                    <button type="submit" class="btn">Ajouter au groupe</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
