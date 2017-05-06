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
SET NAMES utf8;

CREATE TABLE CiviLog
(
  id        INT(11) NOT NULL AUTO_INCREMENT,
  ip        VARCHAR(255) NOT NULL,
  type      ENUM ('insert','remove','create','update','delete') NOT NULL,
  data      VARCHAR(255) NOT NULL,
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE CiviCategorie
(
  id			INT(11) NOT NULL AUTO_INCREMENT,
  type			VARCHAR(255) NOT NULL,
  status 		INT(11) NOT NULL,
  cie			ENUM('soccie', 'maalcie', 'anders') NULL DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE CiviProduct
(
  id           INT(11) NOT NULL AUTO_INCREMENT,
  status       INT(11) NOT NULL,
  beschrijving TEXT NOT NULL,
  prioriteit   INT(11) NOT NULL,
  beheer       TINYINT(1) NOT NULL,
  categorie_id INT(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_CP_categorie FOREIGN KEY (categorie_id)
  REFERENCES CiviCategorie(id)
);

CREATE TABLE CiviPrijs
(
  van       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tot       TIMESTAMP NULL,
  product_id INT(11) NOT NULL,
  prijs     INT(11) NOT NULL,
  PRIMARY KEY (van, product_id),
  CONSTRAINT FK_CP_product FOREIGN KEY (product_id)
  REFERENCES CiviProduct(id)
  ON DELETE CASCADE
);

CREATE TABLE CiviBestelling
(
  id      INT(11)    NOT NULL AUTO_INCREMENT,
  uid     VARCHAR(4) NOT NULL,
  totaal  INT(11)    NOT NULL,
  deleted TINYINT(1) NOT NULL,
  moment   TIMESTAMP NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE CiviBestellingInhoud
(
  bestelling_id INT(11) NOT NULL,
  product_id    INT(11) NOT NULL,
  aantal       INT(11) NOT NULL,
  PRIMARY KEY (bestelling_id, product_id),
  CONSTRAINT FK_CBI_product FOREIGN KEY (product_id)
  REFERENCES CiviProduct(id),
  CONSTRAINT FK_CBI_bestelling FOREIGN KEY (bestelling_id)
  REFERENCES CiviBestelling(id)
);

CREATE TABLE CiviSaldo
(
  id INT(11) NOT NULL AUTO_INCREMENT,
  uid VARCHAR(4) NOT NULL,
  naam TEXT,
  saldo INT(11) NOT NULL,
  laatst_veranderd TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted TINYINT(1) NOT NULL,
  PRIMARY KEY (id)
);

ALTER TABLE mlt_maaltijden ADD COLUMN product_id INT(11) NOT NULL;
ALTER TABLE mlt_maaltijden ADD COLUMN verwerkt TINYINT(1) NOT NULL;
ALTER TABLE mlt_repetities ADD COLUMN product_id INT(11) NOT NULL;
ALTER TABLE mlt_repetities CHANGE standaard_prijs standaard_prijs INT(11) NULL;

INSERT INTO CiviCategorie (id, status, type, cie)
VALUES (1, 1, 'Maaltijd', 'maalcie');

INSERT INTO CiviCategorie (id, status, type, cie)
VALUES (2, 1, 'Mutatie', 'anders');

INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer, categorie_id)
VALUES (1,  1, 'Anders', 1, 1, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NULL, 1, 0);
UPDATE mlt_maaltijden SET product_id = 1 WHERE mlt_repetitie_id IS NULL;

-- Donderdagmaaltijd
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer, categorie_id)
VALUES (2, 1, 'Donderdagmaaltijd', 1, 0, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NULL, 2, 350);
UPDATE mlt_maaltijden SET product_id = 2 WHERE mlt_repetitie_id = 1;
UPDATE mlt_repetities SET product_id = 2 WHERE mlt_repetitie_id = 1;

-- Verticalekring
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer, categorie_id)
VALUES (3, 1, 'Verticalekring', 1, 0, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NULL, 3, 350);
UPDATE mlt_maaltijden SET product_id = 3 WHERE mlt_repetitie_id IN (2,3,4,5,6,7,8,9,13);
UPDATE mlt_repetities SET product_id = 3 WHERE mlt_repetitie_id IN (2,3,4,5,6,7,8,9,13);

-- DéDé Diner
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer, categorie_id)
VALUES (4, 1, 'DéDé-Diner', 1, 0, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NULL, 4, 350);
UPDATE mlt_maaltijden SET product_id = 4 WHERE mlt_repetitie_id = 10;
UPDATE mlt_repetities SET product_id = 4 WHERE mlt_repetitie_id = 10;

-- Alpha cursus
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer, categorie_id)
VALUES (5, 1, 'Alpha Cursus', 1, 0, 1);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NULL, 5, 0);
UPDATE mlt_maaltijden SET product_id = 5 WHERE mlt_repetitie_id = 11;
UPDATE mlt_repetities SET product_id = 5 WHERE mlt_repetitie_id = 11;

-- Cent (voor mutaties)
INSERT INTO CiviProduct (id, status, beschrijving, prioriteit, beheer, categorie_id)
VALUES(6, 1, 'Cent', 1, 1, 2);
INSERT INTO CiviPrijs (van, tot, product_id, prijs)
VALUES (NOW(), NULL, 6, 1);

