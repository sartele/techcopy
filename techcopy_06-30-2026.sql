-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 30, 2026 alle 13:23
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

-- --------------------------------------------------------

--
-- Struttura della tabella `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(64570, 'ORANGES S.R.L', 'Roberta', '0444 1578107', 'dittaoranges@gmail.com', 'VIALE STAZIONE, 10', 'ALTAVILLA VICENTINA', '36077', 45.5125559, 11.4545489, '', 1, '2026-06-15 16:13:35', '2026-06-18 13:39:24'),
(64571, 'SENIOR VENETO', 'Vincenzo - Elisabetta', '0444 363536', 'segreteria@seniorveneto.com', 'VIA CRISTOFORO COLOMBO 7', 'VICENZA', '36100', 45.5567654, 11.5183100, 'BAR 961837 / GIORGIO', 1, '2026-06-29 12:57:23', '2026-06-29 13:58:08'),
(64572, 'COLLEGIO GEOMETRI E GEOMETRI LAUREATI DELLA PROVINCIA DI VICENZA', 'Guido', '0444385311--0', 'vicenza@cng.it', 'VIA LANZA 106', 'VICENZA', '36100', 45.5464945, 11.5121900, '', 1, '2026-06-29 13:03:41', '2026-06-29 13:58:07'),
(64573, 'ROWE SNC DI BRUSATERRA R. & C.', 'Grazia  ROBERTO', '3487266191', 'rowe5@rowe.it', 'VIA LL ZAMENHOF 825-851', 'VICENZA', '36100', NULL, NULL, '', 1, '2026-06-30 11:45:07', '2026-06-30 11:45:07'),
(64574, 'INTERMOBEL S.A.S. ', 'luisa carretta--FRANCESCA', '0444555311', 'amm@intermobel.it', 'VIALE DELL\'ARTIGIANATO, 6', 'LONGARE', '36023', NULL, NULL, '', 1, '2026-06-30 12:19:32', '2026-06-30 12:19:32'),
(64575, 'FIMIC SRL', 'Erika Canton', '049 595 7163 ', 'info@fimic.it', 'VIA OSPITALE 44', 'CARMIGNANO DI BRENTA', '35010', NULL, NULL, '', 1, '2026-06-30 12:39:41', '2026-06-30 12:39:41'),
(64576, 'FIMA  SRL', 'menegazzo Giorgio--SABRINA-silvia', '0444570277', 'gmenegazzo@fimaweb.com', 'VIALE DEL LAVORO, 20', 'VICENZA', '36100', NULL, NULL, '', 1, '2026-06-30 12:44:02', '2026-06-30 12:44:02'),
(64577, 'AUTOFFICINA SANTACATTERINA RENATO SRL', '', '04449700254', 'info@santacatterina.net', 'VIA CAVOUR 39', 'COSTABISSARA', '36030', NULL, NULL, '', 1, '2026-06-30 12:46:28', '2026-06-30 12:46:28'),
(64578, 'METH  SRL', 'Daniela --  LAURA--giulia', '0445891799', 'amministrazione@meth-group.com', 'VIA FONDOVILLA 84/F', 'CARRE\'', '36010', NULL, NULL, '', 1, '2026-06-30 12:49:44', '2026-06-30 12:49:44'),
(64579, 'VICENZA VISION SRL (STUDIO PEDROTTI)', 'Teresa', '0444541000', 'info@studiopedrotti.it', 'VIALE MAZZINI, 11', 'VICENZA', '36100', NULL, NULL, '', 1, '2026-06-30 12:55:45', '2026-06-30 12:55:45'),
(64580, 'P.G. GROUP SRL', 'LUCA PAOLO', '3513409719', 'amministrazione@pggroupsrl.it', 'Viale Verona, 78', 'ALTAVILLA VICENTINA', '36077', NULL, NULL, '', 1, '2026-06-30 13:01:32', '2026-06-30 13:01:32'),
(64581, 'SCHUBERTH PERFORMANCE SRL', 'Francesco Sicara', '04451921006', 'accounting@schuberthperformance.com', 'VIA LAGO TRASIMENO 25/A', 'SCHIO', '36015', NULL, NULL, '', 1, '2026-06-30 13:05:48', '2026-06-30 13:05:48');

-- --------------------------------------------------------

--
-- Struttura della tabella `consumables`
--

CREATE TABLE `consumables` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `consumables`
--

INSERT INTO `consumables` (`id`, `name`, `code`, `brand`, `type`, `color`, `stock`, `min_stock`, `unit`, `price`, `supplier`, `notes`, `created_at`, `updated_at`) VALUES
(9, 'Carta A4 80g/m² (risma)', 'CARTA-A4-80', 'Generica', 'carta', '', 45, 5, 'risma', NULL, '', '', '2026-03-27 08:03:27', '2026-04-01 14:02:03'),
(10, 'Rulli presa carta Toshiba', 'RM1-6414', 'Toshiba', 'ricambio', '', 6, 1, 'pz', NULL, '', '', '2026-03-27 08:03:27', '2026-04-01 14:01:02'),
(11, 'TOSHIBA TONER PER e-STUDIO 2515-4515 CYAN TFC415EC', 'TOSTFC415EC', 'TOSHIBA', 'toner', 'CYAN', 10, 2, 'pz', 90.00, 'Italiana Riprografica', '', '2026-03-30 11:01:15', '2026-03-30 11:01:15'),
(12, 'TOSHIBA TONER PER e-STUDIO 2515-4515 BLACK TFC415EK', 'TOSTFC415EK', 'TOSHIBA', 'toner', 'BLACK', 10, 2, 'pz', 75.00, 'Italiana Riprografica', '', '2026-03-30 11:02:00', '2026-03-30 11:02:00'),
(13, 'TOSHIBA TONER PER e-STUDIO 2515-4515 MAGENTA TFC415EM', 'TOSTFC415EM', 'TOSHIBA', 'toner', 'MAGENTA', 10, 2, 'pz', 91.00, 'Italiana Riprografica', '', '2026-03-30 11:02:26', '2026-04-01 13:59:46'),
(14, 'TOSHIBA TONER PER e-STUDIO 2515-4515 YELLOW TFC415EY', 'TOSTFC415EY', 'TOSHIBA', 'toner', 'YELLOW', 10, 2, 'pz', 90.00, 'Italiana Riprografica', '', '2026-03-30 11:03:21', '2026-06-17 13:55:50');

-- --------------------------------------------------------

--
-- Struttura della tabella `printers`
--

CREATE TABLE `printers` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `printers`
--

INSERT INTO `printers` (`id`, `client_id`, `brand`, `model`, `serial`, `type`, `location`, `rete_lan`, `rete_wifi`, `adf`, `has_duplex`, `has_scan`, `counter_bw`, `counter_color`, `purchase_date`, `warranty_exp`, `notes`, `active`, `created_at`, `updated_at`) VALUES
(17, 64570, 'Pantum', 'CM2100ADW', 'CS1MV008EG', 'color', 'Sede Legale', 1, 1, 'simple', 0, 1, 0, 0, '2026-06-15', '2028-06-15', 'Stampante venduta al cliente con fattura', 1, '2026-06-15 16:15:56', '2026-06-15 16:15:56'),
(18, 64571, 'TOSHIBA', 'E-STUDIO 2510', 'CNLJ23515', 'color', '', 1, 1, 'simple', 1, 1, 162000, 170000, '2026-01-01', '2028-04-30', 'Aggiunta dispositivo WIFI per stampante TOSHIBA', 1, '2026-06-29 12:59:35', '2026-06-29 12:59:35'),
(19, 64572, 'BROTHER', 'HL-L2445DW', 'E83107C5N398613', 'bw', 'Segreteria', 1, 1, 'none', 0, 0, 0, 0, '2026-06-29', NULL, 'Stampante in vendita', 1, '2026-06-29 13:31:32', '2026-06-29 13:32:10'),
(20, 64573, 'HP', 'LASERJET P2055DN', '0000', 'bw', '', 1, 0, 'none', 0, 0, 0, 0, '2020-01-01', NULL, 'Stampante di proprietà', 1, '2026-06-30 11:49:05', '2026-06-30 11:49:05'),
(21, 64573, 'HP', 'LaserJet Enterprise M406', '00000', 'bw', '', 1, 0, 'none', 0, 0, 0, 0, '2020-01-01', NULL, 'Stampante di proprietà', 1, '2026-06-30 11:50:10', '2026-06-30 11:50:10'),
(22, 64573, 'HP', 'LaserJet M501DN', '222222', 'bw', '', 1, 0, 'none', 1, 0, 0, 0, '2018-05-07', '2028-05-30', 'Stampante in Costo copia', 1, '2026-06-30 11:52:42', '2026-06-30 11:52:42'),
(23, 6, 'CANON', 'iR1643i', '5JH12774', 'bw', 'Magazzino', 1, 1, 'duplex', 1, 1, 23064, 0, '2026-04-18', '2031-03-31', '', 1, '2026-06-30 11:56:17', '2026-06-30 11:56:46'),
(24, 7, 'TOSHIBA', 'e-STUDIO3015AC', 'CNDL52529', 'color', '', 1, 0, 'none', 1, 1, 83607, 131121, '2025-12-03', NULL, '', 1, '2026-06-30 11:59:11', '2026-06-30 11:59:11'),
(25, 7, 'TOSHIBA', 'e-STUDIO400AC', 'CRBN46710', 'color', '', 1, 0, 'duplex', 1, 1, 37979, 71785, '2025-01-01', NULL, '', 1, '2026-06-30 12:00:01', '2026-06-30 12:00:01'),
(26, 8, 'TOSHIBA', 'e-STUDIO330AC', 'CRKK15166', 'color', '', 1, 0, 'duplex', 1, 1, 20582, 26488, '2026-01-01', NULL, '', 1, '2026-06-30 12:01:43', '2026-06-30 12:01:43'),
(27, 9, 'EPSON', 'WF-C5790', 'X3B8070134', 'color', '', 1, 1, 'simple', 1, 1, 6655, 28671, '2026-01-01', NULL, '', 1, '2026-06-30 12:13:35', '2026-06-30 12:13:35'),
(28, 12, 'TOSHIBA', 'e-STUDIO330AC', 'CRKL28605', 'color', '', 1, 0, 'duplex', 1, 1, 36983, 13736, '2026-01-01', NULL, '', 1, '2026-06-30 12:16:45', '2026-06-30 12:16:45'),
(29, 10, 'TOSHIBA', 'e-STUDIO3015AC', 'CNKL45672', 'color', '', 1, 0, 'duplex', 1, 1, 159488, 41185, '2026-01-01', NULL, '', 1, '2026-06-30 12:18:06', '2026-06-30 12:18:06'),
(30, 64574, 'TOSHIBA', 'e-STUDIO2515AC', 'CNGK45452', 'color', '', 1, 0, 'duplex', 1, 1, 0, 0, '2026-01-01', NULL, '', 1, '2026-06-30 12:21:12', '2026-06-30 12:21:12'),
(31, 64574, 'TOSHIBA', 'e-STUDIO3505AC', 'CFGH69759', 'color', '', 1, 0, 'duplex', 1, 1, 0, 0, '2026-01-01', NULL, '', 1, '2026-06-30 12:21:41', '2026-06-30 12:21:41'),
(32, 64575, 'CANON', 'iR-ADV C359', '4HP17967', 'color', '', 1, 0, 'duplex', 1, 1, 0, 0, '2024-11-27', NULL, '', 1, '2026-06-30 12:41:50', '2026-06-30 12:41:50'),
(33, 64575, 'CANON', 'iR-ADV C359', '4HP17911', 'color', '', 1, 0, 'duplex', 1, 1, 0, 0, '2024-11-27', NULL, '', 1, '2026-06-30 12:42:29', '2026-06-30 12:42:29'),
(34, 64576, 'TOSHIBA', 'e-STUDIO4515AC', 'CNJK59388', 'color', '', 1, 0, 'duplex', 1, 1, 6800, 50000, '2023-11-01', '2028-10-30', '', 1, '2026-06-30 12:45:04', '2026-06-30 12:45:04'),
(35, 64577, 'TOSHIBA', 'e-STUDIO330AC', 'CRLN63973', 'color', '', 1, 0, 'simple', 1, 1, 11768, 5031, '2024-12-12', '2029-12-01', '', 1, '2026-06-30 12:48:13', '2026-06-30 12:48:13'),
(36, 64578, 'TOSHIBA', 'e-STUDIO2515AC', 'CNAM60258', 'color', '', 1, 0, 'none', 1, 1, 177466, 18640, '2018-06-10', '2028-02-04', '', 1, '2026-06-30 12:51:15', '2026-06-30 12:51:15'),
(37, 64578, 'HP', 'PageWide Pro 477dw MFP', 'CN8ABJX068', 'color', '', 1, 1, 'simple', 1, 1, 82066, 11486, '2019-02-19', '2028-02-28', '', 1, '2026-06-30 12:52:43', '2026-06-30 12:52:43'),
(38, 64579, 'TOSHIBA', 'e-STUDIO330AC', 'CRBM32362', 'color', '', 1, 0, 'duplex', 1, 1, 11923, 55554, '2024-01-01', '2030-05-13', '', 1, '2026-06-30 12:56:49', '2026-06-30 12:56:49'),
(39, 64579, 'BROTHER', 'DCP-L3520CDW', 'E82788D5N931841', 'color', '', 1, 0, 'none', 1, 1, 2882, 3694, '2025-05-14', '2030-05-13', '', 1, '2026-06-30 12:58:47', '2026-06-30 12:58:47'),
(40, 64580, 'TOSHIBA', 'e-STUDIO2508A', 'CGDF12412', 'bw', '', 1, 0, 'simple', 1, 1, 440573, 0, '2026-02-11', NULL, '', 1, '2026-06-30 13:02:42', '2026-06-30 13:02:42'),
(41, 64580, 'TOSHIBA', 'e-STUDIO2508A', 'CGBG58364', 'bw', '', 1, 0, 'simple', 1, 1, 138082, 0, '2026-02-11', NULL, '', 1, '2026-06-30 13:03:28', '2026-06-30 13:03:28'),
(42, 64580, 'CANON', 'MF440 Series', '2TF21038', 'bw', '', 1, 0, 'simple', 1, 1, 174519, 0, '2026-02-11', NULL, '', 1, '2026-06-30 13:04:40', '2026-06-30 13:04:40'),
(43, 64581, 'TOSHIBA', 'e-STUDIO3005', '1', 'color', '', 1, 0, 'simple', 1, 1, 0, 0, NULL, NULL, 'SEDE AMMINISTRATIVA - SISTEMARE NUMERO DI SERIE', 1, '2026-06-30 13:09:03', '2026-06-30 13:09:03');

-- --------------------------------------------------------

--
-- Struttura della tabella `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `consumable_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('carico','scarico','rettifica') NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
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
  `client_signature_path` varchar(255) DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `tickets`
--

INSERT INTO `tickets` (`id`, `client_id`, `printer_id`, `tech_id`, `status`, `priority`, `type`, `title`, `description`, `notes`, `work_report`, `travel_time`, `work_time`, `counter_bw`, `counter_color`, `resolved`, `closed_at`, `created_at`, `updated_at`, `client_signature_path`, `signed_at`) VALUES
(11, 64570, 17, 4, 'closed', 'urgent', 'errore', 'Installazione stampante', 'Installare la stampante presso la sede legale del cliente solo tramite wifi', '', 'Installazione stampante con prove di stampa e spiegato al cliente come utilizzare la scansione.', 30, 30, 0, 0, 1, '0000-00-00 00:00:00', '2026-06-15 16:16:50', '2026-06-23 11:20:03', '/techcopy/uploads/signatures/ticket_11.png', '2026-06-23 11:20:03'),
(12, 64570, 17, 4, 'closed', 'normal', 'guasto', 'Problemi configurazione wifi', 'La stampante non è più raggiungibile tramite wifi', '', '', 30, 0, 0, 0, 1, '2026-06-29 13:56:54', '2026-06-17 14:05:46', '2026-06-29 13:56:54', '/techcopy/uploads/signatures/ticket_12.png', '2026-06-23 16:24:35'),
(13, 64570, 17, 4, 'closed', 'normal', 'guasto', 'Nuovo problema di prova', 'Nuovo problema di prova per controllare la firma', '', '', 0, 0, 0, 0, 1, '2026-06-29 13:56:31', '2026-06-23 16:41:30', '2026-06-29 13:56:31', '/techcopy/uploads/signatures/ticket_13.png', '2026-06-29 13:53:03'),
(14, 64571, 18, 4, 'closed', 'normal', 'guasto', 'Stampante in errore/offline', 'Il cliente dice che non riesce a stampare', 'Fermo carta cassetto 1 A4 rotto, chiedono la sostituzione.', 'Cristian: collegato con Anydesk ma la stampante non è rilevabile nella rete del cliente. Dobbiamo andare in sede dal cliente\r\nDaniele: andato dal cliente, stampante bloccata per cambio vaschetta di recupero, cambiato tutto ok.', 0, 15, 0, 0, 1, '2026-06-29 16:23:12', '2026-06-29 13:00:20', '2026-06-29 16:23:22', '/techcopy/uploads/signatures/ticket_14.png', '2026-06-29 16:23:22'),
(15, 64572, 19, 4, 'closed', 'normal', 'installazione', 'Installazione Brother', 'Istallazione Brother HL-L2445DW, con una cartuccia aggiuntiva.', '', 'Installata stampante in entrata uffici, con collegamento di rete', 15, 60, 0, 0, 0, '2026-06-29 16:21:26', '2026-06-29 13:34:13', '2026-06-29 16:21:26', '/techcopy/uploads/signatures/ticket_15.png', '2026-06-29 16:20:34');

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_files`
--

