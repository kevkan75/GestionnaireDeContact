-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : lun. 14 avr. 2025 à 13:51
-- Version du serveur : 8.0.35
-- Version de PHP : 8.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ad`
--

-- --------------------------------------------------------

--
-- Structure de la table `bloque`
--

CREATE TABLE `bloque` (
  `id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `utilisateur_bloque` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `utilisateur_contact` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contacts`
--

INSERT INTO `contacts` (`id`, `utilisateur_id`, `nom`, `prenom`, `telephone`, `email`, `utilisateur_contact`) VALUES
(52, 8, 'Kan', 'Kevin', NULL, 'kevin@gmail.com', 6),
(57, 6, 'OUAJJOU', 'Ayoub', NULL, 'Ouajjou0405@hotmail.com', 8),
(64, 4, 'Kan', 'Kevin', NULL, 'kevin@gmail.com', 6),
(66, 6, 'Bendjebbar', 'Adam', NULL, 'adambendjebbar@gmail.com', 4);

-- --------------------------------------------------------

--
-- Structure de la table `corbeille`
--

CREATE TABLE `corbeille` (
  `id` int NOT NULL,
  `expediteur_id` int DEFAULT NULL,
  `contact_id` int DEFAULT NULL,
  `message` text,
  `date_envoi` datetime DEFAULT NULL,
  `objet` varchar(255) DEFAULT NULL,
  `audio` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_ami`
--

CREATE TABLE `demandes_ami` (
  `id` int NOT NULL,
  `demandeur_id` int NOT NULL,
  `receveur_id` int NOT NULL,
  `date_demande` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe`
--

CREATE TABLE `groupe` (
  `id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `createur_id` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`id`, `nom`, `createur_id`, `date_creation`) VALUES
(32, 'cs', 4, '2025-03-05 15:25:09'),
(33, 'cs', 4, '2025-03-05 15:27:47'),
(34, 'hh', 4, '2025-03-06 08:58:05'),
(35, 's', 4, '2025-03-06 09:06:33'),
(36, 'asns', 6, '2025-03-09 12:25:02'),
(37, 'sxsx', 6, '2025-03-09 12:25:22'),
(38, 'sxsx', 6, '2025-03-09 12:30:29'),
(39, 'zerverver', 6, '2025-03-09 12:34:04'),
(40, 'zerverver', 6, '2025-03-09 12:37:43'),
(41, 'ede', 6, '2025-03-09 12:39:43'),
(42, 'dcdcdc', 6, '2025-03-09 12:41:37'),
(43, 'ajsn', 4, '2025-03-09 14:45:11'),
(44, 'HBUB', 6, '2025-03-10 12:24:48'),
(45, 'bgvtv', 4, '2025-03-11 16:40:27'),
(46, 'gege', 6, '2025-03-12 13:55:58'),
(47, 'dxd', 8, '2025-03-12 14:25:04'),
(48, 'dxd', 8, '2025-03-12 14:25:11'),
(49, 'adam', 8, '2025-03-12 14:26:05'),
(50, 'as', 4, '2025-03-23 20:49:48'),
(51, 'bbrb', 4, '2025-04-02 11:22:26'),
(52, 'vdvd', 6, '2025-04-02 14:04:16'),
(53, 'www', 6, '2025-04-02 14:35:33'),
(54, 'bob', 6, '2025-04-02 14:39:30'),
(55, 'vsdvs', 4, '2025-04-09 14:14:41'),
(56, 'SDSDS', 4, '2025-04-14 15:39:57');

-- --------------------------------------------------------

--
-- Structure de la table `membres_groupe`
--

