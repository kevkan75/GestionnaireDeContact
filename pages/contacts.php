<?php
session_start();
include "../config/database.php";

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté

// Initialiser les variables
$contacts = [];
$error = "";

// Gestion du blocage
if (isset($_GET['bloquer']) && is_numeric($_GET['bloquer'])) {
    $contact_id = $_GET['bloquer'];
    
    try {
        // Récupérer les infos du contact avant suppression
        $sql_contact = "SELECT utilisateur_id, utilisateur_contact FROM contacts WHERE id = ? AND utilisateur_id = ?";
        $stmt_contact = $pdo->prepare($sql_contact);
        $stmt_contact->execute([$contact_id, $user_id]);
        $contact = $stmt_contact->fetch(PDO::FETCH_ASSOC);
        
        if ($contact) {
            // Supprimer les messages associés au contact
            $sql_delete_messages = "DELETE FROM messages WHERE contact_id = ?";
            $stmt_delete_messages = $pdo->prepare($sql_delete_messages);
            $stmt_delete_messages->execute([$contact_id]);
            
            // Ajouter à la table bloque
            $sql_bloque = "INSERT INTO bloque (utilisateur_id, utilisateur_bloque) VALUES (?, ?)";
            $stmt_bloque = $pdo->prepare($sql_bloque);
            $stmt_bloque->execute([$user_id, $contact['utilisateur_contact']]);
            
            // Supprimer de la table contacts
            $sql_delete = "DELETE FROM contacts WHERE id = ? AND utilisateur_id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$contact_id, $user_id]);
            
            header("Location: contacts.php?success=contact_bloque");
            exit();
        }
    } catch (PDOException $e) {
        $error = "❌ Erreur lors du blocage : " . $e->getMessage();
    }
}

// Récupération des contacts de l'utilisateur connecté depuis la base de données
try {
    $sql = "SELECT id, nom, prenom, email FROM contacts WHERE utilisateur_id = ? ORDER BY nom ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Erreur lors de la récupération des contacts.";
}

// Préparation des données pour affichage
$contacts_data = [];

if (!empty($error)) {
    $contacts_data['error'] = $error;
} elseif (!empty($contacts)) {
    foreach ($contacts as $contact) {
        $contacts_data[] = [
            'id' => $contact['id'],
            'nom' => htmlspecialchars($contact['nom']),
            'prenom' => htmlspecialchars($contact['prenom']),
            'email' => htmlspecialchars($contact['email'])
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📞 Liste des Contacts</title>
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

        /* Comètes rouges plus claires */
        .red-comet {
            position: absolute;
            width: 4px;
            height: 4px;
            background: linear-gradient(45deg, #ff6347, #ff9999);
            border-radius: 50%;
            box-shadow: 0 0 10px 4px rgba(255, 99, 71, 0.7);
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

        .contacts-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 600px;
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

        .contacts-list {
            list-style: none;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .contact-item {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #a663cc;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            transition: background 0.3s ease;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .contact-item:hover {
            background: #ffd60a;
            color: #2a1b3d;
        }

        .contact-info {
            flex-grow: 1;
        }

        .contact-name {
            font-size: 18px;
            color: #fff;
        }

        .contact-email {
            font-size: 14px;
            color: #00d4b4;
        }

        .contact-item:hover .contact-name,
        .contact-item:hover .contact-email {
            color: #2a1b3d;
        }

        .contact-actions {
            display: flex;
            gap: 10px;
        }

        .contact-actions a {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 15px;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .contact-actions a:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .block-btn {
            border-color: #ff4040;
        }

        .block-btn:hover {
            background: #ff4040;
            border-color: #f0a500;
        }

        .error, .no-contacts {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        .success {
            color: #00d4b4;
            font-size: 18px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .contacts-container {
                width: 100%;
                padding: 15px;
            }

            h3 {
                font-size: 24px;
            }

            .contact-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px;
            }

            .contact-actions {
                margin-top: 10px;
                flex-direction: column;
                width: 100%;
            }

            .contact-actions a {
                font-size: 14px;
                padding: 8px 15px;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Comètes rouges -->
    <div class="red-comet" style="top: 20%; left: 15%; animation-duration: 1.3s;"></div>
    <div class="red-comet" style="top: 40%; left: 60%; animation-duration: 1.8s;"></div>
    <div class="red-comet" style="top: 60%; left: 25%; animation-duration: 1.5s;"></div>
    <div class="red-comet" style="top: 80%; left: 70%; animation-duration: 2.1s;"></div>

    <div class="contacts-container">
        <h3>📞 Liste des Contacts</h3>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'contact_bloque'): ?>
            <p class="success">✅ Contact bloqué avec succès !</p>
        <?php endif; ?>

        <?php if (isset($contacts_data['error'])): ?>
            <p class="error"><?php echo $contacts_data['error']; ?></p>
        <?php elseif (empty($contacts_data)): ?>
            <p class="no-contacts">😢 Aucun contact disponible pour le moment.</p>
        <?php else: ?>
            <ul class="contacts-list">
                <?php foreach ($contacts_data as $contact): ?>
                    <li class="contact-item">
                        <div class="contact-info">
                            <div class="contact-name"><?php echo $contact['nom'] . " " . $contact['prenom']; ?></div>
                            <div class="contact-email"><?php echo $contact['email']; ?></div>
                        </div>
                        <div class="contact-actions">
                            <a href="appeler.php?contact_id=<?php echo $contact['id']; ?>" class="call-btn">📞 Appeler</a>
                            <a href="?bloquer=<?php echo $contact['id']; ?>" class="block-btn" onclick="return confirm('Voulez-vous vraiment bloquer ce contact ?');">🚫 Bloquer</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <script>
        // Ajouter dynamiquement plus de comètes rouges
        function createRedComet() {
            const comet = document.createElement('div');
            comet.className = 'red-comet';
            comet.style.top = Math.random() * 80 + '%';
            comet.style.left = Math.random() * 80 + '%';
            comet.style.animationDuration = (Math.random() * 1 + 1.2) + 's';
            document.body.appendChild(comet);
            setTimeout(() => comet.remove(), 2500);
        }

        setInterval(createRedComet, 900);
    </script>
</body>
</html>