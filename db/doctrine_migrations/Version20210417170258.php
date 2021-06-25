<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210417170258 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Maak de nieuwe groep en groep_lid tabellen.';
	}

	public function up(Schema $schema): void
	{
		// Zet de auto increment hoog zodat oud_id altijd lager dan id
		$this->addSql('CREATE TABLE groep (id INT AUTO_INCREMENT NOT NULL, maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, oud_id INT DEFAULT NULL, naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, begin_moment DATETIME NOT NULL, eind_moment DATETIME DEFAULT NULL, status ENUM(\'ft\', \'ht\', \'ot\') COMMENT \'(DC2Type:enumGroepStatus)\' NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', samenvatting LONGTEXT NOT NULL, omschrijving LONGTEXT DEFAULT NULL, keuzelijst VARCHAR(255) DEFAULT NULL, versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL COMMENT \'(DC2Type:enumGroepVersie)\', keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL, groep_type VARCHAR(255) NOT NULL, aanmeld_limiet INT DEFAULT NULL, aanmelden_vanaf DATETIME DEFAULT NULL, aanmelden_tot DATETIME DEFAULT NULL, bewerken_tot DATETIME DEFAULT NULL, afmelden_tot DATETIME DEFAULT NULL, soort VARCHAR(255) DEFAULT NULL, rechten_aanmelden VARCHAR(255) DEFAULT NULL, locatie VARCHAR(255) DEFAULT NULL, in_agenda TINYINT(1) DEFAULT NULL, bijbeltekst LONGTEXT DEFAULT NULL, verticale CHAR(1) DEFAULT NULL, kring_nummer INT DEFAULT NULL, lidjaar INT DEFAULT NULL, letter CHAR(1) DEFAULT NULL, eetplan TINYINT(1) DEFAULT NULL, INDEX IDX_270256943A4A27C1 (maker_uid), UNIQUE INDEX UNIQ_270256944D508A76 (lidjaar), UNIQUE INDEX UNIQ_270256948E02EE0A (letter), PRIMARY KEY(id)) AUTO_INCREMENT = 3000 DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE groep_lid (groep_id INT NOT NULL, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, opmerking VARCHAR(255) DEFAULT NULL, opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL, lid_sinds DATETIME NOT NULL, INDEX IDX_C028741B539B0606 (uid), INDEX IDX_C028741B97983E4 (door_uid), INDEX IDX_C028741B9EB44EC5 (groep_id), INDEX lid_sinds (lid_sinds), PRIMARY KEY(groep_id, uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE groep ADD CONSTRAINT FK_270256943A4A27C1 FOREIGN KEY (maker_uid) REFERENCES profielen (uid)');
		$this->addSql('ALTER TABLE groep_lid ADD CONSTRAINT FK_C028741B539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->addSql('ALTER TABLE groep_lid ADD CONSTRAINT FK_C028741B97983E4 FOREIGN KEY (door_uid) REFERENCES profielen (uid)');
		$this->addSql('ALTER TABLE groep_lid ADD CONSTRAINT FK_C028741B9EB44EC5 FOREIGN KEY (groep_id) REFERENCES groep (id)');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE groep_lid DROP FOREIGN KEY FK_C028741B9EB44EC5');
		$this->addSql('DROP TABLE groep');
		$this->addSql('DROP TABLE groep_lid');
	}
}
