CREATE TABLE IF NOT EXISTS  `forum_gelezen` (
	`tid` INT( 11 ) NOT NULL ,
	`uid` VARCHAR( 4 ) CHARACTER SET latin1 NOT NULL ,
	`moment` DATETIME NOT NULL ,
	UNIQUE KEY  `tid` (  `tid` ,  `uid` )
) ENGINE = INNODB DEFAULT CHARSET = utf8;
