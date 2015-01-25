ALTER TABLE  `activiteiten` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `activiteit_deelnemers` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`activiteiten` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `activiteit_deelnemers` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `besturen` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `bestuurs_leden` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`besturen` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `bestuurs_leden` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `commissies` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `commissie_leden` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`commissies` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `commissie_leden` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `groepen` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `groep_leden` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`groepen` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `groep_leden` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `ketzers` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `ketzer_deelnemers` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`ketzers` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `ketzer_deelnemers` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `onderverenigingen` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `ondervereniging_leden` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`onderverenigingen` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `ondervereniging_leden` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `werkgroepen` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `werkgroep_deelnemers` ADD FOREIGN KEY (  `groep_id` ) REFERENCES  `csrdelft`.`werkgroepen` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `werkgroep_deelnemers` ADD FOREIGN KEY (  `uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `woonoorden` ADD FOREIGN KEY (  `maker_uid` ) REFERENCES  `csrdelft`.`profielen` (
`uid`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;
