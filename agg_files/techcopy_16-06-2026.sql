-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 16, 2026 alle 14:16
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techcopy`
--
CREATE DATABASE IF NOT EXISTS `techcopy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `techcopy`;

-- --------------------------------------------------------

--
-- Struttura della tabella `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64571 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `clients`
--

INSERT INTO `clients` (`id`, `name`, `contact`, `phone`, `email`, `address`, `city`, `zip`, `lat`, `lng`, `notes`, `active`, `created_at`, `updated_at`) VALUES
(6, 'BIOTEC SRL', 'MAX - LAURA - CRISTINA', '0444 591683', 'info@biotecitalia.com', 'VIALE DELLA REPUBBLICA , 20', 'DUEVILLE', '36031', 45.6265689, 11.5645598, '', 1, '2026-03-27 14:24:11', '2026-03-27 14:33:06'),
(7, 'CARROZZERIA RIVA LEONIDO SRL', 'GIULIANA GIOVANNI ALESSIO TIZIANO', '0444595034', 'info@carrozzeriariva.com', 'VIA MAROSTICANA 6', 'POVOLARO DI DUEVILLE', '36030', 45.6120222, 11.5643794, '', 1, '2026-03-27 14:28:13', '2026-03-27 14:33:07'),
(8, 'COSTABARAUSSE SAS DI COSTA RICCARDO & C', 'BARBARA SABRINA MARTINA', '0445897028', 'info@costabarausse.it', 'VIA ASTICO 29', 'FARA VICENTINO', '36030', 45.7067786, 11.5474406, '', 1, '2026-03-27 14:29:17', '2026-03-27 14:33:08'),
(9, 'DORMEX SNC', 'VINCENZO', '0444964838', 'dormex@libero.it', 'STRADA PADANA SUPERIORE VERSO VERONA, 10', 'VICENZA', '36100', 45.5355000, 11.5098000, '', 1, '2026-03-27 14:30:22', '2026-04-14 15:40:59'),
(10, 'TRASPORTI CORTESE SRL', 'CRISTIAN', '0445333309', 'info@cortesetrasporti.it', 'VIA RAFFAELLO 4/A', 'LUGO DI VICENZA', '36030', 45.7451950, 11.5257419, '', 1, '2026-03-27 14:31:30', '2026-03-27 14:33:11'),
(11, 'SCRIVE E RISCRIVE srl', 'MARCO BIGNOTTI', '0444565814', 'info.cavazzale@riscrive.it', 'VIA MERCATO NUOVO, 9', 'VICENZA', '36100', 45.5433639, 11.5203908, 'SEDE LEGALE: VIA MERCATO NUOVO - VICENZA\r\nSEDE OPERATIVA: VIA SAVIABONA, 143/A - CAVAZZALE\r\nMAGAZZINO: VIA TRENTO, 12 - CAVAZZALE', 1, '2026-03-27 14:33:05', '2026-03-27 14:33:10'),
(12, 'MARSETTI GOMME SRLS', 'DILAN', '0444977209', 'autofficinagommemarsetti@gmail.com', 'VIA EUROPA, 45F', 'ISOLA VICENTINA', '36033', 45.6221103, 11.4564603, '', 1, '2026-03-27 14:35:43', '2026-03-30 08:08:43'),
(13, 'ISTITUTO FIDUCIARIO VENETO SRL', 'ZANGUIO', '0444 322233', 'zanguio@studiozanguio.it', 'CONTRA\' CARPAGNON 11', 'VICENZA', '36100', 45.5447101, 11.5457311, '', 1, '2026-03-30 11:22:26', '2026-04-01 14:46:41'),
(64569, 'name', 'contact', 'phone', 'email', 'address', 'city', 'zip', 0.0000000, 0.0000000, 'notes', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(64570, 'ORANGES S.R.L', 'Roberta', '0444 1578107', 'dittaoranges@gmail.com', 'VIALE STAZIONE, 10', 'ALTAVILLA VICENTINA', '36077', NULL, NULL, '', 1, '2026-06-15 16:13:35', '2026-06-15 16:13:35');

-- --------------------------------------------------------

--
-- Struttura della tabella `consumables`
--

DROP TABLE IF EXISTS `consumables`;
CREATE TABLE IF NOT EXISTS `consumables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `code` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `type` enum('toner','drum','carta','ricambio','altro') NOT NULL DEFAULT 'toner',
  `color` varchar(30) DEFAULT '',
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 2,
  `unit` varchar(20) NOT NULL DEFAULT 'pz',
  `price` decimal(10,2) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `consumables`
--

