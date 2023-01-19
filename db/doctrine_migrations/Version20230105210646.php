<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230105210646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update naar league/oauth2-server-bundle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth2_client ADD name VARCHAR(128) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth2_client DROP name');
    }
}
