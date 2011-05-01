-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 01, 2011 at 04:48 AM
-- Server version: 5.1.44
-- PHP Version: 5.2.13

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `oivindoh`
--

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `ref` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `rss` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `frequency` int(11) DEFAULT NULL,
  `clicks` int(11) DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `links`
--

INSERT INTO `links` VALUES('0ab698383376c96398b4a86841d776f4', 'http://blog.us.playstation.com/', 'http://feeds.feedburner.com/PSBlog?format=xml', 'Playstation', 'Blogg for Sonys Playstationteam. Forh&aring;pentligvis brenner Sony ned il&oslash;pet av kort tid for &aring; ha lagret passordet mitt i klartekst.', 1, 2, '');
INSERT INTO `links` VALUES('129ef51f0525066b6a4d8d81c246be1c', 'http://techcrunch.com/', 'http://feeds.feedburner.com/TechCrunch', 'Tech Crunch', 'Ye', 3, 0, 'TechCrunch ');
INSERT INTO `links` VALUES('4ed0cb10d44e2289365c35def406ec09', 'http://www.huffingtonpost.com/', 'http://feeds.huffingtonpost.com/huffingtonpost/raw_feed', 'Huffington Post', 'Meh!', 0, 3, 'Breaking News and Opinion on The Huffington Post');
INSERT INTO `links` VALUES('74b49dcfb06d7445596d481db7a9655e', 'http://www.engadget.com/', 'http://www.engadget.com/rss.xml', 'En Gadget', 'Yedda.', 1, 1, 'Engadget');
INSERT INTO `links` VALUES('d6276ae692bab70d08a7db741eca0e0d', 'http://www.9to5mac.com/', 'http://feeds.feedburner.com/9To5Mac-MacAllDay', 'James May', 'Mac-sladder', 1, 4, ' 9 to 5 Mac  | Apple Intelligence');

-- --------------------------------------------------------

--
-- Table structure for table `subjectlinks`
--

DROP TABLE IF EXISTS `subjectlinks`;
CREATE TABLE IF NOT EXISTS `subjectlinks` (
  `subjects_unique` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `links_ref` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`subjects_unique`,`links_ref`),
  KEY `fk_subjects_has_links_links1` (`links_ref`),
  KEY `fk_subjects_has_links_subjects1` (`subjects_unique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `subjectlinks`
--

INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '0ab698383376c96398b4a86841d776f4');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '129ef51f0525066b6a4d8d81c246be1c');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '4ed0cb10d44e2289365c35def406ec09');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '74b49dcfb06d7445596d481db7a9655e');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', 'd6276ae692bab70d08a7db741eca0e0d');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `unique` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `term` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `users_email` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`unique`),
  KEY `fk_subjects_users1` (`users_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` VALUES('2af64a2e10734463100e4ec5a38f0229', 'PHP202', 'H/2011', 'PHP - Hvordan stille seg Ã¥pen for SQL-injection?', 'bruker1@hist.no');
INSERT INTO `subjects` VALUES('8e48e1fc80795f108bd7653195f05fe8', 'SUP666', 'V/2013', 'Superkrefter', 'bruker1@hist.no');
INSERT INTO `subjects` VALUES('c101a48828bbd17e1b399650d9a521da', 'ALK101', 'H/2011', 'Introduksjon til Alkemi', 'bruker1@hist.no');
INSERT INTO `subjects` VALUES('d4da314f32c0b72b695227b07d7cd089', 'OPSL101', 'V/2011', 'Operativsystemer m/ Linux', 'bruker1@hist.no');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES('bruker1@hist.no', 'baed47df5da4a755c91b164085d7789e', 'Navn Nummer En');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subjectlinks`
--
ALTER TABLE `subjectlinks`
  ADD CONSTRAINT `fk_subjects_has_links_subjects1` FOREIGN KEY (`subjects_unique`) REFERENCES `subjects` (`unique`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_subjects_has_links_links1` FOREIGN KEY (`links_ref`) REFERENCES `links` (`ref`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subjects_users1` FOREIGN KEY (`users_email`) REFERENCES `users` (`email`) ON DELETE NO ACTION ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
