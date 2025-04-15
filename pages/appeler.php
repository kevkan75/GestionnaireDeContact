<?php
session_start();
include "../config/database.php";

// Récupérer l'ID du contact
$contact_id = isset($_GET['contact_id']) ? $_GET['contact_id'] : null;

// Si aucun contact n'est spécifié, rediriger
if (!$contact_id) {
    header('Location: contacts.php');
    exit;
}

// Récupérer les informations du contact depuis la base de données
$sql = "SELECT id, nom, prenom, email FROM contacts WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$contact_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

// Si le contact n'existe pas, rediriger
if (!$contact) {
    header('Location: contacts.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appel Vidéo</title>
    <style>
        body {
            background: #0a0f2c;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
        }

        video {
            width: 45%;
            border: 1px solid white;
            border-radius: 10px;
            margin: 10px;
        }

        .controls {
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .contact-info {
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Appel Vidéo avec <?php echo htmlspecialchars($contact['prenom'] . " " . $contact['nom']); ?></h1>

    <div class="contact-info">
        <p>Email: <?php echo htmlspecialchars($contact['email']); ?></p>
    </div>

    <div class="videos">
        <video id="localVideo" autoplay muted></video>
        <video id="remoteVideo" autoplay></video>
    </div>

    <div class="controls">
        <button id="startCallBtn">Démarrer l'appel</button>
        <button id="endCallBtn" style="display:none;">Terminer l'appel</button>
    </div>
</div>

<script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>
<script>
    const socket = io.connect('http://localhost:8000');
    const localVideo = document.getElementById("localVideo");
    const remoteVideo = document.getElementById("remoteVideo");

    let localStream;
    let peerConnection;
    const configuration = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };
    const isCaller = true; // L'initiateur de l'appel (ici c'est toujours celui qui lance l'appel)

    // Récupérer le flux vidéo local
    async function getUserMedia() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            localVideo.srcObject = stream;
            localStream = stream;
        } catch (err) {
            console.error('Erreur lors de l\'accès à la caméra/microphone: ', err);
        }
    }

    // Fonction pour initier l'appel (offre)
    async function startCall() {
        peerConnection = new RTCPeerConnection(configuration);
        peerConnection.addEventListener('icecandidate', handleICECandidate);
        peerConnection.addEventListener('track', handleTrackEvent);

        // Ajouter les pistes locales au PeerConnection
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

        // Créer une offre
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        socket.emit('offer', offer);
    }

    // Gérer la réception des candidats ICE
    function handleICECandidate(event) {
        if (event.candidate) {
            socket.emit('candidate', event.candidate);
        }
    }

    // Gérer la réception d'un flux distant
    function handleTrackEvent(event) {
        remoteVideo.srcObject = event.streams[0];
    }

    // Gérer la réponse à l'offre
    socket.on('offer', async (offer) => {
        peerConnection = new RTCPeerConnection(configuration);
        peerConnection.addEventListener('icecandidate', handleICECandidate);
        peerConnection.addEventListener('track', handleTrackEvent);

        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

        await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        socket.emit('answer', answer);
    });

    // Gérer la réception de la réponse
    socket.on('answer', (answer) => {
        peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
    });

    // Gérer la réception des candidats ICE
    socket.on('candidate', (candidate) => {
        peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    });

    // Terminer l'appel
    function endCall() {
        peerConnection.close();
        peerConnection = null;
        localStream.getTracks().forEach(track => track.stop());
        localVideo.srcObject = null;
        remoteVideo.srcObject = null;
        document.getElementById('startCallBtn').style.display = 'inline';
        document.getElementById('endCallBtn').style.display = 'none';
    }

    // Lancer la fonction getUserMedia au démarrage
    window.onload = getUserMedia;

    // Gestion des événements des boutons
    document.getElementById('startCallBtn').addEventListener('click', () => {
        startCall();
        document.getElementById('startCallBtn').style.display = 'none';
        document.getElementById('endCallBtn').style.display = 'inline';
    });

    document.getElementById('endCallBtn').addEventListener('click', endCall);
</script>

</body>
</html>

