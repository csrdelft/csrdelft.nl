-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-1~dko1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 06, 2007 at 04:49 PM
-- Server version: 4.1.11
-- PHP Version: 4.3.10-18
-- 
-- Database: `csrdelft`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `bestuur`
-- 

CREATE TABLE `bestuur` (
  `ID` int(11) NOT NULL auto_increment,
  `jaar` year(4) NOT NULL default '0000',
  `naam` varchar(100) NOT NULL default '',
  `praeses` varchar(4) NOT NULL default '',
  `abactis` varchar(4) NOT NULL default '',
  `fiscus` varchar(4) NOT NULL default '',
  `vice_praeses` varchar(4) NOT NULL default '',
  `vice_abactis` varchar(4) NOT NULL default '',
  `verhaal` text NOT NULL,
  `bbcode_uid` varchar(10) NOT NULL default '',
  `tekst` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `bewoner`
-- 

CREATE TABLE `bewoner` (
  `uid` varchar(4) NOT NULL default '',
  `woonoordid` int(11) NOT NULL default '0',
  `op` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`uid`,`woonoordid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `biebauteur`
-- 

CREATE TABLE `biebauteur` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `biebbeschrijving`
-- 

CREATE TABLE `biebbeschrijving` (
  `id` int(11) NOT NULL auto_increment,
  `boek-id` int(11) NOT NULL default '0',
  `schrijver_uid` varchar(4) NOT NULL default '',
  `beschrijving` text NOT NULL,
  `toegevoegd` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `boek-id` (`boek-id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `biebboek`
-- 

CREATE TABLE `biebboek` (
  `id` int(11) NOT NULL auto_increment,
  `auteur_id` int(11) NOT NULL default '0',
  `categorie_id` int(11) NOT NULL default '0',
  `titel` varchar(200) NOT NULL default '',
  `taal` enum('Nederlands','Engels','Duits','Frans','Overig') NOT NULL default 'Nederlands',
  `isbn` varchar(15) NOT NULL default '',
  `uitgavejaar` year(4) default NULL,
  PRIMARY KEY  (`id`),
  KEY `auteur_id` (`auteur_id`,`categorie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `biebcategorie`
-- 

CREATE TABLE `biebcategorie` (
  `id` int(11) NOT NULL default '0',
  `p_id` int(11) NOT NULL default '0',
  `categorie` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `biebexemplaar`
-- 

CREATE TABLE `biebexemplaar` (
  `id` int(11) NOT NULL auto_increment,
  `boek_id` int(11) NOT NULL default '0',
  `eigenaar_uid` varchar(4) NOT NULL default '',
  `uitgeleend_uid` varchar(4) NOT NULL default '',
  `toegevoegd` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `boek_id` (`boek_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `commissie`
-- 

CREATE TABLE `commissie` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(20) NOT NULL default '',
  `stekst` text NOT NULL,
  `titel` varchar(50) NOT NULL default '',
  `tekst` text NOT NULL,
  `bbcode_uid` varchar(10) NOT NULL default '0000000000',
  `link` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Commissies' AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `commissielid`
-- 

CREATE TABLE `commissielid` (
  `cieid` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `op` enum('0','1') NOT NULL default '0',
  `functie` varchar(25) NOT NULL default '',
  `prioriteit` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cieid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ComissieLeden';

-- --------------------------------------------------------

-- 
-- Table structure for table `document`
-- 

CREATE TABLE `document` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  `categorie` int(11) NOT NULL default '0',
  `datum` date NOT NULL default '1000-01-01',
  PRIMARY KEY  (`id`),
  KEY `categorie` (`categorie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `documentbestand`
-- 

CREATE TABLE `documentbestand` (
  `id` int(11) NOT NULL auto_increment,
  `documentID` int(11) NOT NULL default '0',
  `bestandsnaam` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `document_id` (`documentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=79 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `documentencategorie`
-- 

CREATE TABLE `documentencategorie` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(50) NOT NULL default '',
  `beschrijving` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `eetplan`
-- 

CREATE TABLE `eetplan` (
  `avond` smallint(6) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '0',
  `huis` smallint(6) NOT NULL default '0',
  UNIQUE KEY `avond` (`avond`,`uid`,`huis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `eetplanhuis`
-- 

CREATE TABLE `eetplanhuis` (
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

CREATE TABLE `forum_cat` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `forum_poll`
-- 

CREATE TABLE `forum_poll` (
  `id` int(11) NOT NULL auto_increment,
  `topicID` int(11) NOT NULL default '0',
  `optie` varchar(100) NOT NULL default '',
  `stemmen` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topicID` (`topicID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=138 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `forum_poll_stemmen`
-- 

CREATE TABLE `forum_poll_stemmen` (
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

CREATE TABLE `forum_post` (
  `id` int(11) NOT NULL auto_increment,
  `tid` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `tekst` text NOT NULL,
  `bbcode_uid` varchar(10) NOT NULL default '',
  `datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `bewerkDatum` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` varchar(15) NOT NULL default '',
  `zichtbaar` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','verwijderd') NOT NULL default 'zichtbaar',
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`),
  FULLTEXT KEY `tekst` (`tekst`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=4817 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `forum_topic`
-- 

CREATE TABLE `forum_topic` (
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
  `soort` enum('T_NORMAAL','T_POLL','T_LEZING') NOT NULL default 'T_NORMAAL',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `titel` (`titel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=514 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `inschrijving`
-- 

CREATE TABLE `inschrijving` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  `beschrijving` text NOT NULL,
  `verantwoordelijke` varchar(4) NOT NULL default '',
  `moment` date NOT NULL default '0000-00-00',
  `limiet` int(11) NOT NULL default '30',
  `zichtbaar` enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `naam` (`naam`,`moment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `inschrijvinglid`
-- 

CREATE TABLE `inschrijvinglid` (
  `inschrijvingid` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `partner` text NOT NULL,
  `eetwens_partner` text NOT NULL,
  PRIMARY KEY  (`inschrijvingid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ingericht op gala, kan wellicht beter';

-- --------------------------------------------------------

-- 
-- Table structure for table `lid`
-- 

CREATE TABLE `lid` (
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
  `forum_name` enum('nick','civitas') NOT NULL default 'civitas',
  `forum_postsortering` enum('ASC','DESC') NOT NULL default 'ASC',
  `kgb` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `nickname` (`nickname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledenlijst';

-- --------------------------------------------------------

-- 
-- Table structure for table `log`
-- 

CREATE TABLE `log` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=585717 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `maaltijd`
-- 

CREATE TABLE `maaltijd` (
  `id` int(11) NOT NULL auto_increment,
  `datum` int(11) NOT NULL default '0',
  `gesloten` enum('0','1') NOT NULL default '0',
  `tekst` text NOT NULL,
  `abosoort` varchar(20) NOT NULL default '',
  `max` smallint(6) NOT NULL default '0',
  `aantal` smallint(6) NOT NULL default '0',
  `tp` varchar(4) NOT NULL default '',
  `kok1` varchar(4) NOT NULL default '',
  `kok2` varchar(4) NOT NULL default '',
  `afw1` varchar(4) NOT NULL default '',
  `afw2` varchar(4) NOT NULL default '',
  `afw3` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=149 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `maaltijdaanmelding`
-- 

CREATE TABLE `maaltijdaanmelding` (
  `uid` varchar(4) NOT NULL default '',
  `maalid` int(11) NOT NULL default '0',
  `status` enum('AAN','AF') NOT NULL default 'AAN',
  `door` varchar(4) NOT NULL default '',
  `gasten` int(11) NOT NULL default '0',
  `gasten_opmerking` varchar(255) NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (`uid`,`maalid`),
  KEY `maalid` (`maalid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `maaltijdabo`
-- 

CREATE TABLE `maaltijdabo` (
  `uid` varchar(4) NOT NULL default '',
  `abosoort` varchar(20) NOT NULL default '0',
  PRIMARY KEY  (`uid`,`abosoort`),
  KEY `abosoort` (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `maaltijdabosoort`
-- 

CREATE TABLE `maaltijdabosoort` (
  `abosoort` varchar(20) NOT NULL default '',
  `tekst` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`abosoort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `maaltijdgesloten`
-- 

CREATE TABLE `maaltijdgesloten` (
  `uid` varchar(4) NOT NULL default '',
  `naam` text NOT NULL,
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
-- Table structure for table `menu`
-- 

CREATE TABLE `menu` (
  `ID` int(11) NOT NULL auto_increment,
  `pID` int(11) NOT NULL default '0',
  `prioriteit` int(11) NOT NULL default '0',
  `tekst` varchar(50) NOT NULL default '',
  `link` varchar(100) NOT NULL default '',
  `permission` varchar(50) NOT NULL default 'P_NOBODY',
  `zichtbaar` enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (`ID`),
  KEY `pID` (`pID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `nieuws`
-- 

CREATE TABLE `nieuws` (
  `id` int(11) NOT NULL auto_increment,
  `datum` int(11) NOT NULL default '0',
  `titel` text NOT NULL,
  `tekst` text NOT NULL,
  `bbcode_uid` varchar(10) NOT NULL default '',
  `uid` varchar(4) NOT NULL default '',
  `prive` enum('0','1') NOT NULL default '0',
  `verborgen` enum('0','1') NOT NULL default '0',
  `verwijderd` enum('0','1') NOT NULL default '0',
  `plaatje` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `intern` (`prive`),
  FULLTEXT KEY `kopje` (`titel`,`tekst`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten' AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pubciemail`
-- 

CREATE TABLE `pubciemail` (
  `ID` int(11) NOT NULL auto_increment,
  `verzendMoment` datetime NOT NULL default '0000-00-00 00:00:00',
  `template` varchar(50) NOT NULL default 'csrmail.tpl',
  `verzender` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pubciemailbericht`
-- 

CREATE TABLE `pubciemailbericht` (
  `ID` int(11) NOT NULL auto_increment,
  `pubciemailID` int(11) NOT NULL default '0',
  `titel` varchar(100) NOT NULL default '',
  `cat` enum('bestuur','csr','overig','voorwoord') NOT NULL default 'bestuur',
  `bericht` text NOT NULL,
  `volgorde` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pubciemailcache`
-- 

CREATE TABLE `pubciemailcache` (
  `ID` int(11) NOT NULL auto_increment,
  `titel` varchar(100) NOT NULL default '',
  `cat` enum('voorwoord','bestuur','csr','overig') NOT NULL default 'overig',
  `bericht` text NOT NULL,
  `uid` varchar(4) NOT NULL default '',
  `datumTijd` datetime NOT NULL default '0000-00-00 00:00:00',
  `volgorde` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=597 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sjaarsactie`
-- 

CREATE TABLE `sjaarsactie` (
  `ID` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  `beschrijving` text NOT NULL,
  `verantwoordelijke` varchar(4) NOT NULL default '',
  `moment` datetime NOT NULL default '0000-00-00 00:00:00',
  `limiet` int(11) NOT NULL default '15',
  `zichtbaar` enum('ja','nee') NOT NULL default 'ja',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sjaarsactielid`
-- 

CREATE TABLE `sjaarsactielid` (
  `actieID` int(11) NOT NULL default '0',
  `uid` varchar(4) NOT NULL default '',
  `moment` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`actieID`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `socciesaldi`
-- 

CREATE TABLE `socciesaldi` (
  `uid` varchar(4) NOT NULL default '',
  `soccieID` int(11) NOT NULL default '0',
  `saldo` float NOT NULL default '0',
  `createTerm` enum('barvoor','barachter') NOT NULL default 'barvoor',
  `maalSaldo` float NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `soccieID` (`soccieID`,`createTerm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `woonoord`
-- 

CREATE TABLE `woonoord` (
  `id` int(11) NOT NULL auto_increment,
  `naam` varchar(100) NOT NULL default '',
  `sort` varchar(4) NOT NULL default '',
  `tekst` text NOT NULL,
  `adres` varchar(100) NOT NULL default '',
  `status` enum('W_HUIS','W_KOT','W_OVERIG') NOT NULL default 'W_HUIS',
  `plaatje` varchar(150) NOT NULL default '',
  `link` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;
