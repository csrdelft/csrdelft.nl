CREATE TABLE removed_objects (object_id int(11) NOT NULL auto_increment, parent_id int(11) NULL DEFAULT NULL, model_class varchar(255) NOT NULL, removed_moment varchar(255) NOT NULL, removed_by_uid varchar(4) NOT NULL, permission_restore varchar(255) NOT NULL, permission_delete varchar(255) NOT NULL, PRIMARY KEY (object_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1;
CREATE TABLE removed_attributes (object_id int(11) NOT NULL, name varchar(255) NOT NULL, value longtext NULL DEFAULT NULL, PRIMARY KEY (object_id,name)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
