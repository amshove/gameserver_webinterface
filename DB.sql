-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 18. Aug 2013 um 17:13
-- Server Version: 5.5.32
-- PHP-Version: 5.3.10-1ubuntu3.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `gameserver`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `folder` varchar(200) NOT NULL,
  `cmd` text NOT NULL,
  `defaults` text NOT NULL,
  `start_port` int(11) NOT NULL,
  `port_blacklist` varchar(250) NOT NULL,
  `score` int(11) NOT NULL,
  `token_pool` int(10) unsigned NOT NULL,
  `connect_cmd` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `running`
--

CREATE TABLE IF NOT EXISTS `running` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `screen` varchar(200) NOT NULL,
  `serverid` int(10) unsigned NOT NULL,
  `gameid` int(10) unsigned NOT NULL,
  `cmd` text NOT NULL,
  `port` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `token` text NOT NULL,
  `token_pool` int(10) unsigned NOT NULL,
  `vars` text NOT NULL,
  `t_contest_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `server`
--

CREATE TABLE IF NOT EXISTS `server` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `user` varchar(200) NOT NULL,
  `games` varchar(200) NOT NULL,
  `score` int(11) NOT NULL,
  `notes` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `turniere`
--

CREATE TABLE IF NOT EXISTS `turniere` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game` int(10) unsigned NOT NULL,
  `turnier` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE IF NOT EXISTS `token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `token` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(200) NOT NULL,
  `pw` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `ad_level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `login`, `pw`, `name`, `ad_level`) VALUES
(1, 'superadmin', '7505d64a54e061b7acd54ccd58b49dc43500b635', 'Marvin the Paranoid Android', 5);
