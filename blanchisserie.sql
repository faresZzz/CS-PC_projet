-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 21 juil. 2021 à 13:38
-- Version du serveur :  5.7.31
-- Version de PHP : 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `blanchisserie`
--

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

DROP TABLE IF EXISTS `factures`;
CREATE TABLE IF NOT EXISTS `factures` (
  `IdFacture` int(11) NOT NULL AUTO_INCREMENT,
  `NomClient` varchar(255) NOT NULL,
  `NumeroClient` int(11) DEFAULT NULL,
  `NomFacture` varchar(255) NOT NULL,
  `NumeroFacture` int(11) NOT NULL,
  `DateFacture` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdFacture`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`IdFacture`, `NomClient`, `NumeroClient`, `NomFacture`, `NumeroFacture`, `DateFacture`) VALUES
(1, 'client1', 1, 'Facture-client1', 11111, '2021-07-19 09:30:33'),
(2, 'client2', 2, 'Facture_client2', 2222, '2021-07-19 10:24:46'),
(3, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN.pdf', 79867, '2021-07-19 12:01:47'),
(14, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN-181.pdf', 79867, '2021-07-21 12:21:12'),
(15, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN-678.pdf', 79867, '2021-07-21 12:24:24'),
(16, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN-774.pdf', 79867, '2021-07-21 14:07:02'),
(13, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN-3.pdf', 79867, '2021-07-21 11:24:05'),
(12, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN-723.pdf', 79867, '2021-07-21 11:17:56'),
(11, 'ANGOT CHRISTIAN', 0, 'FACTURE-079867-ANGOT CHRISTIAN-470.pdf', 79867, '2021-07-19 18:04:07');

-- --------------------------------------------------------

--
-- Structure de la table `mail`
--

DROP TABLE IF EXISTS `mail`;
CREATE TABLE IF NOT EXISTS `mail` (
  `IdMail` int(11) NOT NULL AUTO_INCREMENT,
  `NomFacture` varchar(255) NOT NULL,
  `DateEnvoi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdMail`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `mail`
--

INSERT INTO `mail` (`IdMail`, `NomFacture`, `DateEnvoi`) VALUES
(1, 'Facture-client1', '2021-07-21 07:19:26'),
(2, 'Facture_client2', '2021-07-21 07:19:26'),
(3, 'FACTURE-079867-ANGOT CHRISTIAN.pdf', '2021-07-21 07:19:26'),
(4, 'FACTURE-079867-ANGOT CHRISTIAN-470.pdf', '2021-07-21 07:19:27');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
