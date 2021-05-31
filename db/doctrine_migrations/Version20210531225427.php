<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210531225427 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Voeg aanwezig tijd toe bij deelnemer';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE civimelder_deelnemer ADD aanwezig DATETIME DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE civimelder_deelnemer DROP aanwezig');
	}
}
