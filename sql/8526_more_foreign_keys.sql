ALTER TABLE  `document` ADD FOREIGN KEY (  `catID` ) REFERENCES  `csrdelft`.`documentcategorie` (
`ID`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `document` ADD FOREIGN KEY (  `eigenaar` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `groeplid` ADD FOREIGN KEY (  `groepid` ) REFERENCES  `csrdelft`.`groep` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `groeplid` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `groep` ADD FOREIGN KEY (  `gtype` ) REFERENCES  `csrdelft`.`groeptype` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `eetplanhuis` ADD UNIQUE (
`groepid`
)
ALTER TABLE  `eetplanhuis` ADD FOREIGN KEY (  `groepid` ) REFERENCES  `csrdelft`.`groep` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;
ALTER TABLE  `eetplan` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;
ALTER TABLE  `eetplan` ADD FOREIGN KEY (  `huis` ) REFERENCES  `csrdelft`.`eetplanhuis` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `fotoalbums` ADD FOREIGN KEY (  `owner` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;
ALTER TABLE  `fotos` ADD FOREIGN KEY (  `owner` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `login_sessions` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`accounts` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;
