<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210509133540 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Maak tabellen aan voor declaraties';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('CREATE TABLE declaratie (id INT AUTO_INCREMENT NOT NULL, indiener_id VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, categorie_id INT NOT NULL, beoordelaar_id VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, csr_pas TINYINT(1) NOT NULL, rekening VARCHAR(255) DEFAULT NULL, naam VARCHAR(255) DEFAULT NULL, opmerkingen LONGTEXT DEFAULT NULL, totaal DOUBLE PRECISION NOT NULL, nummer VARCHAR(255) DEFAULT NULL, ingediend TINYINT(1) NOT NULL, beoordeeld TINYINT(1) NOT NULL, goedgekeurd TINYINT(1) NOT NULL, INDEX IDX_263FF3DDA38FC7CA (indiener_id), INDEX IDX_263FF3DDBCF5E72D (categorie_id), INDEX IDX_263FF3DD2EDC31FD (beoordelaar_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE declaratie_bon (id INT AUTO_INCREMENT NOT NULL, declaratie_id INT DEFAULT NULL, maker_id VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, bestand VARCHAR(255) NOT NULL, datum DATETIME DEFAULT NULL, INDEX IDX_5879E79D6AE7FC36 (declaratie_id), INDEX IDX_5879E79D68DA5EC3 (maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE declaratie_categorie (id INT AUTO_INCREMENT NOT NULL, wachtrij_id INT NOT NULL, naam VARCHAR(255) NOT NULL, INDEX IDX_F1F5C1F8E32FD936 (wachtrij_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE declaratie_regel (id INT AUTO_INCREMENT NOT NULL, bon_id INT NOT NULL, bedrag DOUBLE PRECISION NOT NULL, incl_btw TINYINT(1) NOT NULL, btw INT NOT NULL, omschrijving VARCHAR(255) NOT NULL, INDEX IDX_E75A37F2AD65737C (bon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE declaratie_wachtrij (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(255) NOT NULL, rechten VARCHAR(255) NOT NULL, positie INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE declaratie ADD CONSTRAINT FK_263FF3DDA38FC7CA FOREIGN KEY (indiener_id) REFERENCES profielen (uid)');
		$this->addSql('ALTER TABLE declaratie ADD CONSTRAINT FK_263FF3DDBCF5E72D FOREIGN KEY (categorie_id) REFERENCES declaratie_categorie (id)');
		$this->addSql('ALTER TABLE declaratie ADD CONSTRAINT FK_263FF3DD2EDC31FD FOREIGN KEY (beoordelaar_id) REFERENCES profielen (uid)');
		$this->addSql('ALTER TABLE declaratie_bon ADD CONSTRAINT FK_5879E79D6AE7FC36 FOREIGN KEY (declaratie_id) REFERENCES declaratie (id)');
		$this->addSql('ALTER TABLE declaratie_bon ADD CONSTRAINT FK_5879E79D68DA5EC3 FOREIGN KEY (maker_id) REFERENCES profielen (uid)');
		$this->addSql('ALTER TABLE declaratie_categorie ADD CONSTRAINT FK_F1F5C1F8E32FD936 FOREIGN KEY (wachtrij_id) REFERENCES declaratie_wachtrij (id)');
		$this->addSql('ALTER TABLE declaratie_regel ADD CONSTRAINT FK_E75A37F2AD65737C FOREIGN KEY (bon_id) REFERENCES declaratie_bon (id)');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie_bon DROP FOREIGN KEY FK_5879E79D6AE7FC36');
		$this->addSql('ALTER TABLE declaratie_regel DROP FOREIGN KEY FK_E75A37F2AD65737C');
		$this->addSql('ALTER TABLE declaratie DROP FOREIGN KEY FK_263FF3DDBCF5E72D');
		$this->addSql('ALTER TABLE declaratie_categorie DROP FOREIGN KEY FK_F1F5C1F8E32FD936');
		$this->addSql('DROP TABLE declaratie');
		$this->addSql('DROP TABLE declaratie_bon');
		$this->addSql('DROP TABLE declaratie_categorie');
		$this->addSql('DROP TABLE declaratie_regel');
		$this->addSql('DROP TABLE declaratie_wachtrij');
	}
}