CREATE TABLE `membres_groupe` (
  `groupe_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('membre','admin') DEFAULT 'membre',
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `membres_groupe`
--

INSERT INTO `membres_groupe` (`groupe_id`, `user_id`, `role`, `date_ajout`) VALUES
(41, 6, 'admin', '2025-03-09 12:39:43'),
(41, 8, 'membre', '2025-03-16 13:45:08'),
(42, 4, 'membre', '2025-03-09 12:42:08'),
(42, 6, 'admin', '2025-03-09 12:41:37'),
(43, 4, 'admin', '2025-03-09 14:45:11'),
(43, 6, 'membre', '2025-03-09 14:45:14'),
(44, 6, 'admin', '2025-03-10 12:24:48'),
(45, 4, 'admin', '2025-03-11 16:40:27'),
(45, 6, 'membre', '2025-03-11 16:40:39'),
(45, 8, 'membre', '2025-03-11 16:40:39'),
(46, 4, 'membre', '2025-03-12 13:56:04'),
(46, 6, 'admin', '2025-03-12 13:55:58'),
(47, 8, 'admin', '2025-03-12 14:25:04'),
(48, 8, 'admin', '2025-03-12 14:25:11'),
(50, 4, 'admin', '2025-03-23 20:49:48'),
(50, 6, 'membre', '2025-03-23 20:49:51'),
(51, 4, 'admin', '2025-04-02 11:22:26'),
(51, 6, 'membre', '2025-04-02 11:22:30'),
(52, 6, 'admin', '2025-04-02 14:04:16'),
(53, 6, 'admin', '2025-04-02 14:35:33'),
(54, 4, 'membre', '2025-04-02 14:39:35'),
(54, 6, 'admin', '2025-04-02 14:39:30'),
(54, 8, 'membre', '2025-04-02 14:39:35'),
(55, 4, 'admin', '2025-04-09 14:14:41'),
(56, 4, 'admin', '2025-04-14 15:39:57'),
(56, 6, 'membre', '2025-04-14 15:40:01'),
(56, 8, 'membre', '2025-04-14 15:43:17');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `expediteur_id` int NOT NULL,
  `contact_id` int NOT NULL,
  `message` text NOT NULL,
  `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `objet` varchar(255) DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `fichier` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `expediteur_id`, `contact_id`, `message`, `date_envoi`, `objet`, `audio`, `fichier`) VALUES
