CREATE TABLE IF NOT EXISTS `mlt_archief` (
  `maaltijd_id` int(11) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `prijs` float NOT NULL DEFAULT '0',
  `aanmeldingen` text NOT NULL DEFAULT '',
  PRIMARY KEY (`maaltijd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
