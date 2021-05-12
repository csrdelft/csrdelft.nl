<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210418143428 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Splits soort in groep in meerdere kolommen met enums';
	}

	public function up(Schema $schema): void
	{
		$this->addSql("ALTER TABLE groep ADD activiteit_soort ENUM('vereniging', 'lustrum', 'dies', 'owee', 'sjaarsactie', 'lichting', 'verticale', 'kring', 'huis', 'ondervereniging', 'ifes', 'extern') COMMENT '(DC2Type:enumActiviteitSoort)' DEFAULT NULL COMMENT '(DC2Type:enumActiviteitSoort)', ADD commissie_soort ENUM('c', 's', 'b', 'e') COMMENT '(DC2Type:enumCommissieSoort)' DEFAULT NULL COMMENT '(DC2Type:enumCommissieSoort)', ADD ondervereniging_status ENUM('a', 'o', 'v') COMMENT '(DC2Type:enumOnderverenigingStatus)' DEFAULT NULL COMMENT '(DC2Type:enumOnderverenigingStatus)', ADD huis_status ENUM('w', 'h') COMMENT '(DC2Type:enumHuisStatus)' DEFAULT NULL COMMENT '(DC2Type:enumHuisStatus)',  CHANGE begin_moment begin_moment DATETIME DEFAULT NULL, CHANGE status status ENUM('ft', 'ht', 'ot') COMMENT '(DC2Type:enumGroepStatus)' NOT NULL COMMENT '(DC2Type:enumGroepStatus)'");
		$this->addSql("UPDATE groep SET activiteit_soort = soort WHERE groep_type = 'activiteit'");
		$this->addSql("UPDATE groep SET commissie_soort = soort WHERE groep_type = 'commissie'");
		$this->addSql("UPDATE groep SET ondervereniging_status = soort WHERE groep_type = 'ondervereniging'");
		$this->addSql("UPDATE groep SET huis_status = soort WHERE groep_type = 'woonoord'");
		$this->addSql('ALTER TABLE groep DROP soort');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE groep ADD soort VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, DROP activiteit_soort, DROP commissie_soort, DROP ondervereniging_status, DROP huis_status, CHANGE begin_moment begin_moment DATETIME NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') COMMENT \'(DC2Type:enumGroepStatus)\' CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci` COMMENT \'(DC2Type:enumGroepStatus)\'');
	}
}
