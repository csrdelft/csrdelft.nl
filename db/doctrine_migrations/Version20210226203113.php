<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210226203113 extends AbstractMigration {
	public function getDescription(): string {
		return 'Haal foutmeldingoptie voor zijbalk weg bij leden';
	}

	public function up(Schema $schema): void {
		$this->addSql("DELETE FROM lidinstellingen WHERE module = 'zijbalk' AND instelling = 'ishetal' AND waarde = 'foutmelding'");
	}

	public function down(Schema $schema): void {
		// Niet van toepassing, kan verwijderde rijen niet terugtoveren
	}
}
