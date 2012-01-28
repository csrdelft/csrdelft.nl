ALTER TABLE `maaltijdcorvee` 
ADD `kwalikok` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `uid` ,
ADD `kwaliafwas` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `kok` ;

INSERT INTO `maaltijdcorveeinstellingen` (`instelling`, `type`, `datum`, `tekst`, `int`) VALUES
('puntenkwalikoken', 'int', '0000-00-00', '', 1);


ALTER TABLE `maaltijd` 
ADD `kwalikoks` INT( 11 ) NOT NULL AFTER `tp` ,
ADD `punten_kwalikok` SMALLINT( 4 ) NOT NULL AFTER `theedoeken` ;
