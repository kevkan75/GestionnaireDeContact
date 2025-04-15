<?php
session_start();
include "../config/database.php";

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour accéder à cette page.");
}

// Récupère l'ID de l'utilisateur connecté
$utilisateur_id = $_SESSION['user_id'];

// Récupérer les demandes d'ami reçues
$sql = "SELECT d.id, u1.nom AS nom_demandeur, u1.prenom AS prenom_demandeur, d.demandeur_id, d.receveur_id
        FROM demandes_ami d
        JOIN utilisateurs u1 ON d.demandeur_id = u1.id
        WHERE d.receveur_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$utilisateur_id]);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accepter'])) {
        $demande_id = $_POST['accepter'];

        // Récupérer demandeur et receveur
        $stmt_info = $pdo->prepare("SELECT demandeur_id, receveur_id FROM demandes_ami WHERE id = ?");
        $stmt_info->execute([$demande_id]);
        $demande = $stmt_info->fetch(PDO::FETCH_ASSOC);
        if ($demande) {
            $demandeur_id = $demande['demandeur_id'];
            $receveur_id = $demande['receveur_id'];

            // Récupérer les informations du demandeur
            $stmt_demandeur = $pdo->prepare("SELECT nom, prenom, email FROM utilisateurs WHERE id = ?");
            $stmt_demandeur->execute([$demandeur_id]);
            $demandeur_info = $stmt_demandeur->fetch(PDO::FETCH_ASSOC);

            // Récupérer les informations du receveur
            $stmt_receveur = $pdo->prepare("SELECT nom, prenom, email FROM utilisateurs WHERE id = ?");
            $stmt_receveur->execute([$receveur_id]);
            $receveur_info = $stmt_receveur->fetch(PDO::FETCH_ASSOC);

            // Ajouter la relation dans contacts
            $sql_add_contact = "INSERT INTO contacts (utilisateur_id, utilisateur_contact, nom, prenom, email) VALUES (?, ?, ?, ?, ?), (?, ?, ?, ?, ?)";
            $stmt_add_contact = $pdo->prepare($sql_add_contact);
            $stmt_add_contact->execute([
                $demandeur_id, $receveur_id, $receveur_info['nom'], $receveur_info['prenom'], $receveur_info['email'],
                $receveur_id, $demandeur_id, $demandeur_info['nom'], $demandeur_info['prenom'], $demandeur_info['email']
            ]);

            // Supprimer la demande d'ami
            $sql_delete = "DELETE FROM demandes_ami WHERE id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$demande_id]);
        }

        $_SESSION['success_message'] = "";
        header("Location: dashboard.php");
        exit();
    } elseif (isset($_POST['refuser'])) {
        $demande_id = $_POST['refuser'];

        // Supprimer la demande d'ami
        $sql_delete = "DELETE FROM demandes_ami WHERE id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([$demande_id]);

        $_SESSION['success_message'] = "❌ Demande d'ami refusée.";
        header("Location: dashboard.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤝 Demandes d'Ami</title>
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
        }

        .demandes-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 2px dashed #f0a500;
            text-align: center;
        }

        h3 {
            font-size: 32px;
            color: #f0a500;
            margin: 0 0 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        .demande-list {
            list-style: none;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .demande-item {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid #a663cc;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            transition: all 0.3s ease;
            text-align: left;
        }

        .demande-item:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .demande-name {
            font-size: 18px;
            color: #fff;
        }

        .demande-item:hover .demande-name {
            color: #2a1b3d;
        }

        .demande-actions {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .demande-actions button {
            flex: 1;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #00d4b4;
            border-radius: 15px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .demande-actions button:hover {
            background: #ffd60a;
            color: #2a1b3d;
            border-color: #f0a500;
        }

        .btn-refuser {
            border-color: #ff4040;
        }

        .btn-refuser:hover {
            background: #ff4040;
            border-color: #f0a500;
        }

        .no-demandes {
            color: #a663cc;
            font-size: 18px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .demandes-container {
                width: 100%;
                padding: 15px;
            }

            h3 {
                font-size: 24px;
            }

            .demande-item {
                padding: 10px;
            }

            .demande-actions button {
                font-size: 14px;
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="demandes-container">
        <h3>🤝 Demandes d'Ami</h3>

        <?php if (empty($demandes)): ?>
            <p class="no-demandes">😢 Aucune demande d'ami reçue pour le moment.</p>
        <?php else: ?>
            <ul class="demande-list">
                <?php foreach ($demandes as $demande): ?>
                    <li class="demande-item">
                        <div class="demande-name">Demande de : <?php echo htmlspecialchars($demande['nom_demandeur'] . " " . $demande['prenom_demandeur']); ?></div>
                        <div class="demande-actions">
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="accepter" value="<?php echo $demande['id']; ?>">✅ Accepter</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="refuser" value="<?php echo $demande['id']; ?>" class="btn-refuser">❌ Refuser</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>