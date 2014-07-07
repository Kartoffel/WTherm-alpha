-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Server: localhost
-- Time of generation: 07 jul 2014 at 21:00
-- Server version: 5.5.35
-- PHP-Version: 5.5.12-1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `WTherm`
--

CREATE DATABASE IF NOT EXISTS WTherm;
USE WTherm;

-- --------------------------------------------------------

--
-- Table structure for `archive`
--

CREATE TABLE IF NOT EXISTS `archive` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `max_temp` float(3,1) NOT NULL,
  `min_temp` float(3,1) NOT NULL,
  `outside_max_temp` float(3,1) NOT NULL,
  `outside_min_temp` float(3,1) NOT NULL,
  `max_humidity` int(10) NOT NULL,
  `min_humidity` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `temp` float(3,1) NOT NULL,
  `outside_temp` float(3,1) NOT NULL,
  `target_temp` float(3,1) NOT NULL,
  `humidity` int(10) NOT NULL,
  `heating` tinyint(1) NOT NULL,
  `override` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `TEMP` float(3,1) NOT NULL,
  `HUMIDITY` int(10) NOT NULL,
  `TARGET_TEMP` float(3,1) NOT NULL,
  `HEATING` tinyint(1) NOT NULL,
  `OVERRIDE` tinyint(1) NOT NULL,
  `LAST_UPDATE` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Data for `status`
--

INSERT INTO `status` (`TEMP`, `HUMIDITY`, `TARGET_TEMP`, `HEATING`, `OVERRIDE`, `LAST_UPDATE`) VALUES
(22.0, 50, 15.0, 0, 1, '2014-07-07 21:00:00');

-- --------------------------------------------------------

--
-- Table structure for `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(100) NOT NULL,
  `password` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
