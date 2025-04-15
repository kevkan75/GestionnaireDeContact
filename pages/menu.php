<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💌 Messagerie de Ouf</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Comic Sans MS', 'Arial', sans-serif;
            background: linear-gradient(to bottom, #2a1b3d, #44318d); /* Fond violet-bleu */
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            border-right: 3px dashed #f0a500; /* Bordure orange */
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.5);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            animation: slideInLeft 0.5s ease;
        }

        @keyframes slideInLeft {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(0); }
        }

        .sidebar h2 {
            font-size: 28px;
            color: #f0a500; /* Orange vif */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
            margin: 0 0 20px;
            text-align: center;
        }

        .sidebar a {
            text-decoration: none;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid #a663cc; /* Violet clair */
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background: #ffd60a; /* Jaune vif */
            color: #2a1b3d; /* Violet sombre */
            border-color: #f0a500; /* Orange */
            transform: scale(1.05);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
        }

        .sidebar i {
            margin-right: 10px;
            font-size: 20px;
            color: #00d4b4; /* Turquoise */
        }

        .sidebar a:hover i {
            color: #2a1b3d; /* Violet sombre */
        }

        .content {
            flex: 1;
            margin-left: 270px; /* Espace pour la sidebar */
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .content h2 {
            font-size: 36px;
            color: #f0a500; /* Orange vif */
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
            border-bottom: 3px dashed #00d4b4; /* Turquoise */
            padding-bottom: 10px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .content p {
            font-size: 20px;
            color: #a663cc; /* Violet clair */
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 15px;
            border: 2px solid #ffd60a; /* Jaune vif */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 220px;
            }

            .content h2 {
                font-size: 28px;
            }

            .content p {
                font-size: 18px;
            }
        }

        @media (max-width: 500px) {
            .sidebar {
                width: 150px;
                padding: 15px;
            }

            .sidebar h2 {
                font-size: 24px;
            }

            .sidebar a {
                font-size: 16px;
                padding: 10px;
            }

            .content {
                margin-left: 170px;
            }

            .content h2 {
                font-size: 24px;
            }

            .content p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<!-- MENU LATÉRAL -->
<div class="sidebar">
    <h2>💌 Messagerie</h2>
    <a href="boite_reception.php" class="menu-item"><i class="fas fa-inbox"></i> Boîte de Réception</a>
    <a href="spam.php" class="menu-item"><i class="fas fa-exclamation-triangle"></i> Spams</a>
    <a href="favoris.php" class="menu-item"><i class="fas fa-star"></i> Favoris</a>
    <a href="corbeille.php" class="menu-item"><i class="fas fa-trash"></i> Corbeille</a>
</div>

<!-- CONTENU PRINCIPAL -->
<div class="content">
    <h2>✨ Bienvenue dans ta Messagerie !</h2>
    <p>👉 Choisi une vibe dans le menu pour checker tes messages !</p>
</div>

</body>
</html>