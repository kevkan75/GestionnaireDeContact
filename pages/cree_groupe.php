<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour créer un groupe.");
}

$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Vérifie si le formulaire a été soumis pour créer un groupe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'])) {
    $nom_groupe = $_POST['nom'];  // Nom du groupe

    // Insérer le groupe dans la base de données
    $sql_insert = "INSERT INTO groupe (nom, createur_id, date_creation) VALUES (?, ?, NOW())";
    $stmt_insert = $pdo->prepare($sql_insert);

    if ($stmt_insert->execute([$nom_groupe, $user_id])) {
        // Récupérer l'ID du groupe nouvellement créé
        $groupe_id = $pdo->lastInsertId();

        // Ajouter l'utilisateur connecté comme membre du groupe avec le rôle "admin"
        $sql_member = "INSERT INTO membres_groupe (groupe_id, user_id, role) VALUES (?, ?, 'admin')";
        $stmt_member = $pdo->prepare($sql_member);
        $stmt_member->execute([$groupe_id, $user_id]);

        $_SESSION['success_message'] = "✅ Groupe créé avec succès !";

        // Rediriger vers la page de sélection des contacts pour ajouter des membres
        header("Location: ajout_contact_groupe.php?groupe_id=" . $groupe_id);
        exit();
    } else {
        $_SESSION['error_message'] = "❌ Erreur lors de la création du groupe.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥 Créer un Groupe</title>
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

        /* Étoiles filantes modifiées */
        .shooting-star {
            position: absolute;
            width: 3px;
            height: 3px;
            background: linear-gradient(45deg, #fff, #ffd60a); /* Gradient blanc à jaune */
            border-radius: 50%;
            box-shadow: 0 0 8px 3px rgba(255, 215, 0, 0.6);
            animation: shoot linear infinite;
        }

        @keyframes shoot {
            0% {
                transform: translate(0, 0) rotate(-60deg); /* Angle plus prononcé */
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translate(600px, 400px) rotate(-60deg); /* Distance ajustée */
                opacity: 0;
            }
        }

        .create-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 400px;
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

        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .input-group label {
            color: #00d4b4;
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #a663cc;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            box-sizing: border-box;
            font-size: 16px;
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
            .create-container {
                width: 100%;
                padding: 15px;
            }

            h2 {
                font-size: 24px;
            }

            .btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Génération des étoiles filantes -->
    <div class="shooting-star" style="top: 15%; left: 25%; animation-duration: 1.2s;"></div>
    <div class="shooting-star" style="top: 35%; left: 55%; animation-duration: 1.7s;"></div>
    <div class="shooting-star" style="top: 55%; left: 65%; animation-duration: 1.4s;"></div>
    <div class="shooting-star" style="top: 75%; left: 35%; animation-duration: 2s;"></div>

    <div class="create-container">
        <h2>👥 Créer un Groupe</h2>

        <!-- Affiche le message d'erreur ou de succès -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="error"><?php echo $_SESSION['error_message']; ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Formulaire pour créer un groupe -->
        <form action="cree_groupe.php" method="POST">
            <div class="input-group">
                <label for="nom">Nom du groupe :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <button type="submit" class="btn">✅ Créer le groupe</button>
            <a href="dashboard.php" class="back-link">Retour au tableau de bord</a>
        </form>
    </div>

    <script>
        // Ajouter dynamiquement plus d'étoiles filantes
        function createShootingStar() {
            const star = document.createElement('div');
            star.className = 'shooting-star';
            star.style.top = Math.random() * 80 + '%';
            star.style.left = Math.random() * 80 + '%';
            star.style.animationDuration = (Math.random() * 1 + 1.2) + 's';
            document.body.appendChild(star);
            setTimeout(() => star.remove(), 2200); // Supprime après animation
        }

        setInterval(createShootingStar, 800); // Nouvelle étoile toutes les 0.8 secondes
    </script>
</body>
</html>