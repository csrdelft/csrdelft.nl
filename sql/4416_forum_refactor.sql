RENAME TABLE `csrdelft`.`forum_cat` TO `csrdelft`.`forum_deel` ;
ALTER TABLE `forum_deel` ENGINE = InnoDB;
ALTER TABLE `forum_deel` CHANGE `id` `forum_id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE `forum_deel` ADD `categorie_id` INT NOT NULL AFTER `forum_id` ;
ALTER TABLE `forum_deel` CHANGE `titel` `titel` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `forum_deel` CHANGE `beschrijving` `omschrijving` TEXT NOT NULL ;
ALTER TABLE `forum_deel` DROP `volgorde` ;
ALTER TABLE `forum_deel` CHANGE `lastuser` `lastuser` VARCHAR( 4 ) NOT NULL AFTER `lastpost` ;
ALTER TABLE `forum_deel` CHANGE `lastpost` `laatst_gepost` DATETIME NOT NULL ;
ALTER TABLE `forum_deel` CHANGE `lastpostID` `laatste_post_id` INT( 11 ) NOT NULL AFTER `laatst_gepost` ;
ALTER TABLE `forum_deel` CHANGE `lastuser` `laatste_lid_id` VARCHAR( 4 ) NOT NULL ;
ALTER TABLE `forum_deel` DROP `lasttopic` ;
ALTER TABLE `forum_deel` CHANGE `topics` `aantal_topics` INT( 11 ) NOT NULL AFTER `laatste_lid_id` ;
ALTER TABLE `forum_deel` CHANGE `reacties` `aantal_posts` INT( 11 ) NOT NULL ;
ALTER TABLE `forum_deel` DROP `zichtbaar` ;
ALTER TABLE `forum_deel` CHANGE `rechten_read` `rechten_lezen` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `forum_deel` ADD `volgorde` INT NOT NULL ;
RENAME TABLE `csrdelft`.`forum_deel` TO `csrdelft`.`forum_delen` ;

CREATE TABLE IF NOT EXISTS `forum_categorien` (
  `categorie_id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) NOT NULL,
  `omschrijving` text NOT NULL,
  `rechten_lezen` varchar(255) NOT NULL,
  `volgorde` int(11) NOT NULL,
  PRIMARY KEY (`categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

RENAME TABLE `csrdelft`.`forum_topic` TO `csrdelft`.`forum_draden` ;
ALTER TABLE `forum_draden` CHANGE `id` `topic_id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE `forum_draden` CHANGE `categorie` `forum_id` INT( 11 ) NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `uid` `lid_id` VARCHAR( 4 ) NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `titel` `titel` VARCHAR( 255 ) NOT NULL AFTER `lid_id` ;
ALTER TABLE `forum_draden` CHANGE `datumtijd` `datum_tijd` DATETIME NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `lastuser` `laatste_lid_id` VARCHAR( 4 ) NOT NULL AFTER `lastpostID` ;
ALTER TABLE `forum_draden` CHANGE `lastpost` `laatst_gepost` DATETIME NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `lastpostID` `laatste_post_id` INT( 11 ) NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `reacties` `aantal_posts` INT( 11 ) NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `zichtbaar` `status` ENUM( 'wacht_goedkeuring', 'zichtbaar', 'onzichtbaar', 'verwijderd' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'zichtbaar';
ALTER TABLE `forum_draden` ADD `gesloten` TINYINT( 1 ) NOT NULL ;
UPDATE `forum_draden` SET `gesloten` = TRUE WHERE `open` !=1;
ALTER TABLE `forum_draden` DROP `open` ;
ALTER TABLE `forum_draden` ADD `wacht_goedkeuring` TINYINT NOT NULL AFTER `gesloten` ;
ALTER TABLE `forum_draden` CHANGE `wacht_goedkeuring` `wacht_goedkeuring` TINYINT( 1 ) NOT NULL ;
UPDATE `forum_draden` SET `wacht_goedkeuring` = TRUE WHERE `status` = 'wacht_goedkeuring';
ALTER TABLE `forum_draden` ADD `verwijderd` TINYINT( 1 ) NOT NULL AFTER `gesloten` ;
UPDATE `forum_draden` SET `verwijderd` = TRUE WHERE `status` = 'verwijderd';
ALTER TABLE `forum_draden` DROP `status` ;
ALTER TABLE `forum_draden` CHANGE `belangrijk` `belangrijk` BOOLEAN NOT NULL ;
ALTER TABLE `forum_draden` CHANGE `plakkerig` `plakkerig` BOOLEAN NOT NULL AFTER `wacht_goedkeuring` ;
UPDATE `forum_draden` SET `plakkerig` = FALSE WHERE `plakkerig` !=1;
UPDATE `forum_draden` SET `belangrijk` = FALSE WHERE `belangrijk` !=1;
ALTER TABLE `forum_draden` DROP `soort` ;
ALTER TABLE `forum_draden` DROP INDEX titel;
ALTER TABLE `forum_draden` DROP INDEX datumtijd;
ALTER TABLE `forum_draden` DROP INDEX lastPostID;
ALTER TABLE `forum_draden` DROP INDEX zichtbaar;
ALTER TABLE `forum_draden` ENGINE = InnoDB;

ALTER TABLE `forum_post` DROP INDEX tid;
ALTER TABLE `forum_post` DROP INDEX uid;
ALTER TABLE `forum_post` DROP INDEX datum;
ALTER TABLE `forum_post` DROP INDEX tekst;
ALTER TABLE `forum_post` DROP INDEX zichtbaar;
ALTER TABLE `forum_post` CHANGE `id` `post_id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE `forum_post` CHANGE `tid` `topic_id` INT( 11 ) NOT NULL ;
ALTER TABLE `forum_post` CHANGE `uid` `lid_id` VARCHAR( 4 ) NOT NULL ;
ALTER TABLE `forum_post` CHANGE `datum` `datum_tijd` DATETIME NOT NULL ;
ALTER TABLE `forum_post` CHANGE `bewerkDatum` `laatst_bewerkt` DATETIME NULL DEFAULT NULL ;
ALTER TABLE `forum_post` CHANGE `bewerkt` `bewerkt_tekst` TEXT NULL DEFAULT NULL ;
ALTER TABLE `forum_post` CHANGE `ip` `auteur_ip` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `forum_post` ADD `wacht_goedkeuring` BOOLEAN NOT NULL ;
ALTER TABLE `forum_post` ADD `verwijderd` BOOLEAN NOT NULL AFTER `bewerkt_tekst` ;
UPDATE `forum_post` SET `wacht_goedkeuring` = TRUE WHERE zichtbaar = 'wacht_goedkeuring';
UPDATE `forum_post` SET `verwijderd` = TRUE WHERE `zichtbaar` = 'verwijderd';
ALTER TABLE `forum_post` DROP `zichtbaar` ;
ALTER TABLE `forum_post` ENGINE = InnoDB;
RENAME TABLE `csrdelft`.`forum_post` TO `csrdelft`.`forum_posts` ;
UPDATE `forum_posts` SET `laatst_bewerkt` = NULL ,
`bewerkt_tekst` = NULL WHERE `laatst_bewerkt` = '0000-00-00 00:00:00' AND `bewerkt_tekst` = '';

ALTER TABLE `forum_gelezen` CHANGE `uid` `lid_id` VARCHAR( 4 ) NOT NULL ;
ALTER TABLE `forum_gelezen` CHANGE `tid` `topic_id` INT( 11 ) NOT NULL ;
RENAME TABLE `csrdelft`.`forum_gelezen` TO `csrdelft`.`forum_topic_gelezen` ;
ALTER TABLE `forum_topic_gelezen` CHANGE `moment` `datum_tijd` DATETIME NOT NULL ;

ALTER TABLE `forum_delen` CHANGE `aantal_topics` `aantal_draden` INT( 11 ) NOT NULL ;
ALTER TABLE `forum_topics` CHANGE `topic_id` `draad_id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE `forum_posts` CHANGE `topic_id` `draad_id` INT( 11 ) NOT NULL ;
RENAME TABLE `csrdelft`.`forum_topics` TO `csrdelft`.`forum_draden` ;
ALTER TABLE `forum_topic_gelezen` CHANGE `topic_id` `draad_id` INT( 11 ) NOT NULL ;
RENAME TABLE `csrdelft`.`forum_topic_gelezen` TO `csrdelft`.`forum_draden_gelezen` ;

ALTER TABLE `forum_categorien` ADD INDEX ( `volgorde` ) ;

ALTER TABLE `forum_delen` ADD INDEX ( `categorie_id` ) ;
ALTER TABLE `forum_delen` ADD INDEX ( `volgorde` ) ;

ALTER TABLE `forum_draden` ADD INDEX ( `forum_id` ) ;
ALTER TABLE `forum_draden` ADD INDEX ( `laatst_gepost` ) ;
ALTER TABLE `forum_draden` ADD INDEX ( `wacht_goedkeuring` ) ;

ALTER TABLE `forum_posts` ADD INDEX ( `draad_id` ) ;
ALTER TABLE `forum_posts` ADD INDEX ( `lid_id` ) ;
ALTER TABLE `forum_posts` ADD INDEX ( `wacht_goedkeuring` ) ;

ALTER TABLE `forum_draden_gelezen` DROP INDEX tid;
ALTER TABLE `forum_draden_gelezen` ADD PRIMARY KEY ( `draad_id` , `lid_id` ) ;
ALTER TABLE `forum_draden_gelezen` ADD INDEX ( `draad_id` ) ;
ALTER TABLE `forum_draden_gelezen` ADD INDEX ( `lid_id` ) ;

ALTER TABLE `forum_draden` CHANGE `laatst_gepost` `laatst_gepost` DATETIME NULL DEFAULT NULL ;
ALTER TABLE `forum_draden` CHANGE `laatste_post_id` `laatste_post_id` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE `forum_draden` CHANGE `laatste_lid_id` `laatste_lid_id` VARCHAR( 4 ) NULL DEFAULT NULL ;

ALTER TABLE `forum_delen` CHANGE `laatst_gepost` `laatst_gepost` DATETIME NULL DEFAULT NULL ;
ALTER TABLE `forum_delen` CHANGE `laatste_post_id` `laatste_post_id` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE `forum_delen` CHANGE `laatste_lid_id` `laatste_lid_id` VARCHAR( 4 ) NULL DEFAULT NULL ;