CREATE TABLE `ticket_files` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `filesize` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_history`
--

CREATE TABLE `ticket_history` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `action` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `ticket_history`
--

INSERT INTO `ticket_history` (`id`, `ticket_id`, `user_id`, `user_name`, `action`, `created_at`) VALUES
(26, 11, 1, 'administrator', 'Ticket aperto', '2026-06-15 16:16:50'),
(27, 11, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-15 16:18:00'),
(28, 12, 1, 'administrator', 'Ticket aperto', '2026-06-17 14:05:46'),
(29, 11, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-22 14:30:59'),
(30, 12, 4, 'Daniele', 'Aggiornato — Stato: open', '2026-06-23 15:17:29'),
(31, 12, 4, 'Daniele', 'Aggiornato — Stato: open', '2026-06-23 15:19:07'),
(32, 13, 4, 'Daniele', 'Ticket aperto', '2026-06-23 16:41:30'),
(33, 14, 1, 'administrator', 'Ticket aperto', '2026-06-29 13:00:20'),
(34, 14, 1, 'administrator', 'Aggiornato — Stato: open', '2026-06-29 13:01:24'),
(35, 14, 1, 'administrator', 'Aggiornato — Stato: open', '2026-06-29 13:01:53'),
(36, 14, 1, 'administrator', 'Aggiornato — Stato: open', '2026-06-29 13:01:53'),
(37, 15, 1, 'administrator', 'Ticket aperto', '2026-06-29 13:34:14'),
(39, 13, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-29 13:56:31'),
(40, 12, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-29 13:56:54'),
(41, 15, 4, 'Daniele', 'Aggiornato — Stato: open', '2026-06-29 16:20:40'),
(42, 15, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-29 16:20:44'),
(43, 15, 4, 'Daniele', 'Aggiornato — Stato: closed', '2026-06-29 16:21:24'),
(44, 15, 4, 'Daniele', 'Aggiornato — Stato: closed', '2026-06-29 16:21:26'),
(45, 14, 4, 'Daniele', 'Intervento marcato come risolto — chiuso automaticamente', '2026-06-29 16:23:13');

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_parts`
--

