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
  `cf_url` tinytext NOT NULL,
  `ioos_url` tinytext NOT NULL,
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
  `station_url` tinytext NOT NULL,
  `image_url` tinytext NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `network-name` (`network`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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

