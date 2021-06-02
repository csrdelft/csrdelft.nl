<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210602205013 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Maak tabellen voor CiviMelder';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('CREATE TABLE aanmelder_activiteit (id INT AUTO_INCREMENT NOT NULL, reeks_id INT NOT NULL, titel VARCHAR(255) DEFAULT NULL, beschrijving LONGTEXT DEFAULT NULL, capaciteit INT DEFAULT NULL, rechten_aanmelden VARCHAR(255) DEFAULT NULL, rechten_lijst_bekijken VARCHAR(255) DEFAULT NULL, rechten_lijst_beheren VARCHAR(255) DEFAULT NULL, max_gasten INT DEFAULT NULL, aanmelden_mogelijk TINYINT(1) DEFAULT NULL, aanmelden_vanaf INT DEFAULT NULL, aanmelden_tot INT DEFAULT NULL, afmelden_mogelijk TINYINT(1) DEFAULT NULL, afmelden_tot INT DEFAULT NULL, start DATETIME NOT NULL, einde DATETIME NOT NULL, gesloten TINYINT(1) NOT NULL, INDEX IDX_EA9C634F488E123 (reeks_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE aanmelder_deelnemer (id INT AUTO_INCREMENT NOT NULL, activiteit_id INT NOT NULL, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, aantal INT NOT NULL, aangemeld DATETIME NOT NULL, aanwezig DATETIME DEFAULT NULL, INDEX IDX_D8067E3F5A8A0A1 (activiteit_id), INDEX IDX_D8067E3F539B0606 (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE aanmelder_reeks (id INT AUTO_INCREMENT NOT NULL, titel VARCHAR(255) DEFAULT NULL, beschrijving LONGTEXT DEFAULT NULL, capaciteit INT DEFAULT NULL, rechten_aanmelden VARCHAR(255) DEFAULT NULL, rechten_lijst_bekijken VARCHAR(255) DEFAULT NULL, rechten_lijst_beheren VARCHAR(255) DEFAULT NULL, max_gasten INT DEFAULT NULL, aanmelden_mogelijk TINYINT(1) DEFAULT NULL, aanmelden_vanaf INT DEFAULT NULL, aanmelden_tot INT DEFAULT NULL, afmelden_mogelijk TINYINT(1) DEFAULT NULL, afmelden_tot INT DEFAULT NULL, naam VARCHAR(255) NOT NULL, rechten_aanmaken VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE aanmelder_activiteit ADD CONSTRAINT FK_EA9C634F488E123 FOREIGN KEY (reeks_id) REFERENCES aanmelder_reeks (id)');
		$this->addSql('ALTER TABLE aanmelder_deelnemer ADD CONSTRAINT FK_D8067E3F5A8A0A1 FOREIGN KEY (activiteit_id) REFERENCES aanmelder_activiteit (id)');
		$this->addSql('ALTER TABLE aanmelder_deelnemer ADD CONSTRAINT FK_D8067E3F539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE aanmelder_deelnemer DROP FOREIGN KEY FK_D8067E3F5A8A0A1');
		$this->addSql('ALTER TABLE aanmelder_activiteit DROP FOREIGN KEY FK_EA9C634F488E123');
		$this->addSql('DROP TABLE aanmelder_activiteit');
		$this->addSql('DROP TABLE aanmelder_deelnemer');
		$this->addSql('DROP TABLE aanmelder_reeks');
	}
}
