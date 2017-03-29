CREATE TABLE CiviLog
(
  id        INT(11) NOT NULL AUTO_INCREMENT,
  ip        VARCHAR(15),
  type      ENUM ('insert', 'update', 'remove'),
  value     TEXT,
  timestamp TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE CiviBestelling
(
  id      INT(11)    NOT NULL AUTO_INCREMENT,
  uid     VARCHAR(4) NOT NULL,
  totaal  INT(11)    NOT NULL,
  deleted TINYINT(1) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE CiviBestellinginhoud
(
  bestellingid INT(11) NOT NULL,
  productid    INT(11) NOT NULL,
  aantal       INT(11),
  PRIMARY KEY (bestellingid, productid)
);

CREATE TABLE CiviProduct
(
  id           INT(11) NOT NULL AUTO_INCREMENT,
  status       INT(11),
  beschrijving TEXT,
  prioriteit   INT(11),
  beheer       TINYINT(1),
  PRIMARY KEY (id)
);

CREATE TABLE CiviPrijs
(
  van       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tot       TIMESTAMP NOT NULL,
  productid INT(11),
  prijs     INT(11),
  PRIMARY KEY (van, productid)
);