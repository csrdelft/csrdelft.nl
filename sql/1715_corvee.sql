ALTER TABLE `maaltijdcorvee` CHANGE `schoonmaken_frituur` `schoonmaken_frituur` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `schoonmaken_afzuigkap` `schoonmaken_afzuigkap` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `schoonmaken_keuken` `schoonmaken_keuken` TINYINT( 1 ) NOT NULL DEFAULT '0';