<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Hé, connecte-toi pour voir tes crews !");
}

$user_id = $_SESSION['user_id'];

// Récupérer les groupes de l'utilisateur
$sql = "SELECT g.id AS groupe_id, g.nom AS groupe_nom 
        FROM groupe g
        JOIN membres_groupe mg ON g.id = mg.groupe_id
        WHERE mg.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$groupes = $stmt->fetchAll(PDO::FETCH_ASSOC); // FETCH_ASSOC pour cohérence
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎉 Mes Crews</title>
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

        /* Comètes bleues allongées */
        .cyan-comet {
            position: absolute;
            width: 8px;
            height: 2px;
            background: linear-gradient(90deg, #00d4ff, #ffffff);
            border-radius: 2px;
            box-shadow: 0 0 12px 4px rgba(0, 212, 255, 0.6);
            animation: cometFlow linear infinite;
        }

        @keyframes cometFlow {
            0% {
                transform: translate(0, 0) rotate(-45deg);
                opacity: 1;
            }
            60% {
                opacity: 0.8;
            }
            100% {
                transform: translate(700px, 500px) rotate(-45deg);
                opacity: 0;
            }
        }

        .groupes-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
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

        .groupes-list {
            list-style: none;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .groupe-item {
            margin: 10px 0;
        }

        .groupe-link {
            display: block;
            padding: 12px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            text-decoration: none;
            border-radius: 15px;
            font-size: 18px;
            transition: all 0.3s ease;
            border: 2px solid #a663cc;
        }

        .groupe-link:hover {
            background: #ffd60a;
            color: #2a1b3d;
            transform: scale(1.05);
            border-color: #f0a500;
        }

        .groupe-link i {
            margin-right: 10px;
        }

        .no-groupes {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 20px;
            margin-top: 20px;
            background: #00d4b4;
            color: #2a1b3d;
            text-decoration: none;
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #ffd60a;
            transform: scale(1.05);
        }

        @media (max-width: 500px) {
            .groupes-box {
                width: 100%;
                padding: 15px;
            }

            h2 {
                font-size: 24px;
            }

            .groupe-link {
                font-size: 16px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Comètes bleues -->
    <div class="cyan-comet" style="top: 15%; left: 20%; animation-duration: 1.2s;"></div>
    <div class="cyan-comet" style="top: 35%; left: 65%; animation-duration: 1.7s;"></div>
    <div class="cyan-comet" style="top: 55%; left: 30%; animation-duration: 1.4s;"></div>
    <div class="cyan-comet" style="top: 75%; left: 75%; animation-duration: 2.0s;"></div>

    <div class="groupes-box">
        <h2>🎉 Mes Crews Cool</h2>

        <?php if (empty($groupes)): ?>
            <p class="no-groupes">😢 T’as pas encore de crews, fais-toi des potes !</p>
        <?php else: ?>
            <ul class="groupes-list">
                <?php foreach ($groupes as $groupe): ?>
                    <li class="groupe-item">
                        <a href="message_groupe.php?groupe_id=<?= htmlspecialchars($groupe['groupe_id']); ?>" class="groupe-link">
                            <i class="fas fa-users"></i>
                            <?= htmlspecialchars($groupe['groupe_nom']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Retour au QG</a>
    </div>

    <script>
        // Ajouter dynamiquement plus de comètes bleues
        function createCyanComet() {
            const comet = document.createElement('div');
            comet.className = 'cyan-comet';
            comet.style.top = Math.random() * 80 + '%';
            comet.style.left = Math.random() * 80 + '%';
            comet.style.animationDuration = (Math.random() * 0.8 + 1.2) + 's';
            document.body.appendChild(comet);
            setTimeout(() => comet.remove(), 2500);
        }

        setInterval(createCyanComet, 900);
    </script>
</body>
</html>