INSERT INTO `consumables` (`id`, `name`, `code`, `brand`, `type`, `color`, `stock`, `min_stock`, `unit`, `price`, `supplier`, `notes`, `created_at`, `updated_at`) VALUES
(9, 'Carta A4 80g/m² (risma)', 'CARTA-A4-80', 'Generica', 'carta', '', 45, 5, 'risma', NULL, '', '', '2026-03-27 08:03:27', '2026-04-01 14:02:03'),
(10, 'Rulli presa carta Toshiba', 'RM1-6414', 'Toshiba', 'ricambio', '', 6, 1, 'pz', NULL, '', '', '2026-03-27 08:03:27', '2026-04-01 14:01:02'),
(11, 'TOSHIBA TONER PER e-STUDIO 2515-4515 CYAN TFC415EC', 'TOSTFC415EC', 'TOSHIBA', 'toner', 'CYAN', 10, 2, 'pz', 90.00, 'Italiana Riprografica', '', '2026-03-30 11:01:15', '2026-03-30 11:01:15'),
(12, 'TOSHIBA TONER PER e-STUDIO 2515-4515 BLACK TFC415EK', 'TOSTFC415EK', 'TOSHIBA', 'toner', 'BLACK', 10, 2, 'pz', 75.00, 'Italiana Riprografica', '', '2026-03-30 11:02:00', '2026-03-30 11:02:00'),
(13, 'TOSHIBA TONER PER e-STUDIO 2515-4515 MAGENTA TFC415EM', 'TOSTFC415EM', 'TOSHIBA', 'toner', 'MAGENTA', 10, 2, 'pz', 91.00, 'Italiana Riprografica', '', '2026-03-30 11:02:26', '2026-04-01 13:59:46'),
(14, 'TOSHIBA TONER PER e-STUDIO 2515-4515 YELLOW TFC415EY', 'TOSTFC415EY', 'TOSHIBA', 'toner', 'YELLOW', 10, 2, 'pz', 90.00, 'Italiana Riprografica', 'ITALIANA RIPROGRAFICA', '2026-03-30 11:03:21', '2026-04-01 13:59:54');

-- --------------------------------------------------------

--
-- Struttura della tabella `printers`
--

