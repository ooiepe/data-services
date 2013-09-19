-- phpMyAdmin SQL Dump
-- version 3.4.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 09, 2013 at 02:13 PM
-- Server version: 5.0.67
-- PHP Version: 5.3.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ooi_services`
--

-- --------------------------------------------------------

--
-- Table structure for table `ts_parameters`
--

CREATE TABLE IF NOT EXISTS `ts_parameters` (
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `units` varchar(20) NOT NULL,
  `cf_parameter` tinytext NOT NULL,
  `ioos_parameter` tinytext NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ts_stations`
--

CREATE TABLE IF NOT EXISTS `ts_stations` (
  `id` int(11) NOT NULL auto_increment,
  `network` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `location` point NOT NULL,
  `start_time` datetime default NULL,
  `end_time` datetime default NULL,
  `image_url` tinytext NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `network-name` (`network`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=843 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_stations_parameters`
--

CREATE TABLE IF NOT EXISTS `ts_stations_parameters` (
  `id` int(11) NOT NULL auto_increment,
  `station_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `depth` decimal(6,2) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `station-param` (`station_id`,`parameter_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
