SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données : `twict`
--
DROP DATABASE IF EXISTS `twict`;
CREATE DATABASE IF NOT EXISTS `twict` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `twict`;

-- --------------------------------------------------------

--
-- Structure de la table `bankaccounts`
--

DROP TABLE IF EXISTS `bankaccounts`;
CREATE TABLE IF NOT EXISTS `bankaccounts` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `idOwner` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `financialtransactions`
--

DROP TABLE IF EXISTS `financialtransactions`;
CREATE TABLE IF NOT EXISTS `financialtransactions` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `amount` decimal(15,2) DEFAULT NULL,
  `idSender` int UNSIGNED NOT NULL,
  `idRecipient` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactionmessages`
--

DROP TABLE IF EXISTS `transactionmessages`;
CREATE TABLE IF NOT EXISTS `transactionmessages` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `idTransaction` int UNSIGNED NOT NULL,
  `idAuthor` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `mailAddress` varchar(255) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`) VALUES
(1, 'Albert', 'Adam', 'albert.adam@twict.dev', '123456', '2023-02-15 00:00:00', '2023-02-15 11:50:22'),
(2, 'Béatrice', 'Blanc', 'beatrice.blanc@twict.dev', '', '2023-02-15 00:00:00', '2023-02-15 11:04:14'),
(3, 'Clément', 'Chevalier', 'clement.chevalier@twict.dev', NULL, '2023-02-15 00:00:00', '2023-02-15 00:00:00');

-- --------------------------------------------------------

--
-- Contraintes pour la table `bankaccounts`
--
ALTER TABLE `bankaccounts`
  ADD CONSTRAINT `fk_bankaccounts_users` FOREIGN KEY (`idOwner`) REFERENCES `users` (`id`);

-- --------------------------------------------------------

--
-- Contraintes pour la table `financialtransactions`
--
ALTER TABLE `financialtransactions`
  ADD CONSTRAINT `fk_financialtransactions_bankaccounts_sender` FOREIGN KEY (`idSender`) REFERENCES `bankaccounts` (`id`),
  ADD CONSTRAINT `fk_financialtransactions_bankaccounts_recipient` FOREIGN KEY (`idRecipient`) REFERENCES `bankaccounts` (`id`);

-- --------------------------------------------------------

--
-- Contraintes pour la table `transactionmessages`
--
ALTER TABLE `transactionmessages`
  ADD CONSTRAINT `fk_transactionmessages_financialtransactions` FOREIGN KEY (`idTransaction`) REFERENCES `financialtransactions` (`id`),
  ADD CONSTRAINT `fk_transactionmessages_users` FOREIGN KEY (`idAuthor`) REFERENCES `users` (`id`);

-- --------------------------------------------------------

--
-- Contraintes pour la table `users`
--

ALTER TABLE `users`
  ADD UNIQUE(`mailAddress`);

-- --------------------------------------------------------

COMMIT;