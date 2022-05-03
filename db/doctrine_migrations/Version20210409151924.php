<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210409151924 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Repareer forum_draden en forum_posts laatst_gewijzigd en laatste_post_id';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('UPDATE forum_posts SET laatst_gewijzigd = datum_tijd WHERE laatst_gewijzigd IS NULL');
		$this->addSql('UPDATE forum_draden SET laatste_post_id = (SELECT post_id FROM forum_posts WHERE forum_posts.draad_id = forum_draden.draad_id ORDER BY laatst_gewijzigd DESC LIMIT 1) WHERE laatste_post_id IS NULL');
		$this->addSql('UPDATE forum_draden SET laatst_gewijzigd = (SELECT laatst_gewijzigd FROM forum_posts WHERE draad_id = forum_draden.draad_id ORDER BY laatst_gewijzigd DESC LIMIT 1) WHERE laatst_gewijzigd IS NULL');
		//$this->addSql('UPDATE forum_draden SET laatst_gewijzigd = (SELECT laatst_gewijzigd FROM forum_posts WHERE draad_id = forum_draden.draad_id ORDER BY laatst_gewijzigd DESC LIMIT 1) WHERE laatst_gewijzigd = "0000-00-00 00:00:00"');
		$this->addSql('UPDATE forum_draden SET laatste_wijziging_uid = (SELECT uid FROM forum_posts WHERE draad_id = forum_draden.draad_id ORDER BY laatst_gewijzigd DESC LIMIT 1) WHERE laatste_wijziging_uid IS NULL');
	}

	public function down(Schema $schema): void
	{
		// Liever niet weer stuk maken.
	}
}