CREATE TABLE `ticket_parts` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `consumable_id` int(11) DEFAULT NULL,
  `part_name` varchar(255) NOT NULL,
  `part_code` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ticket_users`
--

CREATE TABLE `ticket_users` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_note` varchar(100) DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `ticket_users`
--

INSERT INTO `ticket_users` (`id`, `ticket_id`, `user_id`, `role_note`, `added_at`) VALUES
(29, 11, 4, 'Responsabile', '2026-06-22 14:30:59'),
(41, 13, 4, 'Responsabile', '2026-06-29 13:56:31'),
(42, 12, 4, 'Responsabile', '2026-06-29 13:56:54'),
(46, 15, 4, 'Responsabile', '2026-06-29 16:21:26'),
(47, 14, 3, 'Supporto', '2026-06-29 16:23:12'),
(48, 14, 4, 'Responsabile', '2026-06-29 16:23:12');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','supervisor','tech','viewer','insert') NOT NULL DEFAULT 'tech',
  `avatar` varchar(4) NOT NULL DEFAULT 'US',
  `color` varchar(10) NOT NULL DEFAULT '#00d4ff',
  `notify_email` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `color`, `notify_email`, `active`, `created_at`, `updated_at`) VALUES
(1, 'administrator', 'admin@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'admin', 'AD', '#a78bfa', 0, 1, '2026-03-27 08:03:27', '2026-06-15 13:59:18'),
(2, 'Giovanni', 'spedizione1@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'supervisor', 'GG', '#00d4ff', 0, 1, '2026-03-27 08:03:27', '2026-06-22 14:22:44'),
(3, 'Christian', 'spedizione2@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'tech', 'CR', '#39d353', 0, 1, '2026-03-27 08:03:27', '2026-06-22 14:22:47'),
(4, 'Daniele', 'spedizione@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'tech', 'DS', '#f79000', 1, 1, '2026-03-27 08:03:27', '2026-04-02 08:23:28'),
(5, 'Marco', 'spedizione3@riscrive.it', '$2y$10$ZsLE1PGxxvglCSYW0EL7Nu/7142wgjO131tW.H/XfRwgiJ6sIryey', 'supervisor', 'M', '#8b98a8', 0, 1, '2026-03-27 08:03:27', '2026-06-17 14:31:09'),
(6, 'Silvia', 'spedizione4@riscrive.it', '$2y$10$lTgHVe1G.xgqTLwX62cNb.AbcTMV4fpTBFxZCQ9zDaSqcF4G8h/ia', 'insert', 'S', '#e879f9', 0, 1, '2026-04-02 08:55:24', '2026-06-17 14:31:18'),
(7, 'Patrizia', 'pzanotto@riscrive.it', '$2y$10$MvdtvmYJnQb2vZ2XqcY0Re6bUZYPHQ2pGImfNzAe0mBEnVNnB1vG.', 'insert', 'P', '#38bdf8', 0, 1, '2026-06-17 14:31:46', '2026-06-22 14:22:41'),
(8, 'Claudia', 'claudiafontana@riscrive.it', '$2y$10$95BY8otfy3/RNo2qJIoHLeXyn5jPjfOLmTxcfuRD02z6hQjn/MDFO', 'insert', 'C', '#a78bfa', 0, 1, '2026-06-17 14:32:24', '2026-06-22 14:22:42');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `consumables`
--
ALTER TABLE `consumables`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `printers`
--
ALTER TABLE `printers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indici per le tabelle `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consumable_id` (`consumable_id`);

--
-- Indici per le tabelle `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `printer_id` (`printer_id`),
  ADD KEY `tech_id` (`tech_id`);

--
-- Indici per le tabelle `ticket_files`
--
ALTER TABLE `ticket_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indici per le tabelle `ticket_history`
--
ALTER TABLE `ticket_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indici per le tabelle `ticket_parts`
--
ALTER TABLE `ticket_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `fk_tp_consumable` (`consumable_id`);

--
-- Indici per le tabelle `ticket_users`
--
ALTER TABLE `ticket_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket_user` (`ticket_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64582;

--
-- AUTO_INCREMENT per la tabella `consumables`
--
ALTER TABLE `consumables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `printers`
--
ALTER TABLE `printers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT per la tabella `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `ticket_files`
--
ALTER TABLE `ticket_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ticket_history`
--
ALTER TABLE `ticket_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT per la tabella `ticket_parts`
--
ALTER TABLE `ticket_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `ticket_users`
--
ALTER TABLE `ticket_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
