ALTER TABLE  `groep` CHANGE  `toonFuncties`  `toonFuncties` ENUM(  'tonen',  'verbergen',  'niet',  'tonenzonderinvoer' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'tonen';
