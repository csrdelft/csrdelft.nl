-- Refactor van mededelingen 20/06/2016
RENAME TABLE mededeling TO mededelingen;

ALTER TABLE mededelingen
  DROP COLUMN datum_old;

ALTER TABLE mededelingen CHANGE id id int(11) NOT NULL auto_increment;
ALTER TABLE mededelingen CHANGE datum datum datetime NOT NULL;
ALTER TABLE mededelingen CHANGE titel titel varchar(255) NOT NULL;
ALTER TABLE mededelingen CHANGE categorie categorie int(11) NOT NULL;
ALTER TABLE mededelingen DROP COLUMN prive;
ALTER TABLE mededelingen CHANGE zichtbaarheid zichtbaarheid varchar(255) NOT NULL;
ALTER TABLE mededelingen CHANGE prioriteit prioriteit int(11) NOT NULL;
ALTER TABLE mededelingen CHANGE uid uid varchar(4) NOT NULL;
ALTER TABLE mededelingen CHANGE doelgroep doelgroep varchar(255) NOT NULL;
ALTER TABLE mededelingen CHANGE verborgen verborgen tinyint(1) NOT NULL;
ALTER TABLE mededelingen CHANGE verwijderd verwijderd tinyint(1) NOT NULL;
ALTER TABLE mededelingen CHANGE plaatje plaatje varchar(255) NULL;