ALTER TABLE `maaltijdgesloten` DROP `punten_toegekend`;
ALTER TABLE `maaltijdcorvee` ADD `punten_toegekend` ENUM('ja','nee','onbekend') DEFAULT 'onbekend' NOT NULL AFTER `theedoek` ;
