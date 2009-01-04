-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 04, 2009 at 09:20 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `csrdelft`
--

-- --------------------------------------------------------

--
-- Table structure for table `biebadmingewijzigd`
--

CREATE TABLE IF NOT EXISTS `biebadmingewijzigd` (
  `id` int(11) NOT NULL auto_increment,
  `exemplaar_id` int(11) NOT NULL default '0',
  `oud_boek_id` int(11) NOT NULL default '0',
  `auteur_id` int(11) NOT NULL default '0',
  `categorie_id` int(11) NOT NULL default '0',
  `titel` varchar(200) NOT NULL default '',
  `taal` enum('Nederlands','Engels','Duits','Frans','Overig') NOT NULL default 'Nederlands',
  `isbn` varchar(15) NOT NULL default '',
  `paginas` smallint(6) default NULL,
  `uitgavejaar` mediumint(4) default NULL,
  `uitgeverij` varchar(100) default NULL,
  `tijdstip` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebauteur`
--

CREATE TABLE IF NOT EXISTS `biebauteur` (
  `id` int(11) NOT NULL auto_increment,
  `auteur` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebbeschrijving`
--

CREATE TABLE IF NOT EXISTS `biebbeschrijving` (
  `id` int(11) NOT NULL auto_increment,
  `boek_id` int(11) NOT NULL default '0',
  `schrijver_uid` varchar(4) NOT NULL default '',
  `beschrijving` text NOT NULL,
  `toegevoegd` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `boek-id` (`boek_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebbevestiging`
--

CREATE TABLE IF NOT EXISTS `biebbevestiging` (
  `id` int(11) NOT NULL auto_increment,
  `exemplaar_id` int(11) NOT NULL default '0',
  `uitgeleend_uid` varchar(4) NOT NULL default '',
  `geleend_of_teruggegeven` enum('geleend','teruggegeven') NOT NULL default 'geleend',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebboek`
--

CREATE TABLE IF NOT EXISTS `biebboek` (
  `id` int(11) NOT NULL auto_increment,
  `auteur_id` int(11) NOT NULL default '0',
  `categorie_id` int(11) NOT NULL default '0',
  `titel` varchar(200) NOT NULL default '',
  `taal` enum('Nederlands','Engels','Duits','Frans','Overig') NOT NULL default 'Nederlands',
  `isbn` varchar(15) NOT NULL default '',
  `paginas` smallint(6) default NULL,
  `uitgavejaar` mediumint(4) default NULL,
  `uitgeverij` varchar(100) default NULL,
  `code` varchar(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `auteur_id` (`auteur_id`,`categorie_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebcategorie`
--

CREATE TABLE IF NOT EXISTS `biebcategorie` (
  `id` int(11) NOT NULL default '0',
  `p_id` int(11) NOT NULL default '0',
  `categorie` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `biebexemplaar`
--

CREATE TABLE IF NOT EXISTS `biebexemplaar` (
  `id` int(11) NOT NULL auto_increment,
  `boek_id` int(11) NOT NULL default '0',
  `eigenaar_uid` varchar(4) NOT NULL default '',
  `uitgeleend_uid` varchar(4) NOT NULL default '',
  `toegevoegd` int(11) NOT NULL default '0',
  `extern` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `boek_id` (`boek_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `courant`
--

CREATE TABLE IF NOT EXISTS `courant` (
  `ID` int(11) NOT NULL auto_increment,
  `verzendMoment` datetime NOT NULL default '0000-00-00 00:00:00',
  `template` varchar(50) NOT NULL default 'csrmail.tpl',
  `verzender` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `courantbericht`
--

CREATE TABLE IF NOT EXISTS `courantbericht` (
  `ID` int(11) NOT NULL auto_increment,
  `courantID` int(11) NOT NULL default '0',
  `titel` varchar(100) NOT NULL default '',
  `cat` enum('voorwoord','bestuur','csr','overig') NOT NULL default 'bestuur',
  `bericht` text NOT NULL,
  `volgorde` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `datumTijd` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `courantcache`
--

CREATE TABLE IF NOT EXISTS `courantcache` (
  `ID` int(11) NOT NULL auto_increment,
  `titel` varchar(100) NOT NULL default '',
  `cat` enum('voorwoord','bestuur','csr','overig') NOT NULL default 'overig',
  `bericht` text NOT NULL,
  `uid` varchar(4) NOT NULL default '',
  `datumTijd` datetime NOT NULL default '0000-00-00 00:00:00',
  `volgorde` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documentbestand`
--

CREATE TABLE IF NOT EXISTS `documentbestand` (
  `id` int(11) NOT NULL auto_increment,
  `documentID` int(11) NOT NULL default '0',
  `bestandsnaam` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `document_id` (`documentID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documentencategorie`
--

CREATE TABLE IF NOT EXISTS `documentencategorie` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(50) NOT NULL default '',
  `beschrijving` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eetplan`
--

CREATE TABLE IF NOT EXISTS `eetplan` (
  `avond` smallint(6) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '0',
  `huis` smallint(6) NOT NULL default '0',
  UNIQUE KEY `avond` (`avond`,`uid`,`huis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eetplanhuis`
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
-- Table structure for table `forum_cat`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll`
--

CREATE TABLE IF NOT EXISTS `forum_poll` (
  `id` int(11) NOT NULL auto_increment,
  `topicID` int(11) NOT NULL default '0',
  `optie` varchar(100) NOT NULL default '',
  `stemmen` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topicID` (`topicID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll_stemmen`
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
-- Table structure for table `forum_post`
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
  `zichtbaar` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','verwijderd') NOT NULL default 'zichtbaar',
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`),
  FULLTEXT KEY `tekst` (`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forum_topic`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groep`
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groeplid`
--

CREATE TABLE IF NOT EXISTS `groeplid` (
  `groepid` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `op` enum('0','1') NOT NULL default '0',
  `functie` varchar(25) NOT NULL default '',
  `prioriteit` int(11) NOT NULL default '0',
  PRIMARY KEY  (`groepid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groeptype`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lid`
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
  `soccieID` int(11) NOT NULL default '0',
  `createTerm` enum('barvoor','barachter') NOT NULL default 'barvoor',
  `soccieSaldo` float NOT NULL default '0',
  `maalcieSaldo` float NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `nickname` (`nickname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledenlijst';

-- --------------------------------------------------------

--
-- Table structure for table `log`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijd`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdaanmelding`
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
-- Table structure for table `maaltijdabo`
--

CREATE TABLE IF NOT EXISTS `maaltijdabo` (
  `uid` varchar(4) NOT NULL default '',
  `abosoort` varchar(20) NOT NULL default '0',
  PRIMARY KEY  (`uid`,`abosoort`),
  KEY `abosoort` (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdabosoort`
--

CREATE TABLE IF NOT EXISTS `maaltijdabosoort` (
  `abosoort` varchar(20) NOT NULL default '',
  `tekst` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maaltijdgesloten`
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
-- Table structure for table `mededeling`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten';

-- --------------------------------------------------------

--
-- Table structure for table `mededelingcategorie`
--

CREATE TABLE IF NOT EXISTS `mededelingcategorie` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(250) NOT NULL,
  `rank` tinyint(3) NOT NULL default '0',
  `plaatje` varchar(250) NOT NULL,
  `beschrijving` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pagina`
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
  `ID` int(11) NOT NULL auto_increment,
  `savedquery` text NOT NULL,
  `beschrijving` varchar(255) NOT NULL,
  `permissie` varchar(255) NOT NULL default 'P_LOGGED_IN',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sjaarsactie`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sjaarsactielid`
--

CREATE TABLE IF NOT EXISTS `sjaarsactielid` (
  `actieID` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `moment` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`actieID`,`uid`)
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
  `dl_bytes` bigint(20) NOT NULL default '0' COMMENT 'Downloaded bytes',
  `seeders` smallint(6) NOT NULL default '0' COMMENT 'Aantal seeders',
  `leechers` smallint(6) NOT NULL default '0' COMMENT 'Aantal leechers',
  `speed` smallint(6) NOT NULL default '0' COMMENT 'unused for now',
  PRIMARY KEY  (`id`),
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
  `seeder` tinyint(1) NOT NULL default '0' COMMENT 'Is a seeder? (or a leecher)',
  `seen` date NOT NULL COMMENT 'Last seen at...',
  `client_version` varchar(250) NOT NULL COMMENT 'Client version',
  PRIMARY KEY  (`id`),
  KEY `seen` (`seen`),
  KEY `torrent_id` (`torrent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vb_source`
--

CREATE TABLE IF NOT EXISTS `vb_source` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(400) NOT NULL,
  `votesum` int(11) NOT NULL,
  `votecount` int(11) NOT NULL,
  `lid` varchar(4) NOT NULL,
  `createdate` datetime NOT NULL,
  `ip` varchar(15) NOT NULL,
  `sourceType` enum('link','file','book','discussion') NOT NULL default 'link',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
  `status` enum('approved','disapproved') NOT NULL default 'approved',
  `public` tinyint(1) NOT NULL default '1',
  UNIQUE KEY `sourcelink` (`source1`,`source2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vb_subject`
--

CREATE TABLE IF NOT EXISTS `vb_subject` (
  `id` int(11) NOT NULL auto_increment,
  `lid` varchar(4) default NULL,
  `parent` int(11) NOT NULL default '0',
  `name` varchar(200) NOT NULL,
  `description` text,
  `isLeaf` tinyint(1) NOT NULL default '1',
  `status` enum('invisible','open','closed') NOT NULL default 'open',
  `ip` varchar(15) NOT NULL,
  `createdate` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='vb subjects';

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