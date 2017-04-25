<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class CiviSaldoMigratie
 *
 * Onderdeel van Maalcie refactor 4 #263
 */
class CiviSaldoMigratie extends AbstractMigration {
	public function up() {
		$this->execute(<<<SQL
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

INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer)
VALUES (1,  1, 'Anders', 1, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NOW(), 1, 0);
UPDATE mlt_maaltijden SET product_id = 1 WHERE mlt_repetitie_id IS NULL;

-- Donderdagmaaltijd
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer)
VALUES (2, 1, 'Donderdagmaaltijd', 1, 0);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, 2, 350);
UPDATE mlt_maaltijden SET product_id = 2 WHERE mlt_repetitie_id = 1;
UPDATE mlt_repetities SET product_id = 2 WHERE mlt_repetitie_id = 1;

-- Verticalekring
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer)
VALUES (3, 1, 'Verticalekring', 1, 0);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, 3, 350);
UPDATE mlt_maaltijden SET product_id = 3 WHERE mlt_repetitie_id IN (2,3,4,5,6,7,8,9,13);
UPDATE mlt_repetities SET product_id = 3 WHERE mlt_repetitie_id IN (2,3,4,5,6,7,8,9,13);

-- DéDé Diner
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer)
VALUES (4, 1, 'DéDé-Diner', 1, 0);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, 4, 350);
UPDATE mlt_maaltijden SET product_id = 4 WHERE mlt_repetitie_id = 10;
UPDATE mlt_repetities SET product_id = 4 WHERE mlt_repetitie_id = 10;

-- Alpha cursus
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer)
VALUES (5, 1, 'Alpha Cursus', 1, 0);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, 5, 0);
UPDATE mlt_maaltijden SET product_id = 5 WHERE mlt_repetitie_id = 11;
UPDATE mlt_repetities SET product_id = 5 WHERE mlt_repetitie_id = 11;

-- Cent (voor mutaties)
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer)
VALUES(6, 1, 'Cent', 1, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), 0, 6, 1);

-- Leg foreign keys aan
ALTER TABLE mlt_maaltijden ADD CONSTRAINT FK_mlt_product FOREIGN KEY (product_id) REFERENCES CiviProduct(id);
ALTER TABLE mlt_repetities ADD CONSTRAINT FK_mltrep_product FOREIGN KEY (product_id) REFERENCES CiviProduct(id);
SQL
		);

		$gebruikers = $this->fetchAll("SELECT uid, saldo, moment AS laatst_veranderd
FROM saldolog WHERE (uid, moment) IN (
  SELECT uid, max(moment) FROM saldolog WHERE cie = 'maalcie' GROUP BY uid
) ORDER BY moment DESC");

		// Migreer de saldolog tabel naar CiviSaldo
		foreach ($gebruikers as $index => $gebruiker) {
			$saldo = $gebruiker['saldo'] * 100;
			$this->execute(sprintf(
				"INSERT INTO CiviSaldo (uid, saldo, laatst_veranderd) 
						VALUES ('%s', %d, '%s')",
				$gebruiker['uid'], $saldo, $gebruiker['laatst_veranderd']));
			$this->execute(sprintf(
				"INSERT INTO CiviBestelling (id, uid, totaal, deleted) 
						VALUES (%d, '%s', %d, %d)",
				$index + 1, $gebruiker['uid'], $saldo, 0));
			$this->execute(sprintf(
				"INSERT INTO CiviBestellingInhoud (bestelling_id, product_id, aantal)
						VALUES (%d, %d, %d)",
				$index + 1, 6, $saldo));
			$this->execute(sprintf(
				"INSERT INTO CiviLog (ip, type, data)
						VALUES ('0.0.0.0', 'create', '{user: %s, saldo: %d}')",
				$gebruiker['uid'], $saldo));
		}
	}

	public function down() {
		$this->execute(<<<SQL
-- Opschonen vorige keer
ALTER TABLE mlt_maaltijden DROP FOREIGN KEY FK_mlt_product;
ALTER TABLE mlt_maaltijden DROP COLUMN product_id;
ALTER TABLE mlt_repetities DROP FOREIGN KEY FK_mltrep_product;
ALTER TABLE mlt_repetities DROP COLUMN product_id;

DROP TABLE `CiviBestellingInhoud`, `CiviBestelling`, `CiviPrijs`, `CiviProduct`, `CiviLog`, `CiviSaldo`;
SQL
		);
	}
}
