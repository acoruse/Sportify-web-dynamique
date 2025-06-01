-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 29 mai 2025 à 19:54
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bdd_projet`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `ID` int NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `adresse` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `code_postal` int DEFAULT NULL,
  `pays` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `num` int DEFAULT NULL,
  `carte_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `type_carte_pay` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `num_carte_pay` int DEFAULT NULL,
  `nom_carte` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `code_carte` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`ID`, `nom`, `adresse`, `ville`, `code_postal`, `pays`, `num`, `carte_id`, `type_carte_pay`, `num_carte_pay`, `nom_carte`, `date_expiration`, `code_carte`) VALUES
(10, NULL, '12 chemin de la rigolade', 'Thiais', 94563, 'France', 783748078, 'aizhbj1752GU71', 'MasterCard', 13878161, 'Martias', '2026-02-11', 162),
(26, 'etienne', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `coach`
--

DROP TABLE IF EXISTS `coach`;
CREATE TABLE IF NOT EXISTS `coach` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `jour` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `spe` varchar(30) DEFAULT NULL,
  `id_coach` int DEFAULT NULL,
  `bureau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `coach`
--

INSERT INTO `coach` (`ID`, `nom`, `jour`, `heure_debut`, `heure_fin`, `spe`, `id_coach`, `bureau`, `image`) VALUES
(8, 'jeanne', '', '00:00:00', '00:00:00', 'musculation', 20, 'Paris', 'images_projet\\coach.jpg'),
(15, 'emile', '', '00:00:00', '00:00:00', 'fitness', 33, 'Dijon', 'images_projet\\coach1.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `event_semaine`
--

DROP TABLE IF EXISTS `event_semaine`;
CREATE TABLE IF NOT EXISTS `event_semaine` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `img` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `event_semaine`
--

INSERT INTO `event_semaine` (`ID`, `Titre`, `date`, `img`) VALUES
(12, 'Boxe', '2007-03-12', 'image_event_projet\\boxe.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `expediteur_id` int NOT NULL,
  `destinataire_id` int NOT NULL,
  `text` varchar(255) NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_message`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id_message`, `expediteur_id`, `destinataire_id`, `text`, `lu`) VALUES
(14, 1, 20, 'wombat', 0),
(13, 1, 20, 'wombat', 0),
(12, 10, 20, 'salut jeanne', 0),
(11, 20, 10, 'oui', 0),
(10, 10, 20, 'le rdv est bien a 18H00', 0),
(15, 1, 20, 'wombat', 0),
(16, 1, 20, 'wombat', 0),
(17, 1, 20, 'wombat', 0),
(18, 1, 20, 'wombat', 0),
(19, 1, 20, 'wombat', 0);

-- --------------------------------------------------------

--
-- Structure de la table `rdv`
--

DROP TABLE IF EXISTS `rdv`;
CREATE TABLE IF NOT EXISTS `rdv` (
  `ID_rdv` int NOT NULL AUTO_INCREMENT,
  `index_client` varchar(20) NOT NULL,
  `index_coach` int NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `jour_rdv` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`ID_rdv`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `rdv`
--

INSERT INTO `rdv` (`ID_rdv`, `index_client`, `index_coach`, `heure_debut`, `heure_fin`, `jour_rdv`) VALUES
(15, '1', 20, '18:00:00', '20:00:00', 'Lundi'),
(16, '10', 33, '14:00:00', '16:00:00', 'Mercredi');

-- --------------------------------------------------------

--
-- Structure de la table `salle`
--

DROP TABLE IF EXISTS `salle`;
CREATE TABLE IF NOT EXISTS `salle` (
  `id_salle` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) DEFAULT NULL,
  `addresse_salle` varchar(40) DEFAULT NULL,
  `img` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_salle`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `salle`
--

INSERT INTO `salle` (`id_salle`, `nom`, `addresse_salle`, `img`) VALUES
(3, 'omnes', '12 chemin de la rigolade', 'image_event_projet\\salle_muscu');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `ID` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `activite_pref` varchar(255) NOT NULL,
  `type` int NOT NULL DEFAULT '1',
  `date` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `id_user` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`ID`, `nom`, `email`, `mdp`, `activite_pref`, `type`, `date`, `image`, `id_user`) VALUES
(1, 'Tikhomiroff', 'clmtff05@gmail.com', '123', 'boxe', 3, '26-05-2025', '', '0'),
(20, 'jeanne', 'jpaka@gmail.com', '14', 'musculation', 2, NULL, 'images_projet\\coach.jpg', ''),
(28, 'jaja', 'jaja@gmail.com', '14', 'fitness', 2, NULL, '', ''),
(10, 'willys', 'willys@gmail.com', 'foutou', 'muscu', 1, NULL, '', ''),
(29, 'francis', 'francis@gmail.com', 'banane', 'Basketball', 2, NULL, 'images_projet\\coach.jpg', ''),
(33, 'emile', 'emile@gmail.com', '123', 'fitness', 2, NULL, 'images_projet\\coach1.jpg', ''),
(26, 'etienne', 'etiennechau@gmail.com', 'jaimeleriz', 'fitness', 1, NULL, '', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
