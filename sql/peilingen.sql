ALTER TABLE peiling CHANGE titel titel varchar(255) NOT NULL;
ALTER TABLE peiling CHANGE tekst tekst text NOT NULL;
ALTER TABLE peilingoptie CHANGE optie optie varchar(255) NOT NULL;
ALTER TABLE peilingoptie CHANGE stemmen stemmen int(11) NOT NULL;
