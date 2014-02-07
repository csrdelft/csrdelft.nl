ALTER TABLE `agenda` CHANGE `id` `item_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ;
ALTER TABLE `agenda` CHANGE `titel` `titel` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `agenda` CHANGE `beschrijving` `beschrijving` TEXT NOT NULL ;
ALTER TABLE `agenda` CHANGE `begin` `begin_moment` DATETIME NOT NULL ;
ALTER TABLE `agenda` CHANGE `eind` `eind_moment` DATETIME NOT NULL ;
ALTER TABLE `agenda` CHANGE `rechtenBekijken` `rechten_bekijken` VARCHAR( 255 ) NOT NULL ;