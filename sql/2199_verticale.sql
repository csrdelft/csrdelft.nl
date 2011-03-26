CREATE TABLE  `csrdelft`.`verticale` (
`id` INT( 11 ) NOT NULL ,
`letter` VARCHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`naam` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE = MYISAM;

INSERT INTO  `csrdelft`.`verticale` (
`id` ,
`letter` ,
`naam`
)
VALUES (
'0',  'Geen',  'Geen'
), (
'1',  'A',  'Archibald'
), (
'2',  'B',  'Faculteit'
), (
'3',  'C',  'Billy'
), (
'4',  'D',  'Diagonaal'
), (
'5',  'E',  'Vrøgd'
), (
'6',  'F',  'Lekker'
), (
'7',  'G',  'Securis'
), (
'8',  'H',  'Primitus'
);

