
UPDATE  `csrdelft`.`biebboek` SET  `uitgavejaar` =  '0' WHERE  `uitgavejaar` IS NULL ;
UPDATE  `csrdelft`.`biebboek` SET  `paginas` =  '0' WHERE  `paginas` IS NULL ;



ALTER TABLE  `biebboek` 
CHANGE  `paginas`  `paginas` SMALLINT( 6 ) NOT NULL  DEFAULT  '0',
CHANGE  `uitgavejaar`  `uitgavejaar` MEDIUMINT( 4 ) NOT NULL DEFAULT  '0',
CHANGE  `uitgeverij`  `uitgeverij` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `code`  `code` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `taal`  `taal` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'Nederlands';

ALTER TABLE  `biebbeschrijving` 
CHANGE  `toegevoegd`  `toegevoegd` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';

UPDATE  `csrdelft`.`biebbeschrijving` SET  `toegevoegd` =  '2007-07-21 14:38:03' WHERE  `biebbeschrijving`.`id` =1;
UPDATE  `csrdelft`.`biebbeschrijving` SET  `toegevoegd` =  '2007-07-21 14:44:52' WHERE  `biebbeschrijving`.`id` =2;
UPDATE  `csrdelft`.`biebbeschrijving` SET  `toegevoegd` =  '2007-07-21 15:59:25' WHERE  `biebbeschrijving`.`id` =3;
UPDATE  `csrdelft`.`biebbeschrijving` SET  `toegevoegd` =  '2007-07-22 20:33:06' WHERE  `biebbeschrijving`.`id` =4;
UPDATE  `csrdelft`.`biebbeschrijving` SET  `toegevoegd` =  '2007-07-22 20:42:25' WHERE  `biebbeschrijving`.`id` =5;
UPDATE  `csrdelft`.`biebbeschrijving` SET  `toegevoegd` =  '2007-07-21 20:46:55' WHERE  `biebbeschrijving`.`id` =6;

ALTER TABLE  `biebbeschrijving` 
ADD  `bewerkdatum` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';

UPDATE biebboek 
	SET auteur_id= 0
	WHERE `auteur_id`=9;


INSERT INTO  `csrdelft`.`menu` (
`ID` ,
`pID` ,
`prioriteit` ,
`tekst` ,
`link` ,
`permission` ,
`zichtbaar` ,
`gasnelnaar`
)
VALUES (
NULL ,  '3',  '20',  'Bibliotheek',  '/communicatie/bibliotheek',  'P_LOGGED_IN',  'ja',  'nee'
);

ALTER TABLE  `biebexemplaar` 
ADD  `status` ENUM(  'beschikbaar',  'uitgeleend',  'teruggegeven',  'vermist' ) NOT NULL DEFAULT  'beschikbaar',
ADD  `uitleendatum` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00',
ADD  `leningen` INT( 11 ) NOT NULL DEFAULT  '0';


ALTER TABLE  `biebexemplaar` 
DROP  `extern`;
ALTER TABLE  `biebexemplaar` 
CHANGE  `toegevoegd`  `toegevoegd` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00'

DROP TABLE  `biebbevestiging`
DROP TABLE  `biebadmingewijzigd`
