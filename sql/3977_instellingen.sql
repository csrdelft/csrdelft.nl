RENAME TABLE `csrdelft`.`mlt_instellingen` TO `csrdelft`.`instellingen` ;
ALTER TABLE `instellingen` ADD `module` VARCHAR( 255 ) NOT NULL FIRST ;
ALTER TABLE `instellingen` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `module` , `instelling_id` ) ;