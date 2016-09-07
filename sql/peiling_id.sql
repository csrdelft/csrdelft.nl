ALTER TABLE `peilingoptie` CHANGE `peilingid` `peiling_id` INT(11) NOT NULL;
RENAME TABLE `peilingoptie` TO `peiling_optie`;
ALTER TABLE `peiling_stemmen` CHANGE `peilingid` `peiling_id` INT(11) NOT NULL;
