<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107140732 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Herbouw de FULLTEXT indexes op forum_draden (titel) en document (naam, filename)';
    }

    public function up(Schema $schema) : void
    {
			$this->addSql("ALTER TABLE `forum_draden`	DROP INDEX `titel`;");
			$this->addSql("ALTER TABLE `forum_draden`	ADD FULLTEXT INDEX `titel` (`titel`);");

			$this->addSql("ALTER TABLE `document`	DROP INDEX `Zoeken`;");
			$this->addSql("ALTER TABLE `document` ADD FULLTEXT INDEX `Zoeken` (`naam`, `filename`);");
    }

    public function down(Schema $schema) : void
    {
        // Er is geen down migratie, vorige state is fout
    }
}
