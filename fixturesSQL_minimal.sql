-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Gegenereerd op: 09 jan 2017 om 10:14
-- Serverversie: 5.7.9
-- PHP-versie: 5.6.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `csrdelft`
--

DELIMITER $$
--
-- Functies
--
DROP FUNCTION IF EXISTS `SPLIT_STR`$$
CREATE DEFINER=`csrdelft`@`localhost` FUNCTION `SPLIT_STR` (`x` VARCHAR(255), `delim` VARCHAR(12), `pos` INT) RETURNS VARCHAR(255) CHARSET utf8 RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
delim, '')$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `accounts`
--

DROP TABLE IF EXISTS `accounts`;
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
  `perm_role` enum('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE','R_VLIEGER') NOT NULL,
  `private_token` varchar(255) DEFAULT NULL,
  `private_token_since` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `accounts`
--

INSERT INTO `accounts` (`uid`, `username`, `email`, `pass_hash`, `pass_since`, `last_login_success`, `last_login_attempt`, `failed_login_attempts`, `blocked_reason`, `perm_role`, `private_token`, `private_token_since`) VALUES
('x404', 'x404', 'pubcie@csrdelft.nl', '{SSHA}kZv1u/oZRhBnI9D78G3+rBanCBxBiYRc', '2017-01-01 00:00:00', '2017-01-09 11:09:11', '2017-01-09 11:09:11', 0, NULL, 'R_PUBCIE', NULL, NULL),
('x999', 'x999', 'pubcie@csrdelft.nl', ' ', '2017-01-01 00:00:00', '2017-01-01 00:00:00', '2017-01-01 00:00:00', 0, NULL, 'R_NOBODY', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `acl`
--

DROP TABLE IF EXISTS `acl`;
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

DROP TABLE IF EXISTS `activiteiten`;
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
  `bewerken_tot` datetime DEFAULT NULL,
  `afmelden_tot` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `familie` (`familie`),
  KEY `status` (`status`),
  KEY `soort` (`soort`),
  KEY `in_agenda` (`in_agenda`),
  KEY `begin_moment` (`begin_moment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `activiteit_deelnemers`
--

DROP TABLE IF EXISTS `activiteit_deelnemers`;
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

DROP TABLE IF EXISTS `agenda`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `agenda_verbergen`
--

DROP TABLE IF EXISTS `agenda_verbergen`;
CREATE TABLE IF NOT EXISTS `agenda_verbergen` (
  `uid` varchar(4) NOT NULL,
  `refuuid` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`,`refuuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `besturen`
--

DROP TABLE IF EXISTS `besturen`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bestuurs_leden`
--

DROP TABLE IF EXISTS `bestuurs_leden`;
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

DROP TABLE IF EXISTS `bewoners`;
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

DROP TABLE IF EXISTS `biebauteur`;
CREATE TABLE IF NOT EXISTS `biebauteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebbeschrijving`
--

DROP TABLE IF EXISTS `biebbeschrijving`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebboek`
--

DROP TABLE IF EXISTS `biebboek`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `biebcategorie`
--

DROP TABLE IF EXISTS `biebcategorie`;
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

DROP TABLE IF EXISTS `biebexemplaar`;
CREATE TABLE IF NOT EXISTS `biebexemplaar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boek_id` int(11) NOT NULL DEFAULT '0',
  `eigenaar_uid` varchar(4) NOT NULL DEFAULT '',
  `opmerking` varchar(255) NOT NULL,
  `uitgeleend_uid` varchar(4) DEFAULT NULL,
  `toegevoegd` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('beschikbaar','uitgeleend','teruggegeven','vermist') NOT NULL DEFAULT 'beschikbaar',
  `uitleendatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `leningen` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `boek_id` (`boek_id`),
  KEY `eigenaar_uid` (`eigenaar_uid`),
  KEY `uitgeleend_uid` (`uitgeleend_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bijbelrooster`
--

DROP TABLE IF EXISTS `bijbelrooster`;
CREATE TABLE IF NOT EXISTS `bijbelrooster` (
  `dag` datetime NOT NULL,
  `stukje` varchar(255) NOT NULL,
  PRIMARY KEY (`dag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bijbelrooster_old`
--

DROP TABLE IF EXISTS `bijbelrooster_old`;
CREATE TABLE IF NOT EXISTS `bijbelrooster_old` (
  `dag` date NOT NULL,
  `stukje` varchar(70) NOT NULL,
  PRIMARY KEY (`dag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `changelog`
--

DROP TABLE IF EXISTS `changelog`;
CREATE TABLE IF NOT EXISTS `changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moment` datetime NOT NULL,
  `subject` varchar(255) NOT NULL,
  `property` varchar(255) NOT NULL,
  `old_value` text,
  `new_value` text,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cms_paginas`
--

DROP TABLE IF EXISTS `cms_paginas`;
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

--
-- Gegevens worden geëxporteerd voor tabel `cms_paginas`
--

INSERT INTO `cms_paginas` (`naam`, `titel`, `inhoud`, `laatst_gewijzigd`, `rechten_bekijken`, `rechten_bewerken`, `inline_html`) VALUES
('thuis', 'Vereniging van Christenstudenten', '[h=1]Civitas Studiosorum Reformatorum Delft[/h]\r\n[prive]\r\n&lt;div class=&quot;col-md-6&quot;&gt;\r\n\r\n	[fotoalbum slider=homepage]Voorpagina[/fotoalbum]\r\n\r\n	[maaltijd=next2]\r\n\r\n	[prive=P_ALLEEN_OUDLID]\r\n		[mededelingen=top3oudleden]\r\n	[/prive]\r\n\r\n	[mededelingen=top3leden]\r\n\r\n&lt;/div&gt;\r\n&lt;div class=&quot;col-md-6&quot;&gt;\r\n\r\n\r\n	[commentaar]Hier kan een poster in[/commentaar]\r\n\r\n&lt;/div&gt;\r\n\r\n[/prive][clear]\r\n\r\n[commentaar][url=http://www.studentalphadelft.nl/][img]/plaetjes/banners/alpha300x75.jpg[/img][/url][/commentaar]\r\n\r\n[clear][hr]\r\n\r\n&lt;div class=&quot;ads&quot;&gt;\r\n\r\n[offtopic]advertenties:[/offtopic]\r\n\r\n&lt;br /&gt;\r\n\r\n&lt;a href=&quot;https://www.dosign.nl/&quot;&gt;\r\n	&lt;img src=&quot;/plaetjes/banners/dosign.gif&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;a href=&quot;http://www.mechdes.nl/&quot;&gt;\r\n	&lt;img src=&quot;/plaetjes/banners/mechdes.gif&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;a href=&quot;http://www.galjemadetachering.nl/&quot;&gt;\r\n       &lt;img src=&quot;/plaetjes/banners/galjema_banner.jpg&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;a href=&quot;http://www.stcgroep.nl/&quot;&gt;\r\n       &lt;img src=&quot;/plaetjes/banners/STC-groep-banner.gif&quot; alt=&quot;STC groep advertentie&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;a href=&quot;http://www.zoover.nl/lastminutes/&quot;&gt;\r\n       &lt;img src=&quot;/plaetjes/banners/Zoover.jpg&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;a href=&quot;https://www.maxilia.nl/banners-drukken/&quot;&gt;\r\n       &lt;img src=&quot;/plaetjes/banners/maxilia.png&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;a href=&quot;http://www.tudelft.nl/&quot;&gt;\r\n      &lt;img src=&quot;/plaetjes/banners/TU_Delft_logo_Black.png&quot;&gt;\r\n&lt;/a&gt;\r\n\r\n&lt;br /&gt;\r\n\r\n&lt;/div&gt;', '2017-01-01 00:00:00', 'P_PUBLIC', 'P_ADMIN', 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissies`
--

DROP TABLE IF EXISTS `commissies`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_leden`
--

DROP TABLE IF EXISTS `commissie_leden`;
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

DROP TABLE IF EXISTS `courant`;
CREATE TABLE IF NOT EXISTS `courant` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `verzendMoment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `template` varchar(50) NOT NULL DEFAULT 'csrmail.tpl',
  `verzender` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `courant_ibfk_1` (`verzender`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `courantbericht`
--

DROP TABLE IF EXISTS `courantbericht`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `courantcache`
--

DROP TABLE IF EXISTS `courantcache`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_functies`
--

DROP TABLE IF EXISTS `crv_functies`;
CREATE TABLE IF NOT EXISTS `crv_functies` (
  `functie_id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `afkorting` varchar(255) NOT NULL,
  `email_bericht` text NOT NULL,
  `standaard_punten` int(11) NOT NULL,
  `kwalificatie_benodigd` tinyint(1) NOT NULL,
  `maaltijden_sluiten` tinyint(1) NOT NULL,
  PRIMARY KEY (`functie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_kwalificaties`
--

DROP TABLE IF EXISTS `crv_kwalificaties`;
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

DROP TABLE IF EXISTS `crv_repetities`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_taken`
--

DROP TABLE IF EXISTS `crv_taken`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_voorkeuren`
--

DROP TABLE IF EXISTS `crv_voorkeuren`;
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

DROP TABLE IF EXISTS `crv_vrijstellingen`;
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

DROP TABLE IF EXISTS `debug_log`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document`
--

DROP TABLE IF EXISTS `document`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `documentcategorie`
--

DROP TABLE IF EXISTS `documentcategorie`;
CREATE TABLE IF NOT EXISTS `documentcategorie` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `zichtbaar` tinyint(1) NOT NULL,
  `leesrechten` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `eetplan`
--

DROP TABLE IF EXISTS `eetplan`;
CREATE TABLE IF NOT EXISTS `eetplan` (
  `uid` varchar(4) NOT NULL,
  `woonoord_id` int(11) NOT NULL,
  `avond` date NOT NULL,
  PRIMARY KEY (`uid`,`woonoord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `eetplan_bekenden`
--

DROP TABLE IF EXISTS `eetplan_bekenden`;
CREATE TABLE IF NOT EXISTS `eetplan_bekenden` (
  `uid1` varchar(4) NOT NULL,
  `uid2` varchar(4) NOT NULL,
  PRIMARY KEY (`uid1`,`uid2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `execution_times`
--

DROP TABLE IF EXISTS `execution_times`;
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

DROP TABLE IF EXISTS `forum_categorien`;
CREATE TABLE IF NOT EXISTS `forum_categorien` (
  `categorie_id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) NOT NULL,
  `rechten_lezen` varchar(255) NOT NULL,
  `volgorde` int(11) NOT NULL,
  PRIMARY KEY (`categorie_id`),
  KEY `volgorde` (`volgorde`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO csrdelft.forum_categorien (titel, rechten_lezen, volgorde) VALUES ('Vereniging', 'P_LOGGED_IN', 1);
INSERT INTO csrdelft.forum_categorien (titel, rechten_lezen, volgorde) VALUES ('Openbaar', 'P_FORUM_READ', 4);
INSERT INTO csrdelft.forum_categorien (titel, rechten_lezen, volgorde) VALUES ('Geloof & Vorming', 'P_LOGGED_IN', 2);
INSERT INTO csrdelft.forum_categorien (titel, rechten_lezen, volgorde) VALUES ('Deelfora', 'P_LOGGED_IN', 3);
INSERT INTO csrdelft.forum_categorien (titel, rechten_lezen, volgorde) VALUES ('Overig', 'P_LOGGED_IN', 5);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_delen`
--

DROP TABLE IF EXISTS `forum_delen`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO csrdelft.forum_delen (categorie_id, titel, omschrijving, rechten_lezen, rechten_posten, rechten_modereren, volgorde) VALUES (1, 'C.S.R.-zaken', 'Afdeling voor interne zaken. Onzichtbaar voor externen die niet kunnen inloggen.', 'P_LOGGED_IN', 'P_LOGGED_IN', 'P_FORUM_MOD', 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden`
--

DROP TABLE IF EXISTS `forum_draden`;
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
  KEY `belangrijk` (`belangrijk`)
) ENGINE=MyISAM AUTO_INCREMENT=9888 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden_gelezen`
--

DROP TABLE IF EXISTS `forum_draden_gelezen`;
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

DROP TABLE IF EXISTS `forum_draden_reageren`;
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

DROP TABLE IF EXISTS `forum_draden_verbergen`;
CREATE TABLE IF NOT EXISTS `forum_draden_verbergen` (
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`draad_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_draden_volgen`
--

DROP TABLE IF EXISTS `forum_draden_volgen`;
CREATE TABLE IF NOT EXISTS `forum_draden_volgen` (
  `draad_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`draad_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_posts`
--

DROP TABLE IF EXISTS `forum_posts`;
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
  KEY `datum_tijd` (`datum_tijd`)
) ENGINE=MyISAM AUTO_INCREMENT=111524 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fotoalbums`
--

DROP TABLE IF EXISTS `fotoalbums`;
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

DROP TABLE IF EXISTS `fotos`;
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
-- Tabelstructuur voor tabel `foto_tags`
--

DROP TABLE IF EXISTS `foto_tags`;
CREATE TABLE IF NOT EXISTS `foto_tags` (
  `refuuid` varchar(255) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `door` varchar(4) NOT NULL,
  `wanneer` datetime NOT NULL,
  `x` float NOT NULL,
  `y` float NOT NULL,
  `size` float NOT NULL,
  PRIMARY KEY (`refuuid`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `gesprekken`
--

DROP TABLE IF EXISTS `gesprekken`;
CREATE TABLE IF NOT EXISTS `gesprekken` (
  `gesprek_id` int(11) NOT NULL AUTO_INCREMENT,
  `laatste_update` datetime NOT NULL,
  PRIMARY KEY (`gesprek_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `gesprek_berichten`
--

DROP TABLE IF EXISTS `gesprek_berichten`;
CREATE TABLE IF NOT EXISTS `gesprek_berichten` (
  `bericht_id` int(11) NOT NULL AUTO_INCREMENT,
  `gesprek_id` int(11) NOT NULL,
  `moment` datetime NOT NULL,
  `auteur_uid` varchar(4) NOT NULL,
  `inhoud` text NOT NULL,
  PRIMARY KEY (`bericht_id`),
  KEY `gesprek_id` (`gesprek_id`),
  KEY `moment` (`moment`),
  KEY `auteur_uid` (`auteur_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `gesprek_deelnemers`
--

DROP TABLE IF EXISTS `gesprek_deelnemers`;
CREATE TABLE IF NOT EXISTS `gesprek_deelnemers` (
  `gesprek_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `toegevoegd_moment` datetime NOT NULL,
  `gelezen_moment` datetime NOT NULL,
  PRIMARY KEY (`gesprek_id`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groep`
--

DROP TABLE IF EXISTS `groep`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groepen`
--

DROP TABLE IF EXISTS `groepen`;
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
  `rechten_aanmelden` varchar(255) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `status` (`status`),
  KEY `familie` (`familie`),
  KEY `begin_moment` (`begin_moment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groeptype`
--

DROP TABLE IF EXISTS `groeptype`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groep_leden`
--

DROP TABLE IF EXISTS `groep_leden`;
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

DROP TABLE IF EXISTS `instellingen`;
CREATE TABLE IF NOT EXISTS `instellingen` (
  `module` varchar(255) NOT NULL,
  `instelling_id` varchar(255) NOT NULL,
  `waarde` text NOT NULL,
  PRIMARY KEY (`module`,`instelling_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `instellingen`
--

INSERT INTO `instellingen` (`module`, `instelling_id`, `waarde`) VALUES
('beveiliging', 'recent_login_seconds', '600'),
('beveiliging', 'session_lifetime_seconds', '1440'),
('beveiliging', 'wachtwoorden_verlopen_ouder_dan', '-1 year'),
('beveiliging', 'wachtwoorden_verlopen_waarschuwing_vooraf', '-2 weeks'),
('forum', 'grafiek_stats_periode', '-6 months'),
('forum', 'reageren_tijd', '-2 minutes'),
('fotoalbum', 'slideshow_interval', '3s'),
('maaltijden', 'beoordeling_periode', '-1 week'),
('maaltijden', 'recent_lidprofiel', '-2 months'),
('maaltijden', 'standaard_prijs', '300'),
('maaltijden', 'toon_ketzer_vooraf', '+1 month'),
('stek', 'beschrijving', 'De Civitas Studiosorum Reformatorum is een bruisende, actieve, christelijke studentenvereniging in Delft, rijk aan tradities die zijn ontstaan in haar 50-jarig bestaan. Het is een breed gezelschap van zo&lsquo;n 270 leden met een zeer gevarieerde (kerkelijke) achtergrond, maar met een duidelijke eenheid door het christelijk geloof. C.S.R. is de plek waar al tientallen jaren studenten goede vrienden van elkaar worden, op intellectueel en geestelijk gebied groeien en goede studentengrappen uithalen.'),
('stek', 'homepage', 'thuis');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ketzers`
--

DROP TABLE IF EXISTS `ketzers`;
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
  `bewerken_tot` datetime DEFAULT NULL,
  `afmelden_tot` datetime DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ketzer_deelnemers`
--

DROP TABLE IF EXISTS `ketzer_deelnemers`;
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

DROP TABLE IF EXISTS `kringen`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kring_leden`
--

DROP TABLE IF EXISTS `kring_leden`;
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

DROP TABLE IF EXISTS `lichtingen`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `lichting_leden`
--

DROP TABLE IF EXISTS `lichting_leden`;
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

DROP TABLE IF EXISTS `lidinstellingen`;
CREATE TABLE IF NOT EXISTS `lidinstellingen` (
  `uid` varchar(4) NOT NULL,
  `module` varchar(255) NOT NULL,
  `instelling_id` varchar(255) NOT NULL,
  `waarde` text NOT NULL,
  PRIMARY KEY (`uid`,`module`,`instelling_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `lidinstellingen`
--

INSERT INTO `lidinstellingen` (`uid`, `module`, `instelling_id`, `waarde`) VALUES
('x404', 'agenda', 'toonBijbelrooster', 'ja'),
('x404', 'agenda', 'toonCorvee', 'eigen'),
('x404', 'agenda', 'toonMaaltijden', 'ja'),
('x404', 'agenda', 'toonVerjaardagen', 'ja'),
('x404', 'forum', 'draden_per_pagina', '20'),
('x404', 'forum', 'naamWeergave', 'civitas'),
('x404', 'forum', 'posts_per_pagina', '20'),
('x404', 'forum', 'zoekresultaten', '20'),
('x404', 'layout', 'fx', 'nee'),
('x404', 'layout', 'minion', 'nee'),
('x404', 'layout', 'opmaak', 'normaal'),
('x404', 'layout', 'toegankelijk', 'standaard'),
('x404', 'layout', 'visitekaartjes', 'ja'),
('x404', 'zijbalk', 'agendaweken', '2'),
('x404', 'zijbalk', 'agenda_max', '15'),
('x404', 'zijbalk', 'favorieten', 'ja'),
('x404', 'zijbalk', 'forum', '10'),
('x404', 'zijbalk', 'forum_belangrijk', '5'),
('x404', 'zijbalk', 'forum_zelf', '0'),
('x404', 'zijbalk', 'fotoalbum', 'ja'),
('x404', 'zijbalk', 'fotos', '6'),
('x404', 'zijbalk', 'ishetal', 'willekeurig'),
('x404', 'zijbalk', 'ledenmemory_topscores', '0'),
('x404', 'zijbalk', 'mededelingen', '5'),
('x404', 'zijbalk', 'scrollen', 'met pagina mee'),
('x404', 'zijbalk', 'verjaardagen', '9'),
('x404', 'zijbalk', 'verjaardagen_pasfotos', 'ja'),
('x404', 'zoeken', 'agenda', 'ja'),
('x404', 'zoeken', 'boeken', 'nee'),
('x404', 'zoeken', 'commissies', 'ja'),
('x404', 'zoeken', 'documenten', 'nee'),
('x404', 'zoeken', 'favorieten', 'ja'),
('x404', 'zoeken', 'forum', 'ja'),
('x404', 'zoeken', 'fotoalbum', 'nee'),
('x404', 'zoeken', 'groepen', 'nee'),
('x404', 'zoeken', 'kringen', 'nee'),
('x404', 'zoeken', 'leden', 'LEDEN'),
('x404', 'zoeken', 'menu', 'ja'),
('x404', 'zoeken', 'onderverenigingen', 'nee'),
('x404', 'zoeken', 'werkgroepen', 'nee'),
('x404', 'zoeken', 'wiki', 'ja'),
('x404', 'zoeken', 'woonoorden', 'ja'),
('x999', 'layout', 'fx', 'nee'),
('x999', 'layout', 'minion', 'nee'),
('x999', 'layout', 'opmaak', 'normaal'),
('x999', 'layout', 'toegankelijk', 'standaard');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(4) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `locatie` varchar(255) NOT NULL,
  `moment` datetime NOT NULL,
  `url` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `uid` (`uid`),
  KEY `moment` (`moment`)
) ENGINE=InnoDB AUTO_INCREMENT=16099826 DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `log`
--

INSERT INTO `log` (`ID`, `uid`, `ip`, `locatie`, `moment`, `url`, `referer`, `useragent`) VALUES
(16099401, 'x999', '127.0.0.1', '', '2017-01-03 16:49:21', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099402, 'x999', '127.0.0.1', '', '2017-01-03 16:49:22', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099403, 'x999', '127.0.0.1', '', '2017-01-03 16:55:18', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099404, 'x999', '127.0.0.1', '', '2017-01-03 16:55:18', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099405, 'x999', '127.0.0.1', '', '2017-01-03 16:56:09', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099406, 'x999', '127.0.0.1', '', '2017-01-03 16:56:09', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099407, 'x999', '127.0.0.1', '', '2017-01-03 17:01:23', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099408, 'x999', '127.0.0.1', '', '2017-01-03 17:01:24', '/tools/css.php?l=layout-owee&m=general&1483459283', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099409, 'x999', '127.0.0.1', '', '2017-01-03 17:01:27', '/tools/js.php?l=layout-owee&m=general&1483459283', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099410, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099411, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099412, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099413, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099414, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099415, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099416, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099417, 'x999', '127.0.0.1', '', '2017-01-03 17:01:29', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099418, 'x999', '127.0.0.1', '', '2017-01-03 17:01:30', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099419, 'x999', '127.0.0.1', '', '2017-01-03 17:01:40', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099420, 'x999', '127.0.0.1', '', '2017-01-03 17:01:40', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099421, 'x999', '127.0.0.1', '', '2017-01-03 17:01:40', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099422, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099423, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099424, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099425, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099426, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099427, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099428, 'x999', '127.0.0.1', '', '2017-01-03 17:01:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099429, 'x999', '127.0.0.1', '', '2017-01-03 17:01:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099430, 'x999', '127.0.0.1', '', '2017-01-03 17:01:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099431, 'x999', '127.0.0.1', '', '2017-01-03 17:01:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099432, 'x999', '127.0.0.1', '', '2017-01-03 17:18:00', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099433, 'x999', '127.0.0.1', '', '2017-01-03 17:18:00', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099434, 'x999', '127.0.0.1', '', '2017-01-03 17:18:00', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099435, 'x999', '127.0.0.1', '', '2017-01-03 17:18:00', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099436, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099437, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099438, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099439, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099440, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099441, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099442, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099443, 'x999', '127.0.0.1', '', '2017-01-03 17:18:01', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099444, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099445, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099446, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099447, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099448, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099449, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099450, 'x999', '127.0.0.1', '', '2017-01-03 17:18:08', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099451, 'x999', '127.0.0.1', '', '2017-01-03 17:18:09', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099452, 'x999', '127.0.0.1', '', '2017-01-03 17:18:09', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099453, 'x999', '127.0.0.1', '', '2017-01-03 17:18:09', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099454, 'x999', '127.0.0.1', '', '2017-01-03 17:18:09', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099455, 'x999', '127.0.0.1', '', '2017-01-03 17:18:41', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099456, 'x404', '127.0.0.1', '', '2017-01-03 17:18:41', '/wachtwoord/wijzigen', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099457, 'x404', '127.0.0.1', '', '2017-01-03 17:18:41', '/favicon.ico', 'http://dev.csrdelft.nl/wachtwoord/wijzigen', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099458, 'x404', '127.0.0.1', '', '2017-01-03 17:20:12', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099459, 'x404', '127.0.0.1', '', '2017-01-03 17:20:12', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099460, 'x404', '127.0.0.1', '', '2017-01-03 17:20:13', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099461, 'x404', '127.0.0.1', '', '2017-01-03 17:20:13', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099462, 'x404', '127.0.0.1', '', '2017-01-03 17:20:14', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099463, 'x404', '127.0.0.1', '', '2017-01-03 17:20:19', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099464, 'x404', '127.0.0.1', '', '2017-01-03 17:20:27', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099465, 'x404', '127.0.0.1', '', '2017-01-03 17:20:32', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099466, 'x404', '127.0.0.1', '', '2017-01-03 17:20:33', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099467, 'x999', '127.0.0.1', '', '2017-01-03 17:20:40', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099468, 'x999', '127.0.0.1', '', '2017-01-03 17:20:40', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099469, 'x999', '127.0.0.1', '', '2017-01-03 17:20:40', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099470, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099471, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099472, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099473, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099474, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099475, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099476, 'x999', '127.0.0.1', '', '2017-01-03 17:20:41', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099477, 'x999', '127.0.0.1', '', '2017-01-03 17:20:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099478, 'x999', '127.0.0.1', '', '2017-01-03 17:20:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099479, 'x999', '127.0.0.1', '', '2017-01-03 17:20:49', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099480, 'x404', '127.0.0.1', '', '2017-01-03 17:20:49', '/wachtwoord/wijzigen', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099481, 'x404', '127.0.0.1', '', '2017-01-03 17:20:49', '/favicon.ico', 'http://dev.csrdelft.nl/wachtwoord/wijzigen', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099482, 'x999', '127.0.0.1', '', '2017-01-03 17:22:05', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099483, 'x999', '127.0.0.1', '', '2017-01-03 17:22:05', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099484, 'x999', '127.0.0.1', '', '2017-01-03 17:22:05', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099485, 'x999', '127.0.0.1', '', '2017-01-03 17:22:05', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099486, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099487, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099488, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099489, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099490, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099491, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099492, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099493, 'x999', '127.0.0.1', '', '2017-01-03 17:22:06', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099494, 'x999', '127.0.0.1', '', '2017-01-03 17:22:12', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099495, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099496, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099497, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099498, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099499, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099500, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099501, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099502, 'x999', '127.0.0.1', '', '2017-01-03 17:22:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099503, 'x999', '127.0.0.1', '', '2017-01-03 17:22:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099504, 'x999', '127.0.0.1', '', '2017-01-03 17:22:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099505, 'x999', '127.0.0.1', '', '2017-01-03 17:22:36', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099506, 'x404', '127.0.0.1', '', '2017-01-03 17:22:36', '/wachtwoord/wijzigen', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099507, 'x404', '127.0.0.1', '', '2017-01-03 17:22:37', '/favicon.ico', 'http://dev.csrdelft.nl/wachtwoord/wijzigen', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099508, 'x999', '127.0.0.1', '', '2017-01-03 19:31:42', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099509, 'x999', '127.0.0.1', '', '2017-01-03 19:31:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099510, 'x999', '127.0.0.1', '', '2017-01-03 19:31:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099511, 'x999', '127.0.0.1', '', '2017-01-03 19:31:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099512, 'x999', '127.0.0.1', '', '2017-01-03 19:31:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099513, 'x999', '127.0.0.1', '', '2017-01-03 19:31:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099514, 'x999', '127.0.0.1', '', '2017-01-03 19:31:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099515, 'x999', '127.0.0.1', '', '2017-01-03 19:31:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099516, 'x999', '127.0.0.1', '', '2017-01-03 19:31:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099517, 'x999', '127.0.0.1', '', '2017-01-03 19:31:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099518, 'x999', '127.0.0.1', '', '2017-01-03 19:33:13', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099519, 'x999', '127.0.0.1', '', '2017-01-03 19:33:13', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099520, 'x999', '127.0.0.1', '', '2017-01-03 19:33:13', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099521, 'x999', '127.0.0.1', '', '2017-01-03 19:33:13', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099522, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099523, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099524, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099525, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099526, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099527, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099528, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099529, 'x999', '127.0.0.1', '', '2017-01-03 19:33:14', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099530, 'x999', '127.0.0.1', '', '2017-01-03 19:33:27', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099531, 'x999', '127.0.0.1', '', '2017-01-03 19:33:27', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099532, 'x999', '127.0.0.1', '', '2017-01-03 19:33:27', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099533, 'x999', '127.0.0.1', '', '2017-01-03 19:33:27', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099534, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099535, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099536, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099537, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099538, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099539, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099540, 'x999', '127.0.0.1', '', '2017-01-03 19:33:28', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099541, 'x999', '127.0.0.1', '', '2017-01-03 19:33:41', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099542, 'x404', '127.0.0.1', '', '2017-01-03 19:33:42', '/wachtwoord/wijzigen', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099543, 'x404', '127.0.0.1', '', '2017-01-03 19:33:42', '/favicon.ico', 'http://dev.csrdelft.nl/wachtwoord/wijzigen', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099544, 'x404', '127.0.0.1', '', '2017-01-03 19:34:45', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099545, 'x404', '127.0.0.1', '', '2017-01-03 19:34:46', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099546, 'x404', '127.0.0.1', '', '2017-01-03 19:34:52', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099547, 'x404', '127.0.0.1', '', '2017-01-03 19:34:52', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099548, 'x404', '127.0.0.1', '', '2017-01-03 19:34:58', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099549, 'x404', '127.0.0.1', '', '2017-01-03 19:34:58', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099550, 'x404', '127.0.0.1', '', '2017-01-03 19:35:17', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099551, 'x404', '127.0.0.1', '', '2017-01-03 19:35:17', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099552, 'x999', '127.0.0.1', '', '2017-01-03 19:35:42', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099553, 'x999', '127.0.0.1', '', '2017-01-03 19:35:42', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099554, 'x999', '127.0.0.1', '', '2017-01-03 19:35:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099555, 'x999', '127.0.0.1', '', '2017-01-03 19:35:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099556, 'x999', '127.0.0.1', '', '2017-01-03 19:35:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099557, 'x999', '127.0.0.1', '', '2017-01-03 19:35:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099558, 'x999', '127.0.0.1', '', '2017-01-03 19:35:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099559, 'x999', '127.0.0.1', '', '2017-01-03 19:35:43', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099560, 'x999', '127.0.0.1', '', '2017-01-03 19:35:44', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099561, 'x999', '127.0.0.1', '', '2017-01-03 19:35:44', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099562, 'x999', '127.0.0.1', '', '2017-01-03 19:35:51', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099563, 'x999', '127.0.0.1', '', '2017-01-03 19:35:51', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099564, 'x999', '127.0.0.1', '', '2017-01-03 19:35:51', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099565, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099566, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099567, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099568, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099569, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099570, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099571, 'x999', '127.0.0.1', '', '2017-01-03 19:35:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099572, 'x999', '127.0.0.1', '', '2017-01-03 19:35:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099573, 'x999', '127.0.0.1', '', '2017-01-03 19:35:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099574, 'x999', '127.0.0.1', '', '2017-01-03 19:36:02', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099575, 'x404', '127.0.0.1', '', '2017-01-03 19:36:02', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099576, 'x404', '127.0.0.1', '', '2017-01-03 19:36:02', '/favicon.ico', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099577, 'x404', '127.0.0.1', '', '2017-01-03 19:56:34', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099578, 'x404', '127.0.0.1', '', '2017-01-03 19:56:35', '/tools/css.php?l=layout&m=general&1483469794', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099579, 'x404', '127.0.0.1', '', '2017-01-03 19:56:43', '/tools/js.php?l=layout&m=fotoalbum&1483469794', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099580, 'x404', '127.0.0.1', '', '2017-01-03 19:56:45', '/tools/js.php?l=layout&m=general&1483469794', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099581, 'x404', '127.0.0.1', '', '2017-01-03 19:56:51', '/tools/css.php?l=layout&m=fotoalbum&1483469794', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099582, 'x404', '127.0.0.1', '', '2017-01-03 19:56:55', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099583, 'x404', '127.0.0.1', '', '2017-01-03 19:56:55', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099584, 'x404', '127.0.0.1', '', '2017-01-03 19:56:55', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099585, 'x404', '127.0.0.1', '', '2017-01-03 19:56:56', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099586, 'x404', '127.0.0.1', '', '2017-01-03 19:56:56', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099587, 'x404', '127.0.0.1', '', '2017-01-03 19:56:56', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099588, 'x404', '127.0.0.1', '', '2017-01-03 19:56:57', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099589, 'x404', '127.0.0.1', '', '2017-01-03 19:57:14', '/profiel/x404', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099590, 'x404', '127.0.0.1', '', '2017-01-03 19:57:15', '/tools/js.php?l=layout&m=general&1483469811', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099591, 'x404', '127.0.0.1', '', '2017-01-03 19:57:15', '/tools/css.php?l=layout&m=profiel&1483469834', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099592, 'x404', '127.0.0.1', '', '2017-01-03 19:57:16', '/tools/css.php?l=layout&m=grafiek&1483469834', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099593, 'x404', '127.0.0.1', '', '2017-01-03 19:57:16', '/tools/js.php?l=layout&m=grafiek&1483469834', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099594, 'x404', '127.0.0.1', '', '2017-01-03 19:57:17', '/tools/css.php?l=layout&m=general&1483469803', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099595, 'x404', '127.0.0.1', '', '2017-01-03 19:57:17', '/tools/js.php?l=layout&m=profiel&1483469834', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099596, 'x404', '127.0.0.1', '', '2017-01-03 19:57:17', '/plaetjes/knopjes/google.ico', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099597, 'x404', '127.0.0.1', '', '2017-01-03 19:57:17', '/tools/saldodata.php?uid=x404&timespan=11', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099598, 'x404', '127.0.0.1', '', '2017-01-03 19:57:36', '/profiel', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099599, 'x404', '127.0.0.1', '', '2017-01-03 19:57:37', '/tools/js.php?l=layout&m=grafiek&1483469837', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099600, 'x404', '127.0.0.1', '', '2017-01-03 19:57:37', '/tools/css.php?l=layout&m=grafiek&1483469836', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099601, 'x404', '127.0.0.1', '', '2017-01-03 19:57:37', '/tools/css.php?l=layout&m=profiel&1483469836', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099602, 'x404', '127.0.0.1', '', '2017-01-03 19:57:38', '/tools/js.php?l=layout&m=profiel&1483469837', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099603, 'x404', '127.0.0.1', '', '2017-01-03 19:57:38', '/plaetjes/knopjes/google.ico', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099604, 'x404', '127.0.0.1', '', '2017-01-03 19:57:38', '/tools/saldodata.php?uid=x404&timespan=11', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099605, 'x404', '127.0.0.1', '', '2017-01-03 19:57:41', '/profiel/x404/bewerken', 'http://dev.csrdelft.nl/profiel', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099606, 'x404', '127.0.0.1', '', '2017-01-03 19:57:42', '/plaetjes/knopjes/cd-arrow.svg', 'http://dev.csrdelft.nl/tools/css.php?l=layout&m=general&1483469803', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099607, 'x404', '127.0.0.1', '', '2017-01-03 19:57:53', '/beheer', 'http://dev.csrdelft.nl/profiel/x404/bewerken', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099608, 'x404', '127.0.0.1', '', '2017-01-03 19:57:54', '/tools/css.php?l=layout&m=fotoalbum&1483469815', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099609, 'x404', '127.0.0.1', '', '2017-01-03 19:57:54', '/tools/js.php?l=layout&m=fotoalbum&1483469805', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099610, 'x404', '127.0.0.1', '', '2017-01-03 19:57:54', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099611, 'x404', '127.0.0.1', '', '2017-01-03 19:57:55', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
INSERT INTO `log` (`ID`, `uid`, `ip`, `locatie`, `moment`, `url`, `referer`, `useragent`) VALUES
(16099612, 'x404', '127.0.0.1', '', '2017-01-03 19:57:55', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099613, 'x404', '127.0.0.1', '', '2017-01-03 19:57:55', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099614, 'x404', '127.0.0.1', '', '2017-01-03 19:57:55', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099615, 'x404', '127.0.0.1', '', '2017-01-03 19:57:56', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099616, 'x404', '127.0.0.1', '', '2017-01-03 19:57:56', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099617, 'x404', '127.0.0.1', '', '2017-01-03 19:57:59', '/beheer', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099618, 'x404', '127.0.0.1', '', '2017-01-03 19:58:00', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099619, 'x404', '127.0.0.1', '', '2017-01-03 19:58:00', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099620, 'x404', '127.0.0.1', '', '2017-01-03 19:58:00', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099621, 'x404', '127.0.0.1', '', '2017-01-03 19:58:01', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099622, 'x404', '127.0.0.1', '', '2017-01-03 19:58:01', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099623, 'x404', '127.0.0.1', '', '2017-01-03 19:58:01', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099624, 'x404', '127.0.0.1', '', '2017-01-03 19:58:01', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099625, 'x404', '127.0.0.1', '', '2017-01-03 19:58:21', '/forum/recent', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099626, 'x404', '127.0.0.1', '', '2017-01-03 19:58:22', '/tools/css.php?l=layout&m=forum&1483469901', 'http://dev.csrdelft.nl/forum/recent', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099627, 'x404', '127.0.0.1', '', '2017-01-03 19:58:22', '/tools/js.php?l=layout&m=forum&1483469901', 'http://dev.csrdelft.nl/forum/recent', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099628, 'x404', '127.0.0.1', '', '2017-01-03 19:58:23', '/forum/grafiekdata', 'http://dev.csrdelft.nl/forum/recent', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099629, 'x404', '127.0.0.1', '', '2017-01-03 19:58:33', '/profiel/x404', 'http://dev.csrdelft.nl/forum/recent', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099630, 'x404', '127.0.0.1', '', '2017-01-03 19:58:34', '/plaetjes/knopjes/google.ico', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099631, 'x404', '127.0.0.1', '', '2017-01-03 19:58:34', '/tools/saldodata.php?uid=x404&timespan=11', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099632, 'x404', '127.0.0.1', '', '2017-01-03 19:58:37', '/leden/stamboom/x404', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099633, 'x404', '127.0.0.1', '', '2017-01-03 19:58:38', '/tools/js.php?l=layout&m=stamboom&1483469917', 'http://dev.csrdelft.nl/leden/stamboom/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099634, 'x404', '127.0.0.1', '', '2017-01-03 19:58:38', '/tools/css.php?l=layout&m=stamboom&1483469917', 'http://dev.csrdelft.nl/leden/stamboom/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099635, 'x404', '127.0.0.1', '', '2017-01-03 19:58:42', '/profiel/x404', 'http://dev.csrdelft.nl/forum/recent', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099636, 'x404', '127.0.0.1', '', '2017-01-03 19:58:43', '/plaetjes/knopjes/google.ico', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099637, 'x404', '127.0.0.1', '', '2017-01-03 19:58:43', '/tools/saldodata.php?uid=x404&timespan=11', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099638, 'x404', '127.0.0.1', '', '2017-01-03 19:59:40', '/agenda/', 'http://dev.csrdelft.nl/profiel/x404', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099639, 'x404', '127.0.0.1', '', '2017-01-03 19:59:41', '/tools/css.php?l=layout&m=agenda&1483469980', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099640, 'x404', '127.0.0.1', '', '2017-01-03 19:59:41', '/tools/js.php?l=layout&m=agenda&1483469980', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099641, 'x404', '127.0.0.1', '', '2017-01-03 19:59:41', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099642, 'x404', '127.0.0.1', '', '2017-01-03 19:59:55', '/agenda/maand/2018/1', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099643, 'x404', '127.0.0.1', '', '2017-01-03 19:59:56', '/tools/css.php?l=layout&m=agenda&1483469981', 'http://dev.csrdelft.nl/agenda/maand/2018/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099644, 'x404', '127.0.0.1', '', '2017-01-03 19:59:56', '/tools/js.php?l=layout&m=agenda&1483469981', 'http://dev.csrdelft.nl/agenda/maand/2018/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099645, 'x404', '127.0.0.1', '', '2017-01-03 19:59:56', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/maand/2018/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099646, 'x404', '127.0.0.1', '', '2017-01-03 20:00:00', '/agenda/maand/2021/1', 'http://dev.csrdelft.nl/agenda/maand/2018/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099647, 'x404', '127.0.0.1', '', '2017-01-03 20:00:01', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/maand/2021/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099648, 'x404', '127.0.0.1', '', '2017-01-03 20:00:32', '/agenda/maand/2016/1', 'http://dev.csrdelft.nl/agenda/maand/2021/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099649, 'x404', '127.0.0.1', '', '2017-01-03 20:00:32', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/maand/2016/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099650, 'x404', '127.0.0.1', '', '2017-01-03 20:02:20', '/agenda/maand/2015/12', 'http://dev.csrdelft.nl/agenda/maand/2016/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099651, 'x404', '127.0.0.1', '', '2017-01-03 20:02:20', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/maand/2015/12', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099652, 'x404', '127.0.0.1', '', '2017-01-03 20:02:25', '/agenda/maand/2016/1', 'http://dev.csrdelft.nl/agenda/maand/2015/12', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099653, 'x404', '127.0.0.1', '', '2017-01-03 20:02:26', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/maand/2016/1', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099654, 'x999', '127.0.0.1', '', '2017-01-09 10:41:46', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099655, 'x999', '127.0.0.1', '', '2017-01-09 10:41:47', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099656, 'x999', '127.0.0.1', '', '2017-01-09 10:41:48', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099657, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099658, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099659, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099660, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099661, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099662, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099663, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099664, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099665, 'x999', '127.0.0.1', '', '2017-01-09 10:41:49', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099666, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099667, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099668, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099669, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099670, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099671, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099672, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099673, 'x999', '127.0.0.1', '', '2017-01-09 10:41:52', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099674, 'x999', '127.0.0.1', '', '2017-01-09 10:41:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099675, 'x999', '127.0.0.1', '', '2017-01-09 10:41:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099676, 'x999', '127.0.0.1', '', '2017-01-09 10:41:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099677, 'x999', '127.0.0.1', '', '2017-01-09 10:42:56', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099678, 'x999', '127.0.0.1', '', '2017-01-09 10:42:56', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099679, 'x999', '127.0.0.1', '', '2017-01-09 10:42:56', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099680, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099681, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099682, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099683, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099684, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099685, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099686, 'x999', '127.0.0.1', '', '2017-01-09 10:42:57', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099687, 'x999', '127.0.0.1', '', '2017-01-09 10:42:58', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099688, 'x999', '127.0.0.1', '', '2017-01-09 10:42:58', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099689, 'x999', '127.0.0.1', '', '2017-01-09 10:43:03', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099690, 'x404', '127.0.0.1', '', '2017-01-09 10:43:03', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099691, 'x404', '127.0.0.1', '', '2017-01-09 10:43:04', '/tools/css.php?l=layout&m=fotoalbum&1483469815', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099692, 'x404', '127.0.0.1', '', '2017-01-09 10:43:04', '/tools/css.php?l=layout&m=general&1483469803', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099693, 'x404', '127.0.0.1', '', '2017-01-09 10:43:04', '/tools/js.php?l=layout&m=general&1483469811', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099694, 'x404', '127.0.0.1', '', '2017-01-09 10:43:05', '/tools/js.php?l=layout&m=fotoalbum&1483469805', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099695, 'x404', '127.0.0.1', '', '2017-01-09 10:43:05', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099696, 'x404', '127.0.0.1', '', '2017-01-09 10:43:06', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099697, 'x404', '127.0.0.1', '', '2017-01-09 10:43:06', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099698, 'x404', '127.0.0.1', '', '2017-01-09 10:43:06', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099699, 'x404', '127.0.0.1', '', '2017-01-09 10:43:06', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099700, 'x404', '127.0.0.1', '', '2017-01-09 10:43:07', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099701, 'x404', '127.0.0.1', '', '2017-01-09 10:43:07', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099702, 'x404', '127.0.0.1', '', '2017-01-09 10:43:26', '/beheer', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099703, 'x404', '127.0.0.1', '', '2017-01-09 10:43:27', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099704, 'x404', '127.0.0.1', '', '2017-01-09 10:43:27', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099705, 'x404', '127.0.0.1', '', '2017-01-09 10:43:27', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099706, 'x404', '127.0.0.1', '', '2017-01-09 10:43:28', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099707, 'x404', '127.0.0.1', '', '2017-01-09 10:43:28', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099708, 'x404', '127.0.0.1', '', '2017-01-09 10:43:28', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099709, 'x404', '127.0.0.1', '', '2017-01-09 10:43:28', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099710, 'x404', '127.0.0.1', '', '2017-01-09 10:43:33', '/tools/naamsuggesties/leden/?status=LEDEN&q=maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099711, 'x404', '127.0.0.1', '', '2017-01-09 10:43:33', '/groepen/woonoorden/zoeken/?q=maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099712, 'x404', '127.0.0.1', '', '2017-01-09 10:43:34', '/forum/titelzoeken/?q=maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099713, 'x404', '127.0.0.1', '', '2017-01-09 10:43:34', '/agenda/zoeken/?q=maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099714, 'x404', '127.0.0.1', '', '2017-01-09 10:43:34', '/groepen/commissies/zoeken/?q=maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099715, 'x404', '127.0.0.1', '', '2017-01-09 10:43:34', '/tools/wikisuggesties/?q=maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099716, 'x404', '127.0.0.1', '', '2017-01-09 10:43:35', '/forum/zoeken/maaltijden', 'http://dev.csrdelft.nl/beheer', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099717, 'x404', '127.0.0.1', '', '2017-01-09 10:43:35', '/tools/css.php?l=layout&m=forum&1483469902', 'http://dev.csrdelft.nl/forum/zoeken/maaltijden', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099718, 'x404', '127.0.0.1', '', '2017-01-09 10:43:36', '/tools/js.php?l=layout&m=forum&1483469903', 'http://dev.csrdelft.nl/forum/zoeken/maaltijden', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099719, 'x404', '127.0.0.1', '', '2017-01-09 10:43:42', '/maaltijden', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099720, 'x404', '127.0.0.1', '', '2017-01-09 10:43:43', '/tools/js.php?l=layout&m=maalcie&1483955022', 'http://dev.csrdelft.nl/maaltijden', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099721, 'x404', '127.0.0.1', '', '2017-01-09 10:43:43', '/tools/css.php?l=layout&m=maalcie&1483955022', 'http://dev.csrdelft.nl/maaltijden', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099722, 'x404', '127.0.0.1', '', '2017-01-09 10:43:50', '/', 'http://dev.csrdelft.nl/maaltijden', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099723, 'x404', '127.0.0.1', '', '2017-01-09 10:43:51', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099724, 'x404', '127.0.0.1', '', '2017-01-09 10:43:51', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099725, 'x404', '127.0.0.1', '', '2017-01-09 10:43:51', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099726, 'x404', '127.0.0.1', '', '2017-01-09 10:43:51', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099727, 'x404', '127.0.0.1', '', '2017-01-09 10:43:52', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099728, 'x404', '127.0.0.1', '', '2017-01-09 10:43:52', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099729, 'x404', '127.0.0.1', '', '2017-01-09 10:43:52', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099730, 'x999', '127.0.0.1', '', '2017-01-09 11:08:52', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099731, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/tools/js.php?l=layout-owee&m=general&1483459289', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099732, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/tools/css.php?l=layout-owee&m=general&1483459287', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099733, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic01.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099734, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic02.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099735, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic04.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099736, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic03.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099737, 'x999', '127.0.0.1', '', '2017-01-09 11:08:53', '/plaetjes/fotoalbum/Voorpagina/Extern/pic06.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099738, 'x999', '127.0.0.1', '', '2017-01-09 11:08:54', '/plaetjes/fotoalbum/Voorpagina/Extern/pic05.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099739, 'x999', '127.0.0.1', '', '2017-01-09 11:08:54', '/plaetjes/fotoalbum/Voorpagina/Extern/pic07.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099740, 'x999', '127.0.0.1', '', '2017-01-09 11:08:54', '/plaetjes/fotoalbum/Voorpagina/Extern/pic08.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099741, 'x999', '127.0.0.1', '', '2017-01-09 11:08:54', '/plaetjes/fotoalbum/Voorpagina/Extern/pic09.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099742, 'x999', '127.0.0.1', '', '2017-01-09 11:09:11', '/login', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099743, 'x404', '127.0.0.1', '', '2017-01-09 11:09:11', '/', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099744, 'x404', '127.0.0.1', '', '2017-01-09 11:09:12', '/tools/js.php?l=layout&m=general&1483469811', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099745, 'x404', '127.0.0.1', '', '2017-01-09 11:09:12', '/tools/js.php?l=layout&m=fotoalbum&1483469805', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099746, 'x404', '127.0.0.1', '', '2017-01-09 11:09:12', '/tools/css.php?l=layout&m=general&1483469803', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099747, 'x404', '127.0.0.1', '', '2017-01-09 11:09:12', '/tools/css.php?l=layout&m=fotoalbum&1483469815', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099748, 'x404', '127.0.0.1', '', '2017-01-09 11:09:12', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099749, 'x404', '127.0.0.1', '', '2017-01-09 11:09:13', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099750, 'x404', '127.0.0.1', '', '2017-01-09 11:09:13', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099751, 'x404', '127.0.0.1', '', '2017-01-09 11:09:13', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099752, 'x404', '127.0.0.1', '', '2017-01-09 11:09:13', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099753, 'x404', '127.0.0.1', '', '2017-01-09 11:09:14', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099754, 'x404', '127.0.0.1', '', '2017-01-09 11:09:14', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099755, 'x404', '127.0.0.1', '', '2017-01-09 11:09:14', '/fotoalbum/2016-2017', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099756, 'x404', '127.0.0.1', '', '2017-01-09 11:09:14', '/fotoalbum', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099757, 'x404', '127.0.0.1', '', '2017-01-09 11:09:15', '/plaetjes/_geen_thumb.jpg', 'http://dev.csrdelft.nl/fotoalbum', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099758, 'x404', '127.0.0.1', '', '2017-01-09 11:09:16', '/fotoalbum/2014-2015/', 'http://dev.csrdelft.nl/fotoalbum', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099759, 'x404', '127.0.0.1', '', '2017-01-09 11:09:17', '/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099760, 'x404', '127.0.0.1', '', '2017-01-09 11:09:21', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099761, 'x404', '127.0.0.1', '', '2017-01-09 11:09:26', '/fotoalbum/verwerken/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099762, 'x404', '127.0.0.1', '', '2017-01-09 11:09:29', '/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099763, 'x404', '127.0.0.1', '', '2017-01-09 11:09:32', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099764, 'x404', '127.0.0.1', '', '2017-01-09 11:09:43', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099765, 'x404', '127.0.0.1', '', '2017-01-09 11:09:43', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099766, 'x404', '127.0.0.1', '', '2017-01-09 11:09:44', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099767, 'x404', '127.0.0.1', '', '2017-01-09 11:09:46', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099768, 'x404', '127.0.0.1', '', '2017-01-09 11:09:47', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099769, 'x404', '127.0.0.1', '', '2017-01-09 11:09:47', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099770, 'x404', '127.0.0.1', '', '2017-01-09 11:09:48', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099771, 'x404', '127.0.0.1', '', '2017-01-09 11:09:49', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099772, 'x404', '127.0.0.1', '', '2017-01-09 11:09:49', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099773, 'x404', '127.0.0.1', '', '2017-01-09 11:09:50', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099774, 'x404', '127.0.0.1', '', '2017-01-09 11:09:50', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099775, 'x404', '127.0.0.1', '', '2017-01-09 11:09:51', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099776, 'x404', '127.0.0.1', '', '2017-01-09 11:09:51', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099777, 'x404', '127.0.0.1', '', '2017-01-09 11:09:52', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099778, 'x404', '127.0.0.1', '', '2017-01-09 11:09:53', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099779, 'x404', '127.0.0.1', '', '2017-01-09 11:09:54', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099780, 'x404', '127.0.0.1', '', '2017-01-09 11:09:54', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099781, 'x404', '127.0.0.1', '', '2017-01-09 11:09:54', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099782, 'x404', '127.0.0.1', '', '2017-01-09 11:09:55', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099783, 'x404', '127.0.0.1', '', '2017-01-09 11:09:56', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099784, 'x404', '127.0.0.1', '', '2017-01-09 11:09:58', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099785, 'x404', '127.0.0.1', '', '2017-01-09 11:09:59', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099786, 'x404', '127.0.0.1', '', '2017-01-09 11:09:59', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099787, 'x404', '127.0.0.1', '', '2017-01-09 11:10:05', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099788, 'x404', '127.0.0.1', '', '2017-01-09 11:10:06', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099789, 'x404', '127.0.0.1', '', '2017-01-09 11:10:06', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099790, 'x404', '127.0.0.1', '', '2017-01-09 11:10:06', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099791, 'x404', '127.0.0.1', '', '2017-01-09 11:10:07', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099792, 'x404', '127.0.0.1', '', '2017-01-09 11:10:08', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099793, 'x404', '127.0.0.1', '', '2017-01-09 11:10:09', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099794, 'x404', '127.0.0.1', '', '2017-01-09 11:10:12', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099795, 'x404', '127.0.0.1', '', '2017-01-09 11:10:15', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099796, 'x404', '127.0.0.1', '', '2017-01-09 11:10:16', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099797, 'x404', '127.0.0.1', '', '2017-01-09 11:10:20', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
INSERT INTO `log` (`ID`, `uid`, `ip`, `locatie`, `moment`, `url`, `referer`, `useragent`) VALUES
(16099798, 'x404', '127.0.0.1', '', '2017-01-09 11:10:21', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099799, 'x404', '127.0.0.1', '', '2017-01-09 11:10:22', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099800, 'x404', '127.0.0.1', '', '2017-01-09 11:10:23', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099801, 'x404', '127.0.0.1', '', '2017-01-09 11:10:24', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099802, 'x404', '127.0.0.1', '', '2017-01-09 11:10:27', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099803, 'x404', '127.0.0.1', '', '2017-01-09 11:10:28', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099804, 'x404', '127.0.0.1', '', '2017-01-09 11:10:29', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099805, 'x404', '127.0.0.1', '', '2017-01-09 11:10:29', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099806, 'x404', '127.0.0.1', '', '2017-01-09 11:10:30', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099807, 'x404', '127.0.0.1', '', '2017-01-09 11:10:33', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099808, 'x404', '127.0.0.1', '', '2017-01-09 11:10:33', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099809, 'x404', '127.0.0.1', '', '2017-01-09 11:10:35', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099810, 'x404', '127.0.0.1', '', '2017-01-09 11:10:36', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099811, 'x404', '127.0.0.1', '', '2017-01-09 11:10:37', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099812, 'x404', '127.0.0.1', '', '2017-01-09 11:10:38', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099813, 'x404', '127.0.0.1', '', '2017-01-09 11:10:39', '/fotoalbum/gettags/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099814, 'x404', '127.0.0.1', '', '2017-01-09 11:10:46', '/agenda/', 'http://dev.csrdelft.nl/fotoalbum/2014-2015/14-11-27%20Feest%20Helemaal%20Kwijt%20in%20de%20IJstijd/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099815, 'x404', '127.0.0.1', '', '2017-01-09 11:10:46', '/tools/css.php?l=layout&m=agenda&1483469981', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099816, 'x404', '127.0.0.1', '', '2017-01-09 11:10:47', '/tools/js.php?l=layout&m=agenda&1483469981', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099817, 'x404', '127.0.0.1', '', '2017-01-09 11:10:47', '/plaetjes/knopjes/ical.gif', 'http://dev.csrdelft.nl/agenda/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099818, 'x404', '127.0.0.1', '', '2017-01-09 11:10:54', '/', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099819, 'x404', '127.0.0.1', '', '2017-01-09 11:10:55', '/plaetjes/banners/dosign.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099820, 'x404', '127.0.0.1', '', '2017-01-09 11:10:55', '/plaetjes/banners/mechdes.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099821, 'x404', '127.0.0.1', '', '2017-01-09 11:10:55', '/plaetjes/banners/STC-groep-banner.gif', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099822, 'x404', '127.0.0.1', '', '2017-01-09 11:10:56', '/plaetjes/banners/maxilia.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099823, 'x404', '127.0.0.1', '', '2017-01-09 11:10:56', '/plaetjes/banners/galjema_banner.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099824, 'x404', '127.0.0.1', '', '2017-01-09 11:10:56', '/plaetjes/banners/Zoover.jpg', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'),
(16099825, 'x404', '127.0.0.1', '', '2017-01-09 11:10:56', '/plaetjes/banners/TU_Delft_logo_Black.png', 'http://dev.csrdelft.nl/', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `logaggregated`
--

DROP TABLE IF EXISTS `logaggregated`;
CREATE TABLE IF NOT EXISTS `logaggregated` (
  `soort` enum('maand','jaar','ip','url') NOT NULL DEFAULT 'maand',
  `waarde` varchar(255) NOT NULL,
  `pageviews` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `login_remember`
--

DROP TABLE IF EXISTS `login_remember`;
CREATE TABLE IF NOT EXISTS `login_remember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `remember_since` datetime NOT NULL,
  `device_name` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `lock_ip` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `login_sessions`
--

DROP TABLE IF EXISTS `login_sessions`;
CREATE TABLE IF NOT EXISTS `login_sessions` (
  `session_hash` varchar(255) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `login_moment` datetime NOT NULL,
  `expire` datetime NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `lock_ip` tinyint(1) NOT NULL,
  `authentication_method` enum('ut','ct','pl','rpl','plaott') NOT NULL,
  PRIMARY KEY (`session_hash`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `login_sessions`
--

INSERT INTO `login_sessions` (`session_hash`, `uid`, `login_moment`, `expire`, `user_agent`, `ip`, `lock_ip`, `authentication_method`) VALUES
('0aec58a6825f097c2dec274c248e7e080158ac283b6a7621430c1a14a1d75422d17a7fde03b8043572be2c504cbd0eefc57dc332c143aaf925e33c212108dc7e', 'x404', '2017-01-09 10:43:03', '2017-01-09 11:07:52', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl'),
('0dc69c2a22973583a99c5f184229faf78dfea56689716d1594caaba40de6aa2326c286383502e4a73a2dd21e73112a407c7683f986d657aac5f8b4627532b93a', 'x404', '2017-01-03 19:36:02', '2017-01-03 20:26:26', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl'),
('4b0f07e4dbb447a427a58a6fad39689ab681b41dae4f63bbffd4cd27abf76253850c4f8528b915a3e1a8f149717b247e647f1e48e502fdb28883210a00740f7b', 'x404', '2017-01-09 11:09:11', '2017-01-09 11:34:56', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl'),
('9063fd61df990cd59f55fd0bff6a948f0fc6485e77b711c2bedf79ee77ebaca5a5d8ef453d945bf08b78178e531ddfa304b2f264f4eed7d4ad359c536ae596a1', 'x404', '2017-01-03 17:18:41', '2017-01-03 17:44:33', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl'),
('a5aa1fa84fb50462ba3efb7d23f095da349a90515b71635f74a0e770c510c153ac10b6f087a8b15852a10314ca7913faac6deb1ee8a8d519c703421fdce1692a', 'x404', '2017-01-03 17:22:36', '2017-01-03 17:46:37', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl'),
('d1930554ca897a767048e8f1c9eda2812875113563057dab583183a06029cf30ae9fdaf2231513e68a5a71dd8edcf7351fe6454f55aa6a4a292e52933bc8067b', 'x404', '2017-01-03 19:33:41', '2017-01-03 19:59:17', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl'),
('e7fe3b14ae0db45d3d15e35eff635f51c7da2b4a9952fc75040532c93dabba0f386d888f8adf8fbcd99dde552dd1c8a7a01bd3af7eb876c94bf03d1077a9bf69', 'x404', '2017-01-03 17:20:49', '2017-01-03 17:44:49', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '127.0.0.1', 0, 'pl');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mededelingcategorie`
--

DROP TABLE IF EXISTS `mededelingcategorie`;
CREATE TABLE IF NOT EXISTS `mededelingcategorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `prioriteit` int(11) NOT NULL,
  `permissie` enum('P_NEWS_POST','P_NEWS_MOD') NOT NULL,
  `plaatje` varchar(255) NOT NULL,
  `beschrijving` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mededelingen`
--

DROP TABLE IF EXISTS `mededelingen`;
CREATE TABLE IF NOT EXISTS `mededelingen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `vervaltijd` datetime DEFAULT NULL COMMENT 'Wanneer vervalt hij?',
  `titel` varchar(255) NOT NULL,
  `tekst` text NOT NULL,
  `categorie` int(11) NOT NULL,
  `zichtbaarheid` varchar(255) NOT NULL,
  `prioriteit` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `doelgroep` varchar(255) NOT NULL,
  `verborgen` tinyint(1) NOT NULL,
  `verwijderd` tinyint(1) NOT NULL,
  `plaatje` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `zichtbaarheid` (`zichtbaarheid`),
  KEY `datum` (`datum`),
  KEY `vervaltijd` (`vervaltijd`),
  KEY `prioriteit` (`prioriteit`)
) ENGINE=MyISAM AUTO_INCREMENT=744 DEFAULT CHARSET=utf8 COMMENT='Nieuwsberichten';

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `memory_scores`
--

DROP TABLE IF EXISTS `memory_scores`;
CREATE TABLE IF NOT EXISTS `memory_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tijd` int(11) NOT NULL,
  `beurten` int(11) NOT NULL,
  `goed` int(11) NOT NULL,
  `groep` text NOT NULL,
  `eerlijk` tinyint(1) NOT NULL,
  `door_uid` varchar(4) NOT NULL,
  `wanneer` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `menus`
--

DROP TABLE IF EXISTS `menus`;
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
) ENGINE=InnoDB AUTO_INCREMENT=1194 DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `menus`
--

INSERT INTO `menus` (`item_id`, `parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES
(0, 0, 0, ' ', ' ', NULL, 0),
(1, 0, 0, 'x999', '/menubeheer/beheer/x999', 'x999', 1),
(2, 0, 0, 'x404', '/menubeheer/beheer/x404', 'x404', 1),
(3, 2, 2, 'Beheertools', '/beheer', 'P_LOGGED_IN', 1),
(4, 1, 2, 'Actueel', '/actueel', 'P_PUBLIC', 1),
(5, 1, 4, 'Communicatie', '/communicatie', 'P_PUBLIC', 1),
(6, 5, 0, 'Contact', '/contact', 'P_PUBLIC', 1),
(7, 2, 3, 'Admin', '/beheer', 'P_ADMIN', 1),
(10, 0, 0, 'remotefora', '#', 'P_LOGGED_IN', 1),
(11, 10, 0, 'Broeders', '#', 'P_LOGGED_IN', 1),
(12, 10, 0, 'Zusters', '#', 'P_LOGGED_IN', 1),
(18, 4, 0, 'Mededelingen', '/mededelingen', 'P_PUBLIC', 1),
(19, 4, 0, 'Courant', '/courant', 'P_MAIL_POST', 1),
(20, 4, 0, 'Fotoalbum', '/fotoalbum', 'P_PUBLIC', 1),
(21, 4, 0, 'Maaltijden', '/maaltijden', 'P_PUBLIC', 1),
(22, 1, 0, 'Groepen', '/groepen', 'P_LEDEN_READ', 1),
(23, 4, 0, 'Eetplan', '/eetplan', 'P_MAAL_IK', 1),
(25, 5, 0, 'Ledenlijst', '/ledenlijst', 'P_LEDEN_READ', 1),
(26, 1, 3, 'Forum', '/forum', 'P_FORUM_READ', 1),
(27, 5, 0, 'IRC', '/irc', 'P_PUBLIC', 1),
(28, 5, 0, 'Documenten', '/documenten', 'P_DOCS_READ', 1),
(29, 2, 1, 'Instellingen', '/instellingen', 'P_LOGGED_IN', 1),
(30, 33, 0, 'Externe links', '/contact/extern', 'P_PUBLIC', 1),
(31, 33, 0, 'Sponsoring', '/contact/sponsoring', 'P_PUBLIC', 1),
(33, 5, 1, 'Overig', '/contact', 'P_PUBLIC', 1),
(34, 33, 0, 'Kamers', '/contact/kamers', 'P_PUBLIC', 1),
(36, 4, 0, 'Agenda', '/agenda', 'P_AGENDA_READ', 1),
(42, 5, 0, 'Vormingsbank', '/wiki/vormingsbank:hoofdpagina', 'P_PUBLIC', 1),
(47, 33, 0, 'LoopbaanoriÃ«ntatie', '/orientatie', 'P_PUBLIC', 1),
(48, 3, 0, 'Noviet toevoegen', '/profiel/2016/nieuw/noviet', 'P_LEDEN_MOD,Commissie:NovCie', 1),
(53, 5, 0, 'Wiki', '/wiki', 'P_PUBLIC', 1),
(56, 33, 0, 'Bedrijven', '/contact/bedrijven', 'P_PUBLIC', 1),
(60, 4, 0, 'Bijbelrooster', '/bijbelrooster', 'P_PUBLIC', 1),
(61, 5, 0, 'Bibliotheek', '/bibliotheek', 'P_PUBLIC', 1),
(66, 4, 0, 'Corvee', '/corvee', 'P_CORVEE_IK', 1),
(73, 3, 0, 'Roodschopper', '/tools/roodschopper.php', 'Commissie:SocCie:Fiscus,Commissie:MaalCie:Fiscus', 1),
(74, 3, 0, 'Streeplijstgenerator', '/tools/streeplijst.php', 'P_LEDEN_READ', 1),
(76, 3, 0, 'Saldostatistieken SocCie', '/pagina/saldostatistiekensoccie', 'Commissie:SocCie:Praeses,Commissie:SocCie:Fiscus,Commissie:SocCie:Provisor', 1),
(80, 21, 10, 'Maaltijdenbeheer', '/maaltijdenbeheer', 'P_MAAL_MOD', 1),
(81, 66, 10, 'Corveebeheer', '/corveebeheer', 'P_CORVEE_MOD', 1),
(83, 25, 0, 'Verjaardagen', '/leden/verjaardagen', 'P_VERJAARDAGEN', 1),
(85, 22, 0, 'Verticalen', '/groepen/verticalen', 'P_PUBLIC', 1),
(87, 22, 0, 'Commissies', '/groepen/commissies', 'P_PUBLIC', 1),
(88, 22, 0, 'Lichtingen', '/groepen/lichtingen/1950', 'P_PUBLIC', 1),
(89, 22, 0, 'Besturen', '/groepen/besturen/1', 'P_PUBLIC', 1),
(90, 22, 0, 'Woonoorden &amp; huizen', '/groepen/woonoorden', 'P_PUBLIC', 1),
(91, 22, 0, 'Werkgroepen', '/groepen/werkgroepen', 'P_PUBLIC', 1),
(92, 22, 0, 'Onderverenigingen', '/groepen/onderverenigingen', 'P_PUBLIC', 1),
(93, 3, 0, 'Menubeheer', '/menubeheer/beheer/main', 'P_ADMIN', 1),
(94, 2, 0, 'Mijn profiel', '/profiel', 'P_LOGGED_IN', 1),
(95, 26, 0, 'Recent gewijzigd', '/forum/recent', 'P_FORUM_READ', 1),
(98, 3, 0, 'Instellingenbeheer', '/instellingenbeheer', 'P_LOGGED_IN', 1),
(100, 21, 1, 'Mijn abonnementen', '/maaltijdenabonnementen', 'P_MAAL_IK', 1),
(102, 66, 0, 'Mijn voorkeuren', '/corveevoorkeuren', 'P_CORVEE_IK', 1),
(103, 21, 2, 'Allergie en diÃ«et', '/corveevoorkeuren', 'P_LOGGED_IN', 1),
(104, 26, 0, 'Belangrijk recent gewijzigd', '/forum/recent/belangrijk', 'P_FORUM_READ', 1),
(105, 25, 0, 'Ledenmemory', '/leden/memory', 'P_OUDLEDEN_READ', 1),
(106, 33, 0, 'English info', '/english', 'P_PUBLIC', 1),
(107, 3, 0, 'Aanschafketzers (oude ketzers)', '/groepen/ketzers/beheren', 'P_LOGGED_IN', 1),
(110, 22, 0, 'Kringen', '/groepen/kringen', 'P_PUBLIC', 1),
(113, 66, 0, 'Corveerooster', '/corveerooster', 'P_PUBLIC', 1),
(119, 3, 0, 'Opgeslagen queries', '/tools/query.php', 'P_LOGGED_IN', 1),
(448, 4, 0, 'Dies', '/dies', 'P_PUBLIC', 1),
(544, 3, 0, 'Commissievoorkeuren', '/commissievoorkeuren', 'bestuur', 1),
(596, 66, 0, 'Mijn corveeoverzicht', '/corvee', 'P_CORVEE_IK', 1),
(597, 21, 0, 'Maaltijdenketzer', '/maaltijdenketzer', 'P_MAAL_IK', 1),
(598, 25, 0, 'Zoeken in ledenlijst', '/ledenlijst', 'P_LEDEN_READ', 1),
(600, 5, 0, 'PandWiki', '/wiki/onderhoudcie:pand:hoofdpagina', 'P_PUBLIC', 1),
(634, 66, 11, 'Functies &amp; kwalificaties', '/corveefuncties', 'P_CORVEE_MOD', 1),
(635, 66, 11, 'Instellingen', '/instellingenbeheer/module/corvee', 'P_CORVEE_MOD', 1),
(636, 66, 11, 'Voorkeurenbeheer', '/corveevoorkeurenbeheer', 'P_CORVEE_MOD', 1),
(637, 66, 11, 'Puntenbeheer', '/corveepuntenbeheer', 'P_CORVEE_MOD', 1),
(638, 66, 11, 'Vrijstellingen', '/corveevrijstellingen', 'P_CORVEE_MOD', 1),
(639, 21, 12, 'Maaltijdenarchief', '/maaltijdenbeheer/archief', 'P_MAAL_MOD', 1),
(641, 21, 13, 'Instellingen', '/instellingenbeheer/module/maaltijden', 'P_MAAL_MOD', 1),
(642, 21, 14, 'Abonnementenbeheer', '/maaltijdenabonnementenbeheer', 'P_MAAL_MOD', 1),
(643, 21, 15, 'MaalCie saldi', '/maaltijdenmaalciesaldi', 'P_MAAL_MOD', 1),
(644, 3, 0, 'Peilingenbeheer', '/peilingen/beheer', 'P_ADMIN,bestuur,commissie:BASFCie', 1),
(645, 33, 1, 'Webmail', 'https://webmail.knorrie.org', 'P_LOGGED_IN', 1),
(646, 7, 0, 'Maillijstenbeheer', 'https://lists.knorrie.org/mailman/listinfo', 'P_ADMIN', 1),
(647, 7, 0, 'Mailboxen en aliassen', 'https://service.mendix.nl/', 'P_ADMIN', 1),
(648, 3, 0, 'Enquetebeheer', '/enquete/admin/admin.php', 'P_LOGGED_IN', 1),
(649, 5, 0, 'Enquetes', '/enquete', 'P_LOGGED_IN', 1),
(650, 7, 0, 'PHP info', '/tools/phpinfo.php', 'P_ADMIN', 1),
(651, 7, 0, 'Memcache statistieken', '/tools/memcachestats.php', 'P_ADMIN', 1),
(653, 7, 0, 'Dump database table', '/tools/dump.php', 'P_ADMIN', 1),
(654, 4, 0, 'Activiteiten', '/groepen/activiteiten', 'P_PUBLIC', 1),
(656, 22, 1, 'Overig', '/groepen/overig', 'P_PUBLIC', 1),
(657, 3, 0, 'Lid toevoegen', '/pagina/lidtoevoegen', 'P_LEDEN_MOD', 1),
(658, 7, 0, 'Eetplanbeheer', '/eetplan/beheer', 'P_ADMIN,commissie:NovCie', 1),
(659, 25, 0, 'Verticale emaillijsten', '/tools/verticalelijsten.php', 'P_LEDEN_READ', 1),
(661, 3, 0, 'Saldostatistieken MaalCie', '/pagina/saldostatistiekenmaalcie', 'Commissie:MaalCie:Praeses,Commissie:MaalCie:Fiscus', 1),
(663, 7, 0, 'Log statistieken', '/tools/stats.php', 'P_ADMIN', 1),
(667, 2, 99, 'Log uit', '/logout', 'P_LOGGED_IN', 1),
(677, 7, 0, 'Sync LDAP', '/tools/syncldap.php', 'P_ADMIN', 1),
(778, 654, 1, 'Vereniging', '/groepen/activiteiten/overzicht/vereniging', 'P_PUBLIC', 1),
(779, 654, 10, 'Onderverenigingen', '/groepen/activiteiten/overzicht/ondervereniging', 'P_PUBLIC', 1),
(780, 654, 5, 'Sjaarsacties', '/groepen/activiteiten/overzicht/sjaarsactie', 'P_PUBLIC', 1),
(781, 654, 4, 'OWee', '/groepen/activiteiten/overzicht/owee', 'P_PUBLIC', 1),
(782, 654, 3, 'Dies', '/groepen/activiteiten/overzicht/dies', 'P_PUBLIC', 1),
(784, 654, 11, 'IFES', '/groepen/activiteiten/overzicht/ifes', 'P_PUBLIC', 1),
(785, 87, 1, 'Commissies', '/groepen/commissies/overzicht/c', 'P_PUBLIC', 1),
(786, 87, 2, 'Bestuurscommissies', '/groepen/commissies/overzicht/b', 'P_PUBLIC', 1),
(787, 87, 3, 'SjaarCies', '/groepen/commissies/overzicht/s', 'P_PUBLIC', 1),
(788, 87, 4, 'Extern', '/groepen/commissies/overzicht/e', 'P_PUBLIC', 1),
(792, 654, 6, 'Lichting', '/groepen/activiteiten/overzicht/lichting', 'P_PUBLIC', 1),
(793, 654, 7, 'Verticale', '/groepen/activiteiten/overzicht/verticale', 'P_PUBLIC', 1),
(794, 654, 8, 'Kring', '/groepen/activiteiten/overzicht/kring', 'P_PUBLIC', 1),
(795, 654, 9, 'Huis', '/groepen/activiteiten/overzicht/huis', 'P_PUBLIC', 1),
(796, 654, 12, 'Extern', '/groepen/activiteiten/overzicht/extern', 'P_PUBLIC', 1),
(824, 12, 0, 'CSV Alpha', 'http://wesp.snt.utwente.nl/~alpha/prlo/index.php/forum/index?func=listcat&template=atomic', 'P_LOGGED_IN', 1),
(825, 12, 0, 'Lux Ad Mosam', 'http://www.luxadmosam.nl/forum/', 'P_LOGGED_IN', 1),
(826, 11, 0, 'VGSD', 'http://vgsd.nl/forum/', 'P_LOGGED_IN', 1),
(827, 11, 0, 'C.S.F.R.', 'http://csfr-delft.nl/forum/', 'P_LOGGED_IN', 1),
(828, 11, 0, 'Navigators Delft', '#', 'P_LOGGED_IN', 1),
(829, 12, 0, 'S.S.R.-N.U.', '#', 'P_LOGGED_IN', 1),
(830, 11, 0, 'RKJ', 'http://www.rkjdelft.nl/forum', 'P_LOGGED_IN', 1),
(848, 3, 0, 'Beheerders-lijst', '/tools/admin.php', 'P_LOGGED_IN', 1),
(920, 0, 0, 'sponsors', '/wiki/leden:mogelijkheden', 'P_LOGGED_IN', 1),
(921, 920, 0, 'Online aankopen', 'https://www.sponsorkliks.com/products/shops.php?club=3605&amp;cn=nl&amp;ln=nl', 'P_LOGGED_IN', 1),
(1135, 920, 1, 'Bijbaan', '/wiki/leden:mogelijkheden#bijbaan_mijnstudent', 'P_LOGGED_IN', 1),
(1136, 920, 2, 'Baan', '/wiki/leden:mogelijkheden#baan_door_detacheerders', 'P_LOGGED_IN', 1),
(1137, 920, 3, 'Rijbewijs', '/wiki/leden:mogelijkheden#rijbewijs', 'P_LOGGED_IN', 1),
(1193, 0, 0, 'main', '', 'x404', 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_aanmeldingen`
--

DROP TABLE IF EXISTS `mlt_aanmeldingen`;
CREATE TABLE IF NOT EXISTS `mlt_aanmeldingen` (
  `maaltijd_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `aantal_gasten` int(11) NOT NULL,
  `gasten_eetwens` varchar(255) DEFAULT NULL,
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

DROP TABLE IF EXISTS `mlt_abonnementen`;
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

DROP TABLE IF EXISTS `mlt_archief`;
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
-- Tabelstructuur voor tabel `mlt_beoordelingen`
--

DROP TABLE IF EXISTS `mlt_beoordelingen`;
CREATE TABLE IF NOT EXISTS `mlt_beoordelingen` (
  `maaltijd_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `kwantiteit` float DEFAULT NULL,
  `kwaliteit` float DEFAULT NULL,
  PRIMARY KEY (`maaltijd_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_maaltijden`
--

DROP TABLE IF EXISTS `mlt_maaltijden`;
CREATE TABLE IF NOT EXISTS `mlt_maaltijden` (
  `maaltijd_id` int(11) NOT NULL AUTO_INCREMENT,
  `mlt_repetitie_id` int(11) DEFAULT NULL,
  `titel` varchar(255) NOT NULL,
  `aanmeld_limiet` int(11) NOT NULL,
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `prijs` int(11) NOT NULL,
  `gesloten` tinyint(1) NOT NULL,
  `laatst_gesloten` int(11) DEFAULT NULL,
  `verwijderd` tinyint(1) NOT NULL,
  `aanmeld_filter` varchar(255) DEFAULT NULL,
  `omschrijving` text,
  PRIMARY KEY (`maaltijd_id`),
  KEY `mlt_repetitie_id` (`mlt_repetitie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_repetities`
--

DROP TABLE IF EXISTS `mlt_repetities`;
CREATE TABLE IF NOT EXISTS `mlt_repetities` (
  `mlt_repetitie_id` int(11) NOT NULL AUTO_INCREMENT,
  `dag_vd_week` int(11) NOT NULL,
  `periode_in_dagen` int(11) NOT NULL,
  `standaard_titel` varchar(255) NOT NULL,
  `standaard_tijd` time NOT NULL,
  `standaard_prijs` int(11) NOT NULL,
  `abonneerbaar` tinyint(1) NOT NULL,
  `standaard_limiet` int(11) NOT NULL,
  `abonnement_filter` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`mlt_repetitie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `onderverenigingen`
--

DROP TABLE IF EXISTS `onderverenigingen`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ondervereniging_leden`
--

DROP TABLE IF EXISTS `ondervereniging_leden`;
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

DROP TABLE IF EXISTS `onetime_tokens`;
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

DROP TABLE IF EXISTS `peiling`;
CREATE TABLE IF NOT EXISTS `peiling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `peiling_optie`
--

DROP TABLE IF EXISTS `peiling_optie`;
CREATE TABLE IF NOT EXISTS `peiling_optie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peiling_id` int(11) NOT NULL,
  `optie` varchar(255) NOT NULL,
  `stemmen` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `optie` (`optie`),
  KEY `peilingid` (`peiling_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `peiling_stemmen`
--

DROP TABLE IF EXISTS `peiling_stemmen`;
CREATE TABLE IF NOT EXISTS `peiling_stemmen` (
  `peiling_id` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  PRIMARY KEY (`peiling_id`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `profielen`
--

DROP TABLE IF EXISTS `profielen`;
CREATE TABLE IF NOT EXISTS `profielen` (
  `uid` varchar(4) NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `duckname` varchar(255) DEFAULT NULL,
  `voornaam` varchar(255) NOT NULL,
  `tussenvoegsel` varchar(255) DEFAULT NULL,
  `achternaam` varchar(255) NOT NULL,
  `voorletters` varchar(255) NOT NULL,
  `postfix` varchar(255) DEFAULT NULL,
  `adres` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `woonplaats` varchar(255) NOT NULL,
  `land` varchar(255) NOT NULL,
  `telefoon` varchar(255) DEFAULT NULL,
  `mobiel` varchar(255) NOT NULL,
  `geslacht` enum('m','v') NOT NULL,
  `voornamen` varchar(255) DEFAULT NULL,
  `echtgenoot` varchar(4) DEFAULT NULL,
  `adresseringechtpaar` varchar(255) DEFAULT NULL,
  `icq` varchar(255) DEFAULT NULL,
  `msn` varchar(255) DEFAULT NULL,
  `skype` varchar(255) DEFAULT NULL,
  `jid` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `beroep` varchar(255) DEFAULT NULL,
  `studie` varchar(255) DEFAULT NULL,
  `patroon` varchar(4) DEFAULT NULL,
  `studienr` int(11) DEFAULT NULL,
  `studiejaar` int(11) DEFAULT NULL,
  `lidjaar` int(11) NOT NULL,
  `lidafdatum` date DEFAULT NULL,
  `gebdatum` date NOT NULL,
  `sterfdatum` date DEFAULT NULL,
  `bankrekening` varchar(255) DEFAULT NULL,
  `machtiging` tinyint(1) DEFAULT NULL,
  `moot` char(1) DEFAULT NULL,
  `verticale` char(1) DEFAULT NULL,
  `verticaleleider` tinyint(1) DEFAULT NULL,
  `kringcoach` tinyint(1) DEFAULT NULL,
  `o_adres` varchar(255) DEFAULT NULL,
  `o_postcode` varchar(255) DEFAULT NULL,
  `o_woonplaats` varchar(255) DEFAULT NULL,
  `o_land` varchar(255) DEFAULT NULL,
  `o_telefoon` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `kerk` varchar(255) DEFAULT NULL,
  `muziek` varchar(255) DEFAULT NULL,
  `status` enum('S_NOVIET','S_LID','S_GASTLID','S_OUDLID','S_ERELID','S_OVERLEDEN','S_EXLID','S_NOBODY','S_CIE','S_KRINGEL') NOT NULL,
  `eetwens` varchar(255) DEFAULT NULL,
  `corvee_punten` int(11) DEFAULT NULL,
  `corvee_punten_bonus` int(11) DEFAULT NULL,
  `ontvangtcontactueel` enum('ja','digitaal','nee') NOT NULL,
  `kgb` text,
  `soccieID` int(11) DEFAULT NULL,
  `createTerm` varchar(255) DEFAULT NULL,
  `soccieSaldo` float DEFAULT NULL,
  `maalcieSaldo` float DEFAULT NULL,
  `changelog` text NOT NULL,
  `ovkaart` varchar(255) NOT NULL,
  `zingen` varchar(255) DEFAULT NULL,
  `novitiaat` text NOT NULL,
  `lengte` int(11) NOT NULL,
  `vrienden` text,
  `middelbareSchool` varchar(255) NOT NULL,
  `novietSoort` varchar(255) NOT NULL,
  `matrixPlek` varchar(255) NOT NULL,
  `startkamp` varchar(255) NOT NULL,
  `medisch` text,
  `novitiaatBijz` text,
  PRIMARY KEY (`uid`),
  KEY `nickname` (`nickname`),
  KEY `verticale` (`verticale`),
  KEY `status` (`status`),
  KEY `achternaam` (`achternaam`),
  KEY `voornaam` (`voornaam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `profielen`
--

INSERT INTO `profielen` (`uid`, `nickname`, `duckname`, `voornaam`, `tussenvoegsel`, `achternaam`, `voorletters`, `postfix`, `adres`, `postcode`, `woonplaats`, `land`, `telefoon`, `mobiel`, `geslacht`, `voornamen`, `echtgenoot`, `adresseringechtpaar`, `icq`, `msn`, `skype`, `jid`, `linkedin`, `website`, `beroep`, `studie`, `patroon`, `studienr`, `studiejaar`, `lidjaar`, `lidafdatum`, `gebdatum`, `sterfdatum`, `bankrekening`, `machtiging`, `moot`, `verticale`, `verticaleleider`, `kringcoach`, `o_adres`, `o_postcode`, `o_woonplaats`, `o_land`, `o_telefoon`, `email`, `kerk`, `muziek`, `status`, `eetwens`, `corvee_punten`, `corvee_punten_bonus`, `ontvangtcontactueel`, `kgb`, `soccieID`, `createTerm`, `soccieSaldo`, `maalcieSaldo`, `changelog`, `ovkaart`, `zingen`, `novitiaat`, `lengte`, `vrienden`, `middelbareSchool`, `novietSoort`, `matrixPlek`, `startkamp`, `medisch`, `novitiaatBijz`) VALUES
('x404', NULL, NULL, 'Admin', 'der', 'C.S.R. Delft', 'A.', NULL, 'Oude Delft 9', '2611BA', 'Delft', 'Nederland', NULL, '+31612345678', 'm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2017-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pubcie@csrdelft.nl', NULL, NULL, 'S_LID', NULL, NULL, NULL, 'ja', NULL, NULL, NULL, NULL, NULL, ' ', 'weekend', NULL, ' ', 200, NULL, ' ', ' ', ' ', ' ', NULL, NULL),
('x999', 'nobody', NULL, 'Niet', NULL, 'Ingelogd', 'N.', NULL, 'Oude Delft 9', '2611BA', 'Delft', 'Nederland', NULL, '+31612345678', 'm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2017-01-01', NULL, NULL, 0, '0', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'pubcie@csrdelft.nl', NULL, NULL, 'S_NOBODY', NULL, 0, 0, 'ja', '0', 0, 'barvoor', 0, 0, ' ', 'weekend', NULL, ' ', 200, NULL, ' ', ' ', ' ', ' ', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `saldolog`
--

DROP TABLE IF EXISTS `saldolog`;
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

DROP TABLE IF EXISTS `savedquery`;
CREATE TABLE IF NOT EXISTS `savedquery` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `savedquery` text NOT NULL,
  `beschrijving` varchar(255) NOT NULL,
  `permissie` varchar(255) NOT NULL DEFAULT 'P_LOGGED_IN',
  `categorie` varchar(255) NOT NULL DEFAULT 'Overig',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socciebestelling`
--

DROP TABLE IF EXISTS `socciebestelling`;
CREATE TABLE IF NOT EXISTS `socciebestelling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `socCieId` int(11) DEFAULT NULL,
  `totaal` int(11) DEFAULT NULL,
  `tijd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socciebestellinginhoud`
--

DROP TABLE IF EXISTS `socciebestellinginhoud`;
CREATE TABLE IF NOT EXISTS `socciebestellinginhoud` (
  `bestellingId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `aantal` int(11) DEFAULT '1',
  PRIMARY KEY (`bestellingId`,`productId`),
  KEY `productId_idx` (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `socciegrootboektype`
--

DROP TABLE IF EXISTS `socciegrootboektype`;
CREATE TABLE IF NOT EXISTS `socciegrootboektype` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `soccieklanten`
--

DROP TABLE IF EXISTS `soccieklanten`;
CREATE TABLE IF NOT EXISTS `soccieklanten` (
  `socCieId` int(11) NOT NULL AUTO_INCREMENT,
  `stekUID` varchar(4) DEFAULT NULL,
  `saldo` int(11) DEFAULT '0',
  `naam` text,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`socCieId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `soccielog`
--

DROP TABLE IF EXISTS `soccielog`;
CREATE TABLE IF NOT EXISTS `soccielog` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `type` enum('insert','update','remove') NOT NULL,
  `value` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `soccieprijs`
--

DROP TABLE IF EXISTS `soccieprijs`;
CREATE TABLE IF NOT EXISTS `soccieprijs` (
  `van` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tot` timestamp NOT NULL DEFAULT '2035-12-01 17:15:57',
  `productId` int(11) NOT NULL,
  `prijs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`van`,`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `soccieproduct`
--

DROP TABLE IF EXISTS `soccieproduct`;
CREATE TABLE IF NOT EXISTS `soccieproduct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT NULL,
  `beschrijving` text,
  `prioriteit` int(11) NOT NULL,
  `grootboekId` int(11) UNSIGNED NOT NULL,
  `beheer` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='socCieProduct';

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `verticalen`
--

DROP TABLE IF EXISTS `verticalen`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `verticale_leden`
--

DROP TABLE IF EXISTS `verticale_leden`;
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
-- Tabelstructuur voor tabel `voorkeurcommissie`
--

DROP TABLE IF EXISTS `voorkeurcommissie`;
CREATE TABLE IF NOT EXISTS `voorkeurcommissie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `zichtbaar` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `voorkeuropmerking`
--

DROP TABLE IF EXISTS `voorkeuropmerking`;
CREATE TABLE IF NOT EXISTS `voorkeuropmerking` (
  `uid` varchar(4) NOT NULL,
  `lidOpmerking` text NOT NULL,
  `praesesOpmerking` text NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `voorkeurvoorkeur`
--

DROP TABLE IF EXISTS `voorkeurvoorkeur`;
CREATE TABLE IF NOT EXISTS `voorkeurvoorkeur` (
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

DROP TABLE IF EXISTS `werkgroepen`;
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
  `bewerken_tot` datetime DEFAULT NULL,
  `afmelden_tot` datetime DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `werkgroep_deelnemers`
--

DROP TABLE IF EXISTS `werkgroep_deelnemers`;
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

DROP TABLE IF EXISTS `woonoorden`;
CREATE TABLE IF NOT EXISTS `woonoorden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `samenvatting` text NOT NULL,
  `omschrijving` text,
  `begin_moment` datetime NOT NULL,
  `eind_moment` datetime DEFAULT NULL,
  `maker_uid` varchar(4) NOT NULL,
  `soort` enum('w','h') NOT NULL,
  `eetplan` tinyint(1) NOT NULL,
  `keuzelijst` varchar(255) DEFAULT NULL,
  `familie` varchar(255) NOT NULL,
  `status` enum('ft','ht','ot') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maker_uid` (`maker_uid`),
  KEY `begin_moment` (`begin_moment`),
  KEY `familie` (`familie`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `forum_draden`
--
ALTER TABLE `forum_draden` ADD FULLTEXT KEY `titel` (`titel`);

--
-- Indexen voor tabel `forum_posts`
--
ALTER TABLE `forum_posts` ADD FULLTEXT KEY `tekst` (`tekst`);

--
-- Indexen voor tabel `mededelingen`
--
ALTER TABLE `mededelingen` ADD FULLTEXT KEY `titel` (`titel`);
ALTER TABLE `mededelingen` ADD FULLTEXT KEY `tekst` (`tekst`);

--
-- Beperkingen voor geëxporteerde tabellen
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
  ADD CONSTRAINT `bestuurs_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `bestuurs_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `bestuurs_leden_ibfk_4` FOREIGN KEY (`groep_id`) REFERENCES `besturen` (`id`);

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
-- Beperkingen voor tabel `eetplanhuis`
--
ALTER TABLE `eetplanhuis`
  ADD CONSTRAINT `eetplanhuis_ibfk_1` FOREIGN KEY (`groepid`) REFERENCES `groep` (`id`);

--
-- Beperkingen voor tabel `eetplan_oud`
--
ALTER TABLE `eetplan_oud`
  ADD CONSTRAINT `eetplan_oud_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `eetplan_oud_ibfk_2` FOREIGN KEY (`huis`) REFERENCES `eetplanhuis` (`id`);

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
-- Beperkingen voor tabel `gesprek_berichten`
--
ALTER TABLE `gesprek_berichten`
  ADD CONSTRAINT `gesprek_berichten_ibfk_1` FOREIGN KEY (`gesprek_id`) REFERENCES `gesprekken` (`gesprek_id`),
  ADD CONSTRAINT `gesprek_berichten_ibfk_2` FOREIGN KEY (`auteur_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `gesprek_deelnemers`
--
ALTER TABLE `gesprek_deelnemers`
  ADD CONSTRAINT `gesprek_deelnemers_ibfk_1` FOREIGN KEY (`gesprek_id`) REFERENCES `gesprekken` (`gesprek_id`),
  ADD CONSTRAINT `gesprek_deelnemers_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

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
  ADD CONSTRAINT `kringen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `kringen_ibfk_2` FOREIGN KEY (`verticale`) REFERENCES `verticalen` (`letter`);

--
-- Beperkingen voor tabel `kring_leden`
--
ALTER TABLE `kring_leden`
  ADD CONSTRAINT `kring_leden_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `kringen` (`id`),
  ADD CONSTRAINT `kring_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `kring_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `lichtingen`
--
ALTER TABLE `lichtingen`
  ADD CONSTRAINT `lichtingen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `lichting_leden`
--
ALTER TABLE `lichting_leden`
  ADD CONSTRAINT `lichting_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `lichting_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `lichting_leden_ibfk_4` FOREIGN KEY (`groep_id`) REFERENCES `lichtingen` (`id`);

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
-- Beperkingen voor tabel `peiling_optie`
--
ALTER TABLE `peiling_optie`
  ADD CONSTRAINT `peiling_optie_ibfk_1` FOREIGN KEY (`peiling_id`) REFERENCES `peiling` (`id`);

--
-- Beperkingen voor tabel `peiling_stemmen`
--
ALTER TABLE `peiling_stemmen`
  ADD CONSTRAINT `peiling_stemmen_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `peiling_stemmen_ibfk_3` FOREIGN KEY (`peiling_id`) REFERENCES `peiling` (`id`);

--
-- Beperkingen voor tabel `verticalen`
--
ALTER TABLE `verticalen`
  ADD CONSTRAINT `verticalen_ibfk_1` FOREIGN KEY (`maker_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `verticale_leden`
--
ALTER TABLE `verticale_leden`
  ADD CONSTRAINT `verticale_leden_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `verticalen` (`id`),
  ADD CONSTRAINT `verticale_leden_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `verticale_leden_ibfk_3` FOREIGN KEY (`door_uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `voorkeuropmerking`
--
ALTER TABLE `voorkeuropmerking`
  ADD CONSTRAINT `voorkeurOpmerking_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

--
-- Beperkingen voor tabel `voorkeurvoorkeur`
--
ALTER TABLE `voorkeurvoorkeur`
  ADD CONSTRAINT `voorkeurVoorkeur_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`),
  ADD CONSTRAINT `voorkeurVoorkeur_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `voorkeurcommissie` (`id`);

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
