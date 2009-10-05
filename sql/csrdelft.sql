-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 05, 2009 at 09:57 PM
-- Server version: 5.1.30
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `csrdelft`
--

-- --------------------------------------------------------

--
-- Table structure for table `biebadmingewijzigd`
--

CREATE TABLE IF NOT EXISTS `biebadmingewijzigd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exemplaar_id` int(11) NOT NULL DEFAULT '0',
  `oud_boek_id` int(11) NOT NULL DEFAULT '0',
  `auteur_id` int(11) NOT NULL DEFAULT '0',
  `categorie_id` int(11) NOT NULL DEFAULT '0',
  `titel` varchar(200) NOT NULL DEFAULT '',
  `taal` enum('Nederlands','Engels','Duits','Frans','Overig') NOT NULL DEFAULT 'Nederlands',
  `isbn` varchar(15) NOT NULL DEFAULT '',
  `paginas` smallint(6) DEFAULT NULL,
  `uitgavejaar` mediumint(4) DEFAULT NULL,
  `uitgeverij` varchar(100) DEFAULT NULL,
  `tijdstip` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `biebauteur`
--

CREATE TABLE IF NOT EXISTS `biebauteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=664 ;

-- --------------------------------------------------------

--
-- Table structure for table `biebbeschrijving`
--

CREATE TABLE IF NOT EXISTS `biebbeschrijving` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boek_id` int(11) NOT NULL DEFAULT '0',
  `schrijver_uid` varchar(4) NOT NULL DEFAULT '',
  `beschrijving` text NOT NULL,
  `toegevoegd` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `boek-id` (`boek_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `biebbevestiging`
--

CREATE TABLE IF NOT EXISTS `biebbevestiging` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exemplaar_id` int(11) NOT NULL DEFAULT '0',
  `uitgeleend_uid` varchar(4) NOT NULL DEFAULT '',
  `geleend_of_teruggegeven` enum('geleend','teruggegeven') NOT NULL DEFAULT 'geleend',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=65 ;

-- --------------------------------------------------------

--
-- Table structure for table `biebboek`
--

CREATE TABLE IF NOT EXISTS `biebboek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur_id` int(11) NOT NULL DEFAULT '0',
  `categorie_id` int(11) NOT NULL DEFAULT '0',
  `titel` varchar(200) NOT NULL DEFAULT '',
  `taal` enum('Nederlands','Engels','Duits','Frans','Overig') NOT NULL DEFAULT 'Nederlands',
  `isbn` varchar(15) NOT NULL DEFAULT '',
  `paginas` smallint(6) DEFAULT NULL,
  `uitgavejaar` mediumint(4) DEFAULT NULL,
  `uitgeverij` varchar(100) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`,`categorie_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=831 ;

-- --------------------------------------------------------

--
-- Table structure for table `biebcategorie`
--

CREATE TABLE IF NOT EXISTS `biebcategorie` (
  `id` int(11) NOT NULL DEFAULT '0',
  `p_id` int(11) NOT NULL DEFAULT '0',
  `categorie` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebexemplaar`
--

CREATE TABLE IF NOT EXISTS `biebexemplaar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boek_id` int(11) NOT NULL DEFAULT '0',
  `eigenaar_uid` varchar(4) NOT NULL DEFAULT '',
  `uitgeleend_uid` varchar(4) NOT NULL DEFAULT '',
  `toegevoegd` int(11) NOT NULL DEFAULT '0',
  `extern` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `boek_id` (`boek_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=842 ;

-- --------------------------------------------------------

--
-- Table structure for table `courant`
--

CREATE TABLE IF NOT EXISTS `courant` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `verzendMoment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `template` varchar(50) NOT NULL DEFAULT 'csrmail.tpl',
  `verzender` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=126 ;

-- --------------------------------------------------------

--
-- Table structure for table `courantbericht`
--

CREATE TABLE IF NOT EXISTS `courantbericht` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `courantID` int(11) NOT NULL DEFAULT '0',
  `titel` varchar(100) NOT NULL DEFAULT '',
  `cat` enum('voorwoord','bestuur','csr','overig','sponsor') NOT NULL DEFAULT 'bestuur',
  `bericht` text NOT NULL,
  `volgorde` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `datumTijd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1632 ;

-- --------------------------------------------------------

--
-- Table structure for table `courantcache`
--

CREATE TABLE IF NOT EXISTS `courantcache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(100) NOT NULL DEFAULT '',
  `cat` enum('voorwoord','bestuur','csr','overig','sponsor') NOT NULL DEFAULT 'overig',
  `bericht` text NOT NULL,
  `uid` varchar(4) NOT NULL DEFAULT '',
  `datumTijd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `volgorde` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL DEFAULT '',
  `categorie` int(11) NOT NULL DEFAULT '0',
  `datum` date NOT NULL DEFAULT '1000-01-01',
  `verwijderd` enum('0','1') NOT NULL DEFAULT '0',
  `eigenaar` varchar(4) NOT NULL DEFAULT 'x101',
  PRIMARY KEY (`id`),
  KEY `categorie` (`categorie`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=484 ;

-- --------------------------------------------------------

--
-- Table structure for table `documentbestand`
--

CREATE TABLE IF NOT EXISTS `documentbestand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) NOT NULL DEFAULT '0',
  `bestandsnaam` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `document_id` (`documentID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=511 ;

-- --------------------------------------------------------

--
-- Table structure for table `documentencategorie`
--

CREATE TABLE IF NOT EXISTS `documentencategorie` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(50) NOT NULL DEFAULT '',
  `beschrijving` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `eetplan`
--

CREATE TABLE IF NOT EXISTS `eetplan` (
  `avond` smallint(6) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '0',
  `huis` smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY `avond` (`avond`,`uid`,`huis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eetplanhuis`
--

CREATE TABLE IF NOT EXISTS `eetplanhuis` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `naam` varchar(50) NOT NULL DEFAULT '',
  `adres` varchar(100) NOT NULL DEFAULT '',
  `telefoon` varchar(20) NOT NULL DEFAULT '',
  `groepid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forum_cat`
--

CREATE TABLE IF NOT EXISTS `forum_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(100) NOT NULL DEFAULT '',
  `beschrijving` text NOT NULL,
  `volgorde` int(11) NOT NULL DEFAULT '0',
  `lastuser` varchar(4) NOT NULL DEFAULT '',
  `lastpost` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lasttopic` int(11) NOT NULL DEFAULT '0',
  `lastpostID` int(11) NOT NULL DEFAULT '0',
  `reacties` int(11) NOT NULL DEFAULT '0',
  `topics` int(11) NOT NULL DEFAULT '0',
  `zichtbaar` enum('1','0') NOT NULL DEFAULT '1',
  `rechten_read` varchar(50) NOT NULL DEFAULT 'P_FORUM_READ',
  `rechten_post` varchar(50) NOT NULL DEFAULT 'P_FORUM_POST',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll`
--

CREATE TABLE IF NOT EXISTS `forum_poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topicID` int(11) NOT NULL DEFAULT '0',
  `optie` varchar(100) NOT NULL DEFAULT '',
  `stemmen` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topicID` (`topicID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=257 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll_stemmen`
--

CREATE TABLE IF NOT EXISTS `forum_poll_stemmen` (
  `topicID` int(11) NOT NULL DEFAULT '0',
  `optieID` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`topicID`,`uid`),
  KEY `optieID` (`optieID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forum_post`
--

CREATE TABLE IF NOT EXISTS `forum_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `tekst` text NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bewerkDatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bewerkt` text NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `zichtbaar` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','spam','verwijderd') NOT NULL DEFAULT 'zichtbaar',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  FULLTEXT KEY `tekst` (`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29395 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_topic`
--

CREATE TABLE IF NOT EXISTS `forum_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie` int(11) NOT NULL DEFAULT '0',
  `titel` varchar(100) NOT NULL DEFAULT '',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `datumtijd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastuser` varchar(4) NOT NULL DEFAULT '',
  `lastpost` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastpostID` int(11) NOT NULL DEFAULT '0',
  `reacties` int(11) NOT NULL DEFAULT '0',
  `zichtbaar` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','verwijderd') NOT NULL DEFAULT 'zichtbaar',
  `plakkerig` enum('1','0') NOT NULL DEFAULT '0',
  `open` enum('1','0') NOT NULL DEFAULT '1',
  `soort` enum('T_NORMAAL','T_POLL','T_VBANK') NOT NULL DEFAULT 'T_NORMAAL',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `titel` (`titel`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2526 ;

-- --------------------------------------------------------

--
-- Table structure for table `groep`
--

CREATE TABLE IF NOT EXISTS `groep` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snaam` varchar(20) NOT NULL,
  `naam` varchar(50) NOT NULL,
  `sbeschrijving` text NOT NULL,
  `beschrijving` text NOT NULL,
  `gtype` int(11) NOT NULL,
  `status` enum('ht','ot','ft') NOT NULL DEFAULT 'ht',
  `begin` date NOT NULL,
  `einde` date NOT NULL,
  `zichtbaar` enum('zichtbaar','onzichtbaar','verwijderd') NOT NULL DEFAULT 'zichtbaar',
  `aanmeldbaar` tinyint(1) NOT NULL DEFAULT '0',
  `limiet` int(11) NOT NULL DEFAULT '0',
  `toonFuncties` enum('tonen','verbergen','niet') NOT NULL DEFAULT 'tonen',
  `toonPasfotos` int(1) NOT NULL DEFAULT '0',
  `lidIsMod` int(1) NOT NULL DEFAULT '0' COMMENT 'Is elk lid mod',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=581 ;

-- --------------------------------------------------------

--
-- Table structure for table `groeplid`
--

CREATE TABLE IF NOT EXISTS `groeplid` (
  `groepid` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `op` enum('0','1') NOT NULL DEFAULT '0',
  `functie` varchar(25) NOT NULL DEFAULT '',
  `prioriteit` int(11) NOT NULL DEFAULT '0',
  `moment` datetime NOT NULL,
  PRIMARY KEY (`groepid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groeptype`
--

CREATE TABLE IF NOT EXISTS `groeptype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(150) NOT NULL,
  `beschrijving` text NOT NULL,
  `zichtbaar` tinyint(1) NOT NULL DEFAULT '1',
  `prioriteit` int(11) NOT NULL,
  `toonHistorie` int(1) NOT NULL DEFAULT '0' COMMENT 'ot-groepen laten zien in overzicht.',
  `toonProfiel` int(1) NOT NULL COMMENT 'Groep in profiel tonen?',
  PRIMARY KEY (`id`),
  KEY `naam` (`naam`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `lid`
--

CREATE TABLE IF NOT EXISTS `lid` (
  `uid` varchar(4) NOT NULL DEFAULT '',
  `nickname` varchar(20) NOT NULL DEFAULT '',
  `voornaam` varchar(50) NOT NULL DEFAULT '',
  `tussenvoegsel` varchar(15) NOT NULL DEFAULT '',
  `achternaam` varchar(50) NOT NULL DEFAULT '',
  `voorletters` varchar(10) NOT NULL,
  `postfix` varchar(7) NOT NULL DEFAULT '',
  `adres` varchar(100) NOT NULL DEFAULT '',
  `postcode` varchar(20) NOT NULL DEFAULT '',
  `woonplaats` varchar(50) NOT NULL DEFAULT '',
  `land` varchar(50) NOT NULL DEFAULT '',
  `telefoon` varchar(20) NOT NULL DEFAULT '',
  `mobiel` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `geslacht` enum('m','v') NOT NULL DEFAULT 'm',
  `voornamen` varchar(100) NOT NULL DEFAULT '',
  `icq` varchar(10) NOT NULL DEFAULT '',
  `msn` varchar(50) NOT NULL DEFAULT '',
  `skype` varchar(50) NOT NULL DEFAULT '',
  `jid` varchar(100) NOT NULL DEFAULT '',
  `website` varchar(80) NOT NULL DEFAULT '',
  `beroep` text NOT NULL,
  `studie` varchar(100) NOT NULL DEFAULT '',
  `patroon` varchar(4) NOT NULL,
  `studienr` varchar(20) NOT NULL,
  `studiejaar` smallint(6) NOT NULL DEFAULT '0',
  `lidjaar` smallint(6) NOT NULL DEFAULT '0',
  `lidafdatum` date NOT NULL,
  `gebdatum` date NOT NULL DEFAULT '0000-00-00',
  `bankrekening` varchar(11) NOT NULL DEFAULT '',
  `moot` tinyint(4) NOT NULL DEFAULT '0',
  `verticale` int(4) NOT NULL,
  `kring` tinyint(4) NOT NULL DEFAULT '0',
  `kringleider` enum('n','e','o') NOT NULL DEFAULT 'n',
  `motebal` enum('0','1') NOT NULL DEFAULT '0',
  `o_adres` varchar(100) NOT NULL DEFAULT '',
  `o_postcode` varchar(20) NOT NULL DEFAULT '',
  `o_woonplaats` varchar(50) NOT NULL DEFAULT '',
  `o_land` varchar(50) NOT NULL DEFAULT '',
  `o_telefoon` varchar(20) NOT NULL DEFAULT '',
  `kerk` varchar(50) NOT NULL DEFAULT '',
  `muziek` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(60) NOT NULL DEFAULT '',
  `permissies` enum('P_LID','P_NOBODY','P_PUBCIE','P_OUDLID','P_MODERATOR','P_MAALCIE','P_BESTUUR','P_KNORRIE','P_VAB','P_ETER') NOT NULL DEFAULT 'P_NOBODY',
  `status` enum('S_CIE','S_GASTLID','S_LID','S_NOBODY','S_NOVIET','S_OUDLID','S_KRINGEL') NOT NULL DEFAULT 'S_CIE',
  `eetwens` text NOT NULL,
  `corvee_wens` varchar(255) NOT NULL,
  `corvee_punten` int(11) NOT NULL,
  `corvee_vrijstelling` int(3) NOT NULL COMMENT 'percentage vrijstelling',
  `corvee_kwalikok` tinyint(1) NOT NULL,
  `corvee_voorkeuren` varchar(8) NOT NULL DEFAULT '11111111',
  `forum_name` enum('nick','civitas') NOT NULL DEFAULT 'civitas',
  `forum_postsortering` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
  `forum_laatstbekeken` datetime NOT NULL,
  `instellingen` text NOT NULL,
  `ontvangtcontactueel` enum('ja','nee') NOT NULL DEFAULT 'ja',
  `kgb` text NOT NULL,
  `rssToken` varchar(25) NOT NULL COMMENT 'Zonder ingelogged te zijn toch volledig rss-feed weergeven',
  `soccieID` int(11) NOT NULL DEFAULT '0',
  `createTerm` enum('barvoor','barachter') NOT NULL DEFAULT 'barvoor',
  `soccieSaldo` float NOT NULL DEFAULT '0',
  `maalcieSaldo` float NOT NULL DEFAULT '0',
  `changelog` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `nickname` (`nickname`),
  KEY `verticale` (`verticale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledenlijst';

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(4) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `locatie` varchar(15) NOT NULL DEFAULT '',
  `moment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `url` varchar(250) NOT NULL DEFAULT '',
  `referer` varchar(250) NOT NULL DEFAULT '',
  `useragent` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3811945 ;

-- --------------------------------------------------------

--
-- Table structure for table `logaggregated`
--

CREATE TABLE IF NOT EXISTS `logaggregated` (
  `soort` enum('maand','jaar','ip','url') NOT NULL DEFAULT 'maand',
  `waarde` varchar(255) NOT NULL,
  `pageviews` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijd`
--

CREATE TABLE IF NOT EXISTS `maaltijd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` int(11) NOT NULL DEFAULT '0',
  `gesloten` enum('0','1') NOT NULL DEFAULT '0',
  `tekst` text NOT NULL,
  `abosoort` varchar(20) NOT NULL DEFAULT '',
  `max` smallint(6) NOT NULL DEFAULT '0',
  `aantal` smallint(6) NOT NULL DEFAULT '0',
  `tp` varchar(4) NOT NULL DEFAULT '',
  `koks` int(11) NOT NULL,
  `afwassers` int(11) NOT NULL,
  `theedoeken` int(11) NOT NULL,
  `punten_kok` smallint(4) NOT NULL,
  `punten_afwas` smallint(4) NOT NULL,
  `punten_theedoek` smallint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=510 ;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdaanmelding`
--

CREATE TABLE IF NOT EXISTS `maaltijdaanmelding` (
  `uid` varchar(4) NOT NULL DEFAULT '',
  `maalid` int(11) NOT NULL DEFAULT '0',
  `status` enum('AAN','AF') NOT NULL DEFAULT 'AAN',
  `door` varchar(4) NOT NULL DEFAULT '',
  `gasten` int(11) NOT NULL DEFAULT '0',
  `gasten_opmerking` varchar(255) NOT NULL DEFAULT '',
  `tijdstip` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY (`uid`,`maalid`),
  KEY `maalid` (`maalid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdabo`
--

CREATE TABLE IF NOT EXISTS `maaltijdabo` (
  `uid` varchar(4) NOT NULL DEFAULT '',
  `abosoort` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`abosoort`),
  KEY `abosoort` (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdabosoort`
--

CREATE TABLE IF NOT EXISTS `maaltijdabosoort` (
  `abosoort` varchar(20) NOT NULL DEFAULT '',
  `tekst` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdcorvee`
--

CREATE TABLE IF NOT EXISTS `maaltijdcorvee` (
  `maalid` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `kok` tinyint(1) NOT NULL DEFAULT '0',
  `afwas` tinyint(1) NOT NULL DEFAULT '0',
  `theedoek` tinyint(1) NOT NULL DEFAULT '0',
  `punten_toegekend` enum('ja','nee','onbekend') NOT NULL DEFAULT 'onbekend',
  PRIMARY KEY (`maalid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdgesloten`
--

CREATE TABLE IF NOT EXISTS `maaltijdgesloten` (
  `uid` varchar(4) NOT NULL DEFAULT '',
  `eetwens` text NOT NULL,
  `maalid` int(11) NOT NULL DEFAULT '0',
  `door` varchar(4) NOT NULL DEFAULT '',
  `gasten` int(11) NOT NULL DEFAULT '0',
  `gasten_opmerking` varchar(255) NOT NULL DEFAULT '',
  `tijdstip` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY (`uid`,`maalid`),
  KEY `maalid` (`maalid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mededeling`
--

CREATE TABLE IF NOT EXISTS `mededeling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `titel` text NOT NULL,
  `tekst` text NOT NULL,
  `categorie` int(11) NOT NULL DEFAULT '0',
  `prioriteit` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT 'Hoe belangrijk is deze mededeling?',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `prive` enum('0','1') NOT NULL DEFAULT '0',
  `zichtbaarheid` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','verwijderd') NOT NULL DEFAULT 'zichtbaar' COMMENT 'Is hij zichtbaar?',
  `plaatje` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `intern` (`prive`),
  FULLTEXT KEY `kopje` (`titel`,`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten' AUTO_INCREMENT=488 ;

-- --------------------------------------------------------

--
-- Table structure for table `mededelingcategorie`
--

CREATE TABLE IF NOT EXISTS `mededelingcategorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(250) NOT NULL,
  `prioriteit` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT 'De volgorde van de categorieën in de dropdown',
  `plaatje` varchar(250) NOT NULL,
  `beschrijving` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `pID` int(11) NOT NULL DEFAULT '0',
  `prioriteit` int(11) NOT NULL DEFAULT '0',
  `tekst` varchar(50) NOT NULL DEFAULT '',
  `link` varchar(100) NOT NULL DEFAULT '',
  `permission` varchar(50) NOT NULL DEFAULT 'P_NOBODY',
  `zichtbaar` enum('ja','nee') NOT NULL DEFAULT 'ja',
  `gasnelnaar` enum('ja','nee') NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `pID` (`pID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Table structure for table `pagina`
--

CREATE TABLE IF NOT EXISTS `pagina` (
  `naam` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `titel` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `inhoud` longtext COLLATE latin1_general_ci NOT NULL,
  `rechten_bekijken` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `rechten_bewerken` varchar(50) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`naam`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peiling`
--

CREATE TABLE IF NOT EXISTS `peiling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(100) DEFAULT NULL,
  `tekst` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `peilingoptie`
--

CREATE TABLE IF NOT EXISTS `peilingoptie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peilingid` int(11) NOT NULL,
  `optie` varchar(255) DEFAULT NULL,
  `stemmen` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `peiling_stemmen`
--

CREATE TABLE IF NOT EXISTS `peiling_stemmen` (
  `peilingid` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `saldolog`
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
-- Table structure for table `savedquery`
--

CREATE TABLE IF NOT EXISTS `savedquery` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `savedquery` text NOT NULL,
  `beschrijving` varchar(255) NOT NULL,
  `permissie` varchar(255) NOT NULL DEFAULT 'P_LOGGED_IN',
  `categorie` varchar(50) NOT NULL DEFAULT 'Overig',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `sjaarsactie`
--

CREATE TABLE IF NOT EXISTS `sjaarsactie` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL DEFAULT '',
  `beschrijving` text NOT NULL,
  `verantwoordelijke` varchar(4) NOT NULL DEFAULT '',
  `moment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `limiet` int(11) NOT NULL DEFAULT '15',
  `zichtbaar` enum('ja','nee') NOT NULL DEFAULT 'ja',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Table structure for table `sjaarsactielid`
--

CREATE TABLE IF NOT EXISTS `sjaarsactielid` (
  `actieID` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `moment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`actieID`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `torrent_info`
--

CREATE TABLE IF NOT EXISTS `torrent_info` (
  `id` char(40) NOT NULL COMMENT 'SHA1 Hash of the complete torrent file',
  `uid` char(4) NOT NULL COMMENT 'UID van degene die de torrent heeft toegevoegd',
  `name` varchar(256) NOT NULL COMMENT 'Bestandsnaam van de torrent',
  `description` text NOT NULL COMMENT 'Omschrijving van de torrent',
  `size` int(11) NOT NULL COMMENT 'Total size of referenced files',
  `raw` text NOT NULL COMMENT 'The torrent file itself, base64',
  `date_added` date NOT NULL COMMENT 'Tijdstip van toevoegen',
  `dl_bytes` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Downloaded bytes',
  `seeders` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Aantal seeders',
  `leechers` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Aantal leechers',
  `speed` smallint(6) NOT NULL DEFAULT '0' COMMENT 'unused for now',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `torrent_status`
--

CREATE TABLE IF NOT EXISTS `torrent_status` (
  `id` char(40) NOT NULL COMMENT 'Peer-ID, SHA1 hash, unique per-peer',
  `torrent_id` char(40) NOT NULL COMMENT 'FK',
  `ip` char(50) NOT NULL COMMENT 'IP Address',
  `port` smallint(6) NOT NULL COMMENT 'IP Port',
  `downloaded` bigint(20) NOT NULL COMMENT 'Downloaded bytes',
  `uploaded` bigint(20) NOT NULL COMMENT 'Uploaded bytes',
  `seeder` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is a seeder? (or a leecher)',
  `seen` date NOT NULL COMMENT 'Last seen at...',
  `client_version` varchar(250) NOT NULL COMMENT 'Client version',
  PRIMARY KEY (`id`),
  KEY `seen` (`seen`),
  KEY `torrent_id` (`torrent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vb_source`
--

CREATE TABLE IF NOT EXISTS `vb_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(400) NOT NULL,
  `votesum` int(11) NOT NULL,
  `votecount` int(11) NOT NULL,
  `lid` varchar(4) NOT NULL,
  `createdate` datetime NOT NULL,
  `ip` varchar(15) NOT NULL,
  `sourceType` enum('link','file','book','discussion') NOT NULL DEFAULT 'link',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=242 ;

-- --------------------------------------------------------

--
-- Table structure for table `vb_sourceopinion`
--

CREATE TABLE IF NOT EXISTS `vb_sourceopinion` (
  `sid` int(11) NOT NULL,
  `lid` varchar(4) NOT NULL,
  `rating` int(11) NOT NULL,
  `createdate` datetime NOT NULL,
  `comment` text NOT NULL,
  UNIQUE KEY `lid_source` (`sid`,`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vb_sourcesource`
--

CREATE TABLE IF NOT EXISTS `vb_sourcesource` (
  `source1` int(11) NOT NULL,
  `source2` int(11) NOT NULL,
  `lid` varchar(4) NOT NULL,
  `date` datetime NOT NULL,
  `reason` text NOT NULL,
  `status` enum('approved','disapproved') NOT NULL DEFAULT 'approved',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `sourcelink` (`source1`,`source2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vb_subject`
--

CREATE TABLE IF NOT EXISTS `vb_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lid` varchar(4) DEFAULT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `description` text,
  `isLeaf` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('invisible','open','closed') NOT NULL DEFAULT 'open',
  `ip` varchar(15) NOT NULL,
  `createdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='vb subjects' AUTO_INCREMENT=93 ;

-- --------------------------------------------------------

--
-- Table structure for table `vb_subjectsource`
--

CREATE TABLE IF NOT EXISTS `vb_subjectsource` (
  `subjid` int(11) NOT NULL,
  `sourceid` int(11) NOT NULL,
  `reason` text NOT NULL,
  `createdate` datetime NOT NULL,
  `lid` varchar(4) NOT NULL,
  UNIQUE KEY `sourcesub` (`subjid`,`sourceid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
