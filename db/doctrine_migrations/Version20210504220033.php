<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210504220033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE remote_login (id INT AUTO_INCREMENT NOT NULL, expires DATETIME NOT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', status ENUM(\'pending\', \'active\', \'accepted\', \'rejected\') COMMENT \'(DC2Type:enumRemoteLoginStatus)\' NOT NULL COMMENT \'(DC2Type:enumRemoteLoginStatus)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE remote_login');
    }
}
