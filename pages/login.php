<?php
session_start();
include "../config/database.php";

// Initialiser $error pour éviter l'erreur "undefined variable"
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM Utilisateurs WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["mot_de_passe"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["prenom"] . " " . $user["nom"];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "❌ Identifiants incorrects.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 Connexion</title>
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
            background: linear-gradient(45deg, #ff6347, #ff9999); /* Rouge clair à rose pâle */
            border-radius: 50%;
            box-shadow: 0 0 10px 4px rgba(255, 99, 71, 0.7); /* Ombre rouge clair */
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

        .login-container {
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

        .register-link {
            color: #a663cc;
            text-decoration: none;
            font-size: 16px;
            margin-top: 20px;
            display: inline-block;
            transition: color 0.3s ease;
        }

        .register-link:hover {
            color: #f0a500;
        }

        @media (max-width: 600px) {
            .login-container {
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
    <!-- Comètes rouges -->
    <div class="red-comet" style="top: 20%; left: 15%; animation-duration: 1.3s;"></div>
    <div class="red-comet" style="top: 40%; left: 60%; animation-duration: 1.8s;"></div>
    <div class="red-comet" style="top: 60%; left: 25%; animation-duration: 1.5s;"></div>
    <div class="red-comet" style="top: 80%; left: 70%; animation-duration: 2.1s;"></div>

    <div class="login-container">
        <h2>🔒 Connexion</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email :</label>
                <input type="email" name="email" maxlength="255" required>
            </div>
            <div class="input-group">
                <label>Mot de passe :</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">🚀 Se connecter</button>
            <a href="register.php" class="register-link">Créer un compte</a>
        </form>
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
            setTimeout(() => comet.remove(), 2500); // Supprime après animation
        }

        setInterval(createRedComet, 900); // Nouvelle comète toutes les 0.9 secondes
    </script>
</body>
</html>