DELETE FROM `csrdelft`.`maaltijdabo` WHERE `maaltijdabo`.`abosoort` = 'A_MAANDAG';
DELETE FROM `csrdelft`.`maaltijdabosoort` WHERE `maaltijdabosoort`.`abosoort` = 'A_MAANDAG';

ALTER TABLE `maaltijdcorvee` ADD `klussen_licht` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `schoonmaken_keuken` ,
ADD `klussen_zwaar` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `klussen_licht` ;

INSERT INTO `maaltijdcorveeinstellingen` (`instelling`, `type`, `datum`, `tekst`, `int`) VALUES
('puntenlichteklus', 'int', '0000-00-00', '', 2),
('puntenzwareklus', 'int', '0000-00-00', '', 3),
('lichteklus', 'tekst', '0000-00-00', 'Beste LIDNAAM, U bent bevoorrecht om op DATUM licht te komen klussen. De ViP zal u nader instrueren. Ik wens u heel veel succes en plezier. Met vriendelijke groet, Am. CorveeCaesar', 0),
('zwareklus', 'tekst', '0000-00-00', 'Beste LIDNAAM, U bent bevoorrecht om op DATUM zwaar te komen klussen. De ViP zal u nader instrueren. Ik wens u heel veel succes en plezier. Met vriendelijke groet, Am. CorveeCaesar', 0);

ALTER TABLE `maaltijd` ADD `klussen_licht` INT( 11 ) NOT NULL AFTER `schoonmaken_keuken` ,
ADD `klussen_zwaar` INT( 11 ) NOT NULL AFTER `klussen_licht` ,
ADD `punten_klussen_licht` SMALLINT( 4 ) NOT NULL AFTER `punten_schoonmaken_keuken` ,
ADD `punten_klussen_zwaar` SMALLINT( 4 ) NOT NULL AFTER `punten_klussen_licht` ;
