-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3305
-- Generation Time: Jan 10, 2025 at 03:58 AM
-- Server version: 8.3.0
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tema10`
--
CREATE DATABASE IF NOT EXISTS `tema10` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tema10`;

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `calculeaza_pret_total`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `calculeaza_pret_total` (`pret_cazare` DECIMAL(10,2), `optiune_transport_id` INT, `adulti` INT, `copii` INT, `este_client_top` BOOLEAN, `tip_plata` VARCHAR(20)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE total DECIMAL(10,2);
    DECLARE total_cazare DECIMAL(10,2);
    DECLARE total_transport DECIMAL(10,2);
    DECLARE pret_transport DECIMAL(10,2);
    
    -- Calculează totalul pentru cazare (copiii beneficiază de reducere de 50%)
    SET total_cazare = (pret_cazare * adulti) + (pret_cazare * 0.5 * copii);
    
    -- Obține și calculează prețul transportului
    IF optiune_transport_id IS NOT NULL THEN
        SELECT pret_per_persoana INTO pret_transport
        FROM optiuni_transport_excursii
        WHERE id = optiune_transport_id;
        -- Transport: toți plătesc 100%
        SET total_transport = COALESCE(pret_transport, 0) * (adulti + copii);
    ELSE
        SET total_transport = 0;
    END IF;
    
    -- Calculează totalul inițial
    SET total = total_cazare + total_transport;
    
    -- Aplică reducerea pentru client top (2%)
    IF este_client_top THEN
        SET total = total * 0.98;
    END IF;
    
    -- Calculează suma de plată în funcție de tipul plății
    IF tip_plata = 'integral' THEN
        SET total = total * 0.95; -- reducere 5% pentru plata integrală
    ELSEIF tip_plata = 'avans' THEN
        SET total = (total_cazare + total_transport) * 0.2; -- 20% din prețul original
    END IF;
    
    RETURN ROUND(total, 2);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `chitante`
--

DROP TABLE IF EXISTS `chitante`;
CREATE TABLE IF NOT EXISTS `chitante` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `rezervare_id` int DEFAULT NULL,
  `suma` decimal(10,2) NOT NULL,
  `tip_operatie` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'plata',
  `data_plata` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `fk_chitante_rezervari` (`rezervare_id`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitante`
--

INSERT INTO `chitante` (`id`, `rezervare_id`, `suma`, `tip_operatie`, `data_plata`, `creat_la`) VALUES
(45, 47, 1902.38, 'plata', '2025-01-08 22:30:01', '2025-01-08 22:30:01'),
(37, 39, 2137.50, 'plata', '2025-01-08 21:05:45', '2025-01-08 21:05:45'),
(36, 38, 2048.20, 'plata', '2025-01-08 20:34:04', '2025-01-08 20:34:04'),
(34, 36, 262.00, 'plata', '2025-01-08 19:41:06', '2025-01-08 19:41:06'),
(33, 35, 1805.00, 'plata', '2025-01-08 19:31:32', '2025-01-08 19:31:32'),
(81, 126, 4.00, 'plata', '2025-01-10 03:44:34', '2025-01-10 03:44:34'),
(82, 127, 655.50, 'plata', '2025-01-10 03:45:34', '2025-01-10 03:45:34'),
(83, 128, 2090.00, 'plata', '2025-01-10 03:46:36', '2025-01-10 03:46:36'),
(84, 129, 200.00, 'plata', '2025-01-10 03:53:45', '2025-01-10 03:53:45'),
(85, 130, 655.50, 'plata', '2025-01-10 03:55:04', '2025-01-10 03:55:04'),
(86, 131, 845.50, 'plata', '2025-01-10 03:55:55', '2025-01-10 03:55:55');

--
-- Triggers `chitante`
--
DROP TRIGGER IF EXISTS `after_chitanta_insert`;
DELIMITER $$
CREATE TRIGGER `after_chitanta_insert` AFTER INSERT ON `chitante` FOR EACH ROW BEGIN
    -- Nu mai actualizăm suma_plata pentru plăți normale
    -- Actualizăm doar pentru retururi
    IF NEW.tip_operatie = 'retur' THEN
        UPDATE rezervari 
        SET suma_plata = suma_plata - NEW.suma
        WHERE id = NEW.rezervare_id;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `calculeaza_suma_chitanta`;
DELIMITER $$
CREATE TRIGGER `calculeaza_suma_chitanta` BEFORE INSERT ON `chitante` FOR EACH ROW BEGIN
    DECLARE v_suma_plata DECIMAL(10,2);
    
    -- Obținem suma_plata din rezervare
    SELECT suma_plata INTO v_suma_plata
    FROM rezervari 
    WHERE id = NEW.rezervare_id;
    
    -- Setăm suma chitanței exact la valoarea din rezervare
    SET NEW.suma = v_suma_plata;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `circuite`
--

DROP TABLE IF EXISTS `circuite`;
CREATE TABLE IF NOT EXISTS `circuite` (
  `excursie_id` int NOT NULL,
  `descriere_traseu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `vizite_incluse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`excursie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `circuite`
--

INSERT INTO `circuite` (`excursie_id`, `descriere_traseu`, `vizite_incluse`, `creat_la`) VALUES
(30, 'Bucuresti - Franta - Germania', 'Muzeul Luvru, Arcul de Triumph', '2025-01-07 20:24:42'),
(36, 'Istanbul: Orasul ce leaga Europa de Asia, cu comori culturale precum Hagia Sophia, Palatul Topkapi si Moscheea Albastra.\r\nAnkara: Capitala Turciei, unde poti vizita Mausoleul lui Atatürk si Muzeul Civilizatiilor Anatoliene.\r\nCappadocia: Peisajele fantastice si casele sapate in stanca, faimoase pentru zborurile cu balonul cu aer cald.', 'Istanbul: Hagia Sophia, Palatul Topkapi, Moscheea Albastra si Marele Bazar.\r\nAnkara: Mausoleul lui Atatürk si Muzeul Civilizatiilor Anatoliene.\r\nCappadocia: Zbor cu balonul cu aer cald si explorarea vaii Goreme.', '2025-01-07 22:11:58');

-- --------------------------------------------------------

--
-- Table structure for table `clienti`
--

DROP TABLE IF EXISTS `clienti`;
CREATE TABLE IF NOT EXISTS `clienti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prenume` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nume` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numar_identitate` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `este_client_top` tinyint(1) DEFAULT '0',
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numar_identitate` (`numar_identitate`)
) ENGINE=MyISAM AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clienti`
--

INSERT INTO `clienti` (`id`, `prenume`, `nume`, `email`, `telefon`, `numar_identitate`, `este_client_top`, `creat_la`) VALUES
(59, 'Gabi', 'Stefan', 'gabriela.stefan@example.com', '0788890123', 'TZ456129', 0, '2025-01-08 22:30:01'),
(58, 'Gabriela', 'Stefan', 'gabriela.stefan@example.com', '0788890123', 'TZ456123', 0, '2025-01-08 22:30:01'),
(41, 'Ana', 'Popa', 'ana.popa@example.com', '0722012345', 'TM009012', 1, '2025-01-08 19:31:32'),
(42, 'Marius', 'Stan', 'mariu.stan@example.com', '0711901234', 'TM456789', 0, '2025-01-08 21:05:45'),
(43, 'Alex', 'Stan', 'mariu.stan@example.com', '0711901234', 'TM454489', 0, '2025-01-08 21:05:45'),
(117, 'Ana', 'Blandi ', 'ana@a.com', '0723981111', 'AB009019', 1, '2025-01-10 03:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `croaziere`
--

DROP TABLE IF EXISTS `croaziere`;
CREATE TABLE IF NOT EXISTS `croaziere` (
  `excursie_id` int NOT NULL,
  `categorie_nava` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `facilitati_vas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `porturi_oprire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `activitati_bord` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `descriere_traseu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `vizite_incluse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`excursie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `croaziere`
--

INSERT INTO `croaziere` (`excursie_id`, `categorie_nava`, `facilitati_vas`, `porturi_oprire`, `activitati_bord`, `descriere_traseu`, `vizite_incluse`, `creat_la`) VALUES
(31, 'Premium', 'Piscina', 'Corfu', 'Bal mascat', 'Barcelona, Spania\r\nRoma, Italia\r\nAtena, Grecia\r\nSantorini, Grecia\r\nDubrovnik, Croatia', 'Roma: Colosseumul, Vaticanul și alte situri istorice\r\nDubrovnik: Orașul Vechi și peisajele pitoresti.', '2025-01-07 20:26:27'),
(35, 'Premium', 'Cabine luxoase: Spatii confortabile, dotate cu toate facilitatile necesare pentru o sedere relaxanta.\r\nRestaurante Gourmet: Bucura-te de o varietate de preparate culinare, de la specialitati locale pana la mancaruri internationale.\r\nSpa si Fitness: Centre de spa pentru relaxare si sali de fitness moderne pentru a-ti mentine rutina de antrenament.', 'Santorini,Creta,Rhodos,Corfu', 'Activitati pentru copii: Zone de joaca si programe special create pentru cei mici.\r\nSeari tematice: Participa la seri tematice si petreceri organizate la bord.', 'Santorini: Apusuri de vis si plaje unice, cu nisip negru si rosu.\r\nCreta: Traditii autentice si peisaje naturale uimitoare.\r\nRhodos: Orasul medieval si frumusetea coastei.\r\nCorfu: Peisaje verzi si golfuri pitoresti.', 'Santorini: Plajele cu nisip vulcanic si apusurile din Oia.\r\nCreta: Palatul Knossos si orasele pitoresc din Rethymnon.\r\nRhodos: Orasul medieval si acropola Lindos.\r\nCorfu: Orasul vechi si palatul Achilleion.', '2025-01-07 21:53:05'),
(52, 'Premium', 'Divertisment: Teatre, cinematografe si cluburi pentru spectacole si filme.\r\nMagazine de Lux: Boutique-uri si magazine duty-free pentru sesiuni de shopping.', 'Singapore, Tokyo', 'Divertisment: Seri tematice, concursuri de dans, spectacole si jocuri.\r\nFacilitati pentru Copii: Programe si activitati special concepute pentru cei mici.', 'Tokyo', 'fara', '2025-01-08 17:37:39');

-- --------------------------------------------------------

--
-- Table structure for table `excursii`
--

DROP TABLE IF EXISTS `excursii`;
CREATE TABLE IF NOT EXISTS `excursii` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `oferta_speciala` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tip_masa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sezon_id` int DEFAULT NULL,
  `nume` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descriere` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `data_inceput` date NOT NULL,
  `data_sfarsit` date NOT NULL,
  `pret_cazare_per_persoana` decimal(10,2) NOT NULL,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `poza1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poza2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('activ','inactiv','anulat') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activ',
  `tip_cazare_id` bigint UNSIGNED NOT NULL,
  `locatie_id` bigint UNSIGNED NOT NULL,
  `numar_nopti` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `idx_excursii_date_tip` (`data_inceput`,`data_sfarsit`,`tip`),
  KEY `idx_excursii_status` (`status`),
  KEY `fk_excursii_tip_cazare` (`tip_cazare_id`),
  KEY `fk_excursii_locatii` (`locatie_id`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `excursii`
--

INSERT INTO `excursii` (`id`, `tip`, `oferta_speciala`, `tip_masa`, `sezon_id`, `nume`, `descriere`, `data_inceput`, `data_sfarsit`, `pret_cazare_per_persoana`, `creat_la`, `poza1`, `poza2`, `status`, `tip_cazare_id`, `locatie_id`, `numar_nopti`) VALUES
(29, 'Sejur', '', 'All inclusive', 3, 'Vacanta in Santorini', 'Santorini, o bijuterie a Marii Egee, te asteapta cu peisaje de neuitat, apusuri spectaculoase și arhitectura sa alb-albastra iconica. Bucura-te de plajele unice din Santorini, savureaza gastronomia locala si descopera istoria fascinanta a insulei intr-o vacanta de vis.               ', '2025-01-08', '2025-01-11', 1000.00, '2025-01-07 20:22:06', 'santo1.jpg', 'santo2.jpg', 'activ', 1, 6, 3),
(30, 'Circuit', 'revelion', 'Demipensiune', 2, 'Franta Romantica', 'Experimenteaza farmecul atemporal al Frantei intr-un circuit romantic de neuitat. De la strazile pline de viata din Paris si castelele din Valea Loarei, la vinurile fine din Bordeaux si frumusetea de pe Coasta de Azur, fiecare destinatie promite momente magice si amintiri pretioase.', '2025-01-17', '2025-01-20', 2000.00, '2025-01-07 20:24:42', 'franta1.jpg', 'franta2.jpg', 'activ', 1, 9, 3),
(31, 'Croaziera', 'paste', 'All inclusive', 1, 'Croaziera Mediterana', 'Descopera frumusetile Marii Mediterane intr-o croaziera de vis. Viziteaza destinatii exotice precum Italia, Grecia, Spania si Turcia, bucurandu-te de peisaje pitoresti, situri istorice si cultura vibranta. Relaxare, explorare si aventura te asteapta la fiecare oprire a navei.                     ', '2025-01-09', '2025-01-16', 1000.00, '2025-01-07 20:26:27', 'medit2.jpg', 'medit1.jpg', 'activ', 1, 13, 7),
(32, 'Sejur', 'paste', 'All inclusive', 1, 'Vacanta in Creta', 'Descopera magia Cretei, cea mai mare insula a Greciei, unde traditiile autentice se impletesc cu peisaje naturale spectaculoase. Relaxeaza-te pe plaje aurii, exploreaza siturile arheologice antice si bucura-te de ospitalitatea localnicilor intr-un sejur de neuitat.', '2025-01-10', '2025-01-14', 590.00, '2025-01-07 20:30:02', 'creta1.jpg', 'creta2.jpg', 'activ', 1, 6, 4),
(33, 'Sejur', 'revelion', 'Demipensiune', 1, 'Barcelona City Break', 'Plonjeaza in vibranta capitala a Cataloniei! Barcelona te asteapta cu arhitectura sa emblematica, bulevarde cosmopolite si plaje însorite. Viziteaza capodoperele lui Gaudí, relaxeaza-te in Parcul Guell si gusta tapas-uri delicioase intr-o atmosfera plina de viata.              ', '2025-01-08', '2025-01-10', 20.00, '2025-01-07 20:33:56', 'barcelona1.jpg', 'barcelona2.jpg', 'activ', 1, 1, 2),
(34, 'Circuit', '1mai', 'Demipensiune', 1, 'Turul Italiei', 'Descopera farmecul Italiei intr-un circuit fascinant prin cele mai iconice destinatii ale Peninsulei Italice. Exploreaza orase istorice, situri culturale de renume mondial si peisaje de vis, de la romanticele strazi din Venetia la ruinele antice din Roma. Gusta deliciile gastronomiei italiene, admira arta si arhitectura si bucura-te de ospitalitatea locala intr-o calatorie memorabila.', '2025-05-01', '2025-05-05', 500.00, '2025-01-07 21:46:55', 'italia1.jpg', 'italia2.jpg', 'activ', 3, 3, 4),
(35, 'Croaziera', '', 'Demipensiune', 4, 'Croaziera Insulele Grecesti', 'Porneste intr-o aventura captivanta prin splendidele insule ale Greciei. Viziteaza destinatii de vis, fiecare cu farmecul si istoria sa unica. Bucura-te de ape cristaline, peisaje pitoresti si ospitalitatea locala pe parcursul acestei croaziere de neuitat.                        ', '2025-05-10', '2025-05-15', 890.00, '2025-01-07 21:53:05', 'maldive1.jpg', 'creta1.jpg', 'activ', 1, 1, 5),
(36, 'Circuit', '', 'All inclusive', 3, 'Turcia - Comorile Anatoliei', '                            Descopera farmecul istoric si cultural al Turciei intr-un circuit fascinant ce te va purta prin cele mai impresionante destinatii ale acestei tari. Exploreaza orase pline de viata, situri istorice si peisaje naturale uluitoare, bucurandu-te de ospitalitatea si gastronomia locala.                        ', '2025-09-01', '2025-09-05', 1200.00, '2025-01-07 22:11:58', 'turcia1.jpg', 'turcia2.jpg', 'activ', 5, 14, 4),
(52, 'Croaziera', '', 'All inclusive', 3, 'Croaziera Asia', 'Exploreaza orasele vibrante si peisajele naturale ale Japoniei si Singapore. Viziteaza Turnul Tokyo, Templul Senso-ji, Marina Bay Sands si Gradina Botanica din Singapore.', '2025-01-18', '2025-01-25', 1900.00, '2025-01-08 17:37:39', 'asia2.jpg', 'asia1.jpg', 'activ', 1, 14, 7);

-- --------------------------------------------------------

--
-- Table structure for table `locatii`
--

DROP TABLE IF EXISTS `locatii`;
CREATE TABLE IF NOT EXISTS `locatii` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tara_id` int DEFAULT NULL,
  `nume` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locatii`
--

INSERT INTO `locatii` (`id`, `tara_id`, `nume`, `creat_la`) VALUES
(1, 1, 'Santorini', '2025-01-05 09:15:38'),
(2, 1, 'Creta', '2025-01-05 09:15:38'),
(3, 2, 'Roma', '2025-01-05 09:15:38'),
(4, 2, 'Venetia', '2025-01-05 09:15:38'),
(5, 3, 'Barcelona', '2025-01-05 09:15:38'),
(6, 4, 'Paris', '2025-01-05 09:15:38'),
(7, 5, 'Antalya', '2025-01-05 09:15:38'),
(9, 1, 'Lefkada', '2025-01-05 09:17:20'),
(10, 2, 'Milano', '2025-01-05 09:17:20'),
(11, 4, 'Le Havre', '2025-01-05 09:17:20'),
(12, 3, 'Madrid', '2025-01-05 09:17:20'),
(13, 4, 'Versailles', '2025-01-05 09:17:20'),
(14, 7, 'Tokyo', '2025-01-05 09:17:20');

-- --------------------------------------------------------

--
-- Table structure for table `mesaje_contact`
--

DROP TABLE IF EXISTS `mesaje_contact`;
CREATE TABLE IF NOT EXISTS `mesaje_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nume` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `subiect` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mesaj` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data_trimitere` datetime NOT NULL,
  `citit` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mesaje_contact`
--

INSERT INTO `mesaje_contact` (`id`, `nume`, `email`, `subiect`, `mesaj`, `data_trimitere`, `citit`) VALUES
(1, 'Oana', 'oanabelu@yahoo.com', 'test', 'test', '2025-01-05 15:21:19', 1),
(2, 'Oana', 'myrra.bell@gmail.com', 'ceva', 'testare', '2025-01-05 19:23:12', 0),
(3, 'Florea Elena', 'myrra.bell22@gmail.com', 'oferta', 'oferta Creta', '2025-01-05 19:36:24', 1),
(4, 'George', 'george@g.com', 'Vrea oferta Maldive', 'Va rog sa imi trimitei o oferta.\r\nMultumesc.', '2025-01-05 20:24:56', 0),
(5, 'Oana', 'a@yahoo.com', 'Vreau oferta Malta', 'Oferta ', '2025-01-06 11:15:50', 0);

-- --------------------------------------------------------

--
-- Table structure for table `optiuni_transport_excursii`
--

DROP TABLE IF EXISTS `optiuni_transport_excursii`;
CREATE TABLE IF NOT EXISTS `optiuni_transport_excursii` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `excursie_id` int DEFAULT NULL,
  `tip_transport` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pret_per_persoana` decimal(10,2) NOT NULL,
  `descriere` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `excursie_id` (`excursie_id`,`tip_transport`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `optiuni_transport_excursii`
--

INSERT INTO `optiuni_transport_excursii` (`id`, `excursie_id`, `tip_transport`, `pret_per_persoana`, `descriere`, `creat_la`) VALUES
(35, 32, 'Autocar', 100.00, '1', '2025-01-07 20:30:02'),
(36, 33, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 20:33:56'),
(34, 31, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 20:26:27'),
(33, 30, 'Avion', 200.00, 'Aeroportul henri coanda', '2025-01-07 20:24:42'),
(32, 29, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 20:22:06'),
(30, 28, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 19:56:02'),
(31, 29, 'Autocar', 100.00, 'De la gara de nord', '2025-01-07 20:22:06'),
(28, 27, 'Autocar', 200.00, 'Autocar modern', '2025-01-07 19:25:30'),
(29, 28, 'Autocar', 150.00, 'Autocar cu aer conditionat si TV', '2025-01-07 19:56:02'),
(37, 34, 'Autocar', 120.00, 'Autocar cu aer conditionat si TV', '2025-01-07 21:46:55'),
(38, 35, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 21:53:05'),
(39, 36, 'Autocar', 110.00, 'Autocar cu aer conditionat si TV', '2025-01-07 22:11:58'),
(40, 36, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 22:11:58'),
(41, 37, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-07 22:34:52'),
(42, 38, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 09:46:25'),
(43, 39, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 09:58:18'),
(44, 40, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 10:01:45'),
(45, 41, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 10:12:01'),
(46, 42, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 10:19:37'),
(47, 43, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 10:24:33'),
(48, 44, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 10:29:24'),
(49, 45, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 11:40:05'),
(50, 46, 'Autocar', 120.00, 'Autocar cu aer conditionat si TV', '2025-01-08 16:53:35'),
(51, 48, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 17:15:13'),
(52, 50, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 17:26:45'),
(53, 51, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 17:32:33'),
(54, 52, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 17:37:39'),
(55, 53, 'Transport propriu', 0.00, 'Transport in regim propriu', '2025-01-08 17:44:32');

-- --------------------------------------------------------

--
-- Table structure for table `participanti`
--

DROP TABLE IF EXISTS `participanti`;
CREATE TABLE IF NOT EXISTS `participanti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rezervare_id` int DEFAULT NULL,
  `nume` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenume` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numar_identitate` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tip_participant` enum('adult','copil') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_participanti_tip` (`tip_participant`),
  KEY `fk_participanti_rezervari` (`rezervare_id`)
) ENGINE=MyISAM AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participanti`
--

INSERT INTO `participanti` (`id`, `rezervare_id`, `nume`, `prenume`, `email`, `telefon`, `numar_identitate`, `tip_participant`) VALUES
(62, 47, 'Stefan', 'Gabi', 'gabriela.stefan@example.com', '0788890123', 'TZ456129', 'copil'),
(61, 47, 'Stefan', 'Gabriela', 'gabriela.stefan@example.com', '0788890123', 'TZ456123', 'adult'),
(45, 39, 'Stan', 'Marius', 'mariu.stan@example.com', '0711901234', 'TM456789', 'adult'),
(46, 39, 'Stan', 'Alex', 'mariu.stan@example.com', '0711901234', 'TM454489', 'copil'),
(44, 38, 'Popa', 'Ana', 'ana.popa@example.com', '0722012345', 'TM009012', 'adult'),
(41, 35, 'Popa', 'Ana', 'ana.popa@example.com', '0722012345', 'TM009012', 'adult'),
(42, 36, 'Popa', 'Ana', 'ana.popa@example.com', '0722012345', 'TM009012', 'adult'),
(43, 37, 'Popa', 'Ana', 'ana.popa@example.com', '0722012345', 'TM009012', 'adult'),
(100, 129, 'Blandi ', 'Ana', 'ana@a.com', '0723981111', 'AB009019', ''),
(101, 130, 'Blandi ', 'Ana', 'ana@a.com', '0744441111', 'AB009019', ''),
(102, 131, 'Blandi ', 'Ana', 'ana@a.com', '0723981111', 'AB009019', '');

-- --------------------------------------------------------

--
-- Table structure for table `rezervari`
--

DROP TABLE IF EXISTS `rezervari`;
CREATE TABLE IF NOT EXISTS `rezervari` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `excursie_id` int DEFAULT NULL,
  `numar_adulti` int NOT NULL DEFAULT '1',
  `numar_copii` int NOT NULL DEFAULT '0',
  `pret_total` decimal(10,2) NOT NULL,
  `status_plata` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `suma_plata` decimal(10,2) NOT NULL,
  `transport_id` int DEFAULT NULL,
  `pret_cazare` decimal(10,2) DEFAULT NULL,
  `pret_transport` decimal(10,2) DEFAULT NULL,
  `data_creare` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_modificare` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `idx_rezervari_status` (`status_plata`),
  KEY `fk_rezervari_clienti` (`client_id`),
  KEY `fk_rezervari_excursii` (`excursie_id`),
  KEY `fk_rezervari_transport` (`transport_id`)
) ENGINE=MyISAM AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rezervari`
--

INSERT INTO `rezervari` (`id`, `client_id`, `excursie_id`, `numar_adulti`, `numar_copii`, `pret_total`, `status_plata`, `suma_plata`, `transport_id`, `pret_cazare`, `pret_transport`, `data_creare`, `data_modificare`) VALUES
(38, 41, 30, 1, 0, 2090.00, 'integral', 4138.20, 33, 2000.00, 200.00, '2025-01-08 20:34:04', '2025-01-08 20:34:04'),
(39, 42, 31, 1, 1, 1425.00, 'integral', 3562.50, 34, 1500.00, 0.00, '2025-01-08 21:05:45', '2025-01-08 21:05:45'),
(36, 41, 36, 1, 0, 1310.00, 'avans', 524.00, 39, 1200.00, 110.00, '2025-01-08 19:41:06', '2025-01-08 19:41:06'),
(35, 41, 52, 1, 0, 1805.00, 'integral', 3610.00, 54, 1900.00, 0.00, '2025-01-08 19:31:32', '2025-01-08 19:31:32'),
(47, 59, 35, 1, 1, 1268.25, 'integral', 3170.63, 38, 1335.00, 0.00, '2025-01-08 22:30:01', '2025-01-08 22:30:01'),
(131, 117, 35, 1, 0, 845.50, 'integral', 845.50, 38, 890.00, 0.00, '2025-01-10 03:55:55', NULL),
(130, 117, 32, 1, 0, 655.50, 'integral', 655.50, 35, 590.00, 100.00, '2025-01-10 03:55:04', NULL),
(129, 117, 31, 1, 0, 1000.00, 'avans', 200.00, 34, 1000.00, 0.00, '2025-01-10 03:53:45', NULL);

--
-- Triggers `rezervari`
--
DROP TRIGGER IF EXISTS `actualizeaza_status_client_top`;
DELIMITER $$
CREATE TRIGGER `actualizeaza_status_client_top` AFTER INSERT ON `rezervari` FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) 
        FROM rezervari 
        WHERE client_id = NEW.client_id
        AND status_plata IN ('integral', 'partial', 'avans')
        AND YEAR(data_creare) = YEAR(CURRENT_DATE)) >= 3 
    THEN
        UPDATE clienti
        SET este_client_top = true
        WHERE id = NEW.client_id;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_rezervare_insert`;
DELIMITER $$
CREATE TRIGGER `before_rezervare_insert` BEFORE INSERT ON `rezervari` FOR EACH ROW BEGIN
    DECLARE locuri_disponibile INT;
    -- Logică pentru verificarea disponibilității
    -- Implementare necesară
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sejururi`
--

DROP TABLE IF EXISTS `sejururi`;
CREATE TABLE IF NOT EXISTS `sejururi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `excursie_id` int NOT NULL,
  `tip_camera` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rating_hotel` int DEFAULT NULL,
  `facilitati_hotel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `excursie_id` (`excursie_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sejururi`
--

INSERT INTO `sejururi` (`id`, `excursie_id`, `tip_camera`, `rating_hotel`, `facilitati_hotel`) VALUES
(21, 29, 'Standard', 2, 'WIFI'),
(22, 32, 'Standard', 1, 'Piscina'),
(23, 33, 'Standard', 2, 'Aer conditionat, TV');

-- --------------------------------------------------------

--
-- Table structure for table `sezoane`
--

DROP TABLE IF EXISTS `sezoane`;
CREATE TABLE IF NOT EXISTS `sezoane` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nume` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sezoane`
--

INSERT INTO `sezoane` (`id`, `nume`, `creat_la`) VALUES
(2, 'Vara', '2025-01-05 09:17:21'),
(3, 'Toamna', '2025-01-05 09:17:21'),
(1, 'Primavara', '2025-01-05 09:17:21'),
(4, 'Iarna', '2025-01-06 22:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `tari`
--

DROP TABLE IF EXISTS `tari`;
CREATE TABLE IF NOT EXISTS `tari` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nume` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tari`
--

INSERT INTO `tari` (`id`, `nume`, `creat_la`) VALUES
(1, 'Grecia', '2025-01-05 09:15:38'),
(2, 'Italia', '2025-01-05 09:15:38'),
(3, 'Spania', '2025-01-05 09:15:38'),
(4, 'Franta', '2025-01-05 09:15:38'),
(5, 'Turcia', '2025-01-05 09:15:38'),
(6, 'Maldive', '2025-01-07 15:56:31'),
(7, 'Japonia', '2025-01-07 22:26:42');

-- --------------------------------------------------------

--
-- Table structure for table `tipuri_cazare`
--

DROP TABLE IF EXISTS `tipuri_cazare`;
CREATE TABLE IF NOT EXISTS `tipuri_cazare` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nume` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `creat_la` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipuri_cazare`
--

INSERT INTO `tipuri_cazare` (`id`, `nume`, `creat_la`) VALUES
(1, 'Apartament', '2025-01-05 09:15:38'),
(2, 'Villa', '2025-01-05 09:15:38'),
(3, 'Pensiune', '2025-01-05 09:17:20'),
(4, 'Hotel', '2025-01-05 09:17:20'),
(5, 'Resort', '2025-01-05 09:15:38');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_raport_rezervari`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_raport_rezervari`;
CREATE TABLE IF NOT EXISTS `vw_raport_rezervari` (
`client` varchar(101)
,`creat_la` timestamp
,`excursie` varchar(200)
,`id` bigint unsigned
,`pret_total` decimal(10,2)
,`status_plata` varchar(50)
,`suma_plata` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_statistici_excursii`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_statistici_excursii`;
CREATE TABLE IF NOT EXISTS `vw_statistici_excursii` (
`numar_rezervari` bigint
,`tip` varchar(50)
,`venit_total` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_raport_rezervari`
--
DROP TABLE IF EXISTS `vw_raport_rezervari`;

DROP VIEW IF EXISTS `vw_raport_rezervari`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_raport_rezervari`  AS SELECT `r`.`id` AS `id`, concat(`c`.`prenume`,' ',`c`.`nume`) AS `client`, `e`.`nume` AS `excursie`, `r`.`pret_total` AS `pret_total`, `r`.`status_plata` AS `status_plata`, `r`.`suma_plata` AS `suma_plata`, `r`.`data_creare` AS `creat_la` FROM ((`rezervari` `r` join `clienti` `c` on((`r`.`client_id` = `c`.`id`))) join `excursii` `e` on((`r`.`excursie_id` = `e`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_statistici_excursii`
--
DROP TABLE IF EXISTS `vw_statistici_excursii`;

DROP VIEW IF EXISTS `vw_statistici_excursii`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_statistici_excursii`  AS SELECT `e`.`tip` AS `tip`, count(`r`.`id`) AS `numar_rezervari`, sum(`r`.`pret_total`) AS `venit_total` FROM (`excursii` `e` left join `rezervari` `r` on((`e`.`id` = `r`.`excursie_id`))) GROUP BY `e`.`tip` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
