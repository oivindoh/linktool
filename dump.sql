-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 01, 2011 at 04:14 AM
-- Server version: 5.1.44
-- PHP Version: 5.2.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oivindoh`
--

--
-- Dumping data for table `links`
--

INSERT INTO `links` VALUES('0ab698383376c96398b4a86841d776f4', 'http://blog.us.playstation.com/', 'http://feeds.feedburner.com/PSBlog?format=xml', 'Playstation', 'Blogg for Sonys Playstationteam. Forh&aring;pentligvis brenner Sony ned il&oslash;pet av kort tid for &aring; ha lagret passordet mitt i klartekst.', 1, 2, '');
INSERT INTO `links` VALUES('129ef51f0525066b6a4d8d81c246be1c', 'http://techcrunch.com/', 'http://feeds.feedburner.com/TechCrunch', 'Tech Crunch', 'Ye', 3, 0, 'TechCrunch ');
INSERT INTO `links` VALUES('4ed0cb10d44e2289365c35def406ec09', 'http://www.huffingtonpost.com/', 'http://feeds.huffingtonpost.com/huffingtonpost/raw_feed', 'Huffington Post', 'Meh!', 0, 3, 'Breaking News and Opinion on The Huffington Post');
INSERT INTO `links` VALUES('74b49dcfb06d7445596d481db7a9655e', 'http://www.engadget.com/', 'http://www.engadget.com/rss.xml', 'En Gadget', 'Yedda.', 1, 1, 'Engadget');
INSERT INTO `links` VALUES('d6276ae692bab70d08a7db741eca0e0d', 'http://www.9to5mac.com/', 'http://feeds.feedburner.com/9To5Mac-MacAllDay', 'James May', 'Mac-sladder', 1, 4, ' 9 to 5 Mac  | Apple Intelligence');

--
-- Dumping data for table `subjectlinks`
--

INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '0ab698383376c96398b4a86841d776f4');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '129ef51f0525066b6a4d8d81c246be1c');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '4ed0cb10d44e2289365c35def406ec09');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', '74b49dcfb06d7445596d481db7a9655e');
INSERT INTO `subjectlinks` VALUES('2af64a2e10734463100e4ec5a38f0229', 'd6276ae692bab70d08a7db741eca0e0d');

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` VALUES('2af64a2e10734463100e4ec5a38f0229', 'PHP202', 'H/2011', 'PHP - Hvordan stille seg Ã¥pen for SQL-injection?', 'bruker1@hist.no');
INSERT INTO `subjects` VALUES('8e48e1fc80795f108bd7653195f05fe8', 'SUP666', 'V/2013', 'Superkrefter', 'bruker1@hist.no');
INSERT INTO `subjects` VALUES('c101a48828bbd17e1b399650d9a521da', 'ALK101', 'H/2011', 'Introduksjon til Alkemi', 'bruker1@hist.no');
INSERT INTO `subjects` VALUES('d4da314f32c0b72b695227b07d7cd089', 'OPSL101', 'V/2011', 'Operativsystemer m/ Linux', 'bruker1@hist.no');

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES('bruker1@hist.no', 'baed47df5da4a755c91b164085d7789e', 'Navn Nummer En');
