<?php
session_start();
include __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour gérer les membres.");
}

$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté
$groupe_id = $_GET['groupe_id'] ?? null; // ID du groupe à partir de l'URL

if (!$groupe_id) {
    die("❌ Groupe non spécifié.");
}

// Récupérer la liste des membres du groupe, excluant l'utilisateur connecté
$sql_members = "
    SELECT u.id AS member_id, u.nom AS member_nom, u.prenom AS member_prenom
    FROM membres_groupe m
    JOIN utilisateurs u ON m.user_id = u.id
    WHERE m.groupe_id = ? AND u.id != ?
";

$stmt_members = $pdo->prepare($sql_members);
$stmt_members->execute([$groupe_id, $user_id]);
$members = $stmt_members->fetchAll();

// Si aucun membre n'est trouvé
if (!$members) {
    echo "❌ Aucun membre à afficher dans ce groupe.";
    exit;
}

// Supprimer un membre
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_member'])) {
    $member_id_to_remove = $_POST['remove_member'];

    // Vérifier si le membre à supprimer existe dans le groupe
    $stmt_check = $pdo->prepare("SELECT * FROM membres_groupe WHERE groupe_id = ? AND user_id = ?");
    $stmt_check->execute([$groupe_id, $member_id_to_remove]);

    if ($stmt_check->rowCount() > 0) {
        // Supprimer le membre du groupe
        $sql_remove_member = "DELETE FROM membres_groupe WHERE groupe_id = ? AND user_id = ?";
        $stmt_remove_member = $pdo->prepare($sql_remove_member);
        $stmt_remove_member->execute([$groupe_id, $member_id_to_remove]);

        // Redirection vers le tableau de bord avec un message de succès
        $_SESSION['success_message'] = "";
        header("Location: dashboard.php");
        exit();
    } else {
        echo "❌ Le membre à supprimer n'existe pas dans ce groupe.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔴 Supprimer un Membre</title>
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

        /* Lignes lumineuses subtiles */
        .light-lines {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
        }

        .line {
            position: absolute;
            width: 1px;
            height: 100px;
            background: linear-gradient(to bottom, rgba(166, 99, 204, 0.5), rgba(0, 212, 180, 0.3));
            animation: lightFlow 6s infinite ease-in-out;
            opacity: 0.6;
        }

        .line:nth-child(2) { left: 20%; animation-delay: 1s; height: 80px; }
        .line:nth-child(3) { left: 40%; animation-delay: 2s; }
        .line:nth-child(4) { left: 60%; animation-delay: 3s; height: 120px; }
        .line:nth-child(5) { left: 80%; animation-delay: 4s; }

        @keyframes lightFlow {
            0% { transform: translateY(100vh); opacity: 0; }
            50% { opacity: 0.6; }
            100% { transform: translateY(-100vh); opacity: 0; }
        }

        .container {
            width: 90%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        h2 {
            font-size: 28px;
            color: #f0a500;
            margin: 0 0 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            border-bottom: 2px solid #a663cc;
            padding-bottom: 10px;
        }

        .member-list {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-group input[type="radio"] {
            accent-color: #00d4b4;
        }

        .input-group label {
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .input-group label:hover {
            color: #ffd60a;
        }

        .btn {
            background: #ff4040;
            color: #fff;
            border: 2px solid #f0a500;
            padding: 12px 20px;
            width: 100%;
            border-radius: 15px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 15px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        @media (max-width: 600px) {
            .container {
                width: 100%;
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .input-group label {
                font-size: 14px;
            }

            .btn, .back-btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="light-lines">
        <div class="line" style="left: 10%;"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>

    <div class="container">
        <h2>🔴 Supprimer un Membre</h2>

        <form action="supprimer_membre.php?groupe_id=<?php echo $groupe_id; ?>" method="POST">
            <div class="member-list">
                <?php foreach ($members as $member): ?>
                    <div class="input-group">
                        <input type="radio" name="remove_member" value="<?php echo $member['member_id']; ?>" id="member-<?php echo $member['member_id']; ?>" required>
                        <label for="member-<?php echo $member['member_id']; ?>">
                            <?php echo htmlspecialchars($member['member_prenom']) . " " . htmlspecialchars($member['member_nom']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn">🗑️ Supprimer</button>
        </form>

        <a href="message_groupe.php?groupe_id=<?php echo $groupe_id; ?>" class="back-btn">⬅ Retour au Chat</a>
    </div>
</body>
</html>