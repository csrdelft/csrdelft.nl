CREATE TABLE `mlt_beoordelingen` (
 `maaltijd_id` int(11) NOT NULL,
 `uid` varchar(4) NOT NULL,
 `kwantiteit` float DEFAULT NULL,
 `kwaliteit` float DEFAULT NULL,
 PRIMARY KEY (`maaltijd_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8