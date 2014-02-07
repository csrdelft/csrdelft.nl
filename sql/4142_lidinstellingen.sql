ALTER TABLE `lid` DROP `instellingen`;
CREATE TABLE `lidinstellingen` (
  `lid_id` varchar(4) NOT NULL,
  `module` varchar(255) NOT NULL,
  `instelling_id` varchar(255) NOT NULL,
  `waarde` varchar(255) NOT NULL,
  PRIMARY KEY (`lid_id`,`module`,`instelling_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;