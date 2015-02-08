-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 08 feb 2015 om 23:14
-- Serverversie: 5.5.41
-- PHP-Versie: 5.4.36-0+deb7u3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `csrdelft`
--

DELIMITER $$
--
-- Functies
--
CREATE DEFINER=`csrdelft`@`localhost` FUNCTION `SPLIT_STR`(
x VARCHAR(255),
delim VARCHAR(12),
pos INT
) RETURNS varchar(255) CHARSET utf8
RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
delim, '')$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `uid` varchar(4) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `pass_since` datetime NOT NULL,
  `last_login_success` datetime DEFAULT NULL,
  `last_login_attempt` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL,
  `blocked_reason` text,
  `perm_role` enum('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE') NOT NULL,
  `private_token` varchar(255) DEFAULT NULL,
  `private_token_since` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `acl`
--

CREATE TABLE IF NOT EXISTS `acl` (
  `environment` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `resource` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY (`environment`,`action`,`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `activiteiten`
--

CREATE TABLE IF NOT EXISTS `activiteiten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `soort` enum('vereniging','lustrum','dies','owee','sjaarsactie','lichting','verticale','kring','huis','ondervereniging','ifes','extern') NOT NULL,
  `rechten_aanmelden` varchar(255) DEFAULT NULL,
  `locatie` varchar(255) DEFAULT NULL,
  `in_agenda` tinyint(1) NOT NULL,
  `aanmeld_limiet` int(11) DEFAULT NULL,
  `aanmelden_vanaf` datetime NOT NULL,
  `aanmelden_tot` datetime NOT NULL,
  `bewerken_tot` datetime NOT NULL,
  `afmelden_tot` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `familie` (`familie`),
  KEY `status` (`status`),
  KEY `soort` (`soort`),
  KEY `in_agenda` (`in_agenda`),
  KEY `begin_moment` (`begin_moment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=458 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `activiteit_deelnemers`
--

CREATE TABLE IF NOT EXISTS `activiteit_deelnemers` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `agenda`
--

CREATE TABLE IF NOT EXISTS `agenda` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) NOT NULL,
  `beschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime NOT NULL,
  `rechten_bekijken` varchar(255) NOT NULL,
  `locatie` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `begin_moment` (`begin_moment`),
  KEY `eind_moment` (`eind_moment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1782 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `agenda_verbergen`
--

CREATE TABLE IF NOT EXISTS `agenda_verbergen` (
  `uid` varchar(4) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`,`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `besturen`
--

CREATE TABLE IF NOT EXISTS `besturen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `bijbeltekst` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `familie` (`familie`),
  KEY `status` (`status`),
  KEY `begin_moment` (`begin_moment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bestuurs_leden`
--

CREATE TABLE IF NOT EXISTS `bestuurs_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bewoners`
--

CREATE TABLE IF NOT EXISTS `bewoners` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebauteur`
--

CREATE TABLE IF NOT EXISTS `biebauteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1065 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebbeschrijving`
--

CREATE TABLE IF NOT EXISTS `biebbeschrijving` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boek_id` int(11) NOT NULL DEFAULT '0',
  `schrijver_uid` varchar(4) NOT NULL DEFAULT '',
  `beschrijving` text NOT NULL,
  `toegevoegd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bewerkdatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `boek_id` (`boek_id`),
  KEY `schrijver_uid` (`schrijver_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=65 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebboek`
--

CREATE TABLE IF NOT EXISTS `biebboek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur` varchar(100) NOT NULL DEFAULT '',
  `auteur_id` int(11) NOT NULL DEFAULT '0',
  `categorie_id` int(11) NOT NULL DEFAULT '0',
  `titel` varchar(200) NOT NULL DEFAULT '',
  `taal` varchar(25) NOT NULL DEFAULT 'Nederlands',
  `isbn` varchar(15) NOT NULL DEFAULT '',
  `paginas` smallint(6) NOT NULL DEFAULT '0',
  `uitgavejaar` mediumint(4) NOT NULL DEFAULT '0',
  `uitgeverij` varchar(100) NOT NULL DEFAULT '',
  `code` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`),
  KEY `categorie_id` (`categorie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1723 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebcategorie`
--

CREATE TABLE IF NOT EXISTS `biebcategorie` (
  `id` int(11) NOT NULL DEFAULT '0',
  `p_id` int(11) NOT NULL DEFAULT '0',
  `categorie` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `p_id` (`p_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebexemplaar`
--

CREATE TABLE IF NOT EXISTS `biebexemplaar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boek_id` int(11) NOT NULL DEFAULT '0',
  `eigenaar_uid` varchar(4) NOT NULL DEFAULT '',
  `opmerking` varchar(255) NOT NULL,
  `uitgeleend_uid` varchar(4) DEFAULT '',
  `toegevoegd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('beschikbaar','uitgeleend','teruggegeven','vermist') NOT NULL DEFAULT 'beschikbaar',
  `uitleendatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `leningen` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `boek_id` (`boek_id`),
  KEY `eigenaar_uid` (`eigenaar_uid`),
  KEY `uitgeleend_uid` (`uitgeleend_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1979 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bijbelrooster`
--

CREATE TABLE IF NOT EXISTS `bijbelrooster` (
  `dag` datetime NOT NULL,
  `stukje` varchar(255) NOT NULL,
  PRIMARY KEY (`dag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bijbelrooster_old`
--

CREATE TABLE IF NOT EXISTS `bijbelrooster_old` (
  `dag` date NOT NULL,
  `stukje` varchar(70) NOT NULL,
  PRIMARY KEY (`dag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `changelog`
--

CREATE TABLE IF NOT EXISTS `changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moment` datetime NOT NULL,
  `subject` varchar(255) NOT NULL,
  `property` varchar(255) NOT NULL,
  `old_value` text,
  `new_value` text,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=556 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cms_paginas`
--

CREATE TABLE IF NOT EXISTS `cms_paginas` (
  `naam` varchar(255) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `inhoud` longtext NOT NULL,
  `laatst_gewijzigd` datetime NOT NULL,
  `rechten_bekijken` varchar(255) NOT NULL,
  `rechten_bewerken` varchar(255) NOT NULL,
  `inline_html` tinyint(1) NOT NULL,
  PRIMARY KEY (`naam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissies`
--

CREATE TABLE IF NOT EXISTS `commissies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `soort` enum('c','s','b','e') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `familie` (`familie`),
  KEY `status` (`status`),
  KEY `soort` (`soort`),
  KEY `begin_moment` (`begin_moment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=383 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_leden`
--

CREATE TABLE IF NOT EXISTS `commissie_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `lid_sinds` (`lid_sinds`),
  KEY `door_uid` (`door_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `courant`
--

CREATE TABLE IF NOT EXISTS `courant` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `verzendMoment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `template` varchar(50) NOT NULL DEFAULT 'csrmail.tpl',
  `verzender` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `courant_ibfk_1` (`verzender`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=395 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `courantbericht`
--

CREATE TABLE IF NOT EXISTS `courantbericht` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `courantID` int(11) NOT NULL DEFAULT '0',
  `titel` varchar(100) NOT NULL DEFAULT '',
  `cat` enum('voorwoord','bestuur','csr','overig','sponsor') NOT NULL DEFAULT 'bestuur',
  `bericht` text NOT NULL,
  `volgorde` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) DEFAULT NULL,
  `datumTijd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `courantID` (`courantID`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3732 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `courantcache`
--

CREATE TABLE IF NOT EXISTS `courantcache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(100) NOT NULL DEFAULT '',
  `cat` enum('voorwoord','bestuur','csr','overig','sponsor') NOT NULL DEFAULT 'overig',
  `bericht` text NOT NULL,
  `uid` varchar(4) NOT NULL DEFAULT '',
  `datumTijd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `volgorde` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_functies`
--

CREATE TABLE IF NOT EXISTS `crv_functies` (
  `functie_id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `afkorting` varchar(255) NOT NULL,
  `email_bericht` text NOT NULL,
  `standaard_punten` int(11) NOT NULL,
  `kwalificatie_benodigd` tinyint(1) NOT NULL,
  `maaltijden_sluiten` tinyint(1) NOT NULL,
  PRIMARY KEY (`functie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_kwalificaties`
--

CREATE TABLE IF NOT EXISTS `crv_kwalificaties` (
  `uid` varchar(4) NOT NULL,
  `functie_id` int(11) NOT NULL,
  `wanneer_toegewezen` datetime NOT NULL,
  PRIMARY KEY (`uid`,`functie_id`),
  KEY `functie_id` (`functie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_repetities`
--

CREATE TABLE IF NOT EXISTS `crv_repetities` (
  `crv_repetitie_id` int(11) NOT NULL AUTO_INCREMENT,
  `mlt_repetitie_id` int(11) DEFAULT NULL,
  `dag_vd_week` int(1) NOT NULL,
  `periode_in_dagen` int(11) NOT NULL,
  `functie_id` int(11) NOT NULL,
  `standaard_punten` int(11) NOT NULL,
  `standaard_aantal` int(11) NOT NULL DEFAULT '1',
  `voorkeurbaar` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`crv_repetitie_id`),
  KEY `mlt_repetitie_id` (`mlt_repetitie_id`),
  KEY `functie_id` (`functie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_taken`
--

CREATE TABLE IF NOT EXISTS `crv_taken` (
  `taak_id` int(11) NOT NULL AUTO_INCREMENT,
  `functie_id` int(11) NOT NULL,
  `uid` varchar(4) DEFAULT NULL,
  `crv_repetitie_id` int(11) DEFAULT NULL,
  `maaltijd_id` int(11) DEFAULT NULL,
  `datum` date NOT NULL,
  `punten` int(11) NOT NULL,
  `bonus_malus` int(11) NOT NULL,
  `punten_toegekend` int(11) NOT NULL,
  `bonus_toegekend` int(11) NOT NULL,
  `wanneer_toegekend` datetime DEFAULT NULL,
  `wanneer_gemaild` text NOT NULL,
  `verwijderd` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`taak_id`),
  KEY `functie_id` (`functie_id`),
  KEY `crv_repetitie_id` (`crv_repetitie_id`),
  KEY `maaltijd_id` (`maaltijd_id`),
  KEY `crv_taken_ibfk_4` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=989 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_voorkeuren`
--

CREATE TABLE IF NOT EXISTS `crv_voorkeuren` (
  `uid` varchar(4) NOT NULL,
  `crv_repetitie_id` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`crv_repetitie_id`),
  KEY `crv_voorkeuren_ibfk_1` (`crv_repetitie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_vrijstellingen`
--

CREATE TABLE IF NOT EXISTS `crv_vrijstellingen` (
  `uid` varchar(4) NOT NULL,
  `begin_datum` date NOT NULL,
  `eind_datum` date NOT NULL,
  `percentage` int(3) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `debug_log`
--

CREATE TABLE IF NOT EXISTS `debug_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_function` varchar(255) NOT NULL,
  `dump` longtext,
  `call_trace` text NOT NULL,
  `moment` datetime NOT NULL,
  `uid` varchar(4) DEFAULT NULL COMMENT 'geen foreign key afdwingen',
  `su_uid` varchar(4) DEFAULT NULL COMMENT 'geen foreign key afdwingen',
  `ip` varchar(255) NOT NULL,
  `request` varchar(255) NOT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `dies_gala_2014`
--

CREATE TABLE IF NOT EXISTS `dies_gala_2014` (
  `uid` varchar(4) NOT NULL,
  `naamDate` varchar(60) NOT NULL,
  `eetZelf` int(11) NOT NULL,
  `eetDate` int(11) NOT NULL,
  `allerZelf` varchar(100) NOT NULL,
  `allerDate` varchar(100) NOT NULL,
  `date18` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document`
--

CREATE TABLE IF NOT EXISTS `document` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `catID` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `mimetype` varchar(255) NOT NULL,
  `toegevoegd` datetime NOT NULL,
  `eigenaar` varchar(4) NOT NULL,
  `leesrechten` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `catID` (`catID`),
  KEY `toegevoegd` (`toegevoegd`),
  KEY `eigenaar` (`eigenaar`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `documentcategorie`
--

CREATE TABLE IF NOT EXISTS `documentcategorie` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `zichtbaar` tinyint(1) NOT NULL,
  `leesrechten` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `eetplan`
--

CREATE TABLE IF NOT EXISTS `eetplan` (
  `avond` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(4) NOT NULL DEFAULT '0',
  `huis` int(11) NOT NULL DEFAULT '0',
  KEY `avond` (`avond`),
  KEY `uid` (`uid`),
  KEY `huis` (`huis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `eetplanhuis`
--

CREATE TABLE IF NOT EXISTS `eetplanhuis` (
  `id` int(11) NOT NULL,
  `naam` varchar(255) NOT NULL,
  `groepid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groepid` (`groepid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `execution_times`
--

CREATE TABLE IF NOT EXISTS `execution_times` (
  `request` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `total_time` float NOT NULL,
  `total_time_view` float NOT NULL,
  PRIMARY KEY (`request`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_categorien`
--

CREATE TABLE IF NOT EXISTS `forum_categorien` (
  `categorie_id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) NOT NULL,
  `rechten_lezen` varchar(255) NOT NULL,
  `volgorde` int(11) NOT NULL,
  PRIMARY KEY (`categorie_id`),
  KEY `volgorde` (`volgorde`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_delen`
--

CREATE TABLE IF NOT EXISTS `forum_delen` (
  `forum_id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie_id` int(11) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `omschrijving` text NOT NULL,
  `rechten_lezen` varchar(255) NOT NULL,
  `rechten_posten` varchar(255) NOT NULL,
  `rechten_modereren` varchar(255) NOT NULL,
  `volgorde` int(11) NOT NULL,
  PRIMARY KEY (`forum_id`),
  KEY `categorie_id` (`categorie_id`),
  KEY `volgorde` (`volgorde`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden`
--

CREATE TABLE IF NOT EXISTS `forum_draden` (
  `draad_id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `gedeeld_met` int(11) DEFAULT NULL,
  `uid` varchar(4) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `datum_tijd` datetime NOT NULL,
  `laatst_gewijzigd` datetime DEFAULT NULL,
  `laatste_post_id` int(11) DEFAULT NULL,
  `laatste_wijziging_uid` varchar(4) DEFAULT NULL,
  `belangrijk` varchar(255) DEFAULT NULL,
  `gesloten` tinyint(1) NOT NULL,
  `verwijderd` tinyint(1) NOT NULL,
  `wacht_goedkeuring` tinyint(1) NOT NULL,
  `plakkerig` tinyint(1) NOT NULL,
  `eerste_post_plakkerig` tinyint(1) NOT NULL,
  `pagina_per_post` tinyint(1) NOT NULL,
  PRIMARY KEY (`draad_id`),
  KEY `forum_id` (`forum_id`),
  KEY `wacht_goedkeuring` (`wacht_goedkeuring`),
  KEY `plakkerig` (`plakkerig`),
  KEY `laatst_gewijzigd` (`laatst_gewijzigd`),
  KEY `verwijderd` (`verwijderd`),
  KEY `belangrijk` (`belangrijk`),
  FULLTEXT KEY `titel` (`titel`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8480 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden_gelezen`
--

CREATE TABLE IF NOT EXISTS `forum_draden_gelezen` (
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `datum_tijd` datetime NOT NULL,
  PRIMARY KEY (`draad_id`,`uid`),
  KEY `draad_id` (`draad_id`),
  KEY `lid_id` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden_reageren`
--

CREATE TABLE IF NOT EXISTS `forum_draden_reageren` (
  `forum_id` int(11) NOT NULL,
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `datum_tijd` datetime NOT NULL,
  `concept` text,
  `titel` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`forum_id`,`draad_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden_verbergen`
--

CREATE TABLE IF NOT EXISTS `forum_draden_verbergen` (
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`draad_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden_volgen`
--

CREATE TABLE IF NOT EXISTS `forum_draden_volgen` (
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`draad_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_posts`
--

CREATE TABLE IF NOT EXISTS `forum_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `tekst` text NOT NULL,
  `datum_tijd` datetime NOT NULL,
  `laatst_gewijzigd` datetime NOT NULL,
  `bewerkt_tekst` text,
  `verwijderd` tinyint(1) NOT NULL,
  `auteur_ip` varchar(255) NOT NULL,
  `wacht_goedkeuring` tinyint(1) NOT NULL,
  PRIMARY KEY (`post_id`),
  KEY `draad_id` (`draad_id`),
  KEY `lid_id` (`uid`),
  KEY `wacht_goedkeuring` (`wacht_goedkeuring`),
  KEY `verwijderd` (`verwijderd`),
  KEY `datum_tijd` (`datum_tijd`),
  FULLTEXT KEY `tekst` (`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=97586 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fotoalbums`
--

CREATE TABLE IF NOT EXISTS `fotoalbums` (
  `subdir` varchar(255) NOT NULL,
  `owner` varchar(4) NOT NULL,
  PRIMARY KEY (`subdir`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fotos`
--

CREATE TABLE IF NOT EXISTS `fotos` (
  `subdir` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `rotation` int(11) NOT NULL,
  `owner` varchar(4) NOT NULL,
  PRIMARY KEY (`subdir`,`filename`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groep`
--

CREATE TABLE IF NOT EXISTS `groep` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snaam` varchar(255) NOT NULL,
  `naam` varchar(255) NOT NULL,
  `gtype` int(11) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `toonFuncties` enum('tonen','verbergen','niet','tonenzonderinvoer') NOT NULL DEFAULT 'tonen',
  `toonPasfotos` tinyint(1) NOT NULL DEFAULT '0',
  `lidIsMod` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is elk lid mod',
  `omnummering` int(11) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `snaam` (`snaam`),
  KEY `gtype` (`gtype`),
  KEY `status` (`status`),
  KEY `omnummering` (`omnummering`),
  KEY `model` (`model`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2390 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groepen`
--

CREATE TABLE IF NOT EXISTS `groepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `familie` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `status` (`status`),
  KEY `familie` (`familie`),
  KEY `begin_moment` (`begin_moment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=116 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groeptype`
--

CREATE TABLE IF NOT EXISTS `groeptype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `zichtbaar` tinyint(1) NOT NULL DEFAULT '1',
  `toonHistorie` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ot-groepen laten zien in overzicht.',
  `toonProfiel` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Groep in profiel tonen?',
  `syncWithLDAP` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Synchroniseer groepen in deze groeptype met LDAP-directory',
  `groepenAanmaakbaar` varchar(255) NOT NULL DEFAULT 'P_LEDEN_MOD' COMMENT 'permissie(s) voor aanmaken van groepen',
  PRIMARY KEY (`id`),
  KEY `naam` (`naam`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groep_leden`
--

CREATE TABLE IF NOT EXISTS `groep_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `instellingen`
--

CREATE TABLE IF NOT EXISTS `instellingen` (
  `module` varchar(255) NOT NULL,
  `instelling_id` varchar(255) NOT NULL,
  `waarde` text NOT NULL,
  PRIMARY KEY (`module`,`instelling_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ketzers`
--

CREATE TABLE IF NOT EXISTS `ketzers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `aanmeld_limiet` int(11) DEFAULT NULL,
  `aanmelden_vanaf` datetime NOT NULL,
  `aanmelden_tot` datetime NOT NULL,
  `bewerken_tot` datetime NOT NULL,
  `afmelden_tot` datetime DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1195 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ketzer_deelnemers`
--

CREATE TABLE IF NOT EXISTS `ketzer_deelnemers` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kringen`
--

CREATE TABLE IF NOT EXISTS `kringen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `familie` varchar(255) NOT NULL,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `verticale` char(1) NOT NULL,
  `kring_nummer` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `verticale` (`verticale`),
  KEY `kring_nummer` (`kring_nummer`),
  KEY `familie` (`familie`),
  KEY `begin_moment` (`begin_moment`),
  KEY `maker_uid` (`maker_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kring_leden`
--

CREATE TABLE IF NOT EXISTS `kring_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `lid_sinds` (`lid_sinds`),
  KEY `door_uid` (`door_uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `lichtingen`
--

CREATE TABLE IF NOT EXISTS `lichtingen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `familie` varchar(255) NOT NULL,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `lidjaar` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lidjaar` (`lidjaar`),
  KEY `familie` (`familie`),
  KEY `begin_moment` (`begin_moment`),
  KEY `status` (`status`),
  KEY `maker_uid` (`maker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `lichting_leden`
--

CREATE TABLE IF NOT EXISTS `lichting_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `lid_sinds` (`lid_sinds`),
  KEY `door_uid` (`door_uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `lidinstellingen`
--

CREATE TABLE IF NOT EXISTS `lidinstellingen` (
  `uid` varchar(4) NOT NULL,
  `module` varchar(255) NOT NULL,
  `instelling_id` varchar(255) NOT NULL,
  `waarde` text NOT NULL,
  PRIMARY KEY (`uid`,`module`,`instelling_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(4) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `locatie` varchar(255) NOT NULL DEFAULT '',
  `moment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `url` varchar(255) NOT NULL DEFAULT '',
  `referer` varchar(255) NOT NULL DEFAULT '',
  `useragent` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1831594 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `logAggregated`
--

CREATE TABLE IF NOT EXISTS `logAggregated` (
  `soort` enum('maand','jaar','ip','url') NOT NULL DEFAULT 'maand',
  `waarde` varchar(255) NOT NULL,
  `pageviews` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `login_remember`
--

CREATE TABLE IF NOT EXISTS `login_remember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `remember_since` datetime NOT NULL,
  `device_name` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `lock_ip` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=267 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `login_sessions`
--

CREATE TABLE IF NOT EXISTS `login_sessions` (
  `session_hash` varchar(255) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `login_moment` datetime NOT NULL,
  `expire` datetime NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `lock_ip` tinyint(1) NOT NULL,
  PRIMARY KEY (`session_hash`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mededeling`
--

CREATE TABLE IF NOT EXISTS `mededeling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum_old` int(11) NOT NULL DEFAULT '0',
  `datum` datetime DEFAULT NULL,
  `vervaltijd` datetime DEFAULT NULL COMMENT 'Wanneer vervalt hij?',
  `titel` text NOT NULL,
  `tekst` text NOT NULL,
  `categorie` int(11) NOT NULL DEFAULT '0',
  `prive` enum('1','0') NOT NULL DEFAULT '1',
  `zichtbaarheid` enum('wacht_goedkeuring','zichtbaar','onzichtbaar','verwijderd') NOT NULL DEFAULT 'zichtbaar' COMMENT 'Is hij zichtbaar?',
  `prioriteit` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT 'Hoe belangrijk is deze mededeling?',
  `uid` varchar(4) NOT NULL DEFAULT '',
  `doelgroep` enum('iedereen','(oud)leden','leden') NOT NULL DEFAULT 'iedereen',
  `verborgen` enum('0','1') NOT NULL DEFAULT '0',
  `verwijderd` enum('0','1') NOT NULL DEFAULT '0',
  `plaatje` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `zichtbaarheid` (`zichtbaarheid`),
  KEY `datum` (`datum`),
  KEY `vervaltijd` (`vervaltijd`),
  KEY `prioriteit` (`prioriteit`),
  FULLTEXT KEY `titel` (`titel`),
  FULLTEXT KEY `tekst` (`tekst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten' AUTO_INCREMENT=730 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mededelingcategorie`
--

CREATE TABLE IF NOT EXISTS `mededelingcategorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `prioriteit` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT 'De volgorde van de categorieën in de dropdown',
  `permissie` enum('P_NEWS_POST','P_NEWS_MOD') NOT NULL DEFAULT 'P_NEWS_POST' COMMENT 'Mag lid berichten toevoegen aan deze categorie?',
  `plaatje` varchar(255) NOT NULL,
  `beschrijving` text,
  PRIMARY KEY (`id`),
  KEY `prioriteit` (`prioriteit`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `menus`
--

CREATE TABLE IF NOT EXISTS `menus` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `volgorde` int(11) NOT NULL,
  `tekst` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `rechten_bekijken` varchar(255) DEFAULT NULL,
  `zichtbaar` tinyint(1) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `pID` (`parent_id`),
  KEY `prioriteit` (`volgorde`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=799 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_aanmeldingen`
--

CREATE TABLE IF NOT EXISTS `mlt_aanmeldingen` (
  `maaltijd_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `aantal_gasten` int(11) NOT NULL DEFAULT '0',
  `gasten_eetwens` varchar(255) NOT NULL DEFAULT '',
  `door_abonnement` int(11) DEFAULT NULL,
  `door_uid` varchar(4) DEFAULT NULL,
  `laatst_gewijzigd` datetime NOT NULL,
  PRIMARY KEY (`maaltijd_id`,`uid`),
  KEY `door_lid` (`door_uid`),
  KEY `door_abonnement` (`door_abonnement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_abonnementen`
--

CREATE TABLE IF NOT EXISTS `mlt_abonnementen` (
  `mlt_repetitie_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `wanneer_ingeschakeld` datetime NOT NULL,
  PRIMARY KEY (`mlt_repetitie_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_archief`
--

CREATE TABLE IF NOT EXISTS `mlt_archief` (
  `maaltijd_id` int(11) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `prijs` int(11) NOT NULL DEFAULT '0',
  `aanmeldingen` text NOT NULL,
  PRIMARY KEY (`maaltijd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_maaltijden`
--

CREATE TABLE IF NOT EXISTS `mlt_maaltijden` (
  `maaltijd_id` int(11) NOT NULL AUTO_INCREMENT,
  `mlt_repetitie_id` int(11) DEFAULT NULL,
  `titel` varchar(255) NOT NULL,
  `aanmeld_limiet` int(11) NOT NULL DEFAULT '0',
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `prijs` int(11) NOT NULL DEFAULT '0',
  `gesloten` tinyint(1) NOT NULL DEFAULT '0',
  `laatst_gesloten` datetime DEFAULT NULL,
  `verwijderd` tinyint(1) NOT NULL DEFAULT '0',
  `aanmeld_filter` varchar(255) DEFAULT NULL,
  `omschrijving` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`maaltijd_id`),
  KEY `mlt_repetitie_id` (`mlt_repetitie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1491 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_repetities`
--

CREATE TABLE IF NOT EXISTS `mlt_repetities` (
  `mlt_repetitie_id` int(11) NOT NULL AUTO_INCREMENT,
  `dag_vd_week` int(1) NOT NULL,
  `periode_in_dagen` int(11) NOT NULL,
  `standaard_titel` varchar(255) NOT NULL,
  `standaard_tijd` time NOT NULL,
  `standaard_prijs` int(11) NOT NULL DEFAULT '0',
  `abonneerbaar` tinyint(1) NOT NULL DEFAULT '0',
  `standaard_limiet` int(11) NOT NULL,
  `abonnement_filter` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`mlt_repetitie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `onderverenigingen`
--

CREATE TABLE IF NOT EXISTS `onderverenigingen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `soort` enum('a','o','v') NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `soort` (`soort`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ondervereniging_leden`
--

CREATE TABLE IF NOT EXISTS `ondervereniging_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `onetime_tokens`
--

CREATE TABLE IF NOT EXISTS `onetime_tokens` (
  `uid` varchar(4) NOT NULL,
  `url` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expire` datetime NOT NULL,
  `verified` tinyint(1) NOT NULL,
  PRIMARY KEY (`uid`,`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `peiling`
--

CREATE TABLE IF NOT EXISTS `peiling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) DEFAULT NULL,
  `tekst` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=105 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `peilingoptie`
--

CREATE TABLE IF NOT EXISTS `peilingoptie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peilingid` int(11) NOT NULL,
  `optie` varchar(255) DEFAULT NULL,
  `stemmen` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `peilingid` (`peilingid`),
  KEY `optie` (`optie`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=492 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `peiling_stemmen`
--

CREATE TABLE IF NOT EXISTS `peiling_stemmen` (
  `peilingid` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`peilingid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `profielen`
--

CREATE TABLE IF NOT EXISTS `profielen` (
  `uid` varchar(4) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `duckname` varchar(255) NOT NULL,
  `voornaam` varchar(255) NOT NULL,
  `tussenvoegsel` varchar(255) NOT NULL,
  `achternaam` varchar(255) NOT NULL,
  `voorletters` varchar(255) NOT NULL,
  `postfix` varchar(255) NOT NULL,
  `adres` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `woonplaats` varchar(255) NOT NULL,
  `land` varchar(255) NOT NULL,
  `telefoon` varchar(255) NOT NULL,
  `mobiel` varchar(255) NOT NULL,
  `geslacht` enum('m','v') NOT NULL,
  `voornamen` varchar(255) NOT NULL,
  `echtgenoot` varchar(4) DEFAULT NULL,
  `adresseringechtpaar` varchar(255) NOT NULL,
  `icq` varchar(255) NOT NULL,
  `msn` varchar(255) NOT NULL,
  `skype` varchar(255) NOT NULL,
  `jid` varchar(255) NOT NULL,
  `linkedin` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `beroep` varchar(255) NOT NULL,
  `studie` varchar(255) NOT NULL,
  `patroon` varchar(4) DEFAULT NULL,
  `studienr` int(11) DEFAULT NULL,
  `studiejaar` int(11) DEFAULT NULL,
  `lidjaar` int(11) NOT NULL,
  `lidafdatum` date DEFAULT NULL,
  `gebdatum` date NOT NULL,
  `sterfdatum` date DEFAULT NULL,
  `bankrekening` varchar(255) NOT NULL,
  `machtiging` tinyint(1) NOT NULL,
  `moot` char(1) NOT NULL,
  `verticale` char(1) NOT NULL,
  `verticaleleider` tinyint(1) NOT NULL,
  `kringcoach` char(1) DEFAULT NULL,
  `o_adres` varchar(255) NOT NULL,
  `o_postcode` varchar(255) NOT NULL,
  `o_woonplaats` varchar(255) NOT NULL,
  `o_land` varchar(255) NOT NULL,
  `o_telefoon` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `kerk` varchar(255) NOT NULL,
  `muziek` varchar(255) NOT NULL,
  `status` enum('S_NOVIET','S_LID','S_GASTLID','S_OUDLID','S_ERELID','S_OVERLEDEN','S_EXLID','S_NOBODY','S_CIE','S_KRINGEL') NOT NULL,
  `eetwens` varchar(255) NOT NULL,
  `corvee_punten` int(11) NOT NULL,
  `corvee_punten_bonus` int(11) NOT NULL,
  `ontvangtcontactueel` enum('ja','digitaal','nee') NOT NULL,
  `kgb` text NOT NULL,
  `soccieID` int(11) NOT NULL,
  `createTerm` varchar(255) NOT NULL,
  `soccieSaldo` float NOT NULL,
  `maalcieSaldo` float NOT NULL,
  `changelog` text NOT NULL,
  `ovkaart` varchar(255) NOT NULL,
  `zingen` varchar(255) NOT NULL,
  `novitiaat` text NOT NULL,
  `lengte` int(11) NOT NULL,
  `vrienden` text NOT NULL,
  `middelbareSchool` varchar(255) NOT NULL,
  `novietSoort` varchar(255) NOT NULL,
  `matrixPlek` varchar(255) NOT NULL,
  `startkamp` varchar(255) NOT NULL,
  `medisch` text NOT NULL,
  `novitiaatBijz` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `nickname` (`nickname`),
  KEY `verticale` (`verticale`),
  KEY `status` (`status`),
  KEY `achternaam` (`achternaam`),
  KEY `voornaam` (`voornaam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `saldolog`
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
-- Tabelstructuur voor tabel `savedquery`
--

CREATE TABLE IF NOT EXISTS `savedquery` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `savedquery` text NOT NULL,
  `beschrijving` varchar(255) NOT NULL,
  `permissie` varchar(255) NOT NULL DEFAULT 'P_LOGGED_IN',
  `categorie` varchar(255) NOT NULL DEFAULT 'Overig',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=116 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCieBestelling`
--

CREATE TABLE IF NOT EXISTS `socCieBestelling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `socCieId` int(11) DEFAULT NULL,
  `totaal` int(11) DEFAULT NULL,
  `tijd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7664 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCieBestellingInhoud`
--

CREATE TABLE IF NOT EXISTS `socCieBestellingInhoud` (
  `bestellingId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `aantal` int(11) DEFAULT '1',
  PRIMARY KEY (`bestellingId`,`productId`),
  KEY `productId_idx` (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCieGrootboekType`
--

CREATE TABLE IF NOT EXISTS `socCieGrootboekType` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCieKlanten`
--

CREATE TABLE IF NOT EXISTS `socCieKlanten` (
  `socCieId` int(11) NOT NULL AUTO_INCREMENT,
  `stekUID` varchar(4) DEFAULT NULL,
  `saldo` int(11) DEFAULT '0',
  `naam` text,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`socCieId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=666 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCieLog`
--

CREATE TABLE IF NOT EXISTS `socCieLog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `type` enum('insert','update','remove') NOT NULL,
  `value` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7944 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCiePrijs`
--

CREATE TABLE IF NOT EXISTS `socCiePrijs` (
  `van` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tot` timestamp NOT NULL DEFAULT '2035-12-01 17:15:57',
  `productId` int(11) NOT NULL,
  `prijs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`van`,`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socCieProduct`
--

CREATE TABLE IF NOT EXISTS `socCieProduct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT NULL,
  `beschrijving` text,
  `prioriteit` int(11) NOT NULL,
  `grootboekId` int(11) unsigned NOT NULL,
  `beheer` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='socCieProduct' AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `verticalen`
--

CREATE TABLE IF NOT EXISTS `verticalen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `letter` char(1) NOT NULL,
  `naam` varchar(255) NOT NULL,
  `familie` varchar(255) NOT NULL,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `letter` (`letter`),
  UNIQUE KEY `naam` (`naam`),
  KEY `familie` (`familie`),
  KEY `status` (`status`),
  KEY `begin_moment` (`begin_moment`),
  KEY `maker_uid` (`maker_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `verticale_leden`
--

CREATE TABLE IF NOT EXISTS `verticale_leden` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `lid_sinds` (`lid_sinds`),
  KEY `door_uid` (`door_uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `voorkeurCommissie`
--

CREATE TABLE IF NOT EXISTS `voorkeurCommissie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `zichtbaar` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `voorkeurOpmerking`
--

CREATE TABLE IF NOT EXISTS `voorkeurOpmerking` (
  `uid` varchar(4) NOT NULL,
  `lidOpmerking` text NOT NULL,
  `praesesOpmerking` text NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `voorkeurVoorkeur`
--

CREATE TABLE IF NOT EXISTS `voorkeurVoorkeur` (
  `uid` varchar(4) NOT NULL,
  `cid` int(11) NOT NULL,
  `actief` int(11) NOT NULL,
  `voorkeur` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`,`cid`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `werkgroepen`
--

CREATE TABLE IF NOT EXISTS `werkgroepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `aanmeld_limiet` int(11) DEFAULT NULL,
  `aanmelden_vanaf` datetime NOT NULL,
  `aanmelden_tot` datetime NOT NULL,
  `bewerken_tot` datetime NOT NULL,
  `afmelden_tot` datetime DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=244 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `werkgroep_deelnemers`
--

CREATE TABLE IF NOT EXISTS `werkgroep_deelnemers` (
  `groep_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `opmerking` varchar(255) DEFAULT NULL,
  `lid_sinds` datetime NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  PRIMARY KEY (`groep_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `door_uid` (`door_uid`),
  KEY `lid_sinds` (`lid_sinds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `woonoorden`
--

CREATE TABLE IF NOT EXISTS `woonoorden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `soort` enum('w','h') NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

--
-- Beperkingen voor gedumpte tabellen
--

--
-- Beperkingen voor tabel `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `activiteiten`
--
ALTER TABLE `activiteiten`
  ADD CONSTRAINT `activiteiten_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `activiteit_deelnemers`
--
ALTER TABLE `activiteit_deelnemers`
  ADD CONSTRAINT `activiteit_deelnemers_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `activiteiten` (`id`),
  ADD CONSTRAINT `activiteit_deelnemers_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `activiteit_deelnemers_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `besturen`
--
ALTER TABLE `besturen`
  ADD CONSTRAINT `besturen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `bestuurs_leden`
--
ALTER TABLE `bestuurs_leden`
  ADD CONSTRAINT `bestuurs_leden_ibfk_4` FOREIGN KEY (`groep_id`) REFERENCES `besturen` (`id`),
  ADD CONSTRAINT `bestuurs_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `bestuurs_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `bewoners`
--
ALTER TABLE `bewoners`
  ADD CONSTRAINT `bewoners_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `woonoorden` (`id`),
  ADD CONSTRAINT `bewoners_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `bewoners_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `biebbeschrijving`
--
ALTER TABLE `biebbeschrijving`
  ADD CONSTRAINT `biebbeschrijving_ibfk_1` FOREIGN KEY (`boek_id`) REFERENCES `biebboek` (`id`),
  ADD CONSTRAINT `biebbeschrijving_ibfk_2` FOREIGN KEY (`schrijver_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `biebboek`
--
ALTER TABLE `biebboek`
  ADD CONSTRAINT `biebboek_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `biebauteur` (`id`),
  ADD CONSTRAINT `biebboek_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `biebcategorie` (`id`);

--
-- Beperkingen voor tabel `biebcategorie`
--
ALTER TABLE `biebcategorie`
  ADD CONSTRAINT `biebcategorie_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `biebcategorie` (`id`);

--
-- Beperkingen voor tabel `biebexemplaar`
--
ALTER TABLE `biebexemplaar`
  ADD CONSTRAINT `biebexemplaar_ibfk_1` FOREIGN KEY (`boek_id`) REFERENCES `biebboek` (`id`),
  ADD CONSTRAINT `biebexemplaar_ibfk_2` FOREIGN KEY (`eigenaar_uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `biebexemplaar_ibfk_3` FOREIGN KEY (`uitgeleend_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `commissies`
--
ALTER TABLE `commissies`
  ADD CONSTRAINT `commissies_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `commissie_leden`
--
ALTER TABLE `commissie_leden`
  ADD CONSTRAINT `commissie_leden_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `commissies` (`id`),
  ADD CONSTRAINT `commissie_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `commissie_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `courant`
--
ALTER TABLE `courant`
  ADD CONSTRAINT `courant_ibfk_1` FOREIGN KEY (`verzender`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `crv_kwalificaties`
--
ALTER TABLE `crv_kwalificaties`
  ADD CONSTRAINT `crv_kwalificaties_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `crv_kwalificaties_ibfk_3` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`);

--
-- Beperkingen voor tabel `crv_repetities`
--
ALTER TABLE `crv_repetities`
  ADD CONSTRAINT `crv_repetities_ibfk_1` FOREIGN KEY (`mlt_repetitie_id`) REFERENCES `mlt_repetities` (`mlt_repetitie_id`),
  ADD CONSTRAINT `crv_repetities_ibfk_2` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`);

--
-- Beperkingen voor tabel `crv_taken`
--
ALTER TABLE `crv_taken`
  ADD CONSTRAINT `crv_taken_ibfk_1` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`),
  ADD CONSTRAINT `crv_taken_ibfk_2` FOREIGN KEY (`crv_repetitie_id`) REFERENCES `crv_repetities` (`crv_repetitie_id`),
  ADD CONSTRAINT `crv_taken_ibfk_3` FOREIGN KEY (`maaltijd_id`) REFERENCES `mlt_maaltijden` (`maaltijd_id`),
  ADD CONSTRAINT `crv_taken_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `crv_voorkeuren`
--
ALTER TABLE `crv_voorkeuren`
  ADD CONSTRAINT `crv_voorkeuren_ibfk_1` FOREIGN KEY (`crv_repetitie_id`) REFERENCES `crv_repetities` (`crv_repetitie_id`),
  ADD CONSTRAINT `crv_voorkeuren_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `crv_vrijstellingen`
--
ALTER TABLE `crv_vrijstellingen`
  ADD CONSTRAINT `crv_vrijstellingen_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `dies_gala_2014`
--
ALTER TABLE `dies_gala_2014`
  ADD CONSTRAINT `dies_gala_2014_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`catID`) REFERENCES `documentcategorie` (`ID`),
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`eigenaar`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `eetplan`
--
ALTER TABLE `eetplan`
  ADD CONSTRAINT `eetplan_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `eetplan_ibfk_2` FOREIGN KEY (`huis`) REFERENCES `eetplanhuis` (`id`);

--
-- Beperkingen voor tabel `eetplanhuis`
--
ALTER TABLE `eetplanhuis`
  ADD CONSTRAINT `eetplanhuis_ibfk_1` FOREIGN KEY (`groepid`) REFERENCES `groep` (`id`);

--
-- Beperkingen voor tabel `forum_delen`
--
ALTER TABLE `forum_delen`
  ADD CONSTRAINT `forum_delen_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `forum_categorien` (`categorie_id`);

--
-- Beperkingen voor tabel `fotoalbums`
--
ALTER TABLE `fotoalbums`
  ADD CONSTRAINT `fotoalbums_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `fotos`
--
ALTER TABLE `fotos`
  ADD CONSTRAINT `fotos_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `groep`
--
ALTER TABLE `groep`
  ADD CONSTRAINT `groep_ibfk_1` FOREIGN KEY (`gtype`) REFERENCES `groeptype` (`id`);

--
-- Beperkingen voor tabel `groepen`
--
ALTER TABLE `groepen`
  ADD CONSTRAINT `groepen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `groep_leden`
--
ALTER TABLE `groep_leden`
  ADD CONSTRAINT `groep_leden_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `groep_leden_ibfk_2` FOREIGN KEY (`groep_id`) REFERENCES `groepen` (`id`),
  ADD CONSTRAINT `groep_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `ketzers`
--
ALTER TABLE `ketzers`
  ADD CONSTRAINT `ketzers_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `ketzer_deelnemers`
--
ALTER TABLE `ketzer_deelnemers`
  ADD CONSTRAINT `ketzer_deelnemers_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `ketzers` (`id`),
  ADD CONSTRAINT `ketzer_deelnemers_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `ketzer_deelnemers_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `kringen`
--
ALTER TABLE `kringen`
  ADD CONSTRAINT `kringen_ibfk_2` FOREIGN KEY (`verticale`) REFERENCES `verticalen` (`letter`),
  ADD CONSTRAINT `kringen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `kring_leden`
--
ALTER TABLE `kring_leden`
  ADD CONSTRAINT `kring_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `kring_leden_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `kringen` (`id`),
  ADD CONSTRAINT `kring_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `lichtingen`
--
ALTER TABLE `lichtingen`
  ADD CONSTRAINT `lichtingen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `lichting_leden`
--
ALTER TABLE `lichting_leden`
  ADD CONSTRAINT `lichting_leden_ibfk_4` FOREIGN KEY (`groep_id`) REFERENCES `lichtingen` (`id`),
  ADD CONSTRAINT `lichting_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `lichting_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `lidinstellingen`
--
ALTER TABLE `lidinstellingen`
  ADD CONSTRAINT `lidinstellingen_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `login_sessions`
--
ALTER TABLE `login_sessions`
  ADD CONSTRAINT `login_sessions_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `accounts` (`uid`);

--
-- Beperkingen voor tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`item_id`);

--
-- Beperkingen voor tabel `mlt_aanmeldingen`
--
ALTER TABLE `mlt_aanmeldingen`
  ADD CONSTRAINT `mlt_aanmeldingen_ibfk_1` FOREIGN KEY (`maaltijd_id`) REFERENCES `mlt_maaltijden` (`maaltijd_id`),
  ADD CONSTRAINT `mlt_aanmeldingen_ibfk_2` FOREIGN KEY (`door_abonnement`) REFERENCES `mlt_repetities` (`mlt_repetitie_id`);

--
-- Beperkingen voor tabel `mlt_abonnementen`
--
ALTER TABLE `mlt_abonnementen`
  ADD CONSTRAINT `mlt_abonnementen_ibfk_1` FOREIGN KEY (`mlt_repetitie_id`) REFERENCES `mlt_repetities` (`mlt_repetitie_id`);

--
-- Beperkingen voor tabel `mlt_maaltijden`
--
ALTER TABLE `mlt_maaltijden`
  ADD CONSTRAINT `mlt_maaltijden_ibfk_1` FOREIGN KEY (`mlt_repetitie_id`) REFERENCES `mlt_repetities` (`mlt_repetitie_id`);

--
-- Beperkingen voor tabel `onderverenigingen`
--
ALTER TABLE `onderverenigingen`
  ADD CONSTRAINT `onderverenigingen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `ondervereniging_leden`
--
ALTER TABLE `ondervereniging_leden`
  ADD CONSTRAINT `ondervereniging_leden_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `onderverenigingen` (`id`),
  ADD CONSTRAINT `ondervereniging_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `ondervereniging_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `peilingoptie`
--
ALTER TABLE `peilingoptie`
  ADD CONSTRAINT `peilingoptie_ibfk_1` FOREIGN KEY (`peilingid`) REFERENCES `peiling` (`id`);

--
-- Beperkingen voor tabel `peiling_stemmen`
--
ALTER TABLE `peiling_stemmen`
  ADD CONSTRAINT `peiling_stemmen_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `peiling_stemmen_ibfk_1` FOREIGN KEY (`peilingid`) REFERENCES `peiling` (`id`);

--
-- Beperkingen voor tabel `verticalen`
--
ALTER TABLE `verticalen`
  ADD CONSTRAINT `verticalen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `verticale_leden`
--
ALTER TABLE `verticale_leden`
  ADD CONSTRAINT `verticale_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `verticale_leden_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `verticalen` (`id`),
  ADD CONSTRAINT `verticale_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `voorkeurOpmerking`
--
ALTER TABLE `voorkeurOpmerking`
  ADD CONSTRAINT `voorkeurOpmerking_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `voorkeurVoorkeur`
--
ALTER TABLE `voorkeurVoorkeur`
  ADD CONSTRAINT `voorkeurVoorkeur_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `voorkeurCommissie` (`id`),
  ADD CONSTRAINT `voorkeurVoorkeur_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `werkgroepen`
--
ALTER TABLE `werkgroepen`
  ADD CONSTRAINT `werkgroepen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `werkgroep_deelnemers`
--
ALTER TABLE `werkgroep_deelnemers`
  ADD CONSTRAINT `werkgroep_deelnemers_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `werkgroepen` (`id`),
  ADD CONSTRAINT `werkgroep_deelnemers_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `werkgroep_deelnemers_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `woonoorden`
--
ALTER TABLE `woonoorden`
  ADD CONSTRAINT `woonoorden_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
