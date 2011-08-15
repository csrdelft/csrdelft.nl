-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 13 Aug 2011 om 18:03
-- Serverversie: 5.1.49
-- PHP-Versie: 5.3.3-1ubuntu9.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `csrdelft`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `maaltijdcorveeinstellingen`
--

CREATE TABLE IF NOT EXISTS `maaltijdcorveeinstellingen` (
  `instelling` varchar(50) NOT NULL,
  `type` enum('tekst','datum','int') NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `tekst` text NOT NULL,
  `int` int(11) NOT NULL,
  UNIQUE KEY `instelling` (`instelling`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Gegevens worden uitgevoerd voor tabel `maaltijdcorveeinstellingen`
--

INSERT INTO `maaltijdcorveeinstellingen` (`instelling`, `type`, `datum`, `tekst`, `int`) VALUES
('periodebegin', 'datum', '2011-08-20', '', 0),
('periodeeind', 'datum', '2012-02-11', '', 0),
('koks', 'tekst', '0000-00-00', 'Geachte LIDNAAM,\r\n\r\nU zult op DATUM een heerlijke maaltijd mogen bereiden op Confide.\r\nBijgevoegd stuur ik de instructie voor koks en de handleiding voor de oven.\r\n\r\nOvenhandleiding: https://docs.google.com/document/d/1DKyl-jvkTRCd0LnrIPZaLmfRYyWdlYRRiQr9wVhnFF8/edit?hl=en&authkey=CNDuhLoK#\r\nInstructie koks: https://docs.google.com/document/d/1QFLBHqcFNuqp8lYCGCnAXz98qyuf4gb87vSC5IvT3H4/edit?hl=en&authkey=CN_Rj44L#\r\n\r\nBijgezegd moet worden dat alleen de oven-functie van de combi-steamer te gebruiken is, de steam en de schoonmaakfunctie dus niet.\r\nVerandert dit, dan licht ik jullie zo snel mogelijk in.\r\nDoor het hygiëne-commissariaat is bepaald dat de koks verantwoordelijk zijn voor de schoonmaak van de oven, dus bij deze.\r\nDaarnaast nog een punt om op te letten, alle overgebleven etenswaren moeten worden geveild, niets mag er in de keuken blijven liggen.\r\n\r\nIk wens u heel veel succes en plezier.\r\n\r\nMet vriendelijke groet,\r\nAm. CorveeCaesar\r\n\r\nPS: MEEETEN\r\n\r\nCorvee-rooster: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odDJ0SV9NY1d4SVVLbUw1R2RqcG1jbEE&hl=nl&authkey=CJeSysIH\r\nCorvee-punten: http://csrdelft.nl/actueel/maaltijden/corveepunten/\r\nRKK-punten: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odEEzY3ZhcHgzX0h4UGltaUhxUWEtWXc&hl=nl&authkey=CKKMlLAB', 0),
('afwas', 'tekst', '0000-00-00', 'Geachte LIDNAAM,\r\n\r\nU bent uitverkoren om op op DATUM de vaat weer schoon te maken op Confide.\r\nDit gebeurt uiteraard met behulp van onze prachtig nieuwe vaatwasser.\r\nDe kwali-afwasser is eindverantwoordelijke en zal met behulp van jullie allen er voor zorgen dat alle taken die op de afwas-instructie uitgevoerd zijn.\r\nIk stuur je dan ook mede de instructie voor het afwassen, lees deze goed door, hij is namelijk vernieuwd in verband met de nieuwe keuken.\r\n\r\nInstructies afwas: https://docs.google.com/document/d/1swbyjJcGqKBN-r2Wvjd7BToroMkbNYbNUpEitgZ-Eb8/edit?hl=en&authkey=CMG8u8sG#\r\n\r\nLet vooral op de volgend punten:\r\n - de braadslede is in z''n geheel ingevet\r\n - de afwasmachine is na gebruik compleet gereinigd, alle roosters ( 3 stuks) geleegd. Naderhand open laten staan.\r\n - vloer schoon, putje schoon.\r\n - de oven, als hij gebruikt is, droog van binnen en ook open\r\n - laat de volle vuilniszakken niet in de service-gang staan maar ruim ze op in het afvalhok\r\n\r\nTips:\r\n - zet de vaatwasser aan voor het toetje het duur meestal even voordat hij opgewarmt is.\r\n - coordineer bij het afruimen van de borden in de keuken en zet het handig neer dat scheelt later een hoop werk.\r\n\r\nIk wens u heel veel succes en plezier.\r\n\r\nMet vriendelijke groet,\r\nAm. CorveeCaesar\r\n\r\nPS: MEEETEN\r\n\r\nCorvee-rooster: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odDJ0SV9NY1d4SVVLbUw1R2RqcG1jbEE&hl=nl&authkey=CJeSysIH\r\nCorvee-punten: http://csrdelft.nl/actueel/maaltijden/corveepunten/\r\nRKK-punten: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odEEzY3ZhcHgzX0h4UGltaUhxUWEtWXc&hl=nl&authkey=CKKMlLAB', 0),
('theedoeken', 'tekst', '0000-00-00', 'Beste LIDNAAM,\r\n\r\nU bent bevoorrecht om op DATUM de theedoeken van Confide te wassen.\r\nMede stuur ik u de instructie daarvoor.\r\n\r\nInstructies theedoeken: https://docs.google.com/document/d/1WNvuYP75wIE0ZwOzjixaV-uz74ngk47C3lZsz_puoVI/edit?hl=en&authkey=CLyTnNYG#\r\n\r\nLet vooral op de volgende punten:\r\n- zorg dat je alle SCHORTEN en theedoeken heb meegenomen.\r\n- zorg dat ze voor maandagmiddag 3 UUR schoon terug zijn.\r\n\r\nAlvast veel plezier en succes toegewenst,\r\n\r\nMet vriendelijke groet,\r\nAm. CorveeCaesar\r\n\r\nPS: MEEETEN\r\n\r\nCorvee-rooster: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odDJ0SV9NY1d4SVVLbUw1R2RqcG1jbEE&hl=nl&authkey=CJeSysIH\r\nCorvee-punten: http://csrdelft.nl/actueel/maaltijden/corveepunten/\r\nRKK-punten: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odEEzY3ZhcHgzX0h4UGltaUhxUWEtWXc&hl=nl&authkey=CKKMlLAB', 0),
('afzuigkap', 'tekst', '0000-00-00', 'Geachte LIDNAAM,\r\n\r\nU hebt de eervolle taak om op DATUM de afzuigkappen van de nieuwe keuken van Confide schoon te maken\r\nDit is een stuk gemakkelijker geworden doordat u gebruik kunt maken van de hypermoderne vaatwasser.\r\nMede stuur ik u de instructies voor het schoonmaken van de afzuigkappen.\r\n\r\nInstructies schoonmaken: https://docs.google.com/document/d/1rv1-zhQwl3GluIUxvRV-QqDFYZ-v84DD6fSKb1wlLkk/edit?hl=en&authkey=CJTr7o4M#\r\n\r\nLet vooral op de volgende dingen:\r\n - De behuizing van de afzuigkappen moeten ook schoon worden gemaakt.\r\n - De vaatwasser moet nadien ook schoon zijn.(filters leeg enz.)\r\n\r\nIk wens u heel veel succes en plezier.\r\n\r\nMet vriendelijke groet,\r\nAm. CorveeCaesar\r\n\r\nCorvee-rooster: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odDJ0SV9NY1d4SVVLbUw1R2RqcG1jbEE&hl=nl&authkey=CJeSysIH\r\nCorvee-punten: http://csrdelft.nl/actueel/maaltijden/corveepunten/\r\nRKK-punten: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odEEzY3ZhcHgzX0h4UGltaUhxUWEtWXc&hl=nl&authkey=CKKMlLAB', 0),
('frituur', 'tekst', '0000-00-00', 'Beste LIDNAAM,\r\n\r\nJe hebt de eervolle taak om op DATUM zorg te dragen voor het schoonmaken der vetput.\r\nMede stuur ik je de instructie voor het schoonmaken.\r\nDe taak hoeft niet per se vrijdagmiddag gedaan te worden maar zorg dat het voor de daaropvolgende donderdagmiddag gedaan is.\r\n\r\nInstructies frituur schoonmaken:https://docs.google.com/document/d/1eybt2L4YsliHKUPg6gkpZN5fJVAZdYw1QTyUkSqI1RY/edit?hl=en&authkey=CIHgx7AO#\r\n\r\nLet vooral op de volgende punten:\r\n - Trek oude kleren aan\r\n - Mors niet ( te veel)\r\n - Maak alles goed schoon nadien\r\n\r\nMet vriendelijke groet,\r\nAm. CorveeCaesar\r\n\r\nCorvee-rooster: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odDJ0SV9NY1d4SVVLbUw1R2RqcG1jbEE&hl=nl&authkey=CJeSysIH\r\nCorvee-punten: http://csrdelft.nl/actueel/maaltijden/corveepunten/\r\nRKK-punten: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odEEzY3ZhcHgzX0h4UGltaUhxUWEtWXc&hl=nl&authkey=CKKMlLAB', 0),
('keuken', 'tekst', '0000-00-00', 'Beste LIDNAAM,\r\n\r\nJe bent bevoorrecht om op DATUM de keuken schoon te maken.\r\nIn de onderstaande stuur ik u mede de keuken-schoonmaak-instructie, deze instructie is slechts een richtlijn.\r\n\r\ninstructie keuken schoonmaken: https://docs.google.com/document/d/1XFFQZgDZLrTi2oaNtNFc-07vgeUDvqLthWdG3eVJwKQ/edit?hl=en&authkey=CJ3Lz7IF#\r\n\r\nKomt u dus dingen tegen die schoongemaakt moeten worden maar niet specifiek in de instructie staan, maakt u dit dan aub schoon.\r\nVindt u dat er andere dingen zijn die wel specifieker in de instructie moeten komen te staan. Bericht het mij.\r\n\r\nIk wens u heel veel succes en plezier.\r\n\r\nMet vriendelijke groet,\r\nAm. CorveeCaesar\r\n\r\nCorvee-rooster: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odDJ0SV9NY1d4SVVLbUw1R2RqcG1jbEE&hl=nl&authkey=CJeSysIH\r\nCorvee-punten: http://csrdelft.nl/actueel/maaltijden/corveepunten/\r\nRKK-punten: https://spreadsheets.google.com/ccc?key=0Ai3inI9swl9odEEzY3ZhcHgzX0h4UGltaUhxUWEtWXc&hl=nl&authkey=CKKMlLAB', 0),
('puntentotaal', 'int', '0000-00-00', '', 11),
('puntenkoken', 'int', '0000-00-00', '', 4),
('puntenafwas', 'int', '0000-00-00', '', 3),
('puntentheedoek', 'int', '0000-00-00', '', 2),
('puntenafzuigkap', 'int', '0000-00-00', '', 2),
('puntenfrituur', 'int', '0000-00-00', '', 2),
('puntenkeuken', 'int', '0000-00-00', '', 3),
('roosterbegin',  'datum',  '2011-08-01',  '',  '0'), 
('roostereind',  'datum',  '2012-08-01',  '',  '0');
