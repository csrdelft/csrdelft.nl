-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-1~dko1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generatie Tijd: 27 Jun 2007 om 15:01
-- Server versie: 4.1.11
-- PHP Versie: 4.3.10-21
-- 
-- Database: 'csrdelft'


-- -- 
-- Tabel structuur voor tabel 'agenda'
-- 

CREATE TABLE agenda (
  id int(11) NOT NULL auto_increment,
  tijd int(11) NOT NULL default '0',
  tekst text character set latin1 NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'bestuur'
-- 

CREATE TABLE bestuur (
  ID int(11) NOT NULL auto_increment,
  jaar year(4) NOT NULL default '0000',
  naam varchar(100) NOT NULL default '',
  praeses varchar(4) NOT NULL default '',
  abactis varchar(4) NOT NULL default '',
  fiscus varchar(4) NOT NULL default '',
  vice_praeses varchar(4) NOT NULL default '',
  vice_abactis varchar(4) NOT NULL default '',
  verhaal text NOT NULL,
  bbcode_uid varchar(10) NOT NULL default '',
  tekst varchar(255) NOT NULL default '',
  PRIMARY KEY  (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'bewoner'
-- 

CREATE TABLE bewoner (
  uid varchar(4) NOT NULL default '',
  woonoordid int(11) NOT NULL default '0',
  op enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (uid,woonoordid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'commissie'
-- 

CREATE TABLE commissie (
  id int(11) NOT NULL auto_increment,
  naam varchar(20) NOT NULL default '',
  stekst text NOT NULL,
  titel varchar(50) NOT NULL default '',
  tekst text NOT NULL,
  bbcode_uid varchar(10) NOT NULL default '0000000000',
  link text NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Commissies' AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'commissielid'
-- 

CREATE TABLE commissielid (
  cieid int(11) NOT NULL default '0',
  uid varchar(4) NOT NULL default '',
  op enum('0','1') NOT NULL default '0',
  functie varchar(25) NOT NULL default '',
  prioriteit int(11) NOT NULL default '0',
  PRIMARY KEY  (cieid,uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ComissieLeden';

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'document'
-- 

CREATE TABLE document (
  id int(11) NOT NULL auto_increment,
  naam varchar(100) NOT NULL default '',
  categorie int(11) NOT NULL default '0',
  datum date NOT NULL default '1000-01-01',
  verwijderd enum('0','1') NOT NULL default '0',
  eigenaar varchar(4) NOT NULL default 'x101',
  PRIMARY KEY  (id),
  KEY categorie (categorie)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=165 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'documentbestand'
-- 

CREATE TABLE documentbestand (
  id int(11) NOT NULL auto_increment,
  documentID int(11) NOT NULL default '0',
  bestandsnaam varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY document_id (documentID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=179 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'documentencategorie'
-- 

CREATE TABLE documentencategorie (
  ID int(11) NOT NULL auto_increment,
  naam varchar(50) NOT NULL default '',
  beschrijving varchar(100) NOT NULL default '',
  PRIMARY KEY  (ID),
  KEY ID (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'eetplan'
-- 

CREATE TABLE eetplan (
  avond smallint(6) NOT NULL default '0',
  uid varchar(4) NOT NULL default '0',
  huis smallint(6) NOT NULL default '0',
  UNIQUE KEY avond (avond,uid,huis)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'eetplanhuis'
-- 

CREATE TABLE eetplanhuis (
  id smallint(6) NOT NULL default '0',
  naam varchar(50) NOT NULL default '',
  adres varchar(100) NOT NULL default '',
  telefoon varchar(20) NOT NULL default '',
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'forum_cat'
-- 

CREATE TABLE forum_cat (
  id int(11) NOT NULL auto_increment,
  titel varchar(100) NOT NULL default '',
  beschrijving text NOT NULL,
  volgorde int(11) NOT NULL default '0',
  lastuser varchar(4) NOT NULL default '',
  lastpost datetime NOT NULL default '0000-00-00 00:00:00',
  lasttopic int(11) NOT NULL default '0',
  lastpostID int(11) NOT NULL default '0',
  reacties int(11) NOT NULL default '0',
  topics int(11) NOT NULL default '0',
  zichtbaar enum('1','0') NOT NULL default '1',
  rechten_read varchar(50) NOT NULL default 'P_FORUM_READ',
  rechten_post varchar(50) NOT NULL default 'P_FORUM_POST',
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (1, 'C.S.R.-zaken', 'Afdeling voor interne zaken. Onzichtbaar voor externen die niet kunnen inloggen.', 1, '9911', '2007-06-27 15:27:53', 757, 7192, 4447, 316, '1', 'P_LOGGED_IN', 'P_FORUM_POST');
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (2, 'Extern', 'Feestjes bij NSx of Ichthusx, een toffe conferentie, of een mooi concert bezoeken? Kom maar door!', 20, '0633', '2007-06-25 16:03:47', 688, 7050, 414, 87, '1', 'P_FORUM_READ', 'P_FORUM_READ');
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (3, 'Webstek terugkoppeling', 'Feuten in de site gevonden? Wilt u nieuwe functies erin? Kom dan hier!', 30, '0539', '2007-06-13 00:31:58', 491, 6868, 578, 56, '1', 'P_FORUM_READ', 'P_LOGGED_IN');
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (6, 'PubCie-forum', 'Dit forum is enkel zichtbaar voor forum moderators.', 200, '0622', '2007-06-24 15:31:15', 684, 7034, 64, 7, '1', 'P_FORUM_MOD', 'P_FORUM_MOD');
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (4, 'Zandbak & blætverhalen', 'Kom hier maar spelen en uw slappe blætverhælen ophangen.', 100, '0307', '2007-06-27 15:09:39', 752, 7191, 1069, 85, '1', 'P_FORUM_READ', 'P_FORUM_POST');
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (7, 'Peilingen', 'Speciale categorie voor peilingen.', 9999, '0217', '2007-02-17 20:39:48', 534, 5088, 7, 4, '0', 'P_FORUM_MOD', 'P_FORUM_POST');
INSERT INTO `forum_cat` (`id`, `titel`, `beschrijving`, `volgorde`, `lastuser`, `lastpost`, `lasttopic`, `lastpostID`, `reacties`, `topics`, `zichtbaar`, `rechten_read`, `rechten_post`) VALUES (8, 'Oudleden', 'De plek om te zijn voor de oudleden onder ons.', 11, '0221', '2007-05-23 12:22:58', 707, 6547, 87, 12, '1', 'P_OUDLEDEN_READ', 'P_OUDLEDEN_READ');

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'forum_poll'
-- 

CREATE TABLE forum_poll (
  id int(11) NOT NULL auto_increment,
  topicID int(11) NOT NULL default '0',
  optie varchar(100) NOT NULL default '',
  stemmen int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY topicID (topicID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=178 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'forum_poll_stemmen'
-- 

CREATE TABLE forum_poll_stemmen (
  topicID int(11) NOT NULL default '0',
  optieID int(11) NOT NULL default '0',
  uid varchar(4) NOT NULL default '',
  PRIMARY KEY  (topicID,uid),
  KEY optieID (optieID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'forum_post'
-- 

CREATE TABLE forum_post (
  id int(11) NOT NULL auto_increment,
  tid int(11) NOT NULL default '0',
  uid varchar(4) NOT NULL default '',
  tekst text NOT NULL,
  bbcode_uid varchar(10) NOT NULL default '',
  datum datetime NOT NULL default '0000-00-00 00:00:00',
  bewerkDatum datetime NOT NULL default '0000-00-00 00:00:00',
  ip varchar(15) NOT NULL default '',
  zichtbaar enum('wacht_goedkeuring','zichtbaar','onzichtbaar','verwijderd') NOT NULL default 'zichtbaar',
  PRIMARY KEY  (id),
  KEY tid (tid),
  FULLTEXT KEY tekst (tekst)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=7190 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'forum_poststatistiek'
-- 

CREATE TABLE forum_poststatistiek (
  id int(11) NOT NULL auto_increment,
  tid int(11) NOT NULL default '0',
  datum datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY tid (tid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=6807 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'forum_topic'
-- 

CREATE TABLE forum_topic (
  id int(11) NOT NULL auto_increment,
  categorie int(11) NOT NULL default '0',
  titel varchar(100) NOT NULL default '',
  uid varchar(4) NOT NULL default '',
  datumtijd datetime NOT NULL default '0000-00-00 00:00:00',
  lastuser varchar(4) NOT NULL default '',
  lastpost datetime NOT NULL default '0000-00-00 00:00:00',
  lastpostID int(11) NOT NULL default '0',
  reacties int(11) NOT NULL default '0',
  zichtbaar enum('wacht_goedkeuring','zichtbaar','onzichtbaar') NOT NULL default 'zichtbaar',
  plakkerig enum('1','0') NOT NULL default '0',
  `open` enum('1','0') NOT NULL default '1',
  soort enum('T_NORMAAL','T_POLL','T_LEZING') NOT NULL default 'T_NORMAAL',
  PRIMARY KEY  (id),
  FULLTEXT KEY titel (titel)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=758 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'inschrijving'
-- 

CREATE TABLE inschrijving (
  ID int(11) NOT NULL auto_increment,
  naam varchar(100) NOT NULL default '',
  beschrijving text NOT NULL,
  verantwoordelijke varchar(4) NOT NULL default '',
  moment date NOT NULL default '0000-00-00',
  limiet int(11) NOT NULL default '30',
  zichtbaar enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (ID),
  UNIQUE KEY naam (naam,moment)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'inschrijvinglid'
-- 

CREATE TABLE inschrijvinglid (
  inschrijvingid int(11) NOT NULL default '0',
  uid varchar(4) NOT NULL default '',
  partner text NOT NULL,
  eetwens_partner text NOT NULL,
  PRIMARY KEY  (inschrijvingid,uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ingericht op gala, kan wellicht beter';

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'lid'
-- 

CREATE TABLE lid (
  uid varchar(4) NOT NULL default '',
  nickname varchar(20) NOT NULL default '',
  voornaam varchar(50) NOT NULL default '',
  tussenvoegsel varchar(15) NOT NULL default '',
  achternaam varchar(50) NOT NULL default '',
  postfix varchar(7) NOT NULL default '',
  adres varchar(100) NOT NULL default '',
  postcode varchar(20) NOT NULL default '',
  woonplaats varchar(50) NOT NULL default '',
  land varchar(50) NOT NULL default '',
  telefoon varchar(20) NOT NULL default '',
  mobiel varchar(20) NOT NULL default '',
  email varchar(150) NOT NULL default '',
  geslacht enum('m','v') NOT NULL default 'm',
  voornamen varchar(100) NOT NULL default '',
  icq varchar(10) NOT NULL default '',
  msn varchar(50) NOT NULL default '',
  skype varchar(50) NOT NULL default '',
  jid varchar(100) NOT NULL default '',
  website varchar(80) NOT NULL default '',
  beroep text NOT NULL,
  studie varchar(100) NOT NULL default '',
  studiejaar smallint(6) NOT NULL default '0',
  lidjaar smallint(6) NOT NULL default '0',
  gebdatum date NOT NULL default '0000-00-00',
  bankrekening varchar(11) NOT NULL default '',
  moot tinyint(4) NOT NULL default '0',
  kring tinyint(4) NOT NULL default '0',
  kringleider enum('n','e','o') NOT NULL default 'n',
  motebal enum('0','1') NOT NULL default '0',
  o_adres varchar(100) NOT NULL default '',
  o_postcode varchar(20) NOT NULL default '',
  o_woonplaats varchar(50) NOT NULL default '',
  o_land varchar(50) NOT NULL default '',
  o_telefoon varchar(20) NOT NULL default '',
  kerk varchar(50) NOT NULL default '',
  muziek varchar(100) NOT NULL default '',
  `password` varchar(60) NOT NULL default '',
  permissies enum('P_LID','P_NOBODY','P_PUBCIE','P_OUDLID','P_MODERATOR','P_MAALCIE','P_BESTUUR','P_KNORRIE','P_VAB') NOT NULL default 'P_NOBODY',
  `status` enum('S_CIE','S_GASTLID','S_LID','S_NOBODY','S_NOVIET','S_OUDLID','S_KRINGEL') NOT NULL default 'S_CIE',
  eetwens text NOT NULL,
  forum_name enum('nick','civitas') NOT NULL default 'civitas',
  forum_postsortering enum('ASC','DESC') NOT NULL default 'ASC',
  kgb text NOT NULL,
  soccieID int(11) NOT NULL default '0',
  createTerm enum('barvoor','barachter') NOT NULL default 'barvoor',
  soccieSaldo float NOT NULL default '0',
  maalcieSaldo float NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY nickname (nickname)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledenlijst';


INSERT INTO lid (uid, nickname, voornaam, tussenvoegsel, achternaam, postfix, adres, postcode, woonplaats, land, telefoon, mobiel, email, geslacht, voornamen, icq, msn, skype, jid, website, beroep, studie, studiejaar, lidjaar, gebdatum, bankrekening, moot, kring, kringleider, motebal, o_adres, o_postcode, o_woonplaats, o_land, o_telefoon, kerk, muziek, password, permissies, status, eetwens, forum_name, forum_postsortering, kgb, soccieID, createTerm, soccieSaldo, maalcieSaldo) VALUES ('x027', 'pubcie', 'Publiciteits', '', 'Commissie', '', '', '', '', '', '', 'pubcie@csrdelft.nl', '', 'm', 'PubCie', '', '', '', '', '', '', '', 0, 0, '0000-00-00', '', 0, 0, 'n', '', '', '', '', '', '', '', '', '{SSHA}rhtmIzQ/phfNCVlLpRpPT6kHU+2GECh1', 'P_PUBCIE', 'S_CIE', '', 'civitas', 'ASC', '', 0, 'barvoor', 0, 0);
INSERT INTO lid (uid, nickname, voornaam, tussenvoegsel, achternaam, postfix, adres, postcode, woonplaats, land, telefoon, mobiel, email, geslacht, voornamen, icq, msn, skype, jid, website, beroep, studie, studiejaar, lidjaar, gebdatum, bankrekening, moot, kring, kringleider, motebal, o_adres, o_postcode, o_woonplaats, o_land, o_telefoon, kerk, muziek, password, permissies, status, eetwens, forum_name, forum_postsortering, kgb, soccieID, createTerm, soccieSaldo, maalcieSaldo) VALUES ('x101', 'feut', 'Jan', '', 'Lid', '', 'Oude Delft 9', '2611 BA', 'Delft', 'Nederland', '0800-3388', '06-34782573', 'feut@csrdelft.nl', 'm', 'Novitus', '', '', '', '', '', '', 'feutenkunde', 1961, 1961, '1941-02-03', '', 0, 0, 'n', '0', '', '', '', '', '', '', '', '{SSHA}ePrEMi7NnGvbh1teAEx3J1qdzwbKKJ2H', 'P_LID', 'S_LID', 'Havermout', 'civitas', 'ASC', '', 0, 'barvoor', 0, 0);
INSERT INTO lid (uid, nickname, voornaam, tussenvoegsel, achternaam, postfix, adres, postcode, woonplaats, land, telefoon, mobiel, email, geslacht, voornamen, icq, msn, skype, jid, website, beroep, studie, studiejaar, lidjaar, gebdatum, bankrekening, moot, kring, kringleider, motebal, o_adres, o_postcode, o_woonplaats, o_land, o_telefoon, kerk, muziek, password, permissies, status, eetwens, forum_name, forum_postsortering, kgb, soccieID, createTerm, soccieSaldo, maalcieSaldo) VALUES ('x999', 'nobody', 'Niet', '', 'ingelogd', '', '', '', '', '', '', '', '', 'm', '', '', '', '', '', '', '', '', 0, 0, '0000-00-00', '', 0, 0, 'n', '', '', '', '', '', '', '', '', '{SSHA}hWVi8Exr2k78R2n/JJbQGnXk6oqc7hJR', 'P_NOBODY', 'S_NOBODY', '', 'civitas', 'ASC', '', 0, 'barvoor', 0, 0);

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'log'
-- 

CREATE TABLE log (
  ID int(11) NOT NULL auto_increment,
  uid varchar(4) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  locatie varchar(15) NOT NULL default '',
  moment datetime NOT NULL default '0000-00-00 00:00:00',
  url varchar(250) NOT NULL default '',
  referer varchar(250) NOT NULL default '',
  useragent varchar(250) NOT NULL default '',
  PRIMARY KEY  (ID),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=985092 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'maaltijd'
-- 

CREATE TABLE maaltijd (
  id int(11) NOT NULL auto_increment,
  datum int(11) NOT NULL default '0',
  gesloten enum('0','1') NOT NULL default '0',
  tekst text NOT NULL,
  abosoort varchar(20) NOT NULL default '',
  max smallint(6) NOT NULL default '0',
  aantal smallint(6) NOT NULL default '0',
  tp varchar(4) NOT NULL default '',
  kok1 varchar(4) NOT NULL default '',
  kok2 varchar(4) NOT NULL default '',
  afw1 varchar(4) NOT NULL default '',
  afw2 varchar(4) NOT NULL default '',
  afw3 varchar(4) NOT NULL default '',
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=221 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'maaltijdaanmelding'
-- 

CREATE TABLE maaltijdaanmelding (
  uid varchar(4) NOT NULL default '',
  maalid int(11) NOT NULL default '0',
  `status` enum('AAN','AF') NOT NULL default 'AAN',
  door varchar(4) NOT NULL default '',
  gasten int(11) NOT NULL default '0',
  gasten_opmerking varchar(255) NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  ip varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (uid,maalid),
  KEY maalid (maalid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'maaltijdabo'
-- 

CREATE TABLE maaltijdabo (
  uid varchar(4) NOT NULL default '',
  abosoort varchar(20) NOT NULL default '0',
  PRIMARY KEY  (uid,abosoort),
  KEY abosoort (abosoort)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'maaltijdabosoort'
-- 

CREATE TABLE maaltijdabosoort (
  abosoort varchar(20) NOT NULL default '',
  tekst varchar(20) NOT NULL default '',
  PRIMARY KEY  (abosoort)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'maaltijdgesloten'
-- 

CREATE TABLE maaltijdgesloten (
  uid varchar(4) NOT NULL default '',
  naam text NOT NULL,
  eetwens text NOT NULL,
  maalid int(11) NOT NULL default '0',
  door varchar(4) NOT NULL default '',
  gasten int(11) NOT NULL default '0',
  gasten_opmerking varchar(255) NOT NULL default '',
  tijdstip int(11) NOT NULL default '0',
  ip varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (uid,maalid),
  KEY maalid (maalid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'menu'
-- 

CREATE TABLE menu (
  ID int(11) NOT NULL auto_increment,
  pID int(11) NOT NULL default '0',
  prioriteit int(11) NOT NULL default '0',
  tekst varchar(50) NOT NULL default '',
  link varchar(100) NOT NULL default '',
  permission varchar(50) NOT NULL default 'P_NOBODY',
  zichtbaar enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (ID),
  KEY pID (pID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=36 ;

INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (1, 0, 0, 'Thuis', '/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (2, 0, 10, 'Vereniging', '/vereniging/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (3, 0, 20, 'Intern', '/intern/', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (4, 0, 30, 'Maaltijden', '/maaltijden/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (5, 0, 50, 'Forum', '/forum/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (6, 0, 60, 'Contact', '/contact/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (34, 4, 0, 'Inschrijven', '/maaltijden/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (8, 2, 10, 'Bestuur', '/vereniging/bestuur/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (9, 2, 20, 'Geschiedenis', '/vereniging/geschiedenis.php', 'P_NOBODY', 'nee');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (10, 2, 30, 'Verbanden', '/vereniging/verbanden.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (11, 2, 40, 'Lid worden', '/vereniging/lidworden.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (12, 5, 10, 'Zoeken', '/forum/zoeken.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (13, 3, 0, 'Eetplan', '/intern/eetplan.php', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (26, 21, 0, 'Woonoorden', '/groepen/woonoorden.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (15, 3, 0, 'Verjaardagen', '/intern/verjaardagen.php', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (35, 6, 0, 'Sponsors', '/contact/sponsors', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (17, 3, 0, 'Ledenlijst', '/intern/lijst.php', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (29, 3, 0, 'Documenten', '/intern/documenten/', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (18, 4, 90, 'Beheer', '/maaltijden/beheer/', 'P_MAAL_MOD', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (19, 4, 30, 'Instellingen', '/maaltijden/voorkeuren.php', 'P_MAAL_WIJ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (20, 6, 0, 'Koppelingen', '/links.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (21, 0, 15, 'Groepen', '/groepen/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (22, 21, 0, 'Commissies', '/groepen/commissie/', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (23, 21, 0, 'Onderverenigingen', '/groepen/onderverenigingen.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (24, 21, 0, 'Werkgroepen', '/groepen/werkgroepen.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (25, 21, 0, 'Moten', '/groepen/moten.php', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (27, 2, 26, 'Lezingen', '/vereniging/lezingen.php', 'P_NOBODY', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (28, 3, 0, 'C.S.R.-courant', '/intern/csrmail/', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (30, 6, 0, 'Webmail', 'http://webmail.csrdelft.nl', 'P_LEDEN_READ', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (31, 5, 0, 'Peiling toevoegen', '/forum/maak-stemming/1', 'P_FORUM_MOD', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (32, 4, 999, 'Saldo''s updaten', '/maaltijden/saldi.php', 'P_MAAL_MOD', 'ja');
INSERT INTO menu (ID, pID, prioriteit, tekst, link, permission, zichtbaar) VALUES (33, 1, 0, 'Nieuws', '/nieuws/', 'P_NOBODY', 'ja');

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'nieuws'
-- 

CREATE TABLE nieuws (
  id int(11) NOT NULL auto_increment,
  datum int(11) NOT NULL default '0',
  titel text NOT NULL,
  tekst text NOT NULL,
  bbcode_uid varchar(10) NOT NULL default '',
  uid varchar(4) NOT NULL default '',
  prive enum('0','1') NOT NULL default '0',
  verborgen enum('0','1') NOT NULL default '0',
  verwijderd enum('0','1') NOT NULL default '0',
  plaatje varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY intern (prive),
  FULLTEXT KEY kopje (titel,tekst)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten' AUTO_INCREMENT=122 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'pubciemail'
-- 

CREATE TABLE pubciemail (
  ID int(11) NOT NULL auto_increment,
  verzendMoment datetime NOT NULL default '0000-00-00 00:00:00',
  template varchar(50) NOT NULL default 'csrmail.tpl',
  verzender varchar(4) NOT NULL default '',
  PRIMARY KEY  (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'pubciemailbericht'
-- 

CREATE TABLE pubciemailbericht (
  ID int(11) NOT NULL auto_increment,
  pubciemailID int(11) NOT NULL default '0',
  titel varchar(100) NOT NULL default '',
  cat enum('voorwoord','bestuur','csr','overig') NOT NULL default 'bestuur',
  bericht text NOT NULL,
  volgorde int(11) NOT NULL default '0',
  uid varchar(4) NOT NULL default '',
  datumTijd datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=278 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'pubciemailcache'
-- 

CREATE TABLE pubciemailcache (
  ID int(11) NOT NULL auto_increment,
  titel varchar(100) NOT NULL default '',
  cat enum('voorwoord','bestuur','csr','overig') NOT NULL default 'overig',
  bericht text NOT NULL,
  uid varchar(4) NOT NULL default '',
  datumTijd datetime NOT NULL default '0000-00-00 00:00:00',
  volgorde int(11) NOT NULL default '0',
  PRIMARY KEY  (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'sjaarsactie'
-- 

CREATE TABLE sjaarsactie (
  ID int(11) NOT NULL auto_increment,
  naam varchar(100) NOT NULL default '',
  beschrijving text NOT NULL,
  verantwoordelijke varchar(4) NOT NULL default '',
  moment datetime NOT NULL default '0000-00-00 00:00:00',
  limiet int(11) NOT NULL default '15',
  zichtbaar enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'sjaarsactielid'
-- 

CREATE TABLE sjaarsactielid (
  actieID int(11) NOT NULL default '0',
  uid varchar(4) NOT NULL default '',
  moment datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (actieID,uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'socciesaldi'
-- 

CREATE TABLE socciesaldi (
  uid varchar(4) NOT NULL default '',
  soccieID int(11) NOT NULL default '0',
  saldo float NOT NULL default '0',
  createTerm enum('barvoor','barachter') NOT NULL default 'barvoor',
  maalSaldo float NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY soccieID (soccieID,createTerm)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel 'woonoord'
-- 

CREATE TABLE woonoord (
  id int(11) NOT NULL auto_increment,
  naam varchar(100) NOT NULL default '',
  sort varchar(4) NOT NULL default '',
  tekst text NOT NULL,
  adres varchar(100) NOT NULL default '',
  `status` enum('W_HUIS','W_KOT','W_OVERIG') NOT NULL default 'W_HUIS',
  plaatje varchar(150) NOT NULL default '',
  link varchar(150) NOT NULL default '',
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;



