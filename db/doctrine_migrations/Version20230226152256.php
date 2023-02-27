<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230226152256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mlt_abonnementen ADD CONSTRAINT FK_CA961AB0539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
        $this->addSql('CREATE INDEX IDX_CA961AB0539B0606 ON mlt_abonnementen (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mlt_abonnementen DROP FOREIGN KEY FK_CA961AB0539B0606');
        $this->addSql('DROP INDEX IDX_CA961AB0539B0606 ON mlt_abonnementen');
    }
}
