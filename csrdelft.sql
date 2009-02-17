-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generatie Tijd: 17 Feb 2009 om 12:04
-- Server versie: 5.0.51
-- PHP Versie: 5.2.6-1+lenny2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `csrdelft`
--

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `courant`
--

CREATE TABLE IF NOT EXISTS `courant` (
  `ID` int(11) NOT NULL auto_increment,
  `verzendMoment` datetime NOT NULL default '0000-00-00 00:00:00',
  `template` varchar(50) NOT NULL default 'csrmail.tpl',
  `verzender` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=108 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `courantbericht`
--

CREATE TABLE IF NOT EXISTS `courantbericht` (
  `ID` int(11) NOT NULL auto_increment,
  `courantID` int(11) NOT NULL default '0',
  `titel` varchar(100) NOT NULL default '',
  `cat` enum('voorwoord','bestuur','csr','overig','sponsor') NOT NULL default 'bestuur',
  `bericht` text NOT NULL,
  `volgorde` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `datumTijd` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1400 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `courantcache`
--

CREATE TABLE IF NOT EXISTS `courantcache` (
  `ID` int(11) NOT NULL auto_increment,
  `titel` varchar(100) NOT NULL default '',
  `cat` enum('voorwoord','bestuur','csr','overig','sponsor') NOT NULL default 'overig',
  `bericht` text NOT NULL,
  `uid` varchar(4) NOT NULL default '',
  `datumTijd` datetime NOT NULL default '0000-00-00 00:00:00',
  `volgorde` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `document`
--

CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  `categorie` int(11) NOT NULL default '0',
  `datum` date NOT NULL default '1000-01-01',
  `verwijderd` enum('0','1') NOT NULL default '0',
  `eigenaar` varchar(4) NOT NULL default 'x101',
  PRIMARY KEY  (`id`),
  KEY `categorie` (`categorie`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=438 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `documentbestand`
--

CREATE TABLE IF NOT EXISTS `documentbestand` (
  `id` int(11) NOT NULL auto_increment,
  `documentID` int(11) NOT NULL default '0',
  `bestandsnaam` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `document_id` (`documentID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=465 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `documentencategorie`
--

CREATE TABLE IF NOT EXISTS `documentencategorie` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(50) NOT NULL default '',
  `beschrijving` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `eetplan`
--

CREATE TABLE IF NOT EXISTS `eetplan` (
  `avond` smallint(6) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '0',
  `huis` smallint(6) NOT NULL default '0',
  UNIQUE KEY `avond` (`avond`,`uid`,`huis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `eetplanhuis`
--

CREATE TABLE IF NOT EXISTS `eetplanhuis` (
  `id` smallint(6) NOT NULL default '0',
  `naam` varchar(50) NOT NULL default '',
  `adres` varchar(100) NOT NULL default '',
  `telefoon` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `forum_cat`
--

CREATE TABLE IF NOT EXISTS `forum_cat` (
  `id` int(11) NOT NULL auto_increment,
  `titel` varchar(100) NOT NULL default '',
  `beschrijving` text NOT NULL,
  `volgorde` int(11) NOT NULL default '0',
  `lastuser` varchar(4) NOT NULL default '',
  `lastpost` datetime NOT NULL default '0000-00-00 00:00:00',
  `lasttopic` int(11) NOT NULL default '0',
  `lastpostID` int(11) NOT NULL default '0',
  `reacties` int(11) NOT NULL default '0',
  `topics` int(11) NOT NULL default '0',
  `zichtbaar` enum('1','0') NOT NULL default '1',
  `rechten_read` varchar(50) NOT NULL default 'P_FORUM_READ',
  `rechten_post` varchar(50) NOT NULL default 'P_FORUM_POST',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `forum_poll`
--

CREATE TABLE IF NOT EXISTS `forum_poll` (
  `id` int(11) NOT NULL auto_increment,
  `topicID` int(11) NOT NULL default '0',
  `optie` varchar(100) NOT NULL default '',
  `stemmen` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topicID` (`topicID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=254 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `forum_poll_stemmen`
--

CREATE TABLE IF NOT EXISTS `forum_poll_stemmen` (
  `topicID` int(11) NOT NULL default '0',
  `optieID` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`topicID`,`uid`),
  KEY `optieID` (`optieID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `forum_post`
--

CREATE TABLE IF NOT EXISTS `forum_post` (
  `id` int(11) NOT NULL auto_increment,
  `tid` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `tekst` text NOT NULL,
  `datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `bewerkDatum` datetime NOT NULL default '0000-00-00 00:00:00',
  `bewerkt` text NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `zichtbaar` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','spam','verwijderd') NOT NULL default 'zichtbaar',
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`),
  FULLTEXT KEY `tekst` (`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25044 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `forum_topic`
--

CREATE TABLE IF NOT EXISTS `forum_topic` (
  `id` int(11) NOT NULL auto_increment,
  `categorie` int(11) NOT NULL default '0',
  `titel` varchar(100) NOT NULL default '',
  `uid` varchar(4) NOT NULL default '',
  `datumtijd` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastuser` varchar(4) NOT NULL default '',
  `lastpost` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastpostID` int(11) NOT NULL default '0',
  `reacties` int(11) NOT NULL default '0',
  `zichtbaar` enum('wacht_goedkeuring','zichtbaar','onzichtbaar') NOT NULL default 'zichtbaar',
  `plakkerig` enum('1','0') NOT NULL default '0',
  `open` enum('1','0') NOT NULL default '1',
  `soort` enum('T_NORMAAL','T_POLL','T_VBANK') NOT NULL default 'T_NORMAAL',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `titel` (`titel`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2147 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `groep`
--

CREATE TABLE IF NOT EXISTS `groep` (
  `id` int(11) NOT NULL auto_increment,
  `snaam` varchar(20) NOT NULL,
  `naam` varchar(50) NOT NULL,
  `sbeschrijving` text NOT NULL,
  `beschrijving` text NOT NULL,
  `gtype` int(11) NOT NULL,
  `status` enum('ht','ot','ft') NOT NULL default 'ht',
  `begin` date NOT NULL,
  `einde` date NOT NULL,
  `zichtbaar` enum('zichtbaar','onzichtbaar','verwijderd') NOT NULL default 'zichtbaar',
  `aanmeldbaar` tinyint(1) NOT NULL default '0',
  `limiet` int(11) NOT NULL default '0',
  `toonFuncties` enum('tonen','verbergen','niet') NOT NULL default 'tonen',
  `toonPasfotos` int(1) NOT NULL default '0',
  `lidIsMod` int(1) NOT NULL default '0' COMMENT 'Is elk lid mod',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=481 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `groeplid`
--

CREATE TABLE IF NOT EXISTS `groeplid` (
  `groepid` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `op` enum('0','1') NOT NULL default '0',
  `functie` varchar(25) NOT NULL default '',
  `prioriteit` int(11) NOT NULL default '0',
  `moment` datetime NOT NULL,
  PRIMARY KEY  (`groepid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `groeptype`
--

CREATE TABLE IF NOT EXISTS `groeptype` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(150) NOT NULL,
  `beschrijving` text NOT NULL,
  `zichtbaar` tinyint(1) NOT NULL default '1',
  `prioriteit` int(11) NOT NULL,
  `toonHistorie` int(1) NOT NULL default '0' COMMENT 'ot-groepen laten zien in overzicht.',
  `toonProfiel` int(1) NOT NULL COMMENT 'Groep in profiel tonen?',
  PRIMARY KEY  (`id`),
  KEY `naam` (`naam`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `lid`
--

CREATE TABLE IF NOT EXISTS `lid` (
  `uid` varchar(4) NOT NULL default '',
  `nickname` varchar(20) NOT NULL default '',
  `voornaam` varchar(50) NOT NULL default '',
  `tussenvoegsel` varchar(15) NOT NULL default '',
  `achternaam` varchar(50) NOT NULL default '',
  `postfix` varchar(7) NOT NULL default '',
  `adres` varchar(100) NOT NULL default '',
  `postcode` varchar(20) NOT NULL default '',
  `woonplaats` varchar(50) NOT NULL default '',
  `land` varchar(50) NOT NULL default '',
  `telefoon` varchar(20) NOT NULL default '',
  `mobiel` varchar(20) NOT NULL default '',
  `email` varchar(150) NOT NULL default '',
  `geslacht` enum('m','v') NOT NULL default 'm',
  `voornamen` varchar(100) NOT NULL default '',
  `icq` varchar(10) NOT NULL default '',
  `msn` varchar(50) NOT NULL default '',
  `skype` varchar(50) NOT NULL default '',
  `jid` varchar(100) NOT NULL default '',
  `website` varchar(80) NOT NULL default '',
  `beroep` text NOT NULL,
  `studie` varchar(100) NOT NULL default '',
  `studienr` varchar(20) NOT NULL,
  `studiejaar` smallint(6) NOT NULL default '0',
  `lidjaar` smallint(6) NOT NULL default '0',
  `gebdatum` date NOT NULL default '0000-00-00',
  `bankrekening` varchar(11) NOT NULL default '',
  `moot` tinyint(4) NOT NULL default '0',
  `kring` tinyint(4) NOT NULL default '0',
  `kringleider` enum('n','e','o') NOT NULL default 'n',
  `motebal` enum('0','1') NOT NULL default '0',
  `o_adres` varchar(100) NOT NULL default '',
  `o_postcode` varchar(20) NOT NULL default '',
  `o_woonplaats` varchar(50) NOT NULL default '',
  `o_land` varchar(50) NOT NULL default '',
  `o_telefoon` varchar(20) NOT NULL default '',
  `kerk` varchar(50) NOT NULL default '',
  `muziek` varchar(100) NOT NULL default '',
  `password` varchar(60) NOT NULL default '',
  `permissies` enum('P_LID','P_NOBODY','P_PUBCIE','P_OUDLID','P_MODERATOR','P_MAALCIE','P_BESTUUR','P_KNORRIE','P_VAB') NOT NULL default 'P_NOBODY',
  `status` enum('S_CIE','S_GASTLID','S_LID','S_NOBODY','S_NOVIET','S_OUDLID','S_KRINGEL') NOT NULL default 'S_CIE',
  `eetwens` text NOT NULL,
  `corvee_wens` varchar(255) NOT NULL,
  `corvee_punten` int(11) NOT NULL,
  `corvee_vrijstelling` int(3) NOT NULL COMMENT 'percentage vrijstelling',
  `corvee_kwalikok` tinyint(1) NOT NULL,
  `forum_name` enum('nick','civitas') NOT NULL default 'civitas',
  `forum_postsortering` enum('ASC','DESC') NOT NULL default 'ASC',
  `forum_laatstbekeken` datetime NOT NULL,
  `kgb` text NOT NULL,
  `rssToken` varchar(25) NOT NULL COMMENT 'Zonder ingelogged te zijn toch volledig rss-feed weergeven',
  `soccieID` int(11) NOT NULL default '0',
  `createTerm` enum('barvoor','barachter') NOT NULL default 'barvoor',
  `soccieSaldo` float NOT NULL default '0',
  `maalcieSaldo` float NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `nickname` (`nickname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledenlijst';

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `ID` int(11) NOT NULL auto_increment,
  `uid` varchar(4) NOT NULL default '',
  `ip` varchar(15) NOT NULL default '',
  `locatie` varchar(15) NOT NULL default '',
  `moment` datetime NOT NULL default '0000-00-00 00:00:00',
  `url` varchar(250) NOT NULL default '',
  `referer` varchar(250) NOT NULL default '',
  `useragent` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2849001 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `logAggregated`
--

CREATE TABLE IF NOT EXISTS `logAggregated` (
  `soort` enum('maand','jaar','ip','url') NOT NULL default 'maand',
  `waarde` varchar(255) NOT NULL,
  `pageviews` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `maaltijd`
--

CREATE TABLE IF NOT EXISTS `maaltijd` (
  `id` int(11) NOT NULL auto_increment,
  `datum` int(11) NOT NULL default '0',
  `gesloten` enum('0','1') NOT NULL default '0',
  `tekst` text NOT NULL,
  `abosoort` varchar(20) NOT NULL default '',
  `max` smallint(6) NOT NULL default '0',
  `aantal` smallint(6) NOT NULL default '0',
  `tp` varchar(4) NOT NULL default '',
  `koks` int(11) NOT NULL,
  `afwassers` int(11) NOT NULL,
  `theedoeken` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=461 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `maaltijdaanmelding`
--

CREATE TABLE IF NOT EXISTS `maaltijdaanmelding` (
  `uid` varchar(4) NOT NULL default '',
  `maalid` int(11) NOT NULL default '0',
  `status` enum('AAN','AF') NOT NULL default 'AAN',
  `kookt` tinyint(1) NOT NULL,
  `wastaf` tinyint(1) NOT NULL,
  `theedoeken` tinyint(1) NOT NULL,
  `door` varchar(4) NOT NULL default '',
  `gasten` int(11) NOT NULL default '0',
  `gasten_opmerking` varchar(255) NOT NULL default '',
  `tijdstip` int(11) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (`uid`,`maalid`),
  KEY `maalid` (`maalid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `maaltijdabo`
--

CREATE TABLE IF NOT EXISTS `maaltijdabo` (
  `uid` varchar(4) NOT NULL default '',
  `abosoort` varchar(20) NOT NULL default '0',
  PRIMARY KEY  (`uid`,`abosoort`),
  KEY `abosoort` (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `maaltijdabosoort`
--

CREATE TABLE IF NOT EXISTS `maaltijdabosoort` (
  `abosoort` varchar(20) NOT NULL default '',
  `tekst` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `maaltijdgesloten`
--

CREATE TABLE IF NOT EXISTS `maaltijdgesloten` (
  `uid` varchar(4) NOT NULL default '',
  `eetwens` text NOT NULL,
  `maalid` int(11) NOT NULL default '0',
  `door` varchar(4) NOT NULL default '',
  `gasten` int(11) NOT NULL default '0',
  `gasten_opmerking` varchar(255) NOT NULL default '',
  `tijdstip` int(11) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (`uid`,`maalid`),
  KEY `maalid` (`maalid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `mededeling`
--

CREATE TABLE IF NOT EXISTS `mededeling` (
  `id` int(11) NOT NULL auto_increment,
  `datum` int(11) NOT NULL default '0',
  `titel` text NOT NULL,
  `tekst` text NOT NULL,
  `categorie` int(11) NOT NULL default '0',
  `rank` tinyint(3) unsigned NOT NULL default '255',
  `uid` varchar(4) NOT NULL default '',
  `prive` enum('0','1') NOT NULL default '0',
  `verborgen` enum('0','1') NOT NULL default '0',
  `verwijderd` enum('0','1') NOT NULL default '0',
  `plaatje` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `intern` (`prive`),
  FULLTEXT KEY `kopje` (`titel`,`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten' AUTO_INCREMENT=453 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `mededelingcategorie`
--

CREATE TABLE IF NOT EXISTS `mededelingcategorie` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(250) NOT NULL,
  `rank` tinyint(3) NOT NULL default '0',
  `plaatje` varchar(250) NOT NULL,
  `beschrijving` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `ID` int(11) NOT NULL auto_increment,
  `pID` int(11) NOT NULL default '0',
  `prioriteit` int(11) NOT NULL default '0',
  `tekst` varchar(50) NOT NULL default '',
  `link` varchar(100) NOT NULL default '',
  `permission` varchar(50) NOT NULL default 'P_NOBODY',
  `zichtbaar` enum('ja','nee') NOT NULL default 'ja',
  `gasnelnaar` enum('ja','nee') NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `pID` (`pID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `pagina`
--

CREATE TABLE IF NOT EXISTS `pagina` (
  `naam` varchar(30) collate latin1_general_ci NOT NULL,
  `titel` varchar(100) collate latin1_general_ci NOT NULL,
  `inhoud` longtext collate latin1_general_ci NOT NULL,
  `rechten_bekijken` varchar(50) collate latin1_general_ci NOT NULL,
  `rechten_bewerken` varchar(50) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`naam`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `saldolog`
--

CREATE TABLE IF NOT EXISTS `saldolog` (
  `uid` varchar(4) NOT NULL,
  `moment` datetime NOT NULL,
  `cie` enum('soccie','maalcie') NOT NULL,
  `saldo` float NOT NULL,
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `savedquery`
--

CREATE TABLE IF NOT EXISTS `savedquery` (
  `ID` int(11) NOT NULL auto_increment,
  `savedquery` text NOT NULL,
  `beschrijving` varchar(255) NOT NULL,
  `permissie` varchar(255) NOT NULL default 'P_LOGGED_IN',
  `categorie` varchar(50) NOT NULL default 'Overig',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `sjaarsactie`
--

CREATE TABLE IF NOT EXISTS `sjaarsactie` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  `beschrijving` text NOT NULL,
  `verantwoordelijke` varchar(4) NOT NULL default '',
  `moment` datetime NOT NULL default '0000-00-00 00:00:00',
  `limiet` int(11) NOT NULL default '15',
  `zichtbaar` enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `sjaarsactielid`
--

CREATE TABLE IF NOT EXISTS `sjaarsactielid` (
  `actieID` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `moment` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`actieID`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lid` (`uid`, `nickname`, `voornaam`, `tussenvoegsel`, `achternaam`, `postfix`, `adres`, `postcode`, `woonplaats`, `land`, `telefoon`, `mobiel`, `email`, `geslacht`, `voornamen`, `icq`, `msn`, `skype`, `jid`, `website`, `beroep`, `studie`, `studienr`, `studiejaar`, `lidjaar`, `gebdatum`, `bankrekening`, `moot`, `kring`, `kringleider`, `motebal`, `o_adres`, `o_postcode`, `o_woonplaats`, `o_land`, `o_telefoon`, `kerk`, `muziek`, `password`, `permissies`, `status`, `eetwens`, `corvee_wens`, `corvee_punten`, `corvee_vrijstelling`, `corvee_kwalikok`, `forum_name`, `forum_postsortering`, `forum_laatstbekeken`, `kgb`, `rssToken`, `soccieID`, `createTerm`, `soccieSaldo`, `maalcieSaldo`) VALUES
('4444', 'oudlid', 'oud', '', 'lid', '', 'oude delft 9', '2613 GL', 'Delft', 'Nederland', '015-2197748', '06-42901018', '', 'm', '', '111111', 'feuten@msn.nl', 'skiep', 'Jabber@bravo.nl', 'http://csrdelft.nl', 'Scheerschooier', 'Schoenmaker', '', 0, 1944, '2005-12-13', '11111112115', 0, 0, 'n', '0', 'papa', '1234ma', 'mama', 'Nederland', '010-5115456', '', '', '{SSHA}YW54T2DvJk6mb4h9Su3P/0Ng+rMwfdoT', 'P_OUDLID', 'S_OUDLID', 'pils!', '', 0, 0, 0, 'civitas', 'ASC', '2009-02-01 01:21:44', '', 'bf563d9dae2ddde27784a6f19', 0, 'barvoor', 0, 0),
('x027', 'pubcie', 'Publiciteits', '', 'Commissie', '', '', '', '', '', '', '', '', 'm', 'PubCie', '', '', '', '', '', '', '', '', 0, 0, '0000-00-00', '', 0, 0, 'n', '0', '', '', '', '', '', '', '', '{SSHA}28E2jEZATuA70rds9sGIvDaOQ9ECL/Ia', 'P_PUBCIE', 'S_LID', '', '', 0, 0, 0, 'civitas', 'ASC', '0000-00-00 00:00:00', '', '', 0, 'barvoor', 0, 0),
('x101', 'feut', 'Jan', '', 'Lid', '', 'Oude Delft 9', '2611 BA', 'Delft', 'Nederland', '0800-3388', '06-34782573', '', 'm', 'Novitus', '', '', '', '', '', '', 'feutenkunde', '', 1961, 1961, '1941-02-03', '', 0, 0, 'n', '0', '', '', '', '', '', '', '', '{SSHA}Fg+jW4r+in3KC32p4JcEvuE9/zBwcuSk', 'P_LID', 'S_LID', 'havermout', '', 0, 0, 0, 'civitas', 'ASC', '2009-02-16 00:20:26', '', '', 0, 'barvoor', 0, 0),
('x999', 'nobody', 'Niet', '', 'ingelogd', '', '', '', '', '', '', '', '', 'm', '', '', '', '', '', '', '', '', '', 0, 0, '0000-00-00', '', 0, 0, 'n', '0', '', '', '', '', '', '', '', '{SSHA}DgnxVkAu6zqB2wLneaFq0v1c75HVjI9A', 'P_NOBODY', 'S_NOBODY', '', '', 0, 0, 0, 'civitas', 'ASC', '0000-00-00 00:00:00', '', '77f1df87d16a7fdfaadaa10ba', 0, 'barvoor', 0, 0);

INSERT INTO `menu` (`ID`, `pID`, `prioriteit`, `tekst`, `link`, `permission`, `zichtbaar`, `gasnelnaar`) VALUES
(1, 0, 10, 'Vereniging', '/vereniging/', 'P_NOBODY', 'ja', 'nee'),
(2, 0, 20, 'Actueel', '/actueel/', 'P_NOBODY', 'ja', 'nee'),
(3, 0, 30, 'Communicatie', '/communicatie/', 'P_NOBODY', 'ja', 'nee'),
(4, 0, 40, 'Contact', '/contact/', 'P_NOBODY', 'ja', 'nee'),
(30, 1, 10, 'C.S.R.', '/vereniging/', 'P_NOBODY', 'ja', 'nee'),
(6, 1, 20, 'Geloof', '/vereniging/geloof/', 'P_NOBODY', 'ja', 'nee'),
(7, 1, 30, 'Vorming', '/vereniging/vorming/', 'P_NOBODY', 'ja', 'nee'),
(8, 1, 40, 'Gezelligheid', '/vereniging/gezelligheid/', 'P_NOBODY', 'ja', 'nee'),
(9, 1, 50, 'Sport', '/vereniging/sport/', 'P_NOBODY', 'ja', 'nee'),
(10, 1, 60, 'Ontspanning', '/vereniging/ontspanning/', 'P_NOBODY', 'ja', 'nee'),
(11, 1, 70, 'Sociëteit', '/vereniging/societeit/', 'P_NOBODY', 'ja', 'nee'),
(12, 1, 80, 'Officieel', '/vereniging/officieel/', 'P_NOBODY', 'ja', 'nee'),
(13, 1, 90, 'Vragen', '/vereniging/vragen/', 'P_NOBODY', 'ja', 'nee'),
(14, 1, 100, 'Interesse', '/vereniging/interesse/', 'P_NOBODY', 'ja', 'nee'),
(16, 2, 20, 'Mededelingen', '/actueel/mededelingen/', 'P_NOBODY', 'ja', 'nee'),
(17, 2, 30, 'Courant', '/actueel/courant/', 'P_MAIL_POST', 'ja', 'ja'),
(18, 2, 40, 'Fotoalbum', '/actueel/fotoalbum/', 'P_NOBODY', 'ja', 'ja'),
(19, 2, 50, 'Maaltijden', '/actueel/maaltijden/', 'P_NOBODY', 'ja', 'ja'),
(20, 2, 60, 'Groepen', '/actueel/groepen/', 'P_NOBODY', 'ja', 'nee'),
(21, 2, 70, 'Eetplan', '/actueel/eetplan/', 'P_LEDEN_READ', 'ja', 'nee'),
(22, 2, 80, 'Sjaarsacties', '/actueel/sjaarsacties/', 'P_LEDEN_READ', 'ja', 'nee'),
(23, 3, 10, 'Ledenlijst', '/communicatie/ledenlijst/', 'P_LEDEN_READ', 'ja', 'nee'),
(24, 3, 20, 'Forum', '/communicatie/forum/', 'P_NOBODY', 'ja', 'nee'),
(25, 3, 30, 'IRC', '/communicatie/irc/', 'P_NOBODY', 'ja', 'nee'),
(26, 3, 40, 'Documenten', '/communicatie/documenten/', 'P_DOCS_READ', 'ja', 'nee'),
(31, 4, 10, 'C.S.R. Delft', '/contact/', 'P_NOBODY', 'ja', 'nee'),
(32, 4, 30, 'Kamers', '/contact/kamers/', 'P_NOBODY', 'ja', 'nee'),
(28, 4, 20, 'Extern', '/contact/extern/', 'P_NOBODY', 'ja', 'nee'),
(29, 4, 40, 'Sponsors', '/contact/sponsors/', 'P_NOBODY', 'ja', 'nee'),
(33, 4, 35, 'Aankomende studenten', '/contact/aankomendestudenten/', 'P_NOBODY', 'ja', 'nee'),
(34, 2, 25, 'Agenda', '/actueel/agenda/', 'P_AGENDA_READ', 'ja', 'nee'),
(35, 2, 28, 'OWee', '/actueel/owee/', 'P_NOBODY', 'nee', 'nee');

INSERT INTO `groeptype` (`id`, `naam`, `beschrijving`, `zichtbaar`, `prioriteit`, `toonHistorie`, `toonProfiel`) VALUES
(1, 'Commissies', '[h=1]Commissies bij C.S.R.[/h]', 1, 1, 0, 1),
(2, 'Woonoorden', '[h=1]Woonoorden der C.S.R.[/h]', 1, 4, 0, 1),
(3, 'Onderverenigingen', '[h=1]Onderverenigingen[/h]', 1, 6, 0, 1),
(5, 'Overig', '[h=1]Overige groepen[/h]', 1, 50, 0, 1),
(6, 'Besturen', '[h=1]Besturen der Civitas[/h]', 1, 4, 1, 1),

