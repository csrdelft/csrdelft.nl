ALTER TABLE `lid` ADD `echtgenoot` VARCHAR( 4 ) NOT NULL AFTER `voornamen`;
ALTER TABLE `lid` ADD `adresseringechtpaar` VARCHAR( 250 ) NOT NULL COMMENT  'Tenaamstelling van post gericht aan het echtpaar' AFTER `echtgenoot`;
