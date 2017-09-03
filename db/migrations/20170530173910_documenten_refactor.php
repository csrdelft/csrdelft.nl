<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class DocumentenRefactor.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentenRefactor extends AbstractMigration {

	/**
	 * Verplaats document en documentcategorie naar documen_old en documentcategorie_old.
	 *
	 * Leg Document en DocumentCategorie tabellen aan.
	 *
	 * Kopieer document_old naar Document en documentcategorie_old naar DocumentCategorie.
	 */
	public function up() {
		$this->execute('ALTER TABLE `document` RENAME TO `document_old`;');
		$this->execute('ALTER TABLE `documentcategorie` RENAME TO `documentcategorie_old`;');

		$this->execute(<<<SQL
CREATE TABLE `Document` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `naam` VARCHAR(255) NOT NULL,
  `categorie_id` INT(11) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `filesize` INT(11) NOT NULL,
  `mimetype` VARCHAR(255) NOT NULL,
  `toegevoegd` DATETIME NOT NULL,
  `eigenaar` VARCHAR(4) NOT NULL,
  `leesrechten` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catID` (`categorie_id`),
  KEY `toegevoegd` (`toegevoegd`),
  KEY `eigenaar` (`eigenaar`),
  FULLTEXT KEY `Zoeken` (`naam`,`filename`)
) ENGINE=MyISAM AUTO_INCREMENT=1208 DEFAULT CHARSET=utf8;
SQL
		);

		$this->execute(<<<SQL
CREATE TABLE `DocumentCategorie` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `naam` VARCHAR(255) NOT NULL,
  `zichtbaar` TINYINT(1) NOT NULL,
  `leesrechten` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
SQL
		);

		$this->execute(<<<SQL
INSERT INTO `DocumentCategorie`
SELECT ID AS id, naam, zichtbaar, leesrechten
FROM `documentcategorie_old`;
SQL
		);

		$this->execute(<<<SQL
INSERT INTO `Document`
SELECT ID AS id, naam, catID AS categorie_id, filename, filesize, mimetype, toegevoegd, eigenaar, leesrechten
FROM `document_old`;
SQL
		);
	}

	/**
	 * Verwijder Document en DocumentCategorie tabellen en plaats document_old en documentcategorie_old weer terug.
	 */
	public function down() {
		$this->execute(<<<SQL
DROP TABLE `Document`;
DROP TABLE `DocumentCategorie`;

ALTER TABLE `document_old` RENAME TO `document`;
ALTER TABLE `documentcategorie_old` RENAME TO `documentcategorie`;
SQL
		);
	}
}
