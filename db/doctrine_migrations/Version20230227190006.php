<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227190006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Toevoegingen voor web-push';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE push_abonnement (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, client_endpoint VARCHAR(255) NOT NULL, client_keys VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE push_abonnement');
    }
}
