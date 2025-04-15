const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server);

app.use(express.static('public'));  // Pour servir les fichiers HTML, CSS et JS statiques

let users = {}; // Pour stocker les utilisateurs connectés

io.on('connection', (socket) => {
    console.log('Un utilisateur est connecté');

    // Enregistrement de l'utilisateur
    socket.on('register', (userId) => {
        users[userId] = socket.id;
    });

    // Lancer un appel
    socket.on('call', (data) => {
        const { contactId } = data;
        const targetSocket = users[contactId];

        if (targetSocket) {
            io.to(targetSocket).emit('offer', { offer: "l'offre WebRTC ici" });
        }
    });

    // Répondre à un appel
    socket.on('answer', (data) => {
        const { answer } = data;
        socket.broadcast.emit('answer', { answer: answer });
    });

    // Envoi des candidats ICE pour la connexion WebRTC
    socket.on('candidate', (candidate) => {
        socket.broadcast.emit('candidate', candidate);
    });

    // Terminer l'appel
    socket.on('hangup', () => {
        socket.broadcast.emit('hangup');
    });

    // Déconnexion de l'utilisateur
    socket.on('disconnect', () => {
        console.log('Utilisateur déconnecté');
    });
});

server.listen(3000, () => {
    console.log('Serveur démarré sur http://localhost:3000');
});
cd /Applications/MAMP/htdocs/projet_php/pages
