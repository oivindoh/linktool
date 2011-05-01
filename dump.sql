-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 01, 2011 at 04:38 AM
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

CREATE TABLE `links` (
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

INSERT INTO `links` VALUES('129ef51f0525066b6a4d8d81c246be1c', 'http://techcrunch.com/', 'http://feeds.feedburner.com/TechCrunch', 'Tech Crunch', 'Ye', 3, 0, 'TechCrunch ');
INSERT INTO `links` VALUES('4ed0cb10d44e2289365c35def406ec09', 'http://www.huffingtonpost.com/', 'http://feeds.huffingtonpost.com/huffingtonpost/raw_feed', 'Huffington Post', 'Meh!', 0, 3, 'Breaking News and Opinion on The Huffington Post');
INSERT INTO `links` VALUES('74b49dcfb06d7445596d481db7a9655e', 'http://www.engadget.com/', 'http://www.engadget.com/rss.xml', 'En Gadget', 'Yedda.', 1, 1, 'Engadget');
INSERT INTO `links` VALUES('d6276ae692bab70d08a7db741eca0e0d', 'http://www.9to5mac.com/', 'http://feeds.feedburner.com/9To5Mac-MacAllDay', 'James May', 'Mac-sladder', 1, 4, ' 9 to 5 Mac  | Apple Intelligence');

-- --------------------------------------------------------

--
-- Table structure for table `subjectlinks`
--

CREATE TABLE `subjectlinks` (
  `subjects_unique` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `links_ref` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`subjects_unique`,`links_ref`),
  KEY `fk_subjects_has_links_links1` (`links_ref`),
  KEY `fk_subjects_has_links_subjects1` (`subjects_unique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `subjectlinks`
--


-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
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


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
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
