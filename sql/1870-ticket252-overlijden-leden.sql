 -- voor ticket #252: overlijden van leden in profiel.
ALTER TABLE `lid` ADD `sterfdatum` DATE NOT NULL AFTER `gebdatum` ;
ALTER TABLE `lid` CHANGE `status` `status` ENUM('S_CIE','S_GASTLID','S_LID','S_NOBODY','S_NOVIET','S_OUDLID','S_KRINGEL','S_OVERLEDEN') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'S_CIE';
