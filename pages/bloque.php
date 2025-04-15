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
$bloques = [];
$error = "";

// Gestion du déblocage
if (isset($_GET['debloquer']) && is_numeric($_GET['debloquer'])) {
    $bloque_id = $_GET['debloquer'];
    
    try {
        $sql_bloque = "SELECT utilisateur_id, utilisateur_bloque FROM bloque WHERE id = ? AND utilisateur_id = ?";
        $stmt_bloque = $pdo->prepare($sql_bloque);
        $stmt_bloque->execute([$bloque_id, $user_id]);
        $bloque = $stmt_bloque->fetch(PDO::FETCH_ASSOC);
        
        if ($bloque) {
            $sql_user = "SELECT nom, prenom, email FROM utilisateurs WHERE id = ?";
            $stmt_user = $pdo->prepare($sql_user);
            $stmt_user->execute([$bloque['utilisateur_bloque']]);
            $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            if ($user_info) {
                $sql_insert = "INSERT INTO contacts (utilisateur_id, utilisateur_contact, nom, prenom, email) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    $user_id,
                    $bloque['utilisateur_bloque'],
                    $user_info['nom'],
                    $user_info['prenom'],
                    $user_info['email']
                ]);
                
                $sql_delete = "DELETE FROM bloque WHERE id = ? AND utilisateur_id = ?";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->execute([$bloque_id, $user_id]);
            } else {
                $error = "❌ Utilisateur bloqué introuvable dans la table utilisateurs.";
            }
        }
    } catch (PDOException $e) {
        $error = "❌ Erreur lors du déblocage : " . $e->getMessage();
    }
}

// Récupération des utilisateurs bloqués
try {
    $sql = "SELECT b.id, u.nom, u.prenom 
            FROM bloque b 
            JOIN utilisateurs u ON b.utilisateur_bloque = u.id 
            WHERE b.utilisateur_id = ? 
            ORDER BY u.nom ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Erreur lors de la récupération des bloqués : " . $e->getMessage();
}

// Préparation des données pour affichage
$bloques_data = [];

if (!empty($error)) {
    $bloques_data['error'] = $error;
} elseif (!empty($bloques)) {
    foreach ($bloques as $bloque) {
        $bloques_data[] = [
            'id' => $bloque['id'],
            'nom' => htmlspecialchars($bloque['nom']),
            'prenom' => htmlspecialchars($bloque['prenom'])
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚫 Utilisateurs Bloqués</title>
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

        .bloques-container {
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

        .bloques-list {
            list-style: none;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .bloque-item {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #a663cc;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .bloque-item:hover {
            background: #ffd60a;
            color: #2a1b3d;
        }

        .bloque-name {
            font-size: 18px;
            color: #fff;
        }

        .bloque-item:hover .bloque-name {
            color: #2a1b3d;
        }

        .bloque-actions a {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 15px;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .bloque-actions a:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .error, .no-bloques {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .bloques-container {
                width: 100%;
                padding: 15px;
            }

            h3 {
                font-size: 24px;
            }

            .bloque-item {
                font-size: 14px;
                padding: 10px;
            }

            .bloque-actions a {
                font-size: 14px;
                padding: 8px 15px;
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

    <div class="bloques-container">
        <h3>🚫 Utilisateurs Bloqués</h3>

        <?php if (isset($bloques_data['error'])): ?>
            <p class="error"><?php echo $bloques_data['error']; ?></p>
        <?php elseif (empty($bloques_data)): ?>
            <p class="no-bloques">😊 Aucun utilisateur bloqué pour le moment.</p>
        <?php else: ?>
            <ul class="bloques-list">
                <?php foreach ($bloques_data as $bloque): ?>
                    <li class="bloque-item">
                        <span class="bloque-name"><?php echo $bloque['nom'] . " " . $bloque['prenom']; ?></span>
                        <div class="bloque-actions">
                            <a href="?debloquer=<?php echo $bloque['id']; ?>">✅ Débloquer</a>
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