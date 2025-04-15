<?php
// Inclure la configuration de la base de données et démarrer la session
include __DIR__ . '/../config/database.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Hé, connecte-toi d'abord pour voir tes potes !");
}

// Récupérer la liste des contacts
$sql = "SELECT id, nom, prenom FROM contacts WHERE utilisateur_id = ? ORDER BY nom ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 Mes Contacts Cool</title>
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

        /* Étoiles filantes violettes */
        .purple-star {
            position: absolute;
            width: 4px;
            height: 4px;
            background: linear-gradient(45deg, #a663cc, #ffffff);
            border-radius: 50%;
            box-shadow: 0 0 10px 3px rgba(166, 99, 204, 0.6);
            animation: starFlow linear infinite;
        }

        @keyframes starFlow {
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

        .contacts-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            text-align: center;
        }

        h2 {
            font-size: 32px;
            color: #f0a500;
            margin: 0 0 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        .search-bar {
            width: 100%;
            padding: 12px;
            border: 2px solid #00d4b4;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
        }

        .search-bar:focus {
            border-color: #f0a500;
            outline: none;
        }

        .search-bar::placeholder {
            color: #a663cc;
        }

        .contact-list {
            list-style: none;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .contact-item {
            margin: 10px 0;
        }

        .contact-link {
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

        .contact-link:hover {
            background: #ffd60a;
            color: #2a1b3d;
            transform: scale(1.05);
            border-color: #f0a500;
        }

        .contact-link i {
            margin-right: 10px;
        }

        .no-contacts {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        @media (max-width: 500px) {
            .contacts-box {
                width: 100%;
                padding: 15px;
            }

            h2 {
                font-size: 24px;
            }

            .contact-link {
                font-size: 16px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Étoiles filantes -->
    <div class="purple-star" style="top: 20%; left: 15%; animation-duration: 1.3s;"></div>
    <div class="purple-star" style="top: 40%; left: 60%; animation-duration: 1.8s;"></div>
    <div class="purple-star" style="top: 60%; left: 25%; animation-duration: 1.5s;"></div>
    <div class="purple-star" style="top: 80%; left: 70%; animation-duration: 2.1s;"></div>

    <div class="contacts-box">
        <h2>💬 Choisis un Pote à Contacter !</h2>
        <input type="text" id="searchContact" class="search-bar" placeholder="Trouve ton pote..." onkeyup="filterContacts()">

        <ul class="contact-list" id="contactsList">
            <?php if (empty($contacts)): ?>
                <li class="no-contacts">😢 T’as pas encore de potes ici !</li>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <li class="contact-item">
                        <a href="message.php?contact_id=<?php echo $contact['id']; ?>" class="contact-link">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($contact['nom'] . ' ' . $contact['prenom']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <script>
        // Gérer la recherche de contacts
        function filterContacts() {
            let input = document.getElementById("searchContact").value.toLowerCase();
            let contacts = document.querySelectorAll(".contact-item");
            contacts.forEach(contact => {
                let text = contact.querySelector(".contact-link").innerText.toLowerCase();
                if (text.includes(input)) {
                    contact.style.display = "block";
                } else {
                    contact.style.display = "none";
                }
            });
        }

        // Ajouter dynamiquement plus d'étoiles filantes
        function createPurpleStar() {
            const star = document.createElement('div');
            star.className = 'purple-star';
            star.style.top = Math.random() * 80 + '%';
            star.style.left = Math.random() * 80 + '%';
            star.style.animationDuration = (Math.random() * 1 + 1.2) + 's';
            document.body.appendChild(star);
            setTimeout(() => star.remove(), 2500);
        }

        setInterval(createPurpleStar, 900);
    </script>
</body>
</html>