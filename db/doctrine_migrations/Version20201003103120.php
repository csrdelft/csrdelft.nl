<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201003103120 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Voeg schrijfrechten toe aan document_categorie';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE document_categorie ADD schrijfrechten VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE document_categorie SET schrijfrechten = \'P_DOCS_MOD\' WHERE schrijfrechten = \'\'');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE document_categorie DROP schrijfrechten');
    }
}
