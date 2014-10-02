ALTER TABLE forum_draden ADD gedeeld_met varchar(255) NOT NULL AFTER eerste_post_plakkerig;
ALTER TABLE `verticale` CHANGE `naam` `naam` VARCHAR(255) NOT NULL;
RENAME TABLE `csrdelft`.`verticale` TO `csrdelft`.`verticalen`;
ALTER TABLE `verticalen` ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;