-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 15 avr. 2026 à 06:53
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `caisse_shop`
--

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `Id` int(11) NOT NULL,
  `Nom_produit` varchar(20) NOT NULL,
  `prix` int(11) NOT NULL,
  `description` varchar(250) NOT NULL,
  `stock` int(11) NOT NULL,
  `code_barre` varchar(8) NOT NULL,
  `image` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`Id`, `Nom_produit`, `prix`, `description`, `stock`, `code_barre`, `image`) VALUES
(51, 'Riz basmati 1kg', 3, 'Riz basmati de qualité supérieure', 14, '10000000', 0x72697a2e6a7067),
(52, 'Pâtes spaghetti 500g', 1, 'Spaghetti classiques', 40, '10000000', 0x70617465732e6a7067),
(53, 'Huile d’olive 1L', 9, 'Huile d’olive extra vierge', 25, '10000000', 0x6875696c652e6a7067),
(54, 'Sucre blanc 1kg', 2, 'Sucre blanc raffiné', 35, '10000000', 0x73756372652e6a7067),
(55, 'Sel fin 500g', 1, 'Sel fin alimentaire', 60, '10000000', 0x73656c2e6a7067),
(56, 'Lait entier 1L', 1, 'Lait entier frais', 30, '10000000', 0x6c6169742e6a7067),
(57, 'Œufs x12', 4, 'Boîte de 12 œufs frais', 20, '10000000', 0x6f657566732e6a7067),
(58, 'Pain de mie', 2, 'Pain de mie moelleux', 25, '10000000', 0x7061696e2e6a7067),
(59, 'Beurre 250g', 3, 'Beurre doux', 20, '10000000', 0x6265757272652e6a7067),
(60, 'Fromage râpé 200g', 2, 'Fromage râpé pour cuisine', 30, '10000000', 0x66726f6d6167652e6a7067),
(61, 'Poulet entier', 8, 'Poulet frais entier', 15, '10000000', 0x706f756c65742e6a7067),
(62, 'Steak haché', 4, 'Steak haché pur bœuf', 20, '10000000', 0x737465616b2e6a7067),
(63, 'Pommes 1kg', 2, 'Pommes fraîches', 40, '10000000', 0x706f6d6d65732e6a7067),
(64, 'Bananes 1kg', 2, 'Bananes mûres', 35, '10000000', 0x62616e616e65732e6a7067),
(65, 'Tomates 1kg', 3, 'Tomates fraîches', 30, '10000000', 0x746f6d617465732e6a7067),
(66, 'Eau minérale 1.5L', 1, 'Bouteille d’eau minérale', 100, '10000000', 0x6561752e6a7067),
(67, 'Jus d’orange 1L', 2, 'Jus d’orange pur jus', 25, '10000000', 0x6a75732e6a7067),
(68, 'Soda cola 1.5L', 2, 'Boisson gazeuse cola', 50, '10000000', 0x636f6c612e6a7067),
(69, 'Café moulu 250g', 4, 'Café moulu intense', 20, '10000000', 0x636166652e6a7067),
(70, 'Thé sachets x25', 2, 'Boîte de thé classique', 30, '10000000', 0x7468652e6a7067);

-- --------------------------------------------------------

--
-- Structure de la table `produit_vendu`
--

CREATE TABLE `produit_vendu` (
  `Id` int(11) NOT NULL,
  `prix_total` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `id_vente` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit_vendu`
--

INSERT INTO `produit_vendu` (`Id`, `prix_total`, `quantite`, `id_vente`, `id_produit`) VALUES
(1, 2124, 36, 1, 33),
(2, 590, 10, 2, 33),
(3, 220, 22, 3, 19),
(4, 4674, 3, 4, 28),
(5, 2147483647, 555555555, 5, 28),
(6, 6232, 4, 6, 28),
(7, 15580000, 10000, 7, 28),
(8, 12, 4, 8, 51),
(9, 96, 32, 9, 51);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `Id` int(11) NOT NULL,
  `Nom` varchar(25) NOT NULL,
  `Prenom` varchar(25) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Motdepasse` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`Id`, `Nom`, `Prenom`, `Email`, `Motdepasse`) VALUES
(1, 'nassur', 'naxo', 'nassur@gmail.com', 'nassur.1234');

-- --------------------------------------------------------

--
-- Structure de la table `vente`
--

CREATE TABLE `vente` (
  `Id` int(11) NOT NULL,
  `Montant` int(11) NOT NULL,
  `Date` datetime NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `total_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vente`
--

INSERT INTO `vente` (`Id`, `Montant`, `Date`, `id_utilisateur`, `total_produit`) VALUES
(1, 2124, '2026-04-09 08:43:36', 1, 36),
(2, 590, '2026-04-09 08:47:08', 1, 10),
(3, 220, '2026-04-09 09:28:18', 1, 22),
(4, 4674, '2026-04-10 10:28:47', 1, 3),
(5, 2147483647, '2026-04-10 10:29:18', 1, 555555555),
(6, 6232, '2026-04-10 10:37:39', 1, 4),
(7, 15580000, '2026-04-10 10:38:58', 1, 10000),
(8, 12, '2026-04-15 07:42:06', 1, 4),
(9, 96, '2026-04-15 07:42:37', 1, 32);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `produit_vendu`
--
ALTER TABLE `produit_vendu`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `vente`
--
ALTER TABLE `vente`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT pour la table `produit_vendu`
--
ALTER TABLE `produit_vendu`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `vente`
--
ALTER TABLE `vente`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
