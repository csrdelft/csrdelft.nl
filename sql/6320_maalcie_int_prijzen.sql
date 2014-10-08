update mlt_maaltijden set prijs = prijs * 100;
ALTER TABLE `mlt_maaltijden` CHANGE `prijs` `prijs` INT NOT NULL DEFAULT '0';
update mlt_archief set prijs = prijs * 100;
ALTER TABLE `mlt_archief` CHANGE `prijs` `prijs` INT NOT NULL DEFAULT '0';
update mlt_repetities set standaard_prijs = standaard_prijs * 100;
ALTER TABLE `mlt_repetities` CHANGE `standaard_prijs` `standaard_prijs` INT NOT NULL DEFAULT '0';