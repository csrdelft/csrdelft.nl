--
-- Nieuwe documentenketzer
--
-- PAS OP: de oude tabellen worden weggegooid, dat houdt in de de huidige
-- documenten weg zijn.

DROP TABLE document;
DROP TABLE documentbestand;
DROP TABLE documentencategorie;

CREATE TABLE IF NOT EXISTS `document` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(150) NOT NULL,
  `catID` int(11) NOT NULL,
  `bestandsnaam` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `mimetype` varchar(50) NOT NULL,
  `toegevoegd` datetime NOT NULL,
  `eigenaar` varchar(4) NOT NULL,
  `leesrechten` varchar(100) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `catID` (`catID`)
);


CREATE TABLE IF NOT EXISTS `documentcategorie` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(255) NOT NULL,
  `zichtbaar` int(1) NOT NULL,
  `leesrechten` varchar(150) NOT NULL,
  PRIMARY KEY  (`ID`)
) ;

INSERT INTO `documentcategorie` (`ID`, `naam`, `zichtbaar`, `leesrechten`) VALUES
(1, 'Bestuur', 1, 'P_DOCS_READ'),
(2, 'Lezingen', 1, 'P_DOCS_READ'),
(3, 'Reglementen', 1, 'P_DOCS_READ'),
(4, 'Kringen', 1, 'P_DOCS_READ');
