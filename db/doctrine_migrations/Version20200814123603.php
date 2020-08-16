<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814123603 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Voeg toestemming afschrijven en huisartsvelden voor novieten toe';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profielen ADD toestemmingAfschrijven TINYINT(1) DEFAULT NULL, ADD huisarts VARCHAR(255) DEFAULT NULL, ADD huisartsPlaats VARCHAR(255) DEFAULT NULL, ADD huisartsTelefoon VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE profielen DROP toestemmingAfschrijven, DROP huisarts, DROP huisartsPlaats, DROP huisartsTelefoon');
    }
}
