<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour ajouter des contacts.");
}

$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté
$groupe_id = $_GET['groupe_id']; // ID du groupe à partir de l'URL

// Récupérer les contacts de l'utilisateur connecté qui ne sont pas déjà dans le groupe
$sql_contacts = "
    SELECT u.id AS contact_id, u.nom AS contact_nom, u.prenom AS contact_prenom, u.email
    FROM contacts c
    JOIN utilisateurs u ON c.utilisateur_contact = u.id
    WHERE c.utilisateur_id = ? 
    AND u.id NOT IN (
        SELECT user_id 
        FROM membres_groupe 
        WHERE groupe_id = ?
    )
";

$stmt_contacts = $pdo->prepare($sql_contacts);
$stmt_contacts->execute([$user_id, $groupe_id]);
$contacts = $stmt_contacts->fetchAll();

// Si aucun contact n'est trouvé
if (empty($contacts)) {
    echo "❌ Aucun contact disponible à ajouter à ce groupe.";
    exit;
}

// Ajouter les contacts sélectionnés au groupe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contacts'])) {
    $contacts_selected = $_POST['contacts']; // Tableau contenant les contacts sélectionnés

    foreach ($contacts_selected as $contact_id) {
        // Vérifier si le contact est déjà dans le groupe
        $stmt_check = $pdo->prepare("SELECT * FROM membres_groupe WHERE groupe_id = ? AND user_id = ?");
        $stmt_check->execute([$groupe_id, $contact_id]);

        if ($stmt_check->rowCount() == 0) {
            // Ajouter le contact au groupe avec un rôle par défaut 'membre'
            $sql_add_member = "INSERT INTO membres_groupe (groupe_id, user_id, role, date_ajout) VALUES (?, ?, 'membre', NOW())";
            $stmt_add_member = $pdo->prepare($sql_add_member);
            $stmt_add_member->execute([$groupe_id, $contact_id]);
        }
    }

    // Message de succès et redirection
    $_SESSION['success_message'] = "✅ Contacts ajoutés au groupe avec succès !";
    header("Location: dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌿 Ajouter des Contacts au Groupe</title>
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

        /* Comètes de magma pour un esprit naturel */
        .magma-comet {
            position: absolute;
            width: 4px;
            height: 4px;
            background: linear-gradient(45deg, #ff4500, #8b0000);
            border-radius: 50%;
            box-shadow: 0 0 10px 4px rgba(255, 69, 0, 0.7);
            animation: cometFlow linear infinite;
        }

        @keyframes cometFlow {
            0% {
                transform: translate(0, 0) rotate(-50deg);
                opacity: 1;
            }
            60% {
                opacity: 0.9;
            }
            100% {
                transform: translate(700px, 500px) rotate(-50deg);
                opacity: 0;
            }
        }

        .group-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        h2 {
            font-size: 32px;
            color: #f0a500;
            margin: 0 0 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        h3 {
            font-size: 20px;
            color: #00d4b4;
            margin: 0 0 15px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .input-group input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.3);
            cursor: pointer;
            accent-color: #a663cc; /* Couleur violette pour les checkbox */
        }

        .input-group label {
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #ffd60a;
            color: #2a1b3d;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }

        .btn:hover {
            background: #f0a500;
        }

        .error {
            color: #a663cc;
            font-size: 18px;
            margin: 20px 0;
        }

        .success-message {
            color: #00d4b4;
            font-size: 18px;
            margin: 20px 0;
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
            .group-container {
                width: 100%;
                padding: 15px;
            }

            h2 {
                font-size: 24px;
            }

            h3 {
                font-size: 18px;
            }

            .btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Comètes de magma -->
    <div class="magma-comet" style="top: 20%; left: 15%; animation-duration: 1.3s;"></div>
    <div class="magma-comet" style="top: 40%; left: 60%; animation-duration: 1.8s;"></div>
    <div class="magma-comet" style="top: 60%; left: 25%; animation-duration: 1.5s;"></div>
    <div class="magma-comet" style="top: 80%; left: 70%; animation-duration: 2.1s;"></div>

    <div class="group-container">
        <h2>🌿 Ajouter des Contacts au Groupe</h2>

        <!-- Affichage des messages -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="error"><?php echo $_SESSION['error_message']; ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Formulaire -->
        <form action="ajout_contact_groupe.php?groupe_id=<?php echo $groupe_id; ?>" method="POST">
            <h3>Sélectionnez les contacts :</h3>
            
            <?php foreach ($contacts as $contact): ?>
                <div class="input-group">
                    <input type="checkbox" name="contacts[]" value="<?php echo $contact['contact_id']; ?>" id="contact-<?php echo $contact['contact_id']; ?>">
                    <label for="contact-<?php echo $contact['contact_id']; ?>">
                        <?php echo htmlspecialchars($contact['contact_prenom']) . " " . htmlspecialchars($contact['contact_nom']); ?>
                    </label>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn">🌟 Ajouter au groupe</button>
            <a href="dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>
        </form>
    </div>

    <script>
        // Ajouter dynamiquement plus de comètes de magma
        function createMagmaComet() {
            const comet = document.createElement('div');
            comet.className = 'magma-comet';
            comet.style.top = Math.random() * 80 + '%';
            comet.style.left = Math.random() * 80 + '%';
            comet.style.animationDuration = (Math.random() * 1 + 1.2) + 's';
            document.body.appendChild(comet);
            setTimeout(() => comet.remove(), 2500); // Supprime après animation
        }

        setInterval(createMagmaComet, 900); // Nouvelle comète toutes les 0.9 secondes
    </script>
</body>
</html>