<?php
session_start();
include "../config/database.php";

$nom = $prenom = $email = $date_naissance = $genre = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $date_naissance = trim($_POST["date_naissance"]);
    $genre = $_POST["genre"];

    // Vérifier si tous les champs sont remplis
    if (empty($nom) || empty($prenom) || empty($email) || empty($_POST["password"]) || empty($date_naissance) || empty($genre)) {
        $error = "❌ Veuillez remplir tous les champs.";
    }

    // Vérifier que l'email est valide avec une liste complète des domaines et extensions
    $regex_email = "/^[a-zA-Z0-9._%+-]+@(?:gmail|yahoo|hotmail|outlook|live|icloud|aol|protonmail|gmx|yandex|zoho|mail|tutanota|orange|sfr|laposte|free|bbox|numericable|neuf|skynet|me|fastmail|msn|gawab|rediff|bluewin|btinternet|bigpond|verizon|comcast|optonline|cox|shaw|sympatico|rogers|telus|earthlink|ntlworld|mailinator)\.(?:com|fr|net|org|edu|gov|co|info|biz|ch|ca|uk|us|de|es|it|eu|be|pt|ru|cn|jp|hk|br|mx|ar|au|nz|za)$/i";

    if (!preg_match($regex_email, $email)) {
        $error = "❌ Adresse email invalide. Veuillez entrer un email valide (ex: nom@gmail.com).";
    }

    // Vérifier que la date est bien au format YYYY-MM-DD (fourni par le calendrier)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_naissance)) {
        $error = "❌ La date doit être au format AAAA-MM-JJ.";
    }

    if (empty($error)) {
        // Vérifier si l'email existe déjà
        $checkEmail = $pdo->prepare("SELECT id FROM Utilisateurs WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            $error = "❌ Cet email est déjà utilisé. <a href='login.php'>Connectez-vous</a>";
        } else {
            // Insérer l'utilisateur dans la base de données
            $sql = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, date_naissance, genre) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$nom, $prenom, $email, $password, $date_naissance, $genre])) {
                echo "✅ Inscription réussie ! <a href='login.php'>Connectez-vous</a>";
                exit();
            } else {
                $error = "❌ Erreur lors de l'inscription.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Inscription</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
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

        .register-container {
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

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #a663cc;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            box-sizing: border-box;
            font-size: 16px;
        }

        /* Style du calendrier */
        .ui-datepicker {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #a663cc;
            border-radius: 15px;
            color: #fff;
            font-family: 'Comic Sans MS', 'Arial', sans-serif;
            padding: 10px;
        }

        .ui-datepicker-header {
            background: #ffd60a;
            color: #2a1b3d;
            border: none;
            border-radius: 10px 10px 0 0;
            padding: 5px;
        }

        .ui-datepicker-title {
            font-weight: bold;
        }

        .ui-datepicker-calendar {
            background: rgba(255, 255, 255, 0.15);
        }

        .ui-datepicker th {
            color: #00d4b4;
        }

        .ui-datepicker td a {
            color: #fff;
            text-align: center;
            padding: 5px;
            border-radius: 50%;
        }

        .ui-datepicker td a:hover, .ui-datepicker td a.ui-state-active {
            background: #f0a500;
            color: #2a1b3d;
        }

        .ui-datepicker-prev, .ui-datepicker-next {
            cursor: pointer;
            color: #2a1b3d;
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
            .register-container {
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
    <!-- Comètes de magma -->
    <div class="magma-comet" style="top: 20%; left: 15%; animation-duration: 1.3s;"></div>
    <div class="magma-comet" style="top: 40%; left: 60%; animation-duration: 1.8s;"></div>
    <div class="magma-comet" style="top: 60%; left: 25%; animation-duration: 1.5s;"></div>
    <div class="magma-comet" style="top: 80%; left: 70%; animation-duration: 2.1s;"></div>

    <div class="register-container">
        <h2>✨ Créer un compte</h2>
        <?php if (!empty($error)) : ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="input-group">
                <label>Nom :</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
            </div>
            <div class="input-group">
                <label>Prénom :</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required>
            </div>
            <div class="input-group">
                <label>Email :</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="input-group">
                <label>Mot de passe :</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Date de naissance :</label>
                <input type="text" id="datepicker" name="date_naissance" value="<?= htmlspecialchars($date_naissance) ?>" required readonly>
            </div>
            <div class="input-group">
                <label>Genre :</label>
                <select name="genre" required>
                    <option value="Homme" <?= ($genre == "Homme") ? "selected" : "" ?>>Homme</option>
                    <option value="Femme" <?= ($genre == "Femme") ? "selected" : "" ?>>Femme</option>
                    <option value="Non précisé" <?= ($genre == "Non précisé") ? "selected" : "" ?>>Non précisé</option>
                </select>
            </div>
            <button type="submit" class="btn">🚀 S'inscrire</button>
            <a href="login.php" class="register-link">Déjà un compte ? Connectez-vous !</a>
        </form>
    </div>

    <script>
        // Initialisation du calendrier jQuery UI
        $(function() {
            $("#datepicker").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                maxDate: new Date(),
                showAnim: "slideDown"
            });
        });

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