DROP TABLE IF EXISTS `printers`;
CREATE TABLE IF NOT EXISTS `printers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `brand` varchar(80) NOT NULL,
  `model` varchar(100) NOT NULL,
  `serial` varchar(80) NOT NULL,
  `type` enum('color','bw') NOT NULL DEFAULT 'bw',
  `location` varchar(150) DEFAULT NULL,
  `rete_lan` tinyint(1) NOT NULL DEFAULT 0,
  `rete_wifi` tinyint(1) NOT NULL DEFAULT 0,
  `adf` enum('none','simple','duplex') NOT NULL DEFAULT 'none',
  `has_duplex` tinyint(1) NOT NULL DEFAULT 0,
  `has_scan` tinyint(1) NOT NULL DEFAULT 0,
  `counter_bw` bigint(20) NOT NULL DEFAULT 0,
  `counter_color` bigint(20) NOT NULL DEFAULT 0,
  `purchase_date` date DEFAULT NULL,
  `warranty_exp` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `printers`
--

INSERT INTO `printers` (`id`, `client_id`, `brand`, `model`, `serial`, `type`, `location`, `rete_lan`, `rete_wifi`, `adf`, `has_duplex`, `has_scan`, `counter_bw`, `counter_color`, `purchase_date`, `warranty_exp`, `notes`, `active`, `created_at`, `updated_at`) VALUES
(17, 64570, 'Pantum', 'CM2100ADW', 'CS1MV008EG', 'color', 'Sede Legale', 1, 1, 'simple', 0, 1, 0, 0, '2026-06-15', '2028-06-15', 'Stampante venduta al cliente con fattura', 1, '2026-06-15 16:15:56', '2026-06-15 16:15:56');

-- --------------------------------------------------------

--
-- Struttura della tabella `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumable_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('carico','scarico','rettifica') NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `consumable_id` (`consumable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `consumable_id`, `ticket_id`, `user_id`, `type`, `quantity`, `notes`, `created_at`) VALUES
(10, 10, NULL, 2, 'carico', 1, '', '2026-04-01 14:00:57'),
(11, 10, NULL, 2, 'carico', 1, '', '2026-04-01 14:00:59'),
(12, 10, NULL, 2, 'carico', 1, '', '2026-04-01 14:01:01'),
(13, 10, NULL, 2, 'carico', 1, '', '2026-04-01 14:01:02');

-- --------------------------------------------------------

--
-- Struttura della tabella `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `printer_id` int(11) DEFAULT NULL,
  `tech_id` int(11) DEFAULT NULL,
  `status` enum('open','pending','closed') NOT NULL DEFAULT 'open',
  `priority` enum('normal','high','urgent') NOT NULL DEFAULT 'normal',
  `type` enum('guasto','manutenzione','errore','installazione','consulenza') NOT NULL DEFAULT 'guasto',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `work_report` text DEFAULT NULL,
  `travel_time` int(11) NOT NULL DEFAULT 0,
  `work_time` int(11) NOT NULL DEFAULT 0,
  `counter_bw` bigint(20) DEFAULT NULL,
  `counter_color` bigint(20) DEFAULT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `closed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `printer_id` (`printer_id`),
  KEY `tech_id` (`tech_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `tickets`
--

INSERT INTO `tickets` (`id`, `client_id`, `printer_id`, `tech_id`, `status`, `priority`, `type`, `title`, `description`, `notes`, `work_report`, `travel_time`, `work_time`, `counter_bw`, `counter_color`, `resolved`, `closed_at`, `created_at`, `updated_at`) VALUES
(11, 64570, 17, 4, 'closed', 'normal', 'installazione', 'Installazione stampante', 'Installare la stampante presso la sede legale del cliente solo tramite wifi', '', 'Installazione stampante con prove di stampa e spiegato al cliente come utilizzare la scansione.', 30, 30, 0, 0, 1, '2026-06-15 16:17:58', '2026-06-15 16:16:50', '2026-06-15 16:17:58');

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_files`
--

DROP TABLE IF EXISTS `ticket_files`;
CREATE TABLE IF NOT EXISTS `ticket_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `filesize` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_history`
--

DROP TABLE IF EXISTS `ticket_history`;
CREATE TABLE IF NOT EXISTS `ticket_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `action` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `ticket_history`
--

INSERT INTO `ticket_history` (`id`, `ticket_id`, `user_id`, `user_name`, `action`, `created_at`) VALUES
(26, 11, 1, 'administrator', 'Ticket aperto', '2026-06-15 16:16:50'),
(27, 11, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-15 16:18:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_parts`
--

DROP TABLE IF EXISTS `ticket_parts`;
CREATE TABLE IF NOT EXISTS `ticket_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `consumable_id` int(11) DEFAULT NULL,
  `part_name` varchar(255) NOT NULL,
  `part_code` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `fk_tp_consumable` (`consumable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_users`
--

DROP TABLE IF EXISTS `ticket_users`;
CREATE TABLE IF NOT EXISTS `ticket_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_note` varchar(100) DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_user` (`ticket_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `ticket_users`
--

INSERT INTO `ticket_users` (`id`, `ticket_id`, `user_id`, `role_note`, `added_at`) VALUES
(27, 11, 4, 'Responsabile', '2026-06-15 16:17:58');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','supervisor','tech','viewer','insert') NOT NULL DEFAULT 'tech',
  `avatar` varchar(4) NOT NULL DEFAULT 'US',
  `color` varchar(10) NOT NULL DEFAULT '#00d4ff',
  `notify_email` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `color`, `notify_email`, `active`, `created_at`, `updated_at`) VALUES
(1, 'administrator', 'admin@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'admin', 'AD', '#a78bfa', 0, 1, '2026-03-27 08:03:27', '2026-06-15 13:59:18'),
(2, 'Giovanni', 'spedizione1@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'supervisor', 'GG', '#00d4ff', 1, 1, '2026-03-27 08:03:27', '2026-06-15 16:11:00'),
(3, 'Christian', 'spedizione2@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'tech', 'CR', '#39d353', 1, 1, '2026-03-27 08:03:27', '2026-06-15 16:11:08'),
(4, 'Daniele', 'spedizione@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'tech', 'DS', '#f79000', 1, 1, '2026-03-27 08:03:27', '2026-04-02 08:23:28'),
(5, 'Marco Bignotti', 'spedizione3@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'supervisor', 'MB', '#8b98a8', 1, 1, '2026-03-27 08:03:27', '2026-06-15 16:11:18'),
(6, 'Silvia Bordin', 'spedizione4@riscrive.it', '$2y$10$lTgHVe1G.xgqTLwX62cNb.AbcTMV4fpTBFxZCQ9zDaSqcF4G8h/ia', 'insert', 'SB', '#e879f9', 0, 1, '2026-04-02 08:55:24', '2026-06-15 16:11:30');

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `printers`
--
ALTER TABLE `printers`
  ADD CONSTRAINT `printers_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`printer_id`) REFERENCES `printers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`tech_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `ticket_files`
--
ALTER TABLE `ticket_files`
  ADD CONSTRAINT `ticket_files_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `ticket_history`
--
ALTER TABLE `ticket_history`
  ADD CONSTRAINT `ticket_history_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `ticket_parts`
--
ALTER TABLE `ticket_parts`
  ADD CONSTRAINT `fk_tp_consumable` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_parts_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `ticket_users`
--
ALTER TABLE `ticket_users`
  ADD CONSTRAINT `ticket_users_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
