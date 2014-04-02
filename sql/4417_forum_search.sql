ALTER TABLE `forum_posts` DROP FOREIGN KEY `forum_posts_ibfk_1`;
ALTER TABLE `forum_posts` ENGINE = MYISAM ;
ALTER TABLE `forum_posts` ADD FULLTEXT (`tekst`);

ALTER TABLE `forum_draden` DROP FOREIGN KEY `forum_draden_ibfk_1`;
ALTER TABLE `forum_draden_gelezen` DROP FOREIGN KEY `forum_draden_gelezen_ibfk_1`;
ALTER TABLE `forum_draden` ENGINE = MYISAM ;
ALTER TABLE `forum_draden` ADD FULLTEXT (`titel`);