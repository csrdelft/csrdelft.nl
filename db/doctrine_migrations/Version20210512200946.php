<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210512200946 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Zet druif aan voor alle leden, novieten en gastleden';
	}

	public function up(Schema $schema): void
	{
		$this->addSql(<<<SQL
					INSERT INTO lidinstellingen (uid, module, instelling, waarde)
					SELECT uid, "layout", "druif", "2020"
					FROM profielen
					WHERE status IN ("S_LID", "S_NOVIET", "S_GASTLID")
				SQL
		);
	}

	public function down(Schema $schema): void
	{
		$this->addSql(<<<SQL
					DELETE FROM lidinstellingen
					WHERE module = "layout" AND instelling = "druif"
				SQL
		);
	}
}
