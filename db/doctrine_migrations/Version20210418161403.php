<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210418161403 extends AbstractMigration {
	public function getDescription(): string {
		return 'Voeg maker veld toe aan declaratieBon';
	}

	public function up(Schema $schema): void {
		$this->addSql('ALTER TABLE declaratie_bon ADD maker_id VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->addSql('ALTER TABLE declaratie_bon ADD CONSTRAINT FK_5879E79D68DA5EC3 FOREIGN KEY (maker_id) REFERENCES profielen (uid)');
		$this->addSql('CREATE INDEX IDX_5879E79D68DA5EC3 ON declaratie_bon (maker_id)');
	}

	public function down(Schema $schema): void {
		$this->addSql('ALTER TABLE declaratie_bon DROP FOREIGN KEY FK_5879E79D68DA5EC3');
		$this->addSql('DROP INDEX IDX_5879E79D68DA5EC3 ON declaratie_bon');
		$this->addSql('ALTER TABLE declaratie_bon DROP maker_id');
	}
}
