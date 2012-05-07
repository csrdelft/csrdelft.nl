ALTER TABLE  `biebboek` ADD  `auteur` VARCHAR( 100 ) NOT NULL DEFAULT  '' AFTER  `id`;


UPDATE biebboek
SET auteur= (SELECT biebauteur.auteur FROM biebauteur WHERE biebauteur.id=biebboek.auteur_id)
WHERE `auteur_id` != 0;




