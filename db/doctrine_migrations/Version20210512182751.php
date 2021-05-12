<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210512182751 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('CREATE TABLE civimelder_activiteit (id INT AUTO_INCREMENT NOT NULL, reeks_id INT NOT NULL, titel VARCHAR(255) DEFAULT NULL, beschrijving LONGTEXT DEFAULT NULL, capaciteit INT DEFAULT NULL, rechten_aanmelden VARCHAR(255) DEFAULT NULL, rechten_lijst_bekijken VARCHAR(255) DEFAULT NULL, rechten_lijst_beheren VARCHAR(255) DEFAULT NULL, max_gasten INT DEFAULT NULL, aanmelden_mogelijk TINYINT(1) DEFAULT NULL, aanmelden_vanaf INT DEFAULT NULL, aanmelden_tot INT DEFAULT NULL, afmelden_mogelijk TINYINT(1) DEFAULT NULL, afmelden_tot INT DEFAULT NULL, start DATETIME NOT NULL, einde DATETIME NOT NULL, gesloten TINYINT(1) NOT NULL, INDEX IDX_99F3BB14488E123 (reeks_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE civimelder_deelnemer (id INT AUTO_INCREMENT NOT NULL, activiteit_id INT NOT NULL, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, aantal INT NOT NULL, aangemeld DATETIME NOT NULL, INDEX IDX_6BC8A6B85A8A0A1 (activiteit_id), INDEX IDX_6BC8A6B8539B0606 (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE civimelder_reeks (id INT AUTO_INCREMENT NOT NULL, titel VARCHAR(255) DEFAULT NULL, beschrijving LONGTEXT DEFAULT NULL, capaciteit INT DEFAULT NULL, rechten_aanmelden VARCHAR(255) DEFAULT NULL, rechten_lijst_bekijken VARCHAR(255) DEFAULT NULL, rechten_lijst_beheren VARCHAR(255) DEFAULT NULL, max_gasten INT DEFAULT NULL, aanmelden_mogelijk TINYINT(1) DEFAULT NULL, aanmelden_vanaf INT DEFAULT NULL, aanmelden_tot INT DEFAULT NULL, afmelden_mogelijk TINYINT(1) DEFAULT NULL, afmelden_tot INT DEFAULT NULL, naam VARCHAR(255) NOT NULL, rechten_aanmaken VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE civimelder_activiteit ADD CONSTRAINT FK_99F3BB14488E123 FOREIGN KEY (reeks_id) REFERENCES civimelder_reeks (id)');
		$this->addSql('ALTER TABLE civimelder_deelnemer ADD CONSTRAINT FK_6BC8A6B85A8A0A1 FOREIGN KEY (activiteit_id) REFERENCES civimelder_activiteit (id)');
		$this->addSql('ALTER TABLE civimelder_deelnemer ADD CONSTRAINT FK_6BC8A6B8539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE civimelder_deelnemer DROP FOREIGN KEY FK_6BC8A6B85A8A0A1');
		$this->addSql('ALTER TABLE civimelder_deelnemer DROP FOREIGN KEY FK_6BC8A6B8539B0606');
		$this->addSql('ALTER TABLE civimelder_activiteit DROP FOREIGN KEY FK_99F3BB14488E123');
		$this->addSql('DROP TABLE civimelder_activiteit');
		$this->addSql('DROP TABLE civimelder_deelnemer');
		$this->addSql('DROP TABLE civimelder_reeks');
	}
}
