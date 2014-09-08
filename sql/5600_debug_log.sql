CREATE TABLE IF NOT EXISTS `debug_log` (
  `class_function` varchar(255) NOT NULL,
  `dump` longtext,
  `call_trace` text NOT NULL,
  `moment` datetime NOT NULL,
  `uid` varchar(4) DEFAULT NULL,
  `su_uid` varchar(4) DEFAULT NULL,
  `ip` varchar(255) NOT NULL,
  `request` varchar(255) NOT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`class_function`,`moment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;