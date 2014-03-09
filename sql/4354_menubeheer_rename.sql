ALTER TABLE `menus` CHANGE `permission` `rechten_bekijken` VARCHAR( 255 ) NOT NULL DEFAULT 'P_NOBODY';
update `menus` set item_id = (item_id * -1) where 1;
update `menus` set item_id = (item_id * -1)+2 where 1;
update `menus` set parent_id = parent_id +2 where 1;
INSERT INTO `csrdelft`.`menus` (
`item_id` ,
`parent_id` ,
`prioriteit` ,
`tekst` ,
`link` ,
`rechten_bekijken` ,
`zichtbaar` ,
`menu_naam`
)
VALUES (
'1', '0', '0', 'main', '', 'P_NOBODY', '1', ''
);
INSERT INTO `csrdelft`.`menus` (
`item_id` ,
`parent_id` ,
`prioriteit` ,
`tekst` ,
`link` ,
`rechten_bekijken` ,
`zichtbaar` ,
`menu_naam`
)
VALUES (
'2' , '0', '0', 'gasnelnaar', '', 'P_NOBODY', '1', ''
);
update `menus` set parent_id=2 where menu_naam='gasnelnaar';
ALTER TABLE `menus` DROP `menu_naam`;