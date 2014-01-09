ALTER TABLE `crv_repetities` ADD `standaard_punten` INT NOT NULL AFTER `functie_id` ;
UPDATE crv_repetities SET crv_repetities.standaard_punten = (
    SELECT crv_functies.standaard_punten FROM crv_functies
    WHERE crv_repetities.functie_id = crv_functies.functie_id
);
