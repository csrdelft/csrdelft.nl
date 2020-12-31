<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201231165008 extends AbstractMigration {
	public function getDescription(): string {
		return 'Voeg lid toe aan CiviMelder deelnemer';
	}

	public function up(Schema $schema): void {
		$this->addSql('ALTER TABLE civimelder_deelnemer ADD uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->addSql('ALTER TABLE civimelder_deelnemer ADD CONSTRAINT FK_6BC8A6B8539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->addSql('CREATE INDEX IDX_6BC8A6B8539B0606 ON civimelder_deelnemer (uid)');
	}

	public function down(Schema $schema): void {
		$this->addSql('ALTER TABLE civimelder_deelnemer DROP FOREIGN KEY FK_6BC8A6B8539B0606');
		$this->addSql('DROP INDEX IDX_6BC8A6B8539B0606 ON civimelder_deelnemer');
		$this->addSql('ALTER TABLE civimelder_deelnemer DROP uid');
	}
}
