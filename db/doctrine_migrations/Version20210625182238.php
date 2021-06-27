<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210625182238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Verwijder de log tabel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE log');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE log (ID INT AUTO_INCREMENT NOT NULL, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, ip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, locatie VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, moment DATETIME NOT NULL, url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, referer VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, useragent VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX uid (uid), INDEX moment (moment), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }
}
