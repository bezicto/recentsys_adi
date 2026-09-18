-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 08, 2026 at 10:01 PM
-- Server version: 12.3.2-MariaDB
-- PHP Version: 8.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recentsys_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `eg_auth`
--

CREATE TABLE `eg_auth` (
  `id` int(5) NOT NULL,
  `username` varchar(15) NOT NULL,
  `passphrase` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `allowed` varchar(255) NOT NULL DEFAULT 'FALSE',
  `name` varchar(255) NOT NULL,
  `division` varchar(255) NOT NULL,
  `lastlogin` varchar(25) DEFAULT NULL,
  `online` varchar(3) NOT NULL DEFAULT 'OFF',
  `devmode` varchar(3) NOT NULL DEFAULT 'NO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_auth`
--

INSERT INTO `eg_auth` (`id`, `username`, `passphrase`, `allowed`, `name`, `division`, `lastlogin`, `online`, `devmode`) VALUES
(1, 'admin', 'ê¬¸*’‰…’Å|ýú\\Ü', 'SUPER', 'Administrator', 'System Admin', 'Wed 09/09/2026 05:49 am', 'OFF', 'NO');

-- --------------------------------------------------------

--
-- Table structure for table `eg_auth_allowedloan`
--

CREATE TABLE `eg_auth_allowedloan` (
  `id` int(11) NOT NULL,
  `usertype` varchar(255) NOT NULL,
  `usertypedesc` varchar(255) NOT NULL,
  `max_day` int(11) NOT NULL,
  `max_loanitem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_auth_allowedloan`
--

INSERT INTO `eg_auth_allowedloan` (`id`, `usertype`, `usertypedesc`, `max_day`, `max_loanitem`) VALUES
(1, 'SUPER', 'Administration', 20, 20),
(2, 'TRUE', 'Basic Administrative Account', 20, 20),
(3, 'PATRON', 'Patron - Library User', 14, 2),
(4, 'FALSE', 'Deactivated Account', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `eg_holiday`
--

CREATE TABLE `eg_holiday` (
  `id` int(11) NOT NULL,
  `38hol_title` varchar(255) NOT NULL,
  `38hol_date` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_holiday`
--

INSERT INTO `eg_holiday` (`id`, `38hol_title`, `38hol_date`) VALUES
(1, 'Hari Kebangsaan Malaysia', '31/08/2026'),
(2, 'Hari Malaysia', '16/09/2026');

-- --------------------------------------------------------

--
-- Table structure for table `eg_item`
--

CREATE TABLE `eg_item` (
  `id` int(11) NOT NULL,
  `38isbn` varchar(60) NOT NULL,
  `38issn` varchar(60) NOT NULL,
  `38localcallnum` varchar(70) NOT NULL,
  `38localcallnum_b` varchar(255) NOT NULL DEFAULT '',
  `38author` varchar(150) NOT NULL,
  `38author_d` varchar(255) NOT NULL DEFAULT '',
  `38title` varchar(255) NOT NULL,
  `38title_b` varchar(255) NOT NULL DEFAULT '',
  `38title_c` varchar(255) NOT NULL DEFAULT '',
  `38edition` varchar(255) NOT NULL,
  `38publication` varchar(255) NOT NULL,
  `38publication_b` varchar(255) NOT NULL DEFAULT '',
  `38publication_c` varchar(255) NOT NULL DEFAULT '',
  `38physicaldesc` varchar(255) NOT NULL,
  `38physicaldesc_b` varchar(255) NOT NULL DEFAULT '',
  `38physicaldesc_c` varchar(255) NOT NULL DEFAULT '',
  `38physicaldesc_e` varchar(255) NOT NULL DEFAULT '',
  `38series` varchar(150) NOT NULL,
  `38series_v` varchar(255) NOT NULL DEFAULT '',
  `38notes` varchar(255) NOT NULL,
  `38fcnotes` varchar(255) DEFAULT NULL,
  `38source` varchar(255) NOT NULL,
  `38source_b` varchar(255) NOT NULL DEFAULT '',
  `38source_e` varchar(255) NOT NULL DEFAULT '',
  `38location` varchar(50) NOT NULL,
  `38location_b` varchar(255) NOT NULL DEFAULT '',
  `38location_c` varchar(255) NOT NULL DEFAULT '',
  `38link` varchar(255) DEFAULT 'NULL',
  `39type` varchar(1) NOT NULL,
  `39subjectheading` varchar(150) DEFAULT 'NULL',
  `39pdfattach` varchar(255) DEFAULT '''FALSE''',
  `39imageatt` varchar(255) DEFAULT 'NULL',
  `39language` varchar(3) NOT NULL DEFAULT 'zsm',
  `40inputby` varchar(50) NOT NULL,
  `40inputdate` varchar(25) NOT NULL,
  `40proposedelete` varchar(5) NOT NULL DEFAULT 'FALSE',
  `40lastupdateby` varchar(50) DEFAULT NULL,
  `40instimestamp` varchar(12) DEFAULT NULL,
  `41hits` int(11) DEFAULT 0,
  `50search_cloud` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_item`
--

INSERT INTO `eg_item` (`id`, `38isbn`, `38issn`, `38localcallnum`, `38localcallnum_b`, `38author`, `38author_d`, `38title`, `38title_b`, `38title_c`, `38edition`, `38publication`, `38publication_b`, `38publication_c`, `38physicaldesc`, `38physicaldesc_b`, `38physicaldesc_c`, `38physicaldesc_e`, `38series`, `38series_v`, `38notes`, `38fcnotes`, `38source`, `38source_b`, `38source_e`, `38location`, `38location_b`, `38location_c`, `38link`, `39type`, `39subjectheading`, `39pdfattach`, `39imageatt`, `39language`, `40inputby`, `40inputdate`, `40proposedelete`, `40lastupdateby`, `40instimestamp`, `41hits`, `50search_cloud`) VALUES
(56, '9789675997228', '', 'DS597.215.M34', 'A3 2011', 'Mahathir bin Mohamad', '', 'A doctor in the house', 'the memoirs of Tun Dr. Mahathir Mohamad', 'Mahathir bin Mohamad', '', '', 'MPH Pub.', '2011', '843 pages', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'TRUE', 'eng', 'admin', '26/08/2026', 'FALSE', NULL, '1787705638', 0, 'A doctor in the house the memoirs of Tun Dr. Mahathir Mohamad Mahathir bin Mohamad \\ Mahathir bin Mohamad  \\ 9789675997228  \\ DS597.215.M34 A3 2011'),
(57, '0471787841', '', 'QA76.2.J63Y677', '2006', 'Jeffrey S. Young; William L. Simon', '', 'iCon Steve Jobs', 'The Greatest Second Act in the History of Business', 'Jeffrey S. Young; William L. Simon', '', '', 'Wiley', '2006', '368 pages', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'TRUE', 'eng', 'admin', '26/08/2026', 'FALSE', NULL, '1787706206', 0, 'iCon Steve Jobs The Greatest Second Act in the History of Business Jeffrey S. Young; William L. Simon \\ Jeffrey S. Young; William L. Simon  \\ 0471787841  \\ QA76.2.J63Y677 2006'),
(45, '9789836202451', '', 'PL5118', '.H3 1997', 'Kassim Ahmad', '1936-2017', 'Hikayat Hang Tuah', '', 'diselenggarakan oleh Kassim Ahmad', 'Cet. 5', 'Kuala Lumpur', 'Dewan Bahasa dan Pustaka', '1997', 'xxvi, 523 ms.', '', '22 cm', '', 'Siri warisan sastera klasik', '', 'Epik Melayu klasik mengenai kepahlawanan laksamana Hang Tuah lima bersaudara.', 'Kelahiran Hang Tuah -- Pelayaran ke Majapahit -- Pertarungan Hang Tuah dan Taming Sari -- Pertelingkahan dengan Hang Jebat -- Pelayaran ke Rom dan China -- Pengunduran Hang Tuah.', 'Dewan Bahasa dan Pustaka', '', '', 'Main Library', 'Koleksi Sastera Melayu', 'Aras 2, Rak PL-02', '', '1', '800|890|', 'FALSE', '', 'zsm', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 1, 'Hikayat Hang Tuah  diselenggarakan oleh Kassim Ahmad \\ Kassim Ahmad 1936-2017 \\ 9789836202451  \\ PL5118 .H3 1997'),
(46, '9789836200259', '', 'PL5138.A4', '.S3 1992', 'A. Samad Said', '1935-', 'Salina: Sebuah Novel', 'Sebuah Novel', 'A. Samad Said', 'Cet. 7', 'Kuala Lumpur', 'Dewan Bahasa dan Pustaka', '1992', '495 ms.', '', '19 cm', '', 'Siri novel DBP', '', 'Novel sastera kebangsaan yang memaparkan realiti kehidupan masyarakat pasca-Perang Dunia Kedua di Singapura.', '', '', '', '', 'Main Library', 'Koleksi Sastera Melayu', 'Aras 2, Rak PL-03', '', '1', '800|890|', 'FALSE', '', 'zsm', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Salina: Sebuah Novel Sebuah Novel A. Samad Said \\ A. Samad Said 1935- \\ 9789836200259  \\ PL5138.A4 .S3 1992'),
(44, '9789836208538', '', 'PL5117', '.S8 1996', 'Tun Seri Lanang', '1565-1659', 'Sulalatus Salatin (Sejarah Melayu)', '', 'diselenggarakan oleh A. Samad Ahmad', 'Cet. 4', 'Kuala Lumpur', 'Dewan Bahasa dan Pustaka', '1996', 'xxxvi, 354 ms.', 'peta', '22 cm', '', 'Siri sastera klasik DBP', 'bil. 24', 'Teks naskhah Raffles No. 18. Mengandungi glosari dan indeks nama watak.', 'Mukaddimah -- Asal-usul raja-raja Melayu -- Pembukaan Melaka -- Zaman kegemilangan Melaka -- Hubungan diplomatik Melaka-China -- Kejatuhan Melaka ke tangan Portugis.', 'Dewan Bahasa dan Pustaka', 'Bahagian Penyelidikan Sastera', '', 'Main Library', 'Koleksi Sastera Melayu', 'Aras 2, Rak PL-01', '', '1', '800|890|950|', 'FALSE', '', 'zsm', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Sulalatus Salatin (Sejarah Melayu)  diselenggarakan oleh A. Samad Ahmad \\ Tun Seri Lanang 1565-1659 \\ 9789836208538  \\ PL5117 .S8 1996'),
(43, '9780307887894', '', 'HD62.5', '.R54 2011', 'Ries, Eric', '1978-', 'The Lean Startup: How Today\'s Entrepreneurs Use Continuous Innovation to Create Radically Successful Businesses', 'How Today\'s Entrepreneurs Use Continuous Innovation to Create Radically Successful Businesses', 'Eric Ries', '1st ed.', 'New York', 'Crown Business', '2011', '320 p.', 'ill.', '24 cm', '', '', '', 'Defines the Lean Startup methodology based on build-measure-learn feedback loops.', 'Vision: Start, define, learn, experiment -- Steer: Leap, test, measure, pivot -- Accelerate: Batch, grow, adapt, innovate.', '', '', '', 'Main Library', 'Management Encyclopedia Reference', 'Level 4, Section HD-Mgt', 'http://theleanstartup.com/', '3', '650|600|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'The Lean Startup: How Today\'s Entrepreneurs Use Continuous Innovation to Create Radically Successful Businesses How Today\'s Entrepreneurs Use Continuous Innovation to Create Radically Successful Businesses Eric Ries \\ Ries, Eric 1978- \\ 9780307887894  \\ HD62.5 .R54 2011'),
(41, '9781501124020', '', 'HD38.2', '.D35 2017', 'Dalio, Ray', '1949-', 'Principles: Life and Work', 'Life and Work', 'Ray Dalio', '1st Simon & Schuster hardcover ed.', 'New York', 'Simon & Schuster', '2017', 'xxii, 567 p.', 'ill.', '24 cm', '', '', '', 'Includes index. Management principles from Bridgewater Associates.', 'Part I: Where I\'m coming from -- Part II: Life principles -- Part III: Work principles.', 'Bridgewater Associates', '', '', 'Main Library', 'Management Encyclopedia Reference', 'Level 4, Section HD-Mgt', 'https://www.principles.com/', '3', '650|150|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Principles: Life and Work Life and Work Ray Dalio \\ Dalio, Ray 1949- \\ 9781501124020  \\ HD38.2 .D35 2017'),
(42, '9780066620992', '', 'HD57.7', '.C645 2001', 'Collins, James C.', '1958-', 'Good to Great: Why Some Companies Make the Leap... and Others Don\'t', 'Why Some Companies Make the Leap... and Others Don\'t', 'Jim Collins', '1st ed.', 'New York, NY', 'HarperBusiness', '2001', 'xii, 300 p.', 'ill.', '24 cm', '', '', '', 'A five-year research study on company performance and management excellence.', 'Good is the enemy of great -- Level 5 leadership -- First who... then what -- Confront the brutal facts -- The hedgehog concept -- A culture of discipline -- Technology accelerators -- The flywheel and the doom loop.', 'Jim Collins Management Research Lab', '', '', 'Main Library', 'Management Encyclopedia Reference', 'Level 4, Section HD-Mgt', 'https://www.jimcollins.com/books/good-to-great.html', '3', '650|330|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Good to Great: Why Some Companies Make the Leap... and Others Don\'t Why Some Companies Make the Leap... and Others Don\'t Jim Collins \\ Collins, James C. 1958- \\ 9780066620992  \\ HD57.7 .C645 2001'),
(40, '9780374533557', '', 'BF441', '.K238 2011', 'Kahneman, Daniel', '1934-2024', 'Thinking, Fast and Slow', '', 'Daniel Kahneman', '1st pbk. ed.', 'New York', 'Farrar, Straus and Giroux', '2011', 'viii, 499 p.', 'ill.', '21 cm', '', '', '', 'Winner of the Nobel Prize in Economics.', 'Two systems -- Heuristics and biases -- Overconfidence -- Choices -- Two selves -- Appendix A: Judgment under uncertainty -- Appendix B: Choices, values, and frames.', 'Princeton University', 'Department of Psychology', '', 'Main Library', 'Open Shelf Collection', 'Level 2, Shelf BF-03', '', '1', '100|150|300|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 1, 'Thinking, Fast and Slow  Daniel Kahneman \\ Kahneman, Daniel 1934-2024 \\ 9780374533557  \\ BF441 .K238 2011'),
(39, '9780062316097', '', 'CB113', '.H37 2015', 'Harari, Yuval N.', '1976-', 'Sapiens: A Brief History of Humankind', 'A Brief History of Humankind', 'Yuval Noah Harari ; translated by the author with the help of John Purcell and Haim Watzman', '1st HarperCollins ed.', 'New York', 'Harper, an imprint of HarperCollinsPublishers', '2015', 'x, 443 p., [16] p. of plates', 'ill., maps', '24 cm', '', '', '', 'Originally published in Hebrew in Israel in 2011 by Kinneret, Zmora-Bitan, Dvir.', 'The cognitive revolution -- The agricultural revolution -- The unification of humankind -- The scientific revolution -- Afterword: The animal that became a god.', 'Hebrew University of Jerusalem', 'Department of History', '', 'Main Library', 'Open Shelf Collection', 'Level 2, Shelf CB-01', 'https://www.ynharari.com/book/sapiens/', '1', '900|930|300|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Sapiens: A Brief History of Humankind A Brief History of Humankind Yuval Noah Harari ; translated by the author with the help of John Purcell and Haim Watzman \\ Harari, Yuval N. 1976- \\ 9780062316097  \\ CB113 .H37 2015'),
(38, '9780262510875', '', 'QA76.6', '.A255 1996', 'Abelson, Harold', '1947-', 'Structure and Interpretation of Computer Programs', '', 'Harold Abelson and Gerald Jay Sussman with Julie Sussman', '2nd ed.', 'Cambridge, Mass.', 'The MIT Press', '1996', 'xxiii, 657 p.', 'ill.', '23 cm', '', 'MIT electrical engineering and computer science series', '', 'Classic MIT textbook on Scheme and computer programming principles.', 'Building abstractions with procedures -- Building abstractions with data -- Modularity, objects, and state -- Metalinguistic abstraction -- Computing with register machines.', 'Massachusetts Institute of Technology', '', '', 'Main Library', 'Open Shelf Collection', 'Level 3, Shelf QA-09', 'https://mitpress.mit.edu/sites/default/files/sicp/index.html', '1', '000|510|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Structure and Interpretation of Computer Programs  Harold Abelson and Gerald Jay Sussman with Julie Sussman \\ Abelson, Harold 1947- \\ 9780262510875  \\ QA76.6 .A255 1996'),
(37, '9780078022159', '', 'QA76.9.D3', '.S56 2019', 'Silberschatz, Abraham', '1952-', 'Database System Concepts', '', 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan', '7th ed.', 'New York, NY', 'McGraw-Hill Education', '2019', 'xxvi, 1349 p.', 'ill.', '24 cm', '', 'McGraw-Hill series in computer science', '', 'Includes index and glossary.', 'Relational databases -- Database design -- Application design and development -- Storage management -- Query processing and optimization -- Transaction management -- Parallel databases.', 'Yale University', 'Department of Computer Science', '', 'Main Library', 'Open Shelf Collection', 'Level 3, Shelf QA-08', 'https://www.db-book.com/', '1', '000|020|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 1, 'Database System Concepts  Abraham Silberschatz, Henry F. Korth, S. Sudarshan \\ Silberschatz, Abraham 1952- \\ 9780078022159  \\ QA76.9.D3 .S56 2019'),
(36, '9780136764045', '', 'TK5105.5', '.T36 2021', 'Tanenbaum, Andrew S.', '1944-', 'Computer Networks', '', 'Andrew S. Tanenbaum, Nick Feamster, David J. Wetherall', '6th ed., global ed.', 'Harlow, England', 'Pearson Education Limited', '2021', '943 p.', 'ill. (some col.)', '24 cm', '', '', '', 'Includes bibliographical references and index.', 'Introduction -- The physical layer -- The data link layer -- The medium access control sublayer -- The network layer -- The transport layer -- The application layer -- Network security.', 'Vrije Universiteit Amsterdam', 'Department of Computer Science', '', 'Main Library', 'Open Shelf Collection', 'Level 3, Shelf TK-02', '', '1', '000|600|620|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Computer Networks  Andrew S. Tanenbaum, Nick Feamster, David J. Wetherall \\ Tanenbaum, Andrew S. 1944- \\ 9780136764045  \\ TK5105.5 .T36 2021'),
(34, '9780262046305', '', 'QA76.6', '.C662 2022', 'Cormen, Thomas H.', '1956-', 'Introduction to Algorithms', '', 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein', '4th ed.', 'Cambridge, Massachusetts', 'The MIT Press', '2022', 'xix, 1312 p.', 'ill.', '24 cm', '', '', '', 'Known widely as CLRS. Includes bibliography and index.', 'Foundations -- Sorting and order statistics -- Data structures -- Advanced design and analysis techniques -- Graph algorithms -- Selected topics.', 'Massachusetts Institute of Technology', 'Computer Science and Artificial Intelligence Laboratory', '', 'Main Library', 'Red Spot / Reserve Collection', 'Reserve Desk, Counter A', 'https://mitpress.mit.edu/9780262046305/introduction-to-algorithms/', '2', '000|510|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Introduction to Algorithms  Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein \\ Cormen, Thomas H. 1956- \\ 9780262046305  \\ QA76.6 .C662 2022'),
(35, '9780201633610', '', 'QA76.64', '.D47 1994', 'Gamma, Erich', '1961-', 'Design Patterns: Elements of Reusable Object-Oriented Software', 'Elements of Reusable Object-Oriented Software', 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides', '1st ed.', 'Reading, Mass.', 'Addison-Wesley', '1994', 'xv, 395 p.', 'ill.', '24 cm', '', 'Addison-Wesley professional computing series', '', 'Classic Gang of Four (GoF) software design patterns reference.', 'Introduction -- A case study: designing a document editor -- Creational patterns -- Structural patterns -- Behavioral patterns -- Conclusion.', '', '', '', 'Main Library', 'Open Shelf Collection', 'Level 3, Shelf QA-06', '', '1', '000|600|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Design Patterns: Elements of Reusable Object-Oriented Software Elements of Reusable Object-Oriented Software Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides \\ Gamma, Erich 1961- \\ 9780201633610  \\ QA76.64 .D47 1994'),
(33, '9780134610993', '', 'Q335', '.R87 2020', 'Russell, Stuart J.', '1962-', 'Artificial Intelligence: A Modern Approach', 'A Modern Approach', 'Stuart Russell, Peter Norvig', '4th ed., global ed.', 'Hoboken, NJ', 'Pearson Education', '2020', '1166 p.', 'col. ill.', '26 cm', '', 'Pearson series in artificial intelligence', '', 'Comprehensive textbook on artificial intelligence algorithms and systems.', 'Artificial intelligence -- Problem-solving -- Knowledge, reasoning, and planning -- Uncertain knowledge -- Machine learning -- Communicating, perceiving, acting -- Conclusions.', 'University of California, Berkeley', 'Department of Electrical Engineering and Computer Sciences', 'sponsor', 'Main Library', 'Red Spot / Reserve Collection', 'Reserve Desk, Counter A', 'http://aima.cs.berkeley.edu/', '2', '000|500|600|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Artificial Intelligence: A Modern Approach A Modern Approach Stuart Russell, Peter Norvig \\ Russell, Stuart J. 1962- \\ 9780134610993  \\ Q335 .R87 2020'),
(32, '9780135957059', '', 'QA76.6', '.H85 2019', 'Thomas, David', '1956-', 'The Pragmatic Programmer: Your Journey To Mastery', 'Your Journey To Mastery', 'David Thomas, Andrew Hunt', '20th anniversary ed.', 'Boston', 'Addison-Wesley', '2019', 'xxxi, 321 p.', 'ill.', '23 cm', '', 'Addison-Wesley professional computing series', '', 'Foreword by Ward Cunningham. Includes index.', 'A pragmatic philosophy -- A pragmatic approach -- The basic tools -- Pragmatic paranoia -- Bend, or break -- Concurrency -- While you are coding -- Before the project -- Pragmatic projects.', 'The Pragmatic Bookshelf', '', '', 'Main Library', 'Open Shelf Collection', 'Level 3, Shelf QA-05', 'https://pragprog.com/titles/tpp20/the-pragmatic-programmer-20th-anniversary-edition/', '1', '000|600|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'The Pragmatic Programmer: Your Journey To Mastery Your Journey To Mastery David Thomas, Andrew Hunt \\ Thomas, David 1956- \\ 9780135957059  \\ QA76.6 .H85 2019'),
(31, '9780132350884', '', 'QA76.76.C66', '.M37 2008', 'Martin, Robert C.', '1952-', 'Clean Code: A Handbook of Agile Software Craftsmanship', 'A Handbook of Agile Software Craftsmanship', 'Robert C. Martin (\"Uncle Bob\")', '1st ed.', 'Upper Saddle River, NJ', 'Prentice Hall', '2008', 'xxix, 431 p.', 'ill.', '24 cm', '', 'Robert C. Martin series', 'v. 1', 'Includes index and bibliographical references.', 'Clean code -- Meaningful names -- Functions -- Comments -- Formatting -- Objects and data structures -- Error handling -- Boundaries -- Unit tests -- Classes -- Systems -- Emergence -- Concurrency -- Successive refinement.', 'Object Mentor Inc.', 'Software Division', 'sponsor', 'Main Library', 'Open Shelf Collection', 'Level 3, Shelf QA-04', 'https://www.pearson.com/en-us/subject-catalog/p/clean-code-a-handbook-of-agile-software-craftsmanship/P200000000184', '1', '000|600|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 1, 'Clean Code: A Handbook of Agile Software Craftsmanship A Handbook of Agile Software Craftsmanship Robert C. Martin (\"Uncle Bob\") \\ Martin, Robert C. 1952- \\ 9780132350884  \\ QA76.76.C66 .M37 2008'),
(47, '9780553380163', '', 'QC179', '.H38 1998', 'Hawking, Stephen', '1942-2018', 'A Brief History of Time: From the Big Bang to Black Holes', 'From the Big Bang to Black Holes', 'Stephen W. Hawking ; with an introduction by Carl Sagan ; illustrations by Ron Miller', 'Updated and expanded 10th anniversary ed.', 'New York', 'Bantam Books', '1998', 'x, 212 p.', 'col. ill.', '24 cm', '', '', '', 'Explores cosmological theories, space-time, expanding universe, black holes, and the origin and fate of the universe.', 'Our picture of the universe -- Space and time -- The expanding universe -- Uncertainty principle -- Black holes -- Origin and fate of the universe -- The arrow of time -- Wormholes and time travel -- Unification of physics.', 'University of Cambridge', 'Department of Applied Mathematics and Theoretical Physics', '', 'Main Library', 'Open Shelf Collection', 'Level 2, Shelf QC-01', '', '1', '500|520|530|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 1, 'A Brief History of Time: From the Big Bang to Black Holes From the Big Bang to Black Holes Stephen W. Hawking ; with an introduction by Carl Sagan ; illustrations by Ron Miller \\ Hawking, Stephen 1942-2018 \\ 9780553380163  \\ QC179 .H38 1998'),
(48, '9780201896831', '', 'QA76.6', '.K64 1997', 'Knuth, Donald Ervin', '1938-', 'The Art of Computer Programming, Volume 1: Fundamental Algorithms', 'Fundamental Algorithms', 'Donald E. Knuth', '3rd ed.', 'Reading, Mass.', 'Addison-Wesley', '1997', 'xx, 650 p.', 'ill.', '24 cm', '', 'The Art of Computer Programming', 'v. 1', 'Seminal multi-volume work covering fundamental data structures, algorithms, and mathematical foundations.', 'Chapter 1: Basic concepts (Mathematical induction, numbers, permutations, algorithm analysis) -- Chapter 2: Information structures (Linear lists, trees, dynamic storage allocation).', 'Stanford University', 'Computer Science Department', '', 'Main Library', 'Red Spot / Reserve Collection', 'Reserve Desk, Counter B', 'https://www-cs-faculty.stanford.edu/~knuth/taocp.html', '2', '000|510|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'The Art of Computer Programming, Volume 1: Fundamental Algorithms Fundamental Algorithms Donald E. Knuth \\ Knuth, Donald Ervin 1938- \\ 9780201896831  \\ QA76.6 .K64 1997'),
(49, '9780345539434', '', 'QB44.2', '.S24 2013', 'Sagan, Carl', '1934-1996', 'Cosmos', '', 'Carl Sagan', '1st Ballantine Books trade pbk. ed.', 'New York', 'Ballantine Books', '2013', 'xxvii, 384 p., [16] p. of plates', 'ill. (some col.)', '23 cm', '', '', '', 'Foreword by Neil deGrasse Tyson. Based on the celebrated television series.', 'The shores of the cosmic ocean -- One voice in the cosmic fugue -- The harmony of worlds -- Heaven and hell -- Blues for a red planet -- The backbone of night -- Travels in space and time -- The lives of the stars -- Who speaks for Earth?', 'Cornell University', 'Laboratory for Planetary Studies', '', 'Main Library', 'Open Shelf Collection', 'Level 2, Shelf QB-02', '', '1', '500|520|', 'FALSE', '', 'eng', 'admin', '24/08/2026', 'FALSE', NULL, '1787539142', 0, 'Cosmos  Carl Sagan \\ Sagan, Carl 1934-1996 \\ 9780345539434  \\ QB44.2 .S24 2013'),
(50, '9780307352156', '', 'BF698.35.I54', '.C35 2013', 'Cain, Susan', '1968-', 'Quiet: The Power of Introverts in a World That Can\'t Stop Talking', 'The Power of Introverts in a World That Can\'t Stop Talking', 'Susan Cain', '1st Broadway Books trade pbk. ed.', 'New York', 'Broadway Books', '2013', '352 p.', '', '21 cm', '', '', '', 'Includes bibliographical references (pages 273-334) and index.', 'The extrovert ideal -- Your biology, your self? -- Do all cultures have an extrovert ideal? -- How to love, how to work -- Wonderland.', '', '', '', 'Main Library', 'Open Shelf Collection', 'Level 2, Shelf BF-05', 'https%3A%2F%2Fwww.quietrev.com%2F', '1', '100|150|', 'TRUE', 'TRUE', 'eng', 'admin', '24/08/2026', 'FALSE', 'admin', '1787539142', 1, 'Quiet: The Power of Introverts in a World That Can\'t Stop Talking The Power of Introverts in a World That Can\'t Stop Talking Susan Cain \\ Cain, Susan 1968- \\ 9780307352156  \\ BF698.35.I54 .C35 2013'),
(58, '9784088725093', '', 'PN6790.J34', 'O63 1997', '尾田栄一郎', '', 'ONE PIECE 1', 'ROMANCE DAWN — 冒険の夜明け—', '尾田栄一郎', '', '', 'Shueisha', '1997', '207 pages', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'TRUE', 'oth', 'admin', '26/08/2026', 'FALSE', NULL, '1787708065', 1, 'ONE PIECE 1 ROMANCE DAWN — 冒険の夜明け— 尾田栄一郎 \\ 尾田栄一郎  \\ 9784088725093  \\ PN6790.J34 O63 1997'),
(59, '9784088807232', '', 'PN6790.J33G67513', '2016', 'Koyoharu Gotoge', '', '鬼滅の刃 1', '', 'Koyoharu Gotoge', '', '', '集英社', '2016', '192 pages', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'TRUE', 'oth', 'admin', '26/08/2026', 'FALSE', NULL, '1787708514', 1, '鬼滅の刃 1  Koyoharu Gotoge \\ Koyoharu Gotoge  \\ 9784088807232  \\ PN6790.J33G67513 2016'),
(60, '', '00368075', '050', 'S34', '', '', 'Science', '', 'American Association for the Advancement of Science (AAAS)', '', '', 'American Association for the Advancement of Science (AAAS)', '', 'v. : ill. ; 28 cm', '', '28 cm', '', '', '', 'Serial / Journal Publication. ISSN: 0036-8075, 1095-9203.', '', '', '', '', '', '', '', '', '4', '', 'FALSE', 'FALSE', 'eng', 'admin', '26/08/2026', 'FALSE', 'admin', '1787710168', 2, 'Science  American Association for the Advancement of Science (AAAS) \\   \\  00368075 \\ 050 S34'),
(62, '9784789017305', '', 'PL539.5.E5', 'B36 2020 stud.ed.', 'Banno Eri', '', 'Genki', 'An Integrated Course in Elementary Japanese I Textbook', 'Banno Eri', '', '', 'Japan Times', '2020', '382 pages', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'TRUE', 'oth', 'admin', '26/08/2026', 'FALSE', NULL, '1787710818', 1, 'Genki An Integrated Course in Elementary Japanese I Textbook Banno Eri \\ Banno Eri  \\ 9784789017305  \\ PL539.5.E5 B36 2020 stud.ed.'),
(63, '9786297665122', '', '899.2333', 'TEM 2024', 'Teme Abdullah, 1993-, author.', '', 'BAYANG SOFEA', '', 'Teme Abdullah, 1993-, author.', '', 'KAJANG, SELANGOR', 'IMAN PUBLICATION SDN BHD, [2024?]', '2024', '492 pages', '', '19 cm', '', '', '', 'LULUS', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'FALSE', 'zsm', 'admin', '26/08/2026', 'FALSE', NULL, '1787719671', 1, 'BAYANG SOFEA  Teme Abdullah, 1993-, author. \\ Teme Abdullah, 1993-, author.  \\ 9786297665122  \\ 899.2333 TEM 2024'),
(70, '9780007270934', '', 'GV1032', '2007', 'Lewis Hamilton', '', 'Lewis Hamilton', 'My Story', 'Lewis Hamilton', '', '', 'HarperSport', '2007', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'FALSE', 'eng', 'admin', '01/09/2026', 'FALSE', NULL, '1788234085', 0, 'Lewis Hamilton My Story Lewis Hamilton \\ Lewis Hamilton  \\ 9780007270934  \\ GV1032 2007'),
(71, '9781805264194', '', '320', 'I27 2025', 'Anwar Ibrahim', '2025', 'Rethinking Ourselves', 'Justice, Reform and Ignorance in Postnormal Times', 'Anwar Ibrahim', '1', 'Kuala Lumpur', 'Oxford University Press', '2025', '322 pages', 'Tebal', 'Sangat', 'Menarik', 'Series', 'Vol 1', 'A groundbreaking exploration of justice, democracy and Islamophobia, inviting us to reconsider our assumptions and build a more equitable future.', 'A groundbreaking exploration of justice, democracy and Islamophobia, inviting us to reconsider our assumptions and build a more equitable future part II.', 'BRAND', 'SUB', 'RELATOR', 'KOLEKSI TERBUKA', 'RAK TERBUKA', 'Level 2, Shelf BF-05', 'https%3A%2F%2Fpnm.gov.my', '1', '000|', 'TRUE', 'TRUE', 'eng', 'admin', '03/09/2026', 'FALSE', 'admin', '1788394062', 0, 'Rethinking Ourselves Justice, Reform and Ignorance in Postnormal Times Anwar Ibrahim \\ Anwar Ibrahim 2025 \\ 9781805264194  \\ 320 I27 2025'),
(67, '9786297575292', '', '320.9595', 'OOI 2024', 'Ooi, Kee Beng, 1955- author.', '1955', 'THE RELUCTANT NATION', 'MALAYSIA AND ITS VAIN QUEST FOR COMMON PURPOSE', 'Ooi, Kee Beng, 1955- author.', '2', 'Petaling Jaya, Selangor', 'Strategic Information and Research Development Centre', '2024', 'xix, 191 pages', '1kg', '23 cm', 'Brochure', '1', 'v1', 'LULUS', 'PDF', 'BRAND', 'SUB', 'RELATOR', 'KOLEKSI TERBUKA', 'RAK TERBUKA', 'BERBUKA PUASA', 'https%3A%2F%2Fpnm.gov.my', '1', '170|', 'TRUE', 'TRUE', 'zsm', 'admin', '26/08/2026', 'FALSE', NULL, '1787725979', 5, 'THE RELUCTANT NATION MALAYSIA AND ITS VAIN QUEST FOR COMMON PURPOSE Ooi, Kee Beng, 1955- author. \\ Ooi, Kee Beng, 1955- author. 1955 \\ 9786297575292  \\ 320.9595 OOI 2024'),
(68, '9781982134594', '', '', '', 'David Pouge', '', 'Apple: The First 50 Years', '', 'David Pouge', '', '', 'Simon & Schuster, New York, 2026', '2026', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'FALSE', 'eng', 'admin', '28/08/2026', 'FALSE', NULL, '1787889166', 1, 'Apple: The First 50 Years  David Pouge \\ David Pouge  \\ 9781982134594  \\  '),
(69, '9780063009813', '', '', '', 'Tripp Mickle', '', 'After Steve', 'How Apple Became a Trillion-Dollar Company and Lost Its Soul', 'Tripp Mickle', '', '', 'HarperCollins Publishers', '2022', '400', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '1', '', 'FALSE', 'TRUE', 'eng', 'admin', '28/08/2026', 'FALSE', NULL, '1787889267', 2, 'After Steve How Apple Became a Trillion-Dollar Company and Lost Its Soul Tripp Mickle \\ Tripp Mickle  \\ 9780063009813  \\  ');

-- --------------------------------------------------------

--
-- Table structure for table `eg_item_charge`
--

CREATE TABLE `eg_item_charge` (
  `id` int(11) NOT NULL,
  `39accessnum` varchar(150) NOT NULL,
  `39patron` varchar(150) NOT NULL,
  `39charged_on` varchar(150) NOT NULL,
  `39charged_by` varchar(150) NOT NULL,
  `39duedate` int(11) DEFAULT 0,
  `40dc` varchar(2) NOT NULL,
  `40dc_on` varchar(150) NOT NULL,
  `40dc_by` varchar(150) NOT NULL,
  `40dc_enforcedfine` int(11) DEFAULT NULL,
  `41f_pay` varchar(255) NOT NULL DEFAULT 'NO',
  `41f_paidon` varchar(255) DEFAULT NULL,
  `41f_received` varchar(255) DEFAULT NULL,
  `41f_received_amount` decimal(10,2) NOT NULL,
  `41f_discount_amount` decimal(10,2) NOT NULL,
  `41f_given_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `41f_receipt_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eg_item_copies`
--

CREATE TABLE `eg_item_copies` (
  `id` int(11) NOT NULL,
  `eg_item_id` int(11) DEFAULT NULL,
  `39accessnum` varchar(150) NOT NULL,
  `39status` varchar(255) NOT NULL,
  `39volume` varchar(50) DEFAULT '',
  `39issue` varchar(50) DEFAULT '',
  `39year` varchar(10) DEFAULT '',
  `39is_reference` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `39invoice_a` varchar(255) NOT NULL DEFAULT '0.00',
  `39invoice_b` varchar(255) NOT NULL,
  `39invoice_c` varchar(255) NOT NULL,
  `39addedon` varchar(255) DEFAULT NULL,
  `39lastchange` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_item_copies`
--

INSERT INTO `eg_item_copies` (`id`, `eg_item_id`, `39accessnum`, `39status`, `39volume`, `39issue`, `39year`, `39is_reference`, `39invoice_a`, `39invoice_b`, `39invoice_c`, `39addedon`, `39lastchange`) VALUES
(66, 31, '0000000018', 'AVAILABLE', '', '', '', 'NO', '45.00', 'Pearson Education Malaysia', 'INV-2026-001', '1787539142', '1787539142'),
(67, 31, '0000000019', 'AVAILABLE', '', '', '', 'NO', '45.00', 'Pearson Education Malaysia', 'INV-2026-001', '1787539142', '1787539142'),
(68, 31, '0000000020', 'AVAILABLE', '', '', '', 'NO', '45.00', 'Pearson Education Malaysia', 'INV-2026-001', '1787539142', '1787539142'),
(69, 32, '0000000021', 'AVAILABLE', '', '', '', 'NO', '52.50', 'Kinokuniya Bookstores', 'INV-2026-002', '1787539142', '1787539142'),
(70, 32, '0000000022', 'AVAILABLE', '', '', '', 'NO', '52.50', 'Kinokuniya Bookstores', 'INV-2026-002', '1787539142', '1787539142'),
(71, 33, '0000000023', 'AVAILABLE', '', '', '', 'NO', '120.00', 'Pearson Education Malaysia', 'INV-2026-003', '1787539142', '1787539142'),
(72, 33, '0000000024', 'AVAILABLE', '', '', '', 'NO', '120.00', 'Pearson Education Malaysia', 'INV-2026-003', '1787539142', '1787539142'),
(73, 34, '0000000025', 'AVAILABLE', '', '', '', 'NO', '115.00', 'University Book Store Custom', 'INV-2026-004', '1787539142', '1787539142'),
(74, 34, '0000000026', 'AVAILABLE', '', '', '', 'NO', '115.00', 'University Book Store Custom', 'INV-2026-004', '1787539142', '1787539142'),
(75, 35, '0000000027', 'AVAILABLE', '', '', '', 'NO', '54.00', 'MPH Distributors', 'INV-2026-005', '1787539142', '1787539142'),
(76, 35, '0000000028', 'AVAILABLE', '', '', '', 'NO', '54.00', 'MPH Distributors', 'INV-2026-005', '1787539142', '1787539142'),
(77, 36, '0000000029', 'AVAILABLE', '', '', '', 'NO', '85.00', 'Pearson Education Malaysia', 'INV-2026-006', '1787539142', '1787539142'),
(78, 36, '0000000030', 'AVAILABLE', '', '', '', 'NO', '85.00', 'Pearson Education Malaysia', 'INV-2026-006', '1787539142', '1787539142'),
(79, 36, '0000000031', 'AVAILABLE', '', '', '', 'NO', '85.00', 'Pearson Education Malaysia', 'INV-2026-006', '1787539142', '1787539142'),
(80, 37, '0000000032', 'AVAILABLE', '', '', '', 'NO', '92.00', 'McGraw-Hill Asia', 'INV-2026-007', '1787539142', '1787539142'),
(81, 37, '0000000033', 'AVAILABLE', '', '', '', 'NO', '92.00', 'McGraw-Hill Asia', 'INV-2026-007', '1787539142', '1787539142'),
(82, 38, '0000000034', 'AVAILABLE', '', '', '', 'NO', '65.00', 'Kinokuniya Bookstores', 'INV-2026-008', '1787539142', '1787539142'),
(83, 38, '0000000035', 'AVAILABLE', '', '', '', 'NO', '65.00', 'Kinokuniya Bookstores', 'INV-2026-008', '1787539142', '1787539142'),
(84, 39, '0000000036', 'AVAILABLE', '', '', '', 'NO', '38.00', 'Popular Book Co.', 'INV-2026-009', '1787539142', '1787539142'),
(85, 39, '0000000037', 'AVAILABLE', '', '', '', 'NO', '38.00', 'Popular Book Co.', 'INV-2026-009', '1787539142', '1787539142'),
(86, 39, '0000000038', 'AVAILABLE', '', '', '', 'NO', '38.00', 'Popular Book Co.', 'INV-2026-009', '1787539142', '1787539142'),
(87, 40, '0000000039', 'AVAILABLE', '', '', '', 'NO', '42.00', 'MPH Bookstores', 'INV-2026-010', '1787539142', '1787539142'),
(88, 40, '0000000040', 'CIRCULATED', '', '', '', 'NO', '42.00', 'MPH Bookstores', 'INV-2026-010', '1787539142', '1787539142'),
(89, 40, '0000000041', 'AVAILABLE', '', '', '', 'NO', '42.00', 'MPH Bookstores', 'INV-2026-010', '1787539142', '1787539142'),
(90, 41, '0000000042', 'AVAILABLE', '', '', '', 'NO', '55.00', 'Kinokuniya Bookstores', 'INV-2026-011', '1787539142', '1787539142'),
(91, 41, '0000000043', 'AVAILABLE', '', '', '', 'NO', '55.00', 'Kinokuniya Bookstores', 'INV-2026-011', '1787539142', '1787539142'),
(92, 42, '0000000044', 'AVAILABLE', '', '', '', 'NO', '48.00', 'MPH Bookstores', 'INV-2026-012', '1787539142', '1787539142'),
(93, 42, '0000000045', 'AVAILABLE', '', '', '', 'NO', '48.00', 'MPH Bookstores', 'INV-2026-012', '1787539142', '1787539142'),
(94, 43, '0000000046', 'AVAILABLE', '', '', '', 'NO', '46.00', 'Popular Book Co.', 'INV-2026-013', '1787539142', '1787539142'),
(95, 43, '0000000047', 'AVAILABLE', '', '', '', 'NO', '46.00', 'Popular Book Co.', 'INV-2026-013', '1787539142', '1787539142'),
(96, 44, '0000000048', 'AVAILABLE', '', '', '', 'NO', '28.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-014', '1787539142', '1787539142'),
(97, 44, '0000000049', 'AVAILABLE', '', '', '', 'NO', '28.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-014', '1787539142', '1787539142'),
(98, 44, '0000000050', 'AVAILABLE', '', '', '', 'NO', '28.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-014', '1787539142', '1787539142'),
(99, 45, '0000000051', 'AVAILABLE', '', '', '', 'NO', '32.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-015', '1787539142', '1787539142'),
(100, 45, '0000000052', 'AVAILABLE', '', '', '', 'NO', '32.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-015', '1787539142', '1787539142'),
(101, 45, '0000000053', 'AVAILABLE', '', '', '', 'NO', '32.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-015', '1787539142', '1787539142'),
(102, 46, '0000000054', 'AVAILABLE', '', '', '', 'NO', '25.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-016', '1787539142', '1787539142'),
(103, 46, '0000000055', 'AVAILABLE', '', '', '', 'NO', '25.00', 'Dewan Bahasa dan Pustaka', 'INV-2026-016', '1787539142', '1787539142'),
(104, 47, '0000000056', 'AVAILABLE', '', '', '', 'NO', '39.00', 'MPH Bookstores', 'INV-2026-017', '1787539142', '1787539142'),
(105, 47, '0000000057', 'AVAILABLE', '', '', '', 'NO', '39.00', 'MPH Bookstores', 'INV-2026-017', '1787539142', '1787539142'),
(106, 47, '0000000058', 'AVAILABLE', '', '', '', 'NO', '39.00', 'MPH Bookstores', 'INV-2026-017', '1787539142', '1787539142'),
(107, 48, '0000000059', 'CIRCULATED', '', '', '', 'NO', '130.00', 'Addison-Wesley International', 'INV-2026-018', '1787539142', '1787817351'),
(108, 48, '0000000060', 'AVAILABLE', '', '', '', 'NO', '130.00', 'Addison-Wesley International', 'INV-2026-018', '1787539142', '1787817445'),
(109, 49, '0000000061', 'AVAILABLE', '', '', '', 'NO', '36.00', 'Kinokuniya Bookstores', 'INV-2026-019', '1787539142', '1787539142'),
(110, 49, '0000000062', 'CIRCULATED', '', '', '', 'NO', '36.00', 'Kinokuniya Bookstores', 'INV-2026-019', '1787539142', '1787539493'),
(111, 50, '0000000063', 'CIRCULATED', '', '', '', 'NO', '34.50', 'Popular Book Co.', 'INV-2026-020', '1787539142', '1787539395'),
(112, 50, '0000000064', 'AVAILABLE', '', '', '', 'NO', '34.50', 'Popular Book Co.', 'INV-2026-020', '1787539142', '1787539142'),
(113, 50, '0000000065', 'AVAILABLE', '', '', '', 'NO', '34.50', 'Popular Book Co.', 'INV-2026-020', '1787539142', '1787539142'),
(117, 60, '0000000066', 'AVAILABLE', 'Vol. 45', 'Mac 2026', '2026', 'YES', '2.50', 'Abda', 'INV02020202', '1787791028', '1787791028'),
(118, 68, '0000000067', 'AVAILABLE', '', '', '', 'NO', '', '', '', '1787889166', '1787889166'),
(119, 69, '0000000068', 'AVAILABLE', '', '', '', 'NO', '', '', '', '1787889267', '1788244311'),
(120, 70, '0000000069', 'AVAILABLE', '', '', '', 'NO', '59.90', 'MPH', 'INV020202099', '1788234109', '1788244421'),
(121, 71, '0000000070', 'AVAILABLE', '', '', '', 'NO', '250.99', 'SASBADI', 'INV99911222', '1788394085', '1788394085'),
(122, 71, '0000000071', 'AVAILABLE', '', '', '', 'NO', '250.99', 'SASBADI', 'INV99911222', '1788394085', '1788394085');

-- --------------------------------------------------------

--
-- Table structure for table `eg_item_det`
--

CREATE TABLE `eg_item_det` (
  `id` int(11) NOT NULL,
  `eg_item_id` int(11) DEFAULT NULL,
  `39logdate` bigint(20) NOT NULL,
  `39ipaddr` varchar(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eg_item_indicator`
--

CREATE TABLE `eg_item_indicator` (
  `id` int(11) NOT NULL,
  `eg_item_id` int(11) DEFAULT NULL,
  `38author_i` varchar(2) DEFAULT NULL,
  `38title_i` varchar(2) DEFAULT NULL,
  `38fcnotes_i` varchar(2) DEFAULT NULL,
  `38source_i` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci ROW_FORMAT=COMPRESSED;

--
-- Dumping data for table `eg_item_indicator`
--

INSERT INTO `eg_item_indicator` (`id`, `eg_item_id`, `38author_i`, `38title_i`, `38fcnotes_i`, `38source_i`) VALUES
(54, 31, '1 ', '10', '0 ', '2 '),
(55, 32, '1 ', '10', '0 ', '2 '),
(56, 33, '1 ', '10', '0 ', '2 '),
(57, 34, '1 ', '10', '0 ', '2 '),
(58, 35, '1 ', '10', '0 ', '2 '),
(59, 36, '1 ', '10', '0 ', '2 '),
(60, 37, '1 ', '10', '0 ', '2 '),
(61, 38, '1 ', '10', '0 ', '2 '),
(62, 39, '1 ', '10', '0 ', '2 '),
(63, 40, '1 ', '10', '0 ', '2 '),
(64, 41, '1 ', '10', '0 ', '2 '),
(65, 42, '1 ', '10', '0 ', '2 '),
(66, 43, '1 ', '10', '0 ', '2 '),
(67, 44, '1 ', '00', '0 ', '2 '),
(68, 45, '1 ', '00', '0 ', '2 '),
(69, 46, '1 ', '10', '0 ', '2 '),
(70, 47, '1 ', '10', '0 ', '2 '),
(71, 48, '1 ', '10', '0 ', '2 '),
(72, 49, '1 ', '10', '0 ', '2 '),
(77, 50, '1 ', '10', '0 ', '2 '),
(80, 56, '', '', '', ''),
(81, 57, '', '', '', ''),
(82, 58, '', '', '', ''),
(83, 59, '', '', '', ''),
(86, 62, '', '', '', ''),
(87, 63, '', '', '', ''),
(88, 67, '00', '11', '0*', '1*'),
(90, 60, '', '', '', ''),
(91, 68, '1', '10', '', ''),
(92, 69, '1', '10', '', ''),
(93, 70, '1', '10', '', ''),
(95, 71, '1', '10', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `eg_item_isbn`
--

CREATE TABLE `eg_item_isbn` (
  `id` int(11) NOT NULL,
  `eg_item_id` int(11) DEFAULT NULL,
  `isbn1` varchar(255) NOT NULL,
  `isbn2` varchar(255) NOT NULL,
  `isbn3` varchar(255) NOT NULL,
  `isbn4` varchar(255) NOT NULL,
  `isbn5` varchar(255) NOT NULL,
  `isbn6` varchar(255) NOT NULL,
  `isbn7` varchar(255) NOT NULL,
  `isbn8` varchar(255) NOT NULL,
  `isbn9` varchar(255) NOT NULL,
  `isbn10` varchar(255) NOT NULL,
  `isbn11` varchar(255) NOT NULL,
  `isbn12` varchar(255) NOT NULL,
  `isbn13` varchar(255) NOT NULL,
  `isbn14` varchar(255) NOT NULL,
  `isbn15` varchar(255) NOT NULL,
  `isbn16` varchar(255) NOT NULL,
  `isbn17` varchar(255) NOT NULL,
  `isbn18` varchar(255) NOT NULL,
  `isbn19` varchar(255) NOT NULL,
  `isbn20` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_item_isbn`
--

INSERT INTO `eg_item_isbn` (`id`, `eg_item_id`, `isbn1`, `isbn2`, `isbn3`, `isbn4`, `isbn5`, `isbn6`, `isbn7`, `isbn8`, `isbn9`, `isbn10`, `isbn11`, `isbn12`, `isbn13`, `isbn14`, `isbn15`, `isbn16`, `isbn17`, `isbn18`, `isbn19`, `isbn20`) VALUES
(43, 31, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(44, 32, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(45, 33, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(46, 34, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(47, 35, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(48, 36, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(49, 37, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(50, 38, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(51, 39, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(52, 40, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(53, 41, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(54, 42, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(55, 43, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(56, 44, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(57, 45, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(58, 46, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(59, 47, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(60, 48, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(61, 49, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(66, 50, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(69, 56, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(70, 57, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(71, 58, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(72, 59, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(75, 62, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(76, 63, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(77, 67, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(79, 60, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(80, 68, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(81, 69, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(82, 70, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(84, 71, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `eg_selfreg_tokens`
--

CREATE TABLE `eg_selfreg_tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` int(11) NOT NULL,
  `expires_at` int(11) NOT NULL,
  `status` enum('active','scanned','used','expired') NOT NULL DEFAULT 'active',
  `first_scanned_at` int(11) DEFAULT NULL,
  `first_scanned_ip` varchar(45) DEFAULT NULL,
  `client_nonce` varchar(64) DEFAULT NULL,
  `used_at` int(11) DEFAULT NULL,
  `registered_username` varchar(255) DEFAULT NULL,
  `registered_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eg_subjectheading`
--

CREATE TABLE `eg_subjectheading` (
  `43subjectid` int(11) NOT NULL,
  `43acronym` varchar(255) NOT NULL,
  `43subject` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_subjectheading`
--

INSERT INTO `eg_subjectheading` (`43subjectid`, `43acronym`, `43subject`) VALUES
(1, '000', 'Computer Science, knowledge and system'),
(2, '010', 'Bibliographies'),
(3, '020', 'Library and information sciences'),
(4, '030', 'Encyclopaedias and books of facts'),
(5, '050', 'Magazines, journals and serials'),
(6, '060', 'Associations, organizations and museums'),
(7, '070', 'News media, journalism and publishing'),
(8, '080', 'General knowledge'),
(9, '090', 'Manuscripts and rare books'),
(10, '100', 'Philosophy and Psychology'),
(11, '110', 'Metaphysics'),
(12, '120', 'Epistemology, causation and humankind '),
(13, '130', 'Parapsychology and occultism '),
(14, '140', 'Specific philosophical schools '),
(15, '150', 'Psychology'),
(16, '160', 'Philosophical logic '),
(17, '170', 'Ethics '),
(18, '180', 'Ancient, medieval, and eastern philosophy'),
(19, '190', 'Modern western philosophy'),
(20, '200', 'Religion '),
(21, '210', 'Philosophy and theory of religion'),
(22, '220', 'Bible '),
(23, '230', 'Christianity'),
(24, '240', 'Christian moral and devotional theology'),
(25, '250', 'Christian orders and local church '),
(26, '260', 'Social and ecclesiastical theology '),
(27, '270', 'History, geography, biography of Christianity '),
(28, '280', 'Christian denominations and sects'),
(29, '290', 'Other religions'),
(30, '300', 'Social sciences '),
(31, '310', 'Collections of general statistics'),
(32, '320', 'Political science'),
(33, '330', 'Economics'),
(34, '340', 'Law'),
(35, '350', 'Public administration & military science'),
(36, '360', 'Social problems and services'),
(37, '370', 'Education'),
(38, '380', 'Commerce, communications, transportation'),
(39, '390', 'Customs, etiquette, folklore'),
(40, '400', 'Language '),
(41, '410', 'Linguistics'),
(42, '420', 'English and Old English language'),
(43, '430', 'German and related languages'),
(44, '440', 'French and related languages '),
(45, '450', 'Italian, Romanian and related languages'),
(46, '460', 'Spanish, Portuguese, Galician '),
(47, '470', 'Latin and related Italic language'),
(48, '480', 'Classical Greek and related languages'),
(49, '490', 'Other languages'),
(50, '500', 'Science'),
(51, '510', 'Mathematics'),
(52, '520', 'Astronomy and allied sciences'),
(53, '530', 'Physics'),
(54, '540', 'Chemistry and allied sciences'),
(55, '550', 'Earth sciences'),
(56, '560', 'Palaeontology'),
(57, '570', 'Biology'),
(58, '580', 'Plants (Botany)'),
(59, '590', 'Animals (Zoology)'),
(60, '600', 'Technology'),
(61, '610', 'Medicine and health'),
(62, '620', 'Engineering and allied operations'),
(63, '630', 'Agriculture and related technologies'),
(64, '640', 'Home economics, catering'),
(65, '650', 'Management'),
(66, '660', 'Chemical engineering, food technology'),
(67, '670', 'Manufacturing'),
(68, '680', 'Manufacture for specific uses'),
(69, '690', 'Construction of buildings'),
(70, '700', 'Arts and recreation'),
(71, '710', 'Planning and landscape architecture'),
(72, '720', 'Architecture'),
(73, '730', 'Sculpture and related arts'),
(74, '740', 'Graphic arts and decorative arts'),
(75, '750', 'Painting and paintings'),
(76, '760', 'Printmaking and prints'),
(77, '770', 'Photography, computer art, film, video'),
(78, '780', 'Music'),
(79, '790', 'Recreational and performing arts, sport'),
(80, '800', 'Literature'),
(81, '810', 'American literature'),
(82, '820', 'English and Old English literatures'),
(83, '830', 'German and related literatures'),
(84, '840', 'French and related literatures'),
(85, '850', 'Italian, Romanian and related literatures'),
(86, '860', 'Spanish, Portuguese, Galician literatures'),
(87, '870', 'Latin and Italic literatures'),
(88, '880', 'Classical Greek and related Literatures'),
(89, '890', 'Literature of other languages'),
(90, '900', 'History and geography'),
(91, '910', 'Geography and travel'),
(92, '920', 'Biography'),
(93, '930', 'History of the ancient world'),
(94, '940', 'History of Europe'),
(95, '950', 'History of Asia'),
(96, '960', 'History of Africa'),
(97, '970', 'History of North America'),
(98, '980', 'History of South America'),
(99, '990', 'History of Other Areas');

-- --------------------------------------------------------

--
-- Table structure for table `eg_type`
--

CREATE TABLE `eg_type` (
  `38typeid` int(4) NOT NULL,
  `38type` varchar(50) NOT NULL,
  `38defaultlocation` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_type`
--

INSERT INTO `eg_type` (`38typeid`, `38type`, `38defaultlocation`) VALUES
(1, 'Open Shelf', NULL),
(2, 'Red Spot', NULL),
(3, 'Audio Visual', NULL),
(4, 'Serial', NULL),
(5, 'Digital File', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `eg_type_fines`
--

CREATE TABLE `eg_type_fines` (
  `id` int(11) NOT NULL,
  `38typeid` int(11) NOT NULL,
  `39fines_initdays` int(11) NOT NULL,
  `39fines_initamount` varchar(150) NOT NULL,
  `39fines_subsequenceamount` varchar(150) NOT NULL,
  `40enforcedon` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `eg_type_fines`
--

INSERT INTO `eg_type_fines` (`id`, `38typeid`, `39fines_initdays`, `39fines_initamount`, `39fines_subsequenceamount`, `40enforcedon`) VALUES
(1, 1, 7, '0.50', '1.00', '1370109805'),
(2, 2, 7, '1', '5', '1542603568'),
(3, 3, 7, '1', '5', '1542603577');

-- --------------------------------------------------------

--
-- Table structure for table `eg_userlog_det`
--

CREATE TABLE `eg_userlog_det` (
  `id` int(11) NOT NULL,
  `38keyword` varchar(255) NOT NULL,
  `38logdate` bigint(20) NOT NULL,
  `38ipaddr` varchar(15) DEFAULT NULL,
  `38type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eg_working_days`
--

CREATE TABLE `eg_working_days` (
  `id` int(1) NOT NULL,
  `mon` int(1) NOT NULL DEFAULT 0,
  `tue` int(1) NOT NULL DEFAULT 0,
  `wed` int(1) NOT NULL DEFAULT 0,
  `thu` int(1) NOT NULL DEFAULT 0,
  `fri` int(1) NOT NULL DEFAULT 0,
  `sat` int(1) NOT NULL DEFAULT 0,
  `sun` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci ROW_FORMAT=COMPRESSED;

--
-- Dumping data for table `eg_working_days`
--

INSERT INTO `eg_working_days` (`id`, `mon`, `tue`, `wed`, `thu`, `fri`, `sat`, `sun`) VALUES
(1, 1, 1, 1, 1, 1, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eg_auth`
--
ALTER TABLE `eg_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eg_auth_allowedloan`
--
ALTER TABLE `eg_auth_allowedloan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auth_id` (`usertype`);

--
-- Indexes for table `eg_holiday`
--
ALTER TABLE `eg_holiday`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eg_item`
--
ALTER TABLE `eg_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `40lastupdateby` (`40lastupdateby`),
  ADD KEY `40inputby` (`40inputby`) USING BTREE,
  ADD KEY `39subjectheading` (`39subjectheading`) USING BTREE;
ALTER TABLE `eg_item` ADD FULLTEXT KEY `38title` (`38title`);
ALTER TABLE `eg_item` ADD FULLTEXT KEY `38author` (`38author`);
ALTER TABLE `eg_item` ADD FULLTEXT KEY `50search_cloud` (`50search_cloud`);

--
-- Indexes for table `eg_item_charge`
--
ALTER TABLE `eg_item_charge`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eg_item_copies`
--
ALTER TABLE `eg_item_copies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_serial_vol_iss` (`eg_item_id`,`39volume`,`39issue`);

--
-- Indexes for table `eg_item_det`
--
ALTER TABLE `eg_item_det`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_det_logdate` (`39logdate`),
  ADD KEY `idx_item_det_item_logdate` (`eg_item_id`,`39logdate`);

--
-- Indexes for table `eg_item_indicator`
--
ALTER TABLE `eg_item_indicator`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eg_item_isbn`
--
ALTER TABLE `eg_item_isbn`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eg_selfreg_tokens`
--
ALTER TABLE `eg_selfreg_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `eg_subjectheading`
--
ALTER TABLE `eg_subjectheading`
  ADD PRIMARY KEY (`43subjectid`);

--
-- Indexes for table `eg_type`
--
ALTER TABLE `eg_type`
  ADD PRIMARY KEY (`38typeid`),
  ADD KEY `38typeid` (`38typeid`);

--
-- Indexes for table `eg_type_fines`
--
ALTER TABLE `eg_type_fines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eg_userlog_det`
--
ALTER TABLE `eg_userlog_det`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_userlog_det_kw` (`38keyword`(50)),
  ADD KEY `idx_userlog_det_dt` (`38logdate`),
  ADD KEY `idx_userlog_logdate` (`38logdate`);

--
-- Indexes for table `eg_working_days`
--
ALTER TABLE `eg_working_days`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eg_auth`
--
ALTER TABLE `eg_auth`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `eg_auth_allowedloan`
--
ALTER TABLE `eg_auth_allowedloan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `eg_holiday`
--
ALTER TABLE `eg_holiday`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `eg_item`
--
ALTER TABLE `eg_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `eg_item_charge`
--
ALTER TABLE `eg_item_charge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eg_item_copies`
--
ALTER TABLE `eg_item_copies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `eg_item_det`
--
ALTER TABLE `eg_item_det`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eg_item_indicator`
--
ALTER TABLE `eg_item_indicator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `eg_item_isbn`
--
ALTER TABLE `eg_item_isbn`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `eg_selfreg_tokens`
--
ALTER TABLE `eg_selfreg_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eg_subjectheading`
--
ALTER TABLE `eg_subjectheading`
  MODIFY `43subjectid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `eg_type`
--
ALTER TABLE `eg_type`
  MODIFY `38typeid` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `eg_type_fines`
--
ALTER TABLE `eg_type_fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `eg_userlog_det`
--
ALTER TABLE `eg_userlog_det`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
