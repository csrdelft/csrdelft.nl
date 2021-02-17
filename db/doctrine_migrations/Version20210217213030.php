<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210217213030 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Verwijder inline_html veld uit cms_paginas';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE cms_paginas DROP inline_html');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE cms_paginas ADD inline_html TINYINT(1) NOT NULL');
	}
}
