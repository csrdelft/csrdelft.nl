ALTER TABLE  `lid` CHANGE  `corvee_voorkeuren`  `corvee_voorkeuren` VARCHAR( 10 ) NOT NULL DEFAULT  '1111111111';

UPDATE lid SET corvee_voorkeuren = CONCAT( SUBSTRING(lid.corvee_voorkeuren,1,2), '11', SUBSTRING(lid.corvee_voorkeuren,3,6) );
