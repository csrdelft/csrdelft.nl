ALTER TABLE `activiteiten` CHANGE `soort` `soort` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
update activiteiten set soort = 'vereniging' where soort = 'i';
update activiteiten set soort = 'sjaarsactie' where soort = 's';
update activiteiten set soort = 'dies' where soort = 'd';
update activiteiten set soort = 'lustrum' where soort = 'l';
update activiteiten set soort = 'owee' where soort = 'o';
update activiteiten set soort = 'ifes' where soort = 'f';
update activiteiten set soort = 'extern' where soort = 'e';