(62, 4, 64, 'f  xwxw', '2025-04-02 09:26:02', 'f f', '/Applications/MAMP/htdocs/projet_php/pages/../fichier_vocal/62/message_vocal_62.mp4', '/Applications/MAMP/htdocs/projet_php/pages/../fichier_vocal/62/Bonjour copie.java'),
(63, 4, 64, 'jbyg', '2025-04-02 09:30:52', 'xdx', '/Applications/MAMP/htdocs/projet_php/pages/../fichier_vocal/63/message_vocal_63.mp4', '/Applications/MAMP/htdocs/projet_php/pages/../fichier_vocal/63/Bonjour.class'),
(65, 6, 66, 'cdcdc', '2025-04-04 14:09:14', 'dcd', '/Applications/MAMP/htdocs/projet_php/pages/../fichier_vocal/65/message_vocal_65.mp4', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messages_groupe`
--

CREATE TABLE `messages_groupe` (
  `id` int NOT NULL,
  `groupe_id` int NOT NULL,
  `expediteur_id` int NOT NULL,
  `message` text NOT NULL,
  `audio_path` varchar(255) DEFAULT NULL,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages_groupe`
--

INSERT INTO `messages_groupe` (`id`, `groupe_id`, `expediteur_id`, `message`, `audio_path`, `date_envoi`) VALUES
(9, 49, 8, 'edde', NULL, '2025-03-12 15:16:45'),
(10, 49, 8, 'dedede', NULL, '2025-03-12 15:16:46'),
(11, 49, 8, 'deded', NULL, '2025-03-12 15:16:48'),
(12, 49, 8, 'ceded', NULL, '2025-03-12 15:16:49'),
(13, 49, 8, 'deded', NULL, '2025-03-12 15:16:51'),
(14, 49, 8, 'des', NULL, '2025-03-12 15:17:00'),
(15, 41, 6, 'sss', NULL, '2025-03-16 13:44:35'),
(16, 41, 6, 'sss', NULL, '2025-03-16 13:44:37'),
(17, 41, 6, 'sss', NULL, '2025-03-16 13:44:38'),
(18, 41, 6, 'ss', NULL, '2025-03-16 13:45:13'),
(19, 42, 4, 'n', NULL, '2025-03-23 20:47:30'),
(20, 42, 4, 'd d', NULL, '2025-03-23 21:01:13'),
(21, 42, 4, 'dcd', NULL, '2025-03-26 11:40:31'),
(22, 42, 4, 'dcd', NULL, '2025-03-26 11:40:34'),
(23, 56, 4, 'CDCDCD', NULL, '2025-04-14 15:40:08'),
(24, 56, 4, 'DCDC', NULL, '2025-04-14 15:40:11'),
(25, 56, 6, 'DCDC', NULL, '2025-04-14 15:40:37'),
(26, 56, 8, 'X X X', NULL, '2025-04-14 15:43:35');

-- --------------------------------------------------------

--
-- Structure de la table `spam`
--

CREATE TABLE `spam` (
  `id` int NOT NULL,
  `expediteur_id` int NOT NULL,
  `contact_id` int NOT NULL,
  `message` text NOT NULL,
  `objet` varchar(255) DEFAULT NULL,
  `audio` text,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `spam`
--

INSERT INTO `spam` (`id`, `expediteur_id`, `contact_id`, `message`, `objet`, `audio`, `date_envoi`) VALUES
(7, 8, 8, '', NULL, '', '2025-03-16 14:34:50'),
(8, 4, 6, '', NULL, '', '2025-03-16 15:05:41'),
(9, 4, 6, 'cc', NULL, '', '2025-03-16 15:28:45');

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateurs`
--

CREATE TABLE `Utilisateurs` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `genre` enum('Homme','Femme','Non précisé') DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Utilisateurs`
--

INSERT INTO `Utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_naissance`, `genre`, `date_creation`) VALUES
(4, 'Bendjebbar', 'Adam', 'adambendjebbar@gmail.com', '$2y$10$kGP3mttdMNrN2hFF47xIce88f.aBtel5l4fto5p534T58pMeikPbG', NULL, NULL, '2025-02-01 11:09:28'),
(5, 'AD', 'AD', 'adamben@gmail.com', '$2y$10$0Yp0wrdfMrDW1C8Ps/VuPeabggssRLvUx4RXXiJJt3rjLER2my/y6', NULL, NULL, '2025-02-01 11:13:38'),
(6, 'Kan', 'Kevin', 'kevin@gmail.com', '$2y$10$OwDMlPCEkFhhzwLOaExbIOe9RMJM.MFW4yxD26LEbp2f7C8h6jGV2', '2004-02-29', 'Homme', '2025-02-02 16:51:46'),
(7, 'Fille', 'Fille', 'fille@gmail.com', '$2y$10$hupn3439dbm78rFtcWHoTuLdoUuJQeP0si1n4n1D3Pyb1Rqq7a3Gm', '2003-03-01', 'Homme', '2025-02-07 11:48:57'),
(8, 'OUAJJOU', 'Ayoub', 'Ouajjou0405@hotmail.com', '$2y$10$al4UxAaC3HpVp/bPz6oqlum0LUHS1rsapOCpTzzBKgSKoA8MAeI66', '2005-04-11', 'Homme', '2025-02-18 08:26:38'),
(9, 'Mazni', 'Amine', 'amine@gmail.com', '$2y$10$Cijl/JeWaHtIIMSo8OZHI.JuCiJsJI5.uGy4gxsqoztJSKnYGDmHW', '2004-06-07', 'Non précisé', '2025-04-04 14:05:36');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bloque`
--
ALTER TABLE `bloque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `utilisateur_bloque` (`utilisateur_bloque`);

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `fk_utilisateur_contact` (`utilisateur_contact`);

--
-- Index pour la table `corbeille`
--
ALTER TABLE `corbeille`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_ami`
--
ALTER TABLE `demandes_ami`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demande_unique` (`demandeur_id`,`receveur_id`),
  ADD KEY `receveur_id` (`receveur_id`);

--
-- Index pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `createur_id` (`createur_id`);

--
-- Index pour la table `membres_groupe`
--
ALTER TABLE `membres_groupe`
  ADD PRIMARY KEY (`groupe_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Index pour la table `messages_groupe`
--
ALTER TABLE `messages_groupe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `expediteur_id` (`expediteur_id`);

--
-- Index pour la table `spam`
--
ALTER TABLE `spam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Index pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `bloque`
--
ALTER TABLE `bloque`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `demandes_ami`
--
ALTER TABLE `demandes_ami`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `groupe`
--
ALTER TABLE `groupe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `messages_groupe`
--
ALTER TABLE `messages_groupe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `spam`
--
ALTER TABLE `spam`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bloque`
--
ALTER TABLE `bloque`
  ADD CONSTRAINT `bloque_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `bloque_ibfk_2` FOREIGN KEY (`utilisateur_bloque`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `Utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_utilisateur_contact` FOREIGN KEY (`utilisateur_contact`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_ami`
--
ALTER TABLE `demandes_ami`
  ADD CONSTRAINT `demandes_ami_ibfk_1` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demandes_ami_ibfk_2` FOREIGN KEY (`receveur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD CONSTRAINT `groupe_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `Utilisateurs` (`id`);

--
-- Contraintes pour la table `membres_groupe`
--
ALTER TABLE `membres_groupe`
  ADD CONSTRAINT `membres_groupe_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupe` (`id`),
  ADD CONSTRAINT `membres_groupe_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `Utilisateurs` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`);

--
-- Contraintes pour la table `messages_groupe`
--
ALTER TABLE `messages_groupe`
  ADD CONSTRAINT `messages_groupe_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupe` (`id`),
  ADD CONSTRAINT `messages_groupe_ibfk_2` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `spam`
--
ALTER TABLE `spam`
  ADD CONSTRAINT `spam_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `spam_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
