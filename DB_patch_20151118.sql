--
-- Table structure for table `token`
--

CREATE TABLE IF NOT EXISTS `token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `token` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


ALTER TABLE `games` ADD `token_pool` INT UNSIGNED NOT NULL ;

ALTER TABLE `running` ADD `token` TEXT NOT NULL AFTER `score` ;
ALTER TABLE `running` ADD `token_pool` int(10) unsigned NOT NULL AFTER `score` ;
