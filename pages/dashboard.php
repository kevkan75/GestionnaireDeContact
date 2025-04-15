<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

include __DIR__ . '/../config/database.php';

// Statistiques rapides
$stmt = $pdo->prepare("SELECT COUNT(*) as contacts FROM contacts WHERE utilisateur_id = ?");
$stmt->execute([$user_id]);
$contact_count = $stmt->fetch(PDO::FETCH_ASSOC)['contacts'];

$stmt = $pdo->prepare("SELECT COUNT(*) as messages FROM messages WHERE expediteur_id = ?");
$stmt->execute([$user_id]);
$message_count = $stmt->fetch(PDO::FETCH_ASSOC)['messages'];

$stmt = $pdo->prepare("SELECT COUNT(*) as inbox FROM messages WHERE contact_id = ?");
$stmt->execute([$user_id]);
$inbox_count = $stmt->fetch(PDO::FETCH_ASSOC)['inbox'];

$stmt = $pdo->prepare("SELECT COUNT(*) as demande FROM demandes_ami WHERE receveur_id = ?");
$stmt->execute([$user_id]);
$demande_count = $stmt->fetch(PDO::FETCH_ASSOC)['demandes'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as bloqués FROM bloque WHERE utilisateur_id = ?");
$stmt->execute([$user_id]);
$bloque_count = $stmt->fetch(PDO::FETCH_ASSOC)['bloques'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as groupes FROM groupe WHERE createur_id = ?");
$stmt->execute([$user_id]);
$groupe_count = $stmt->fetch(PDO::FETCH_ASSOC)['groupes'] ?? 0;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌌 QG de <?php echo htmlspecialchars($user_name); ?></title>
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

        /* Comètes de magma */
        .magma-comet {
            position: absolute;
            width: 5px;
            height: 5px;
            background: linear-gradient(45deg, #ff4500, #ffd60a);
            border-radius: 50%;
            box-shadow: 0 0 12px 5px rgba(255, 69, 0, 0.8);
            animation: cometFlow linear infinite;
        }

        @keyframes cometFlow {
            0% {
                transform: translate(0, 0) rotate(-45deg);
                opacity: 1;
            }
            70% {
                opacity: 0.9;
            }
            100% {
                transform: translate(800px, 600px) rotate(-45deg);
                opacity: 0;
            }
        }

        .dashboard {
            width: 90%;
            max-width: 1100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6);
            border: 4px dashed #f0a500;
            position: relative;
            z-index: 1;
            animation: glowPulse 2s infinite alternate;
        }

        @keyframes glowPulse {
            0% { box-shadow: 0 15px 30px rgba(240, 165, 0, 0.6); }
            100% { box-shadow: 0 15px 30px rgba(240, 165, 0, 0.9); }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(45deg, #a663cc, #f0a500);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3), transparent);
            animation: rotateGlow 10s infinite linear;
        }

        @keyframes rotateGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .header h1 {
            font-size: 40px;
            margin: 0;
            text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.8);
            animation: bounce 2s infinite;
            z-index: 2;
            position: relative;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-7px); }
        }

        .header p {
            font-size: 20px;
            margin: 10px 0 0;
            color: #ffd60a;
            z-index: 2;
            position: relative;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            border: 3px solid #a663cc;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.1);
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.6);
        }

        .stat-card i {
            font-size: 36px;
            margin-bottom: 15px;
            color: #00d4b4;
            transition: transform 0.4s ease;
        }

        .stat-card:hover i {
            color: #2a1b3d;
            transform: rotate(360deg);
        }

        .stat-card h3 {
            font-size: 20px;
            margin: 5px 0;
        }

        .stat-card p {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            color: #f0a500;
        }

        .stat-card:hover p {
            color: #2a1b3d;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .success-message {
            background: #00d4b4;
            color: #2a1b3d;
            padding: 20px;
            border-radius: 20px;
            margin: 25px auto;
            max-width: 500px;
            text-align: center;
            font-weight: bold;
            border: 3px dashed #f0a500;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 600px) {
            .dashboard {
                width: 100%;
                padding: 20px;
            }

            .header h1 {
                font-size: 30px;
            }

            .header p {
                font-size: 16px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card h3 {
                font-size: 18px;
            }

            .stat-card p {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Comètes de magma -->
    <div class="magma-comet" style="top: 10%; left: 20%; animation-duration: 1.2s;"></div>
    <div class="magma-comet" style="top: 30%; left: 70%; animation-duration: 1.7s;"></div>
    <div class="magma-comet" style="top: 50%; left: 40%; animation-duration: 1.4s;"></div>
    <div class="magma-comet" style="top: 80%; left: 60%; animation-duration: 2s;"></div>

    <div class="dashboard">
        <div class="header">
            <h1>🌌 Bienvenue, <?php echo htmlspecialchars($user_name); ?> !</h1>
            <p>Ton QG intergalactique est en orbite !</p>
        </div>

        <div class="stats">
            <a href="contacts.php" class="stat-card">
                <i class="fas fa-address-book"></i>
                <h3>Contacts</h3>
                <p><?php echo $contact_count; ?></p>
            </a>
            <a href="messages_envoyes.php" class="stat-card">
                <i class="fas fa-paper-plane"></i>
                <h3>Messages envoyés</h3>
                <p><?php echo $message_count; ?></p>
            </a>
            <a href="message_detail.php" class="stat-card">
                <i class="fas fa-inbox"></i>
                <h3>Boîte de Réception</h3>
                <p><?php echo $inbox_count; ?></p>
            </a>
            <a href="demande.php" class="stat-card">
                <i class="fas fa-user-plus"></i>
                <h3>Demandes d'Ajout</h3>
                <p><?php echo $demande_count; ?></p>
            </a>
            <a href="bloque.php" class="stat-card">
                <i class="fas fa-ban"></i>
                <h3>Utilisateurs Bloqués</h3>
                <p><?php echo $bloque_count; ?></p>
            </a>
            <a href="mes_groupes.php" class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Mes Crews</h3>
                <p><?php echo $groupe_count; ?></p>
            </a>
            <a href="envoyer_message.php" class="stat-card">
                <i class="fas fa-paper-plane"></i>
                <h3>Envoyer un Message</h3>
                <p>-</p>
            </a>
            <a href="ajouter.php" class="stat-card">
                <i class="fas fa-user-plus"></i>
                <h3>Ajouter un Pote</h3>
                <p>-</p>
            </a>
            <a href="cree_groupe.php" class="stat-card">
                <i class="fas fa-users-cog"></i>
                <h3>Créer un Crew</h3>
                <p>-</p>
            </a>
            <a href="logout.php" class="stat-card" style="border: 3px solid #ff3399;">
                <i class="fas fa-sign-out-alt"></i>
                <h3>Déconnexion</h3>
                <p>-</p>
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])) : ?>
            <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
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
            setTimeout(() => comet.remove(), 2500);
        }

        setInterval(createMagmaComet, 700); // Nouvelle comète toutes les 0.7 secondes

        // Cacher le message de succès après 3 secondes
        setTimeout(() => {
            const message = document.querySelector('.success-message');
            if (message) message.style.display = 'none';
        }, 3000);
    </script>
</body>
</html>