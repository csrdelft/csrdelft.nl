ALTER TABLE `forum_draden` DROP INDEX laatst_gepost;
ALTER TABLE `forum_draden` ADD INDEX ( `laatst_gewijzigd` ) ;
ALTER TABLE `forum_draden` ADD INDEX ( `verwijderd` ) ;
ALTER TABLE `forum_draden` ADD INDEX ( `belangrijk` ) ;
ALTER TABLE `forum_posts` ADD INDEX ( `verwijderd` ) ;
