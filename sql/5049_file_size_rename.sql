ALTER TABLE `document` CHANGE `bestandsnaam` `filename` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `document` CHANGE `size` `filesize` INT( 11 ) NOT NULL ;
