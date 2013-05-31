ALTER TABLE  `lid` CHANGE  `ontvangtcontactueel`  `ontvangtcontactueel` ENUM(  'ja',  'digitaal',  'nee' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'ja';
ALTER TABLE  `lid` DROP  `contactueel`;
