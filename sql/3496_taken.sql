-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 20 nov 2012 om 03:42
-- Serverversie: 5.5.24-log
-- PHP-versie: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `csrdelft`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_functies`
--

CREATE TABLE IF NOT EXISTS `crv_functies` (
  `functie_id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `afkorting` varchar(11) NOT NULL,
  `omschrijving` text NOT NULL,
  `email_bericht` text NOT NULL,
  `standaard_punten` int(11) NOT NULL,
  `kwalificatie_benodigd` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`functie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_kwalificaties`
--

CREATE TABLE IF NOT EXISTS `crv_kwalificaties` (
  `lid_id` varchar(4) NOT NULL,
  `functie_id` int(11) NOT NULL,
  `wanneer_toegewezen` datetime NOT NULL,
  PRIMARY KEY (`lid_id`,`functie_id`)
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
  `standaard_aantal` int(11) NOT NULL DEFAULT '1',
  `voorkeurbaar` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`crv_repetitie_id`),
  KEY `mlt_repetitie_id` (`mlt_repetitie_id`),
  KEY `functie_id` (`functie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_taken`
--

CREATE TABLE IF NOT EXISTS `crv_taken` (
  `taak_id` int(11) NOT NULL AUTO_INCREMENT,
  `functie_id` int(11) NOT NULL,
  `lid_id` varchar(4) DEFAULT NULL,
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
  KEY `maaltijd_id` (`maaltijd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_voorkeuren`
--

CREATE TABLE IF NOT EXISTS `crv_voorkeuren` (
  `lid_id` varchar(4) NOT NULL,
  `crv_repetitie_id` int(11) NOT NULL,
  PRIMARY KEY (`lid_id`,`crv_repetitie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `crv_vrijstellingen`
--

CREATE TABLE IF NOT EXISTS `crv_vrijstellingen` (
  `lid_id` varchar(4) NOT NULL,
  `begin_datum` date NOT NULL,
  `eind_datum` date NOT NULL,
  `percentage` int(3) NOT NULL,
  PRIMARY KEY (`lid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_aanmeldingen`
--

CREATE TABLE IF NOT EXISTS `mlt_aanmeldingen` (
  `maaltijd_id` int(11) NOT NULL,
  `lid_id` varchar(4) NOT NULL,
  `aantal_gasten` int(11) NOT NULL DEFAULT '0',
  `gasten_opmerking` varchar(255) NOT NULL DEFAULT '',
  `door_abonnement` int(11) DEFAULT NULL,
  `door_lid_id` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`maaltijd_id`,`lid_id`),
  KEY `door_lid` (`door_lid_id`),
  KEY `door_abonnement` (`door_abonnement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_abonnementen`
--

CREATE TABLE IF NOT EXISTS `mlt_abonnementen` (
  `mlt_repetitie_id` int(11) NOT NULL,
  `lid_id` varchar(4) NOT NULL,
  PRIMARY KEY (`mlt_repetitie_id`,`lid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_instellingen`
--

CREATE TABLE IF NOT EXISTS `mlt_instellingen` (
  `instelling_id` varchar(255) NOT NULL,
  `waarde` text NOT NULL,
  PRIMARY KEY (`instelling_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mlt_maaltijden`
--

CREATE TABLE IF NOT EXISTS `mlt_maaltijden` (
  `maaltijd_id` int(11) NOT NULL AUTO_INCREMENT,
  `mlt_repetitie_id` int(11),
  `titel` varchar(255) NOT NULL,
  `aanmeld_limiet` int(11) NOT NULL DEFAULT '0',
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `prijs` float NOT NULL DEFAULT '0',
  `gesloten` tinyint(1) NOT NULL DEFAULT '0',
  `laatst_gesloten` datetime DEFAULT NULL,
  `verwijderd` tinyint(1) NOT NULL DEFAULT '0',
  `aanmeld_filter` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`maaltijd_id`),
  KEY `mlt_repetitie_id` (`mlt_repetitie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
  `standaard_prijs` float NOT NULL DEFAULT '0',
  `abonneerbaar` tinyint(1) NOT NULL DEFAULT '0',
  `standaard_limiet` int(11) NOT NULL,
  `abonnement_filter` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mlt_repetitie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  AUTO_INCREMENT=1 ;

--
-- Beperkingen voor gedumpte tabellen
--

--
-- Beperkingen voor tabel `crv_repetities`
--
ALTER TABLE `crv_repetities`
  ADD CONSTRAINT `crv_repetities_ibfk_1` FOREIGN KEY (`mlt_repetitie_id`) REFERENCES `mlt_repetities` (`mlt_repetitie_id`),
  ADD CONSTRAINT `crv_repetities_ibfk_2` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`);

--
-- Beperkingen voor tabel `crv_kwalificaties`
--
ALTER TABLE `crv_kwalificaties`
  ADD CONSTRAINT `crv_kwalificaties_ibfk_1` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`);

--
-- Beperkingen voor tabel `crv_taken`
--
ALTER TABLE `crv_taken`
  ADD CONSTRAINT `crv_taken_ibfk_1` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`),
  ADD CONSTRAINT `crv_taken_ibfk_2` FOREIGN KEY (`crv_repetitie_id`) REFERENCES `crv_repetities` (`crv_repetitie_id`),
  ADD CONSTRAINT `crv_taken_ibfk_3` FOREIGN KEY (`maaltijd_id`) REFERENCES `mlt_maaltijden` (`maaltijd_id`);

--
-- Beperkingen voor tabel `crv_voorkeuren`
--
ALTER TABLE `crv_voorkeuren`
  ADD CONSTRAINT `crv_voorkeuren_ibfk_1` FOREIGN KEY (`crv_repetitie_id`) REFERENCES `crv_repetities` (`crv_repetitie_id`);

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


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
