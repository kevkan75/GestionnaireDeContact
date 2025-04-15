<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour envoyer une demande d'ami.");
}

$utilisateur_id = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté

// Vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère l'email du formulaire
    $email = $_POST['email'];

    // Recherche un utilisateur avec cet email, en s'assurant que ce n'est pas l'utilisateur lui-même
    $sql = "SELECT id, nom, prenom FROM utilisateurs WHERE email = ? AND id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $utilisateur_id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contact) {
        $contact_id = $contact['id']; // Récupération de l'ID du contact trouvé

        // Vérifie que l'utilisateur ne s'ajoute pas lui-même
        if ($contact_id == $utilisateur_id) {
            $message = "❌ Vous ne pouvez pas vous ajouter vous-même.";
        } else {
            // Vérifie si le contact est déjà dans la liste des contacts
            $sql_check_contact = "SELECT * FROM contacts WHERE utilisateur_id = ? AND utilisateur_contact = ?";
            $stmt_check_contact = $pdo->prepare($sql_check_contact);
            $stmt_check_contact->execute([$utilisateur_id, $contact_id]);
            $existing_contact = $stmt_check_contact->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_contact) {
                $message = "❌ " . htmlspecialchars($contact['prenom']) . " " . htmlspecialchars($contact['nom']) . " fait déjà partie de vos contacts.";
            } else {
                // Vérifie si une demande d'ami a déjà été envoyée
                $sql_check_request = "SELECT * FROM demandes_ami WHERE demandeur_id = ? AND receveur_id = ?";
                $stmt_check_request = $pdo->prepare($sql_check_request);
                $stmt_check_request->execute([$utilisateur_id, $contact_id]);
                $existing_request = $stmt_check_request->fetch(PDO::FETCH_ASSOC);

                if ($existing_request) {
                    $message = "❌ Vous avez déjà envoyé une demande à cet utilisateur.";
                } else {
                    // Ajoute la demande d'ami
                    $sql_insert = "INSERT INTO demandes_ami (demandeur_id, receveur_id) VALUES (?, ?)";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    if ($stmt_insert->execute([$utilisateur_id, $contact_id])) {
                        $_SESSION['success_message'] = "✅ Demande d'ami envoyée avec succès.";
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $message = "❌ Erreur lors de l'envoi de la demande.";
                    }
                }
            }
        }
    } else {
        $message = "❌ Aucun utilisateur trouvé avec cet email.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Ajouter un Contact</title>
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

        /* Étoiles filantes */
        .shooting-star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 5px 2px rgba(255, 255, 255, 0.8);
            animation: shoot linear infinite;
        }

        @keyframes shoot {
            0% {
                transform: translate(0, 0) rotate(-45deg);
                opacity: 1;
            }
            70% {
                opacity: 1;
            }
            100% {
                transform: translate(500px, 500px) rotate(-45deg);
                opacity: 0;
            }
        }

        .add-container {
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

        h3 {
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
            .add-container {
                width: 100%;
                padding: 15px;
            }

            h3 {
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
    <div class="shooting-star" style="top: 10%; left: 20%; animation-duration: 1s;"></div>
    <div class="shooting-star" style="top: 30%; left: 50%; animation-duration: 1.5s;"></div>
    <div class="shooting-star" style="top: 50%; left: 70%; animation-duration: 1.2s;"></div>
    <div class="shooting-star" style="top: 70%; left: 30%; animation-duration: 1.8s;"></div>

    <div class="add-container">
        <h3>🔍 Ajouter un Contact</h3>

        <!-- Affiche le message d'erreur ou de succès -->
        <?php if (isset($message)): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Formulaire pour rechercher un utilisateur -->
        <form action="ajouter.php" method="POST">
            <div class="input-group">
                <label>Email :</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit" class="btn">📩 Envoyer la demande</button>
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
            star.style.animationDuration = (Math.random() * 1 + 1) + 's';
            document.body.appendChild(star);
            setTimeout(() => star.remove(), 2000); // Supprime après animation
        }

        setInterval(createShootingStar, 1000); // Nouvelle étoile toutes les secondes
    </script>
</body>
</html>