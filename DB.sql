-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Januar 2011 um 17:16
-- Server Version: 5.0.51
-- PHP-Version: 5.2.6-1+lenny9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `gameserver`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `icon` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `folder` varchar(200) NOT NULL,
  `cmd` text NOT NULL,
  `defaults` text NOT NULL,
  `start_port` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `games`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `running`
--

CREATE TABLE IF NOT EXISTS `running` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `screen` varchar(200) NOT NULL,
  `serverid` int(10) unsigned NOT NULL,
  `gameid` int(10) unsigned NOT NULL,
  `cmd` text NOT NULL,
  `port` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `vars` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `running`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `server`
--

CREATE TABLE IF NOT EXISTS `server` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `user` varchar(200) NOT NULL,
  `games` varchar(200) NOT NULL,
  `score` int(11) NOT NULL,
  `notes` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `server`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(200) NOT NULL,
  `pw` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `ad_level` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `login`, `pw`, `name`, `ad_level`) VALUES
(1, 'superadmin', '7505d64a54e061b7acd54ccd58b49dc43500b635', 'Marvin the Paranoid Android', 5);
