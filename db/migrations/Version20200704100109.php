<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200704100109 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE civibestelling DROP FOREIGN KEY CiviBestelling_ibfk_1');
        $this->addSql('DROP INDEX IDX_290D88AC539B0606 ON civibestelling');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CiviBestelling ADD CONSTRAINT CiviBestelling_ibfk_1 FOREIGN KEY (uid) REFERENCES civisaldo (uid)');
        $this->addSql('CREATE INDEX IDX_290D88AC539B0606 ON CiviBestelling (uid)');
    }
}