-- Leg foreign keys aan
ALTER TABLE mlt_maaltijden ADD CONSTRAINT FK_mlt_product FOREIGN KEY (product_id) REFERENCES CiviProduct(id);
ALTER TABLE mlt_repetities ADD CONSTRAINT FK_mltrep_product FOREIGN KEY (product_id) REFERENCES CiviProduct(id);
SQL
		);

		// Voeg de menu items toe in de database
		$actueelMenu = $this->fetchRow("SELECT item_id FROM menus WHERE link='/actueel'")[0];
		$this->insert('menus', [
			'parent_id' => $actueelMenu,
			'volgorde' => 0,
			'tekst' => 'CiviSaldo',
			'link' => '/fiscaat',
			'rechten_bekijken' => 'P_MAAL_IK',
			'zichtbaar' => 1
		]);
		$civiMenu = $this->fetchRow("SELECT LAST_INSERT_ID()")[0];
		$this->insert('menus', [
			[
				'parent_id' => $civiMenu,
				'volgorde' => 1,
				'tekst' => 'Overzicht',
				'link' => '/fiscaat/overzicht',
				'rechten_bekijken' => 'P_MAAL_MOD',
				'zichtbaar' => 1
			], [
				'parent_id' => $civiMenu,
				'volgorde' => 2,
				'tekst' => 'Saldobeheer',
				'link' => '/fiscaat/saldo',
				'rechten_bekijken' => 'P_MAAL_MOD',
				'zichtbaar' => 1
			], [
				'parent_id' => $civiMenu,
				'volgorde' => 3,
				'tekst' => 'Productbeheer',
				'link' => '/fiscaat/producten',
				'rechten_bekijken' => 'P_MAAL_MOD',
				'zichtbaar' => 1
			], [
				'parent_id' => $civiMenu,
				'volgorde' => 4,
				'tekst' => 'Bestellingen',
				'link' => '/fiscaat/bestellingen',
				'rechten_bekijken' => 'P_MAAL_IK',
				'zichtbaar' => 1
			]
		]);

		// Migreer de saldolog tabel naar CiviSaldo
		$gebruikers = $this->fetchAll("SELECT distinct(uid) FROM saldolog WHERE cie = 'maalcie'");

		foreach ($gebruikers as $index => $gebruiker) {
			$gebruiker = $this->fetchRow("SELECT uid, saldo, moment as laatst_veranderd FROM saldolog WHERE uid='${gebruiker['uid']}' ORDER BY moment DESC LIMIT 1");

			$saldo = $gebruiker['saldo'] * 100;
			$this->execute(sprintf(
				"INSERT INTO CiviSaldo (uid, saldo, laatst_veranderd, deleted)
						VALUES ('%s', %d, '%s', FALSE)",
				$gebruiker['uid'], $saldo, $gebruiker['laatst_veranderd']));
			$this->execute(sprintf(
				"INSERT INTO CiviBestelling (id, uid, totaal, deleted, moment)
						VALUES (%d, '%s', %d, %d, '%s')",
				$index + 1, $gebruiker['uid'], -$saldo, 0, $gebruiker['laatst_veranderd']));
			$this->execute(sprintf(
				"INSERT INTO CiviBestellingInhoud (bestelling_id, product_id, aantal)
						VALUES (%d, %d, %d)",
				$index + 1, 6, -$saldo));
			$this->execute(sprintf(
				"INSERT INTO CiviLog (ip, type, data)
						VALUES ('0.0.0.0', 'create', '{\"user\": \"%s\", \"saldo\": %d}')",
				$gebruiker['uid'], $saldo));
		}

		// Zet alle (verleden) maaltijden op verwerkt
		$this->execute("UPDATE mlt_maaltijden SET verwerkt = TRUE WHERE verwijderd = FALSE AND gesloten = FALSE");
	}

	public function down() {
		try {
			$this->execute("ALTER TABLE mlt_maaltijden DROP FOREIGN KEY FK_mlt_product;");
		} catch (Exception $ignore) { echo "FK_mlt_product al verwijderd"; }
		try {
			$this->execute("ALTER TABLE mlt_maaltijden DROP COLUMN product_id;");
		} catch (Exception $ignore) { echo "mlt_maaltijden(product_id) al verwijderd"; }
		try {
			$this->execute("ALTER TABLE mlt_maaltijden DROP COLUMN verwerkt;");
		} catch (Exception $ignore) { echo "mlt_maaltijden(verwerkt) al verwijderd"; }
		try {
			$this->execute("ALTER TABLE mlt_repetities DROP FOREIGN KEY FK_mltrep_product;");
		} catch (Exception $ignore) { echo "FK_mltrep_product al verwijderd"; }
		try {
			$this->execute("ALTER TABLE mlt_repetities DROP COLUMN product_id;");
		} catch (Exception $ignore) { echo "mlt_repetities(product_id) al verwijderd"; }
		try {
			$actueelMenu = $this->fetchRow("SELECT item_id FROM menus WHERE link='/actueel'")[0];
			$civiMenu = $this->fetchRow("SELECT item_id FROM menus WHERE link='/fiscaat' AND parent_id=$actueelMenu")[0];
			$this->execute("DELETE FROM menus WHERE parent_id=$civiMenu");
			$this->execute("DELETE FROM menus WHERE item_id=$civiMenu");
		} catch (Exception $ignore) { echo "CiviSaldo menus al verwijderd"; }
		try {
			$this->execute("DROP TABLE IF EXISTS CiviBestellingInhoud, CiviBestelling, CiviPrijs, CiviProduct, CiviLog, CiviSaldo, CiviCategorie;");
		} catch (Exception $ignore) { echo "sommige tabellen al verwijderd"; }
		$this->execute("ALTER TABLE `mlt_repetities` CHANGE `standaard_prijs` `standaard_prijs` INT(11) NOT NULL;");
	}
}
