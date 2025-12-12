-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 01:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `embun_slowbar`
--
USE `embun_slowbar`;

-- --------------------------------------------------------

--
-- Table structure for table `boardgames`
--

CREATE TABLE `boardgames` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `min_players` int(11) DEFAULT NULL,
  `max_players` int(11) DEFAULT NULL,
  `play_time` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boardgames`
--

INSERT INTO `boardgames` (`id`, `name`, `description`, `image_url`, `min_players`, `max_players`, `play_time`, `created_at`) VALUES
(1, 'SCYTHE', 'Scythe is a competitive 4x game set in an alternate-history 1920s. <br>It is a time of farming and war, broken hearts and rusted gear, innovation and valor.', '/embun/uploads/boardgames/6932c99164f9b_1764936081.jpg', 1, 5, 115, '2025-10-15 16:37:33'),
(2, 'DUNE IMPERIUM UPRISING', 'Uprising is a stand-alone expansion that expands on Dune: Imperium\'s blend of deck-building and worker placement. Continue to balance military might with political intrigue, wielding new tools in pursuit of victory. Spies will shore up your plans. Vital contracts will expand your resources. Or learn the ways of the Fremen and ride mighty sandworms into battle! Plus, a new six-player mode pits two teams against one other in the biggest struggle yet! The choices are yours.<br>The Imperium awaits!', '/embun/uploads/boardgames/6932c9cd95b72_1764936141.jpg', 1, 6, 180, '2025-10-15 16:37:33'),
(3, 'TERRAFORMING MARS', 'Terraforming Mars is a terrific game that gives players a chance to explore the way to the grandest work humans have ever attempted - the creation of a new living world.<br>Life to Mars, and Mars to Life! Great fun!<>Dr. Robert Zubrin, President Mars Society', '/embun/uploads/boardgames/6932c9d68bd2a_1764936150.jpg', 1, 5, 120, '2025-10-15 16:37:33'),
(4, 'PALEO', 'Paleo is a co-operative adventure game set in the stone age, a game in which players try to keep the human beings in their care alive while completing missions.<br>Sometimes you need a fur, sometimes a tent, but these are all minor quests compared to your long-term goal: Painting a woolly mammoth on the wall so that humans thousands of years later will know that you once existed. (Okay, you just think the mammoth painting looks cool. Preserving a record of your past existence is gravy.)', '/embun/uploads/boardgames/6932c9e5dd67a_1764936165.jpg', 2, 4, 60, '2025-10-15 16:37:33'),
(5, 'PANDEMIC LEGACY: SEASON 1', 'Pandemic Legacy - Season 1 is a unique and epic cooperative game for 2 to 4 players. Unlike most other games, some of the actions you take in Pandemic Legacy will carry over to future games. What\'s more, the game is working against you. Playing through the campaign, you will open-up sealed packets, reveal hidden information, and find the secrets locked within the game. The clock is ticking and you will only have two chances to win in a month before moving to the next.<br>Characters will gain scars. Cities will panic. Diseases will mutate.<br>No two worlds will ever be alike.', '/embun/uploads/boardgames/6932c9ed9f1fd_1764936173.jpg', 2, 4, 60, '2025-10-15 16:37:33'),
(6, 'OBSCURIO', 'Obscurio is a cooperative and asymmetrical game of images and interpretation.<br> One player is the Grimoire and must guide their team towards the exit, room by room, by giving clues about the correct door to pass through.<br> The Wizards (the rest of the team) cooperate to decipher the Grimore\'s cryptic clues and find the right image card.<br> But there is a Traitor in their ranks! They must confuse the team with image cards that will deceive the Wizards while trying to push the discussions in the wrong direction.<br> Communicate efficiently and avoid the illusions on your path to get out of the Sorcerer\'s Liblrary!<br> Be careful, you can trust no one...', '/embun/uploads/boardgames/6932ca01f4207_1764936193.jpg', 2, 8, 45, '2025-10-15 16:37:33'),
(7, 'FIT TO PRINT', 'Fit to Print is a puzzly tile-laying game about breaking news, designed by Peter McPherson and set in a charming woodland world created by Ian O\'Toole!<br>Fit to Print has various game modes from frantic real-time action, to relaxed solo puzzle modes to suit any gaming group. With so many unique ways to lay out your paper and score points, lovers of spatial puzzles will have lots to enjoy!', '/embun/uploads/boardgames/6932ca094bd45_1764936201.jpg', 1, 6, 30, '2025-10-15 16:37:33'),
(8, 'PARKS', 'PARKS celebrates national parks around the U.S. with art form the <i>Fifty-Nine Parks Print Series</i>. Players take on the role of two hikers as they trek across a new trail each season to see sites and visit parks. Each journey is unique with gear, canteens, and photos earned along the way. Welcome to PARKS!', '/embun/uploads/boardgames/6932ca1368f9f_1764936211.jpg', 1, 5, 70, '2025-10-15 16:37:33'),
(9, 'VITICULTURE: ESSENTIAL EDITION', 'Viticulture is a worker-placement strategy game that allows players to create their own Tuscan vineyard anywhere a table and a friend can be found.', '/embun/uploads/boardgames/6932ca1c37760_1764936220.jpg', 1, 6, 90, '2025-10-15 16:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `cover_image`, `category_id`, `created_at`) VALUES
(1, 'All-Star Superman', 'Grant Morrison', '/embun/uploads/books/68f4fd7b0c365_1760886139.jpg', 3, '2025-10-15 23:37:33'),
(2, 'Batman: Hush', 'Jeph Loeb', '/embun/uploads/books/68f4fdba4d632_1760886202.jpg', 3, '2025-10-15 23:37:33'),
(3, 'Yowis Ben', 'Bayu Skak', '/embun/uploads/books/68f50288e5262_1760887432.jpg', 3, '2025-10-15 23:37:33'),
(4, 'Kemala : Volume 1', 'Sweta Kartika', '/embun/uploads/books/68f4ff86a4860_1760886662.jpg', 3, '2025-10-15 23:37:33'),
(5, 'Wind Rider Sky Age', 'Is Yuniarto', '/embun/uploads/books/68f50280d7711_1760887424.jpg', 3, '2025-10-15 23:37:33'),
(6, 'Their Story', 'Egi Mugia', '/embun/uploads/books/68f5022c7bf55_1760887340.jpg', 3, '2025-10-15 23:37:33'),
(7, 'Sherlock : A Study in Pink', 'Steven Moffat', '/embun/uploads/books/68f501631a5e4_1760887139.jpg', 3, '2025-10-15 23:37:33'),
(8, 'The Screw People 1', 'Tamiki Wakaki', '/embun/uploads/books/68f501fb5a0d7_1760887291.jpg', 3, '2025-10-15 23:37:33'),
(9, 'Solanin 1', 'Inio Asano', '/embun/uploads/books/68f5017e6258b_1760887166.jpg', 3, '2025-10-15 23:37:33'),
(10, 'Masak Apa Papa Hari Ini?', 'Sihanjir', '/embun/uploads/books/68f4ffbd46903_1760886717.jpg', 3, '2025-10-15 23:37:33'),
(11, 'Hoshi no Koe : Voices of a Distant Star', 'Makoto Shinkai', '/embun/uploads/books/68f4ff3d9849b_1760886589.jpg', 14, '2025-10-15 23:37:33'),
(12, 'Rajasa 5jett and the Demon of the Wood', 'Akhmad Fadly', '/embun/uploads/books/68f5011fa50b7_1760887071.jpg', 3, '2025-10-15 23:37:33'),
(13, 'Sherlock : The Great Game', 'Steven Moffat', '/embun/uploads/books/68f50169e950e_1760887145.jpg', 3, '2025-10-15 23:37:33'),
(14, 'Solanin 2', 'Inio Asano', '/embun/uploads/books/68f50185448d7_1760887173.jpg', 3, '2025-10-15 23:37:33'),
(15, 'Not Today Human!', 'Yachi-chan', '/embun/uploads/books/68f4fffedde05_1760886782.jpg', 3, '2025-10-15 23:37:33'),
(16, 'RWBY', 'Miwa Shirow', '/embun/uploads/books/68f5013801cc9_1760887096.jpg', 3, '2025-10-15 23:37:33'),
(17, 'Daily Lives of High School Boys 3', 'Yasunobu Yamauchi', '/embun/uploads/books/68f4fe2edf35c_1760886318.jpg', 3, '2025-10-15 23:37:33'),
(18, 'The Screw People 2', 'Tamiki Wakaki', '/embun/uploads/books/68f502050115f_1760887301.jpg', 3, '2025-10-15 23:37:33'),
(19, 'The Screw People 3', 'Tamiki Wakaki', '/embun/uploads/books/68f5020e1313c_1760887310.jpg', 3, '2025-10-15 23:37:33'),
(20, 'The Scary Stories', 'Maita Nao', '/embun/uploads/books/68f501f35961b_1760887283.jpg', 3, '2025-10-15 23:37:33'),
(21, 'Daily Lives of High School Boys 4', 'Yasunobu Yamauchi', '/embun/uploads/books/68f4fe366e383_1760886326.jpg', 3, '2025-10-15 23:37:33'),
(22, 'Daily Lives of High School Boys 2', 'Yasunobu Yamauchi', '/embun/uploads/books/68f4fe25df599_1760886309.jpg', 3, '2025-10-15 23:37:33'),
(23, 'Daily Lives of High School Boys 1', 'Yasunobu Yamauchi', '/embun/uploads/books/68f4fe1c3f51d_1760886300.jpg', 3, '2025-10-15 23:37:33'),
(24, 'Grey &amp; Jingga : The Twilight', 'Sweta Kartika', '/embun/uploads/books/68f4feffddeeb_1760886527.jpg', 3, '2025-10-15 23:37:33'),
(25, 'Club Activities? So What!', 'Katsuki Izumi', '/embun/uploads/books/68f4fdf5430ac_1760886261.jpg', 3, '2025-10-15 23:37:33'),
(26, 'Nusa Five : Volume 1', 'Sweta Kartika', '/embun/uploads/books/68f500068008a_1760886790.jpg', 3, '2025-10-15 23:37:33'),
(27, 'Kill Starter 1', 'Erfan Fajar', '/embun/uploads/books/68f4ff9451339_1760886676.jpg', 3, '2025-10-15 23:37:33'),
(28, '5 Menit Sebelum Tayang 5', 'Ockto', '/embun/uploads/books/68f4fd5c7ea12_1760886108.jpg', 3, '2025-10-15 23:37:33'),
(29, '5 Menit Sebelum Tayang 4', 'Ockto', '/embun/uploads/books/68f4fd55bcc9a_1760886101.jpg', 3, '2025-10-15 23:37:33'),
(30, '5 Menit Sebelum Tayang 3', 'Ockto', '/embun/uploads/books/68f4fd4f0e312_1760886095.jpg', 3, '2025-10-15 23:37:33'),
(31, '5 Menit Sebelum Tayang 2', 'Ockto', '/embun/uploads/books/68f4fd488217b_1760886088.jpg', 3, '2025-10-15 23:37:33'),
(32, '5 Menit Sebelum Tayang 1', 'Ockto', '/embun/uploads/books/68f4fd418e74a_1760886081.jpg', 3, '2025-10-15 23:37:33'),
(33, 'Partikelir', 'Sweta Kartika', '/embun/uploads/books/68f5001cb4722_1760886812.jpg', 3, '2025-10-15 23:37:33'),
(34, 'H2O : Reborn Phase 0.1', 'Sweta Kartika', '/embun/uploads/books/68f4ff0913e89_1760886537.jpg', 3, '2025-10-15 23:37:33'),
(35, 'H2O : Reborn Phase 0.2', 'Sweta Kartika', '/embun/uploads/books/68f4ff11aaf26_1760886545.jpg', 3, '2025-10-15 23:37:33'),
(36, 'H2O : Reborn Phase 0.3', 'Sweta Kartika', '/embun/uploads/books/68f4ff193f5ee_1760886553.jpg', 3, '2025-10-15 23:37:33'),
(37, 'Three Survive', 'Aveca Tanbert', '/embun/uploads/books/68f5023789ead_1760887351.jpg', 3, '2025-10-15 23:37:33'),
(38, 'Planetes : Jelajah Ruang Hampa 2', 'Makoto Yukimura', '/embun/uploads/books/68f500a3b67b8_1760886947.jpg', 3, '2025-10-15 23:37:33'),
(39, 'Planetes : Jelajah Ruang Hampa 1', 'Makoto Yukimura', '/embun/uploads/books/68f500924471e_1760886930.jpg', 3, '2025-10-15 23:37:33'),
(40, 'Planetes : Jelajah Ruang Hampa 3', 'Makoto Yukimura', '/embun/uploads/books/68f500af9412d_1760886959.jpg', 3, '2025-10-15 23:37:33'),
(41, 'Planetes : Jelajah Ruang Hampa 4', 'Makoto Yukimura', '/embun/uploads/books/68f500ba9a0a3_1760886970.jpg', 3, '2025-10-15 23:37:33'),
(42, 'Psikologi : Sebuah Pengantar Singkat', 'Gillian Butler', '/embun/uploads/books/68f500ed499b8_1760887021.jpg', 4, '2025-10-15 23:37:33'),
(43, 'Sejarah Ideologi Dunia', 'Nur Sayyid Santoso Kristeva, M.A.', '/embun/uploads/books/68f50146ef7a0_1760887110.jpg', 5, '2025-10-15 23:37:33'),
(44, 'Tangan Emas J.K. Rowling : Rahasia-Rahasia Ajaib di Balik Novel-Novel Dahsyatnya', 'Alex Jemiah S.', '/embun/uploads/books/68f50192b3733_1760887186.jpg', 5, '2025-10-15 23:37:33'),
(45, 'Pudarnya Pesona Cleopatra', 'Habiburrahman El Shirazy', '/embun/uploads/books/68f500f380814_1760887027.jpg', 4, '2025-10-15 23:37:33'),
(46, 'Melania TRUMP : The Unauthorized Biography', 'Bojan Pozar', '/embun/uploads/books/68f4ffc50e8f2_1760886725.jpg', 5, '2025-10-15 23:37:33'),
(47, 'Pemikiran Emas Tokoh-Tokoh Politik Dunia', 'A. Faidi', '/embun/uploads/books/68f5005359ec0_1760886867.jpg', 6, '2025-10-15 23:37:33'),
(48, 'Mitos Kecantikan : Kala Kecantikan Menindas Perempuan', 'Naomi Wolf', '/embun/uploads/books/68f4ffda5df02_1760886746.jpg', 6, '2025-10-15 23:37:33'),
(49, 'Potret Budaya Lokal Dalam Ranah Hukum', 'Selvia Wisuda', '/embun/uploads/books/68f500dde4c68_1760887005.jpg', 6, '2025-10-15 23:37:33'),
(50, 'Minna no Nihongo 2', '3A Corporation', '/embun/uploads/books/68f4ffd36b093_1760886739.jpg', 14, '2025-10-15 23:37:33'),
(51, 'The Interpretation of Financial Statements', 'Benjamin Graham', '/embun/uploads/books/68f501e499940_1760887268.jpg', 7, '2025-10-15 23:37:33'),
(52, 'Coffee Roasting', 'Eris Susandi', '/embun/uploads/books/68f4fdfd85807_1760886269.jpg', 8, '2025-10-15 23:37:33'),
(53, 'Simple Trading Simple Investing', 'Ryan Filbert Team', '/embun/uploads/books/68f5017804c6b_1760887160.jpg', 7, '2025-10-15 23:37:33'),
(54, 'Ngopi Yuk!', 'Doddy Samsura', '/embun/uploads/books/68f4fff810dc4_1760886776.jpg', 7, '2025-10-15 23:37:33'),
(55, 'Trading Vs Investing', 'Ryan Filbert', '/embun/uploads/books/68f5025b30b0d_1760887387.jpg', 7, '2025-10-15 23:37:33'),
(56, 'The Host : Sang Pengelana', 'Stephenie Meyer', '/embun/uploads/books/68f501da555e6_1760887258.jpg', 9, '2025-10-15 23:37:33'),
(57, 'Eight Pillars of Prosperity', 'James Allen', '/embun/uploads/books/68f4fea666b8f_1760886438.jpg', 4, '2025-10-15 23:37:33'),
(58, 'Minna no Nihongo 1', '3A Corporation', '/embun/uploads/books/68f4ffcc4b543_1760886732.jpg', 14, '2025-10-15 23:37:33'),
(59, 'Politik Dalam Sejarah Kerajaan Jawa', 'Sri Wintala Achmad', '/embun/uploads/books/68f500d7708f7_1760886999.jpg', 6, '2025-10-15 23:37:33'),
(60, 'The Psychology of Selling', 'Brian Tracy', '/embun/uploads/books/68f501ec7a311_1760887276.jpg', 7, '2025-10-15 23:37:33'),
(61, 'Color Harmony 2', 'Bride M. Whelan', '/embun/uploads/books/68f4fe0642568_1760886278.jpg', 10, '2025-10-15 23:37:33'),
(62, 'UMKM 4.0', 'Wulan Ayodya', '/embun/uploads/books/68f5027965cb5_1760887417.jpg', 7, '2025-10-15 23:37:33'),
(63, 'Get Over Your Damn Self', 'Romi Neustadt', '/embun/uploads/books/68f4fef354e85_1760886515.jpg', 4, '2025-10-15 23:37:33'),
(64, 'Murder is Easy', 'Agatha Christie', '/embun/uploads/books/68f4ffeb30101_1760886763.jpg', 14, '2025-10-15 23:37:33'),
(65, 'Moby-Dick', 'Herman Melville', '/embun/uploads/books/68f4ffe4e085f_1760886756.jpg', 14, '2025-10-15 23:37:33'),
(66, 'Death Note', 'Tsugumi Ohba', '/embun/uploads/books/68f4fe7c19f77_1760886396.jpg', 3, '2025-10-15 23:37:33'),
(67, 'Il Principe', 'Niccolo Machiavelli', '/embun/uploads/books/68f4ff44b5731_1760886596.jpg', 6, '2025-10-15 23:37:33'),
(68, 'As Long As The Lemon Trees Grow', 'Zoulfa Katouh', '/embun/uploads/books/68f4fd8378272_1760886147.jpg', 14, '2025-10-15 23:37:33'),
(69, 'Catch-22', 'Joseph Heller', '/embun/uploads/books/68f4fddb2e00a_1760886235.jpg', 1, '2025-10-15 23:37:33'),
(70, 'The Thirteen Problems', 'Agatha Christie', '/embun/uploads/books/68f5021bafa3f_1760887323.jpg', 14, '2025-10-15 23:37:33'),
(71, 'Dead Man Folly', 'Agatha Christie', '/embun/uploads/books/68f4fe44dd1d0_1760886340.jpg', 14, '2025-10-15 23:37:33'),
(73, 'Twenty-Four Eyes', 'Sakae Tsuboi', '/embun/uploads/books/68f502728e501_1760887410.jpg', 14, '2025-10-15 23:37:33'),
(74, 'Funiculi Funicula: Before The Coffee Gets Cold', 'Toshikazu Kawaguchi', '/embun/uploads/books/68f4fee979366_1760886505.jpg', 14, '2025-10-15 23:37:33'),
(75, 'The Stranger', 'Albert Camus', '/embun/uploads/books/68f50214a3eb3_1760887316.jpg', 1, '2025-10-15 23:37:33'),
(76, 'Dengarlah Nyanyian Angin', 'Haruki Murakami', '/embun/uploads/books/68f4fe857388e_1760886405.jpg', 14, '2025-10-15 23:37:33'),
(77, 'Fiesta', 'Ernest Hemingway', '/embun/uploads/books/68f4fedac4c28_1760886490.jpg', 14, '2025-10-15 23:37:33'),
(78, 'Teror', 'Lexie Xu', '/embun/uploads/books/68f5019f863e4_1760887199.jpg', 2, '2025-10-15 23:37:33'),
(79, 'Obsesi', 'Lexie Xu', '/embun/uploads/books/68f5000d0ee80_1760886797.jpg', 2, '2025-10-15 23:37:33'),
(80, 'The Complete Short Stories: Volume 1', 'Franz Kafka', '/embun/uploads/books/68f502b157fad_1760887473.jpg', 14, '2025-10-15 23:37:33'),
(81, 'Scheduled Suicide Day', 'Akiyoshi Rikako', '/embun/uploads/books/68f5014044abc_1760887104.jpg', 14, '2025-10-15 23:37:33'),
(82, 'The Tokyo Zodiac Murders', 'Soji Shimada', '/embun/uploads/books/68f50223c641f_1760887331.jpg', 14, '2025-10-15 23:37:33'),
(83, 'Enemies &amp; Allies', 'Kevin J. Anderson', '/embun/uploads/books/68f4fecdb2886_1760886477.jpg', 1, '2025-10-15 23:37:33'),
(84, '1Q84: Jilid 3', 'Haruki Murakami', '/embun/uploads/books/68f4fd377f72b_1760886071.jpg', 14, '2025-10-15 23:37:33'),
(85, '1Q84: Jilid 2', 'Haruki Murakami', '/embun/uploads/books/691ea2400aab6_1763615296.jpg', 14, '2025-10-15 23:37:33'),
(86, '1Q84: Jilid 1', 'Haruki Murakami', '/embun/uploads/books/691ea2291e726_1763615273.jpg', 14, '2025-10-15 23:37:33'),
(87, 'Insecurity is My Middle Name', 'Alvin Syahrini', '/embun/uploads/books/68f4ff794e885_1760886649.jpg', 4, '2025-10-15 23:37:33'),
(88, 'Ronggeng Dukuh Paruk', 'Ahmad Tohari', '/embun/uploads/books/68f5012e7f894_1760887086.jpg', 5, '2025-10-15 23:37:33'),
(89, 'Berteriak dalam Bisikan', 'GM Sudarta', '/embun/uploads/books/68f4fdc9e523b_1760886217.jpg', 6, '2025-10-15 23:37:33'),
(90, 'Indonesia Banget', 'Mice Cartoon', '/embun/uploads/books/68f4ff66bbf24_1760886630.jpg', 3, '2025-10-15 23:37:33'),
(91, 'PR buat Presiden', 'Benny Rachmadi', '/embun/uploads/books/68f500e467c66_1760887012.jpg', 3, '2025-10-15 23:37:33'),
(92, 'Belantara: The Dark Forest', 'Liu Cixin', '/embun/uploads/books/68f4fdc369e4b_1760886211.jpg', 14, '2025-10-15 23:37:33'),
(93, 'Pralaya: Death End', 'Liu Cixin', '/embun/uploads/books/68f500cae53d5_1760886986.jpg', 14, '2025-10-15 23:37:33'),
(94, 'Pulang', 'Leila S. Chudori', '/embun/uploads/books/68f500fa735fd_1760887034.jpg', 14, '2025-10-15 23:37:33'),
(95, 'Steal Like an Artist', 'Austin Kleon', '/embun/uploads/books/68f5018c7669c_1760887180.jpg', 4, '2025-10-15 23:37:33'),
(96, 'Origami Hati', 'Boy Candra', '/embun/uploads/books/68f5001513249_1760886805.jpg', 14, '2025-10-15 23:37:33'),
(97, 'Trisurya: The Three-Body Problem', 'Liu Cixin', '/embun/uploads/books/68f5026b7a995_1760887403.jpg', 14, '2025-10-15 23:37:33'),
(99, 'Tom Clancy: True Faith and Allegiance', 'Mark Greaney', '/embun/uploads/books/68f50253d1d9b_1760887379.jpg', 1, '2025-10-15 23:37:33'),
(100, 'Tom Clancy: Duty and Honour', 'Grant Blackwood', '/embun/uploads/books/68f5024a2a75c_1760887370.jpg', 1, '2025-10-15 23:37:33'),
(101, 'Luftwaffe: Kisah Angkatan Udara Jerman Nazi 1935-1945', 'Nino Oktorino', '/embun/uploads/books/68f4ffaeb0f11_1760886702.jpg', 5, '2025-10-15 23:37:33'),
(102, 'Tom Clancy: Commander in Chief', 'Mark Greaney', '/embun/uploads/books/68f50242bdea6_1760887362.jpg', 1, '2025-10-15 23:37:33'),
(103, 'Assassin Creed: Renaissance', 'Oliver Bowden', '/embun/uploads/books/68f4fd9a02565_1760886170.jpg', 9, '2025-10-15 23:37:33'),
(104, 'Assassin Creed: Forsaken', 'Oliver Bowden', '/embun/uploads/books/68f4fd92b0bfc_1760886162.jpg', 14, '2025-10-15 23:37:33'),
(105, 'Assassin Creed: Brotherhood', 'Oliver Bowden', '/embun/uploads/books/68f4fd8a5b039_1760886154.jpg', 14, '2025-10-15 23:37:33'),
(106, 'Encyclopaedia Eorzea Volume 1', 'Final Fantasy XIV', '/embun/uploads/books/68f4feb501454_1760886453.jpg', 9, '2025-10-15 23:37:33'),
(107, 'Encyclopaedia Eorzea Volume 2', 'Final Fantasy XIV', '/embun/uploads/books/68f4febd51329_1760886461.jpg', 9, '2025-10-15 23:37:33'),
(108, 'Kriegsmarine Battleships: Kehancuran Kapal-Kapal Tempur Jerman dalam Perang Dunia II', 'Ari Subiakto', '/embun/uploads/books/68f4ffa0df637_1760886688.jpg', 5, '2025-10-15 23:37:33'),
(109, 'Clash of Titans: Kisah-Kisah Pertempuran Laut Terbesar dalam Perang Dunia II', 'Nino Oktorino', '/embun/uploads/books/68f4fdebc3d62_1760886251.jpg', 5, '2025-10-15 23:37:33'),
(110, 'Heiho: Barisan Pejuang Indonesia yang Terlupakan', 'Nino Oktorino', '/embun/uploads/books/68f4ff2faaaf2_1760886575.jpg', 5, '2025-10-15 23:37:33'),
(111, 'Atlas Dinosaurus : Link Internet', 'Erlangga for Kids', '/embun/uploads/books/68f4fda4a016c_1760886180.jpg', 11, '2025-10-15 23:37:33'),
(112, 'Batalion Panzer Jerman', 'Joseph Lebani', '/embun/uploads/books/68f4fdae42efd_1760886190.jpg', 5, '2025-10-15 23:37:33'),
(113, 'Rembulan Tenggelam di Wajahmu', 'Tere-Liye', '/embun/uploads/books/68f50127c26f5_1760887079.jpg', 14, '2025-10-15 23:37:33'),
(114, 'All Creatives Great and Small', 'James Herriot', '/embun/uploads/books/68f4fd7102dd5_1760886129.jpg', 14, '2025-10-15 23:37:33'),
(115, 'Perahu Kertas', 'Dee', '/embun/uploads/books/68f5005c2082d_1760886876.jpg', 14, '2025-10-15 23:37:33'),
(116, 'Bumi', 'Tere-Liye', '/embun/uploads/books/68f4fdd1b0f55_1760886225.jpg', 14, '2025-10-15 23:37:33'),
(117, 'Manusia Setengah Salmon', 'Raditya Dika', '/embun/uploads/books/68f4ffb596309_1760886709.jpg', 12, '2025-10-15 23:37:33'),
(118, 'Infinity Love', 'Nindya Ivana', '/embun/uploads/books/68f4ff6fa06c8_1760886639.jpg', 14, '2025-10-15 23:37:33'),
(119, 'Kemelut  Rondasih dan Dua Anaknya', 'Minanto', '/embun/uploads/books/68f4ff8d89899_1760886669.jpg', 14, '2025-10-15 23:37:33'),
(120, 'Impianku kan Jadi Nyata', 'Antero Literasi Indonesia', '/embun/uploads/books/68f4ff50045fa_1760886608.jpg', 1, '2025-10-15 23:37:33'),
(121, 'Sila ke-6 : Kreatif Sampai Mati', 'Wahyu Aditya', '/embun/uploads/books/68f501712becf_1760887153.jpg', 4, '2025-10-15 23:37:33'),
(122, 'Halaqah Cinta', 'Teladanrasul', '/embun/uploads/books/68f4ff26613f6_1760886566.jpg', 13, '2025-10-15 23:37:33'),
(123, 'Semua untuk Hindia', 'Iksaka Banu', '/embun/uploads/books/68f501586265e_1760887128.jpg', 14, '2025-10-15 23:37:33'),
(124, 'Drunken Mama', 'Pidi Baiq', '/embun/uploads/books/68f4fe9413721_1760886420.jpg', 12, '2025-10-15 23:37:33'),
(125, 'Lelehan Musim API', 'Jean Rocher', '/embun/uploads/books/68f4ffa736ecc_1760886695.jpg', 14, '2025-10-15 23:37:33'),
(126, 'Temu', 'Wirasakti Setyawan', '/embun/uploads/books/68f50198e78f7_1760887192.jpg', 1, '2025-10-15 23:37:33'),
(127, 'Cinta Brontosaurus', 'Raditya Dika', '/embun/uploads/books/68f4fde371081_1760886243.jpg', 14, '2025-10-15 23:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `book_categories`
--

CREATE TABLE `book_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_categories`
--

INSERT INTO `book_categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Fiksi', 'fiksi', '2025-10-15 23:37:33'),
(2, 'Misteri', 'misteri', '2025-10-15 23:37:33'),
(3, 'Komik dan Novel Grafis', 'komik-dan-novel-grafis', '2025-10-15 23:37:33'),
(4, 'Pengembangan Diri dan Psikologi', 'pengembangan-diri-dan-psikologi', '2025-10-15 23:37:33'),
(5, 'Sejarah dan Biografi', 'sejarah-dan-biografi', '2025-10-15 23:37:33'),
(6, 'Sosial dan Politik', 'sosial-dan-politik', '2025-10-15 23:37:33'),
(7, 'Finansial', 'finansial', '2025-10-15 23:37:33'),
(8, 'Makanan dan Minuman', 'makanan-dan-minuman', '2025-10-15 23:37:33'),
(9, 'Fantasi', 'fantasi', '2025-10-15 23:37:33'),
(10, 'Seni', 'seni', '2025-10-15 23:37:33'),
(11, 'Edukasi', 'edukasi', '2025-10-15 23:37:33'),
(12, 'Komedi', 'komedi', '2025-10-15 23:37:33'),
(13, 'Religius', 'religius', '2025-10-15 23:37:33'),
(14, 'Sastra', 'sastra', '2025-10-15 23:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrower_name` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `final_fine` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Black Coffee', 'coffee', '2025-10-15 23:37:32'),
(2, 'Milky Coffee', 'milky-coffee', '2025-10-15 23:37:32'),
(3, 'Tea', 'tea', '2025-10-15 23:37:32'),
(4, 'Matcha', 'matcha', '2025-10-15 23:37:32'),
(5, 'Squash', 'squash', '2025-10-15 23:37:32'),
(6, 'Dairy Milk', 'dairy', '2025-10-15 23:37:32'),
(7, 'Santapan', 'food', '2025-10-15 23:37:32'),
(8, 'Kudapan', 'snack', '2025-10-15 23:37:32'),
(9, 'Dessert', 'dessert', '2025-10-15 23:37:32');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `is_best_seller` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `image_url`, `category_id`, `is_best_seller`, `created_at`) VALUES
(1, 'Tubruk', 'Kopi tradisional', 7500.00, '/embun/uploads/menu/6932c863c1516_1764935779.jpg', 1, 1, '2025-10-15 23:37:32'),
(2, 'Espresso', 'Kopi espresso murni', 8000.00, NULL, 1, 0, '2025-10-15 23:37:32'),
(3, 'Americano', 'Espresso dengan air panas', 11000.00, NULL, 1, 0, '2025-10-15 23:37:32'),
(4, 'Aeropress', 'Kopi seduh aeropress', 18000.00, NULL, 1, 0, '2025-10-15 23:37:32'),
(5, 'V60 / Japanese', 'Kopi seduh manual V60', 18000.00, NULL, 1, 0, '2025-10-15 23:37:32'),
(6, 'Embun Ori Latte', 'Embun signature latte', 15000.00, '', 2, 1, '2025-10-15 23:37:32'),
(7, 'Gula Aren Latte', 'Pepaduan kopi & gula aren', 15000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(8, 'Salted Caramel Latte', 'Latte dengan notes asin', 15000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(9, 'Hazelnut Latte', 'Latte dengan rasa nutty', 15000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(10, 'Butterscotch Latte', 'Rasa butterscotch rich', 15000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(11, 'Embun Swante Latte', 'Premium latte sophisticated', 18000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(12, 'Tubruk Susu', 'Kopi tubruk plus susu', 9000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(13, 'Vietnam Susu', 'Kopi Vietnam autentik', 13000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(14, 'Cafe Banban', 'Kopi susu spesial', 14000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(15, 'Cafe Latte', 'Classic creamy', 15000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(16, 'Mocchacino', 'Cokelat dan kopi', 16000.00, '', 2, 0, '2025-10-15 23:37:32'),
(17, 'Signature Blend', 'Blend eksklusif', 5000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(18, 'Full Arabika', 'Arabika murni', 5000.00, NULL, 2, 0, '2025-10-15 23:37:32'),
(19, 'Java Tea', 'Teh Jawa asli', 8000.00, NULL, 3, 0, '2025-10-15 23:37:32'),
(20, 'Lemon Tea', 'Teh segar berlemon', 10000.00, NULL, 3, 0, '2025-10-15 23:37:32'),
(21, 'Java Milk Tea', 'Teh Jawa creamy', 12000.00, NULL, 3, 0, '2025-10-15 23:37:32'),
(22, 'Vanilla Milk Tea', 'Teh susu vanilla', 12000.00, '', 3, 1, '2025-10-15 23:37:32'),
(23, 'Lychee Tea', 'Teh rasa leci', 12000.00, NULL, 3, 0, '2025-10-15 23:37:32'),
(24, 'Mango Tea', 'Teh rasa mangga', 12000.00, NULL, 3, 0, '2025-10-15 23:37:32'),
(25, 'Koicha', 'Matcha kental intense', 13000.00, NULL, 4, 0, '2025-10-15 23:37:32'),
(26, 'Usucha', 'Matcha encer tradisional', 13000.00, NULL, 4, 0, '2025-10-15 23:37:32'),
(27, 'Matcha Latte', 'Matcha creamy', 16000.00, '', 4, 1, '2025-10-15 23:37:32'),
(28, 'Berry Matcha', 'Matcha twist berry', 18000.00, NULL, 4, 0, '2025-10-15 23:37:32'),
(29, 'Red Katuwen Squash', 'Rasa buah merah segar', 12000.00, NULL, 5, 0, '2025-10-15 23:37:32'),
(30, 'Blue Hawaiian Squash', 'Vibes Hawaii', 12000.00, NULL, 5, 0, '2025-10-15 23:37:32'),
(31, 'Pink Karnuman Squash', 'Manis playful', 12000.00, NULL, 5, 0, '2025-10-15 23:37:32'),
(32, 'White Lemon Squash', 'Lemon elegant', 12000.00, NULL, 5, 0, '2025-10-15 23:37:32'),
(33, 'CokePresso', 'Soda meets coffee', 14000.00, '', 5, 0, '2025-10-15 23:37:32'),
(34, 'Fresh Milk', 'Susu segar murni', 8000.00, NULL, 6, 0, '2025-10-15 23:37:32'),
(35, 'Strawberry Milk', 'Susu stroberi manis', 12000.00, '', 6, 1, '2025-10-15 23:37:32'),
(36, 'SweetMelon Milk', 'Susu melon segar', 12000.00, NULL, 6, 0, '2025-10-15 23:37:32'),
(37, 'ButterMelt Milk', 'Rasa butter rich', 15000.00, NULL, 6, 0, '2025-10-15 23:37:32'),
(38, 'Nasi Bayam Polos', 'Nasi bayam sederhana', 12000.00, '', 7, 0, '2025-10-15 23:37:32'),
(39, 'Nasi Bayam Ayam', 'Nasi bayam plus ayam', 15000.00, '', 7, 1, '2025-10-15 23:37:32'),
(40, 'Nasi Bayam Nugget/Sosis', 'Comfort food', 15000.00, '', 7, 0, '2025-10-15 23:37:32'),
(41, 'Nasi Bayam Seafood', 'Laut meets greens', 15000.00, '', 7, 0, '2025-10-15 23:37:32'),
(42, 'Nasi Ayam Telur Asin', 'Gurih premium', 18000.00, '', 7, 1, '2025-10-15 23:37:32'),
(43, 'Nasi Ayam Mentai', 'Japanese twist', 18000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(44, 'Nasi Ayam Ladha', 'Rempah berani', 15000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(45, 'Nasi Ayam Keju', 'Cheesy indulgence', 15000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(46, 'Nasi Koro Chicken', 'Feast premium', 32000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(47, 'Nasi Koro Mitae', 'Signature dish', 32000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(48, 'Nasi Koro Beef Rice', 'Beef juicy', 32000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(49, 'Nasi Goreng Bayam', 'Classic healthy twist', 13000.00, '', 7, 1, '2025-10-15 23:37:32'),
(50, 'Indomie Goreng', 'Legendary comfort', 9000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(51, 'Nasi Bola Pompon', 'Creative presentation', 18000.00, NULL, 7, 0, '2025-10-15 23:37:32'),
(52, 'French Fries', 'Crispy perfection', 10000.00, NULL, 8, 0, '2025-10-15 23:37:32'),
(53, 'Loaded Fries', 'Fries dengan topping', 14000.00, NULL, 8, 0, '2025-10-15 23:37:32'),
(54, 'Dimsum Mentai', 'East meets West', 14000.00, '', 8, 0, '2025-10-15 23:37:32'),
(55, 'Snack Platter', 'Variasi rasa', 18000.00, NULL, 8, 0, '2025-10-15 23:37:32'),
(56, 'Brownies Embun 3', 'Moist & fudgy', 14000.00, '', 8, 1, '2025-10-15 23:37:32'),
(57, 'Cookies', 'Bakery fresh', 14000.00, NULL, 8, 0, '2025-10-15 23:37:32'),
(58, 'Ice Cream Vanilla', 'Classic creamy', 8000.00, NULL, 9, 0, '2025-10-15 23:37:32'),
(59, 'Ice Cream Brownies', 'Ice cream + brownies', 15000.00, NULL, 9, 0, '2025-10-15 23:37:32'),
(60, 'Café Affogato', 'Hot meets cold', 15000.00, NULL, 9, 0, '2025-10-15 23:37:32'),
(61, 'Matcha Affogato', 'Japanese elegance', 15000.00, NULL, 9, 0, '2025-10-15 23:37:32'),
(62, 'Choco Affogato', 'Chocolate dream', 15000.00, NULL, 9, 0, '2025-10-15 23:37:32');

-- --------------------------------------------------------

--
-- Table structure for table `option_types`
--

CREATE TABLE `option_types` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `applies_from_category` int(11) DEFAULT NULL,
  `applies_to_category` int(11) DEFAULT NULL,
  `values_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`values_json`)),
  `price_per_selected` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `option_types`
--

INSERT INTO `option_types` (`id`, `key_name`, `label`, `applies_from_category`, `applies_to_category`, `values_json`, `price_per_selected`, `created_at`) VALUES
(1, 'sugar', 'Sugar %', 1, 6, '[\"100%\",\"75%\",\"50%\",\"No Sugar\"]', 0, '2025-11-11 11:27:04'),
(2, 'ice', 'Ice level', 1, 6, '[\"100%\",\"75%\",\"50%\",\"No Ice (Hot)\"]', 0, '2025-11-11 11:27:04'),
(3, 'add_sugar', 'Add sugar (extra)', 1, 6, NULL, 500, '2025-11-11 11:27:04'),
(4, 'notes', 'Notes', NULL, NULL, NULL, 0, '2025-11-11 11:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(64) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `amount` int(11) NOT NULL,
  `status` enum('pending','paid','expired','failed','cancelled') DEFAULT 'pending',
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_time` datetime DEFAULT NULL,
  `va_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`va_numbers`)),
  `snap_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_items_json` text DEFAULT NULL,
  `snap_response` longtext DEFAULT NULL,
  `wa_number` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_id`, `customer_name`, `email`, `item_name`, `amount`, `status`, `payment_type`, `transaction_time`, `va_numbers`, `snap_token`, `created_at`, `order_items_json`, `snap_response`, `wa_number`) VALUES
(15, 'EMBUN-20251111135342-8092', 'Jophiel', 'jophielnuralim@gmail.com', 'Tubruk x1, Americano x1', 19000, 'paid', 'qris', '2025-11-11 19:53:45', NULL, 'a9749ea5-c424-4a70-902f-9a0bbb97aa2d', '2025-11-11 12:53:42', '[{\"id\":\"1\",\"name\":\"Tubruk\",\"price\":7500,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"75%\",\"add_sugar\":true,\"notes\":\"\"},\"extra_price\":500},{\"id\":\"3\",\"name\":\"Americano\",\"price\":11000,\"quantity\":1,\"options\":{\"sugar\":\"75%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"a9749ea5-c424-4a70-902f-9a0bbb97aa2d\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/a9749ea5-c424-4a70-902f-9a0bbb97aa2d\"}', '08113222240'),
(18, 'EMBUN-20251116161232-1097', 'Jophiel', 'jophielnuralim@gmail.com', 'Tubruk x1, Espresso x1, Espresso x1, Nasi Bayam Seafood x1, ButterMelt Milk x1, Nasi Bayam Nugget/Sosis x1, Espresso x6, Americano x8', 209000, 'paid', 'qris', '2025-11-16 22:12:32', NULL, '398774dc-1761-41d7-88cf-237102e3a61b', '2025-11-16 15:12:32', '[{\"id\":\"1\",\"name\":\"Tubruk\",\"price\":7500,\"quantity\":1,\"options\":{\"sugar\":\"No Sugar\",\"ice\":\"75%\",\"add_sugar\":true,\"notes\":\"Yang enak ya\"},\"extra_price\":500},{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"No Sugar\",\"ice\":\"100%\",\"add_sugar\":true,\"notes\":\"\"},\"extra_price\":500},{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":true,\"notes\":\"\"},\"extra_price\":500},{\"id\":\"41\",\"name\":\"Nasi Bayam Seafood\",\"price\":15000,\"quantity\":1,\"options\":{\"notes\":\"\"},\"extra_price\":0},{\"id\":\"37\",\"name\":\"ButterMelt Milk\",\"price\":15000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"No Ice (Hot)\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0},{\"id\":\"40\",\"name\":\"Nasi Bayam Nugget\\/Sosis\",\"price\":15000,\"quantity\":1,\"options\":{\"notes\":\"\"},\"extra_price\":0},{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":6,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":true,\"notes\":\"\"},\"extra_price\":500},{\"id\":\"3\",\"name\":\"Americano\",\"price\":11000,\"quantity\":8,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"398774dc-1761-41d7-88cf-237102e3a61b\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/398774dc-1761-41d7-88cf-237102e3a61b\"}', '08113222240'),
(23, 'EMBUN-20251120062142-8530', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1', 8000, 'expired', 'qris', '2025-11-20 12:21:45', NULL, 'f0ca90cb-b902-48da-9402-89c145545f33', '2025-11-19 23:21:42', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"f0ca90cb-b902-48da-9402-89c145545f33\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/f0ca90cb-b902-48da-9402-89c145545f33\"}', '08113222240'),
(24, 'EMBUN-20251120062153-9755', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1', 8000, 'expired', 'qris', '2025-11-20 12:21:57', NULL, '88e810f7-0a49-496d-b441-b2c81fb9711d', '2025-11-19 23:21:53', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"88e810f7-0a49-496d-b441-b2c81fb9711d\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/88e810f7-0a49-496d-b441-b2c81fb9711d\"}', '08113222240'),
(25, 'EMBUN-20251120062223-6993', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1, Americano x1', 19000, 'paid', 'bank_transfer', '2025-11-20 12:22:31', NULL, 'e926f3d9-9933-42b3-bb45-6b18ec5211a0', '2025-11-19 23:22:23', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0},{\"id\":\"3\",\"name\":\"Americano\",\"price\":11000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"e926f3d9-9933-42b3-bb45-6b18ec5211a0\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/e926f3d9-9933-42b3-bb45-6b18ec5211a0\"}', '08113222240');

-- --------------------------------------------------------

--
-- Table structure for table `orders_history`
--

CREATE TABLE `orders_history` (
  `id` int(11) NOT NULL,
  `order_db_id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_time` datetime DEFAULT NULL,
  `va_numbers` text DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `order_items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`order_items_json`)),
  `snap_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snap_response`)),
  `wa_number` varchar(30) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `action_by` varchar(100) DEFAULT NULL,
  `action_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_history`
--

INSERT INTO `orders_history` (`id`, `order_db_id`, `order_id`, `customer_name`, `email`, `item_name`, `amount`, `status`, `payment_type`, `transaction_time`, `va_numbers`, `snap_token`, `created_at`, `order_items_json`, `snap_response`, `wa_number`, `action`, `action_by`, `action_at`) VALUES
(5, 23, 'EMBUN-20251120062142-8530', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1', 8000, 'pending', NULL, NULL, NULL, NULL, '2025-11-20 06:21:42', NULL, NULL, '08113222240', 'created', 'user', '2025-11-20 12:21:42'),
(6, 24, 'EMBUN-20251120062153-9755', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1', 8000, 'pending', NULL, NULL, NULL, NULL, '2025-11-20 06:21:53', NULL, NULL, '08113222240', 'created', 'user', '2025-11-20 12:21:53'),
(7, 25, 'EMBUN-20251120062223-6993', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1, Americano x1', 19000, 'pending', NULL, NULL, NULL, NULL, '2025-11-20 06:22:23', NULL, NULL, '08113222240', 'created', 'user', '2025-11-20 12:22:23'),
(8, 25, 'EMBUN-20251120062223-6993', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1, Americano x1', 19000, 'paid', 'bank_transfer', '2025-11-20 12:22:31', NULL, 'e926f3d9-9933-42b3-bb45-6b18ec5211a0', '2025-11-20 06:22:23', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0},{\"id\":\"3\",\"name\":\"Americano\",\"price\":11000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"e926f3d9-9933-42b3-bb45-6b18ec5211a0\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/e926f3d9-9933-42b3-bb45-6b18ec5211a0\"}', '08113222240', 'sync_status', 'Jophiel', '2025-11-20 17:27:29'),
(9, 25, 'EMBUN-20251120062223-6993', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1, Americano x1', 19000, 'paid', 'bank_transfer', '2025-11-20 12:22:31', NULL, 'e926f3d9-9933-42b3-bb45-6b18ec5211a0', '2025-11-20 06:22:23', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0},{\"id\":\"3\",\"name\":\"Americano\",\"price\":11000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"e926f3d9-9933-42b3-bb45-6b18ec5211a0\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/e926f3d9-9933-42b3-bb45-6b18ec5211a0\"}', '08113222240', 'sync_status', 'Jophiel', '2025-11-20 17:27:31'),
(10, 25, 'EMBUN-20251120062223-6993', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1, Americano x1', 19000, 'paid', 'bank_transfer', '2025-11-20 12:22:31', NULL, 'e926f3d9-9933-42b3-bb45-6b18ec5211a0', '2025-11-20 06:22:23', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0},{\"id\":\"3\",\"name\":\"Americano\",\"price\":11000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"e926f3d9-9933-42b3-bb45-6b18ec5211a0\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/e926f3d9-9933-42b3-bb45-6b18ec5211a0\"}', '08113222240', 'sync_status', 'Jophiel', '2025-11-20 17:27:36'),
(11, 24, 'EMBUN-20251120062153-9755', 'Jophiel', 'jophielnuralim@gmail.com', 'Espresso x1', 8000, 'expired', 'qris', '2025-11-20 12:21:57', NULL, '88e810f7-0a49-496d-b441-b2c81fb9711d', '2025-11-20 06:21:53', '[{\"id\":\"2\",\"name\":\"Espresso\",\"price\":8000,\"quantity\":1,\"options\":{\"sugar\":\"100%\",\"ice\":\"100%\",\"add_sugar\":false,\"notes\":\"\"},\"extra_price\":0}]', '{\"token\":\"88e810f7-0a49-496d-b441-b2c81fb9711d\",\"redirect_url\":\"https://app.sandbox.midtrans.com/snap/v4/redirection/88e810f7-0a49-496d-b441-b2c81fb9711d\"}', '08113222240', 'sync_status', 'Jophiel', '2025-11-20 17:27:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_item_options`
--

CREATE TABLE `order_item_options` (
  `id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `option_key` varchar(100) NOT NULL,
  `option_value` text DEFAULT NULL,
  `extra_price` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `order_id` varchar(64) NOT NULL,
  `raw_payload` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `book_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL,
  `people` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(100) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations_history`
--

CREATE TABLE `reservations_history` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `whatsapp` varchar(30) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL,
  `people` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(100) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `action_by` varchar(100) DEFAULT NULL,
  `action_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations_history`
--

INSERT INTO `reservations_history` (`id`, `reservation_id`, `user_id`, `whatsapp`, `book_id`, `notes`, `room`, `people`, `date`, `time`, `duration`, `created_at`, `action`, `action_by`, `action_at`) VALUES
(11, 68, 'Jophiel', '628113222240', NULL, 'AC', '2', 3, '2025-11-20', '10:00-12:00, 12:00-14:00, 16:00-18:00', 6, '2025-11-18 22:34:18', 'deleted', 'Jophiel', '2025-11-18 22:37:51'),
(14, 70, 'Jophiel', '628113222240', 53, '', NULL, NULL, NULL, '', NULL, '2025-11-18 22:41:53', 'deleted', 'Jophiel', '2025-11-18 23:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `capacity`, `description`, `image`, `facilities`, `created_at`, `updated_at`) VALUES
(1, 'Meeting Room', 8, 'Ruangan nyaman dengan suasana tenang, cocok untuk meeting kecil, belajar kelompok, atau berkumpul dengan teman serta membaca buku.', '/embun/uploads/rooms/691eabb69bd62_1763617718.jpg', 'AC, Proyektor', '2025-10-19 15:49:34', '2025-11-20 05:48:38'),
(2, 'Cozy Corner', 4, 'Ruangan nyaman dengan suasana cozy, tersedia berbagai boardgame yang dapat dipinjam secara gratis', '/embun/uploads/rooms/691eabbb95a79_1763617723.jpg', 'AC, Boardgame Collection', '2025-10-19 15:49:34', '2025-11-20 05:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `avatar_path`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Joanne', '$2y$10$gFq4n/9KtCeskklF7Drr9uasUBuqgWEMx1MPa9p9Bxh7OR8exeoNy', 'uploads/admins/admin_1_1762877062.jpg', 'admin', '2025-10-23 13:50:25', '2025-11-20 17:39:54'),
(2, 'user', '$2y$10$OK3kTW3RbBnW6zGriG/dHuhBn74n0hzPvkhkFU7SHWlEyH35DwbWq', NULL, 'user', '2025-10-23 13:50:25', '2025-11-17 19:38:29'),
(4, 'sasa', '$2y$10$UunB988jU8/0rxB1vvrhneck62tNwIlvpRN3oF94Wh4pxCp5Ngd5e', NULL, 'user', '2025-10-23 14:27:35', '2025-11-12 00:37:35'),
(5, 'jop', '$2y$10$6Mx//zBzEUHih866ioMBmO7F6vZix8dxROTxw9.i4keRHssKUTN9m', NULL, 'user', '2025-11-07 08:33:46', '2025-11-11 23:18:07'),
(6, 'joen', '123', NULL, 'user', '2025-11-10 15:35:24', '2025-11-11 23:04:19'),
(8, 'jo', '$2y$10$t/5R2NxDDQI9xe8Zj8SPouiN6QhDVDhxxJ.D.i5QzQ5NNYPrA8vGW', NULL, 'user', '2025-12-05 11:51:03', '2025-12-05 18:51:14'),
(10, 'admin', '$2y$10$ILWT81xkPEI4xk.GfZ2LGeBCvLHd0RjhYWLNzszIhvrRvdCRTKDcy', NULL, 'admin', '2025-12-05 11:53:42', '2025-12-05 18:54:21');

-- --------------------------------------------------------

--
-- Table structure for table `website_content`
--

CREATE TABLE `website_content` (
  `id` int(11) NOT NULL,
  `content_key` varchar(100) NOT NULL,
  `content_value` text DEFAULT NULL,
  `content_type` enum('text','image','html') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_content`
--

INSERT INTO `website_content` (`id`, `content_key`, `content_value`, `content_type`, `created_at`, `updated_at`) VALUES
(1, 'hero_background', '/embun/uploads/website/691ea3068e54b_1763615494.jpg', 'image', '2025-10-19 00:14:54', '2025-11-20 05:11:34'),
(2, 'hero_subtitle', 'Best budget friendly cafe near BINUS', 'text', '2025-10-19 00:14:54', '2025-11-20 10:31:51'),
(3, 'about_title', 'Cozy Places for Every Moments', 'text', '2025-10-19 00:14:54', '2025-11-20 10:31:51'),
(4, 'about_content', 'Kafe Embun hadir sebagai tempat berkumpul yang hangat dan nyaman bagi para pecinta kopi, buku, dan boardgame. Dengan konsep perpustakaan kafe, kami menyediakan lingkungan yang tenang untuk membaca dan bekerja, serta ruang yang menyenangkan untuk bersosial.\r\n<br><br>\r\nKami menyajikan berbagai pilihan kopi spesialti dari berbagai daerah di Indonesia, dipadukan dengan menu makanan ringan yang lezat. Perpustakaan kami memiliki koleksi buku yang beragam, dari fiksi hingga non-fiksi, yang dapat dinikmati selama Anda berada di kafe.\r\n<br><br>\r\nUntuk hiburan, kami menyediakan berbagai boardgame yang dapat dimainkan bersama teman atau keluarga. Kami juga memiliki ruang khusus di lantai dua dan tiga yang dapat dipesan untuk belajar dan bermain.', 'html', '2025-10-19 00:14:54', '2025-11-20 10:31:51'),
(5, 'about_image', '/embun/uploads/website/691ea306940d0_1763615494.jpg', 'image', '2025-10-19 00:14:54', '2025-11-20 05:11:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boardgames`
--
ALTER TABLE `boardgames`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `book_categories`
--
ALTER TABLE `book_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_loans_book` (`book_id`),
  ADD KEY `fk_loans_user` (`user_id`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `option_types`
--
ALTER TABLE `option_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `orders_history`
--
ALTER TABLE `orders_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_db_id` (`order_db_id`);

--
-- Indexes for table `order_item_options`
--
ALTER TABLE `order_item_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_item` (`item_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `fk_reservations_user` (`user_id`);

--
-- Indexes for table `reservations_history`
--
ALTER TABLE `reservations_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `website_content`
--
ALTER TABLE `website_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `content_key` (`content_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boardgames`
--
ALTER TABLE `boardgames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `book_categories`
--
ALTER TABLE `book_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `option_types`
--
ALTER TABLE `option_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `orders_history`
--
ALTER TABLE `orders_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_item_options`
--
ALTER TABLE `order_item_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `reservations_history`
--
ALTER TABLE `reservations_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `website_content`
--
ALTER TABLE `website_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `book_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `fk_loans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders_history`
--
ALTER TABLE `orders_history`
  ADD CONSTRAINT `orders_history_ibfk_1` FOREIGN KEY (`order_db_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
