<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407093557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Maak foreign key van onetime_tokens naar accounts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE onetime_tokens ADD CONSTRAINT FK_D73D607A539B0606 FOREIGN KEY (uid) REFERENCES accounts (uid)');
        $this->addSql('CREATE INDEX IDX_D73D607A539B0606 ON onetime_tokens (uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE onetime_tokens DROP FOREIGN KEY FK_D73D607A539B0606');
        $this->addSql('DROP INDEX IDX_D73D607A539B0606 ON onetime_tokens');
    }
}
