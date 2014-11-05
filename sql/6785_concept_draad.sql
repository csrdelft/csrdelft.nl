ALTER TABLE bijbelrooster CHANGE dag dag datetime NOT NULL;
ALTER TABLE forum_draden_reageren ADD forum_id int(11) NOT NULL FIRST;
ALTER TABLE forum_draden_reageren ADD titel varchar(255) NULL DEFAULT NULL AFTER concept;
ALTER TABLE  `csrdelft`.`forum_draden_reageren` DROP PRIMARY KEY ,
ADD PRIMARY KEY (  `forum_id` ,  `draad_id` ,  `uid` );