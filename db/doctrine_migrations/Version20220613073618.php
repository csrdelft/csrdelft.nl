<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220613073618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix rechten savedquery';
    }

    public function up(Schema $schema): void
    {
			$this->addSql("UPDATE savedquery SET permissie = REPLACE(permissie, 'P_', 'ROLE_') WHERE TRUE;");
    }

    public function down(Schema $schema): void
    {
			$this->addSql("UPDATE savedquery SET permissie = REPLACE(permissie, 'ROLE_', 'P_') WHERE TRUE;");
    }
}
