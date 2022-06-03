<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220525195704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Maak oauth2_remember tabel voor het bewaren dat we ooit een applicatie hebben gebruikt.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oauth2_remember (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, client_identifier VARCHAR(255) NOT NULL, remember_since DATETIME NOT NULL, last_used DATETIME NOT NULL, scopes VARCHAR(255) NOT NULL, INDEX IDX_8D079B71539B0606 (uid), UNIQUE INDEX account_client (uid, client_identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth2_remember ADD CONSTRAINT FK_8D079B71539B0606 FOREIGN KEY (uid) REFERENCES accounts (uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE oauth2_remember');
    }
}
