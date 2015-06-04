INSERT INTO `csrdelft`.`menus` (`item_id`, `parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES ('10', '0', '0', 'remotefora', '#', 'P_LOGGED_IN', '1');
INSERT INTO `csrdelft`.`menus` (`item_id`, `parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES ('11', '10', '0', 'Broeders', '#', 'P_LOGGED_IN', '1'), ('12', '10', '0', 'Zusters', '#', 'P_LOGGED_IN', '1');
INSERT INTO `csrdelft`.`menus` (`item_id`, `parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES (NULL, '12', '0', 'CSV Alpha', 'http://wesp.snt.utwente.nl/~alpha/prlo/index.php/forum/index?func=listcat&template=atomic', 'P_LOGGED_IN', '1'), (NULL, '12', '0', 'Lux Ad Mosam', 'http://www.luxadmosam.nl/forum/', 'P_LOGGED_IN', '1');
INSERT INTO `csrdelft`.`menus` (`item_id`, `parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES (NULL, '11', '0', 'VGSD', 'http://vgsd.nl/forum/', 'P_LOGGED_IN', '1'), (NULL, '11', '0', 'C.S.F.R.', 'http://csfr-delft.nl/forum/', 'P_LOGGED_IN', '1');
INSERT INTO `csrdelft`.`menus` (`item_id`, `parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES (NULL, '11', '0', 'Navigators Delft', '#', 'P_LOGGED_IN', '1'), (NULL, '12', '0', 'S.S.R.-N.U.', '#', 'P_LOGGED_IN', '1');
INSERT INTO  `csrdelft`.`menus` (
`item_id` ,
`parent_id` ,
`volgorde` ,
`tekst` ,
`link` ,
`rechten_bekijken` ,
`zichtbaar`
)
VALUES (
NULL ,  '11',  '0',  'RKJ',  'http://www.rkjdelft.nl/forum',  'P_LOGGED_IN',  '1'
);
