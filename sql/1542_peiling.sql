CREATE TABLE IF NOT EXISTS `peiling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(100) DEFAULT NULL,
  `tekst` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `peilingoptie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peilingid` int(11) NOT NULL,
  `optie` varchar(100) DEFAULT NULL,
  `stemmen` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `peiling_stemmen` (
  `peilingid` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;