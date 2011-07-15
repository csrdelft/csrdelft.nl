
UPDATE  `csrdelft`.`biebboek` SET  `uitgavejaar` =  '0' WHERE  `uitgavejaar` IS NULL ;
UPDATE  `csrdelft`.`biebboek` SET  `paginas` =  '0' WHERE  `paginas` IS NULL ;



ALTER TABLE  `biebboek` 
CHANGE  `paginas`  `paginas` SMALLINT( 6 ) NOT NULL  DEFAULT  '0',
CHANGE  `uitgavejaar`  `uitgavejaar` MEDIUMINT( 4 ) NOT NULL DEFAULT  '0',
CHANGE  `uitgeverij`  `uitgeverij` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `code`  `code` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
