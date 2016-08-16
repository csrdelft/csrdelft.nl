-- Hernoem de originele eetplan tabel
ALTER TABLE eetplan
  RENAME TO eetplan_oud;

-- Maak de nieuwe eetplan tabel aan
CREATE TABLE eetplan (
  id          INT(11)    NOT NULL AUTO_INCREMENT,
  uid         VARCHAR(4) NOT NULL,
  woonoord_id INT(11)    NOT NULL,
  avond       DATE       NOT NULL,
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 1;

-- Migreer de huidige data uit de oude eetplan tabel
INSERT INTO eetplan (avond, uid, woonoord_id)
  SELECT
    (CASE eetplan_oud.avond
     WHEN 1
       THEN DATE('2015-10-06')
     WHEN 2
       THEN DATE('2015-12-01')
     WHEN 3
       THEN DATE('2016-01-12') END) AS avond,
    eetplan_oud.uid,
    groep.omnummering               AS woonoord_id
  FROM eetplan_oud
    INNER JOIN eetplanhuis ON eetplanhuis.id = eetplan_oud.huis
    INNER JOIN groep ON eetplanhuis.groepid = groep.id;
