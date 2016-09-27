ALTER TABLE mededelingcategorie CHANGE prioriteit prioriteit int(11) NOT NULL;
ALTER TABLE mededelingcategorie CHANGE permissie permissie enum('P_NEWS_POST','P_NEWS_MOD') NOT NULL;
