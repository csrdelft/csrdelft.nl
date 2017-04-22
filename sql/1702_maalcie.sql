-- Opschonen vorige keer
ALTER TABLE mlt_maaltijden DROP FOREIGN KEY FK_mlt_product;
ALTER TABLE mlt_maaltijden DROP COLUMN product_id;
ALTER TABLE mlt_repetities DROP FOREIGN KEY FK_mltrep_product;
ALTER TABLE mlt_repetities DROP COLUMN product_id;

DROP TABLE `CiviBestellingInhoud`, `CiviBestelling`, `CiviPrijs`, `CiviProduct`, `CiviLog`;

CREATE TABLE CiviLog
(
  id        INT(11) NOT NULL AUTO_INCREMENT,
  ip        VARCHAR(255) NOT NULL,
  type      ENUM ('insert','remove','create','update','delete') NOT NULL,
  data      VARCHAR(255) NOT NULL,
  timestamp TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE CiviProduct
(
  id           INT(11) NOT NULL AUTO_INCREMENT,
  status       INT(11) NOT NULL,
  beschrijving TEXT NOT NULL,
  prioriteit   INT(11) NOT NULL,
  beheer       TINYINT(1) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE CiviPrijs
(
  van       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tot       TIMESTAMP NOT NULL,
  product_id INT(11) NOT NULL,
  prijs     INT(11) NOT NULL,
  PRIMARY KEY (van, product_id),
  CONSTRAINT FK_CP_product FOREIGN KEY (product_id)
  REFERENCES CiviProduct(id)
);

CREATE TABLE CiviBestelling
(
  id      INT(11)    NOT NULL AUTO_INCREMENT,
  uid     VARCHAR(4) NOT NULL,
  totaal  INT(11)    NOT NULL,
  deleted TINYINT(1) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE CiviBestellingInhoud
(
  bestelling_id INT(11) NOT NULL,
  product_id    INT(11) NOT NULL,
  aantal       INT(11),
  PRIMARY KEY (bestelling_id, product_id),
  CONSTRAINT FK_CBI_product FOREIGN KEY (product_id)
  REFERENCES CiviProduct(id),
  CONSTRAINT FK_CBI_bestelling FOREIGN KEY (bestelling_id)
  REFERENCES CiviBestelling(id)
);

CREATE TABLE CiviSaldo
(
  uid VARCHAR(4) NOT NULL,
  saldo INT(11) NOT NULL,
  laatst_veranderd TIMESTAMP NOT NULL,
  PRIMARY KEY (uid)
);

ALTER TABLE mlt_maaltijden ADD COLUMN product_id INT(11) NOT NULL;
ALTER TABLE mlt_repetities ADD COLUMN product_id INT(11) NOT NULL;

INSERT INTO CiviProduct (status, beschrijving, prioriteit, beheer)
VALUES (1, 'Anders', 1, 1);
SET @mlt = LAST_INSERT_ID();
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NOW(), @mlt, 0);
UPDATE mlt_maaltijden SET product_id = @mlt WHERE mlt_repetitie_id IS NULL;

-- Donderdagmaaltijd
INSERT INTO CiviProduct (status, beschrijving, prioriteit, beheer)
VALUES (1, 'Donderdagmaaltijd', 1, 0);
SET @mlt = LAST_INSERT_ID();
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, @mlt, 350);
UPDATE mlt_maaltijden SET product_id = @mlt WHERE mlt_repetitie_id = 1;
UPDATE mlt_repetities SET product_id = @mlt WHERE mlt_repetitie_id = 1;

-- Verticalekring
INSERT INTO CiviProduct (status, beschrijving, prioriteit, beheer)
VALUES (1, 'Verticalekring', 1, 0);
SET @mlt = LAST_INSERT_ID();
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, @mlt, 350);
UPDATE mlt_maaltijden SET product_id = @mlt WHERE mlt_repetitie_id IN (2,3,4,5,6,7,8,9,13);
UPDATE mlt_repetities SET product_id = @mlt WHERE mlt_repetitie_id IN (2,3,4,5,6,7,8,9,13);

-- DéDé Diner
INSERT INTO CiviProduct (status, beschrijving, prioriteit, beheer)
VALUES (1, 'DéDé-Diner', 1, 0);
SET @mlt = LAST_INSERT_ID();
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, @mlt, 350);
UPDATE mlt_maaltijden SET product_id = @mlt WHERE mlt_repetitie_id = 10;
UPDATE mlt_repetities SET product_id = @mlt WHERE mlt_repetitie_id = 10;

-- Alpha cursus
INSERT INTO CiviProduct (status, beschrijving, prioriteit, beheer)
VALUES (1, 'Alpha Cursus', 1, 0);
SET @mlt = LAST_INSERT_ID();
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, @mlt, 0);
UPDATE mlt_maaltijden SET product_id = @mlt WHERE mlt_repetitie_id = 11;
UPDATE mlt_repetities SET product_id = @mlt WHERE mlt_repetitie_id = 11;

-- Cent (voor mutaties)
INSERT INTO CiviProduct (status, beschrijving, prioriteit, beheer)
VALUES(1, 'Cent', 1, 1);
SET @cent = LAST_INSERT_ID();
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, @cent, 1);

-- Leg foreign keys aan
ALTER TABLE mlt_maaltijden ADD CONSTRAINT FK_mlt_product FOREIGN KEY (product_id) REFERENCES CiviProduct(id);
ALTER TABLE mlt_repetities ADD CONSTRAINT FK_mltrep_product FOREIGN KEY (product_id) REFERENCES CiviProduct(id);

-- Vul de CiviSaldo tabel uit de saldolog
INSERT INTO CiviSaldo SELECT uid, saldo*100, moment AS laatst_veranderd
FROM saldolog WHERE (uid, moment) IN (
  SELECT uid, max(moment) FROM saldolog WHERE cie = 'maalcie' GROUP BY uid
) ORDER BY moment DESC