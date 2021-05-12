<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200923201917 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Maak streeplijsten tabel';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE streeplijsten (id INT AUTO_INCREMENT NOT NULL, maker VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, aanmaakdatum DATETIME NOT NULL, inhoud_streeplijst LONGTEXT DEFAULT NULL, leden_streeplijst LONGTEXT DEFAULT NULL, naam_streeplijst VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE streeplijsten');
     }
}
