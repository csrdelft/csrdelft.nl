<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261013221513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Voegt een perm_role BaalCie toe (MaalCie+BASFCie). Bij downgraden wordt BaalCie in BASFCie veranderd.';
    }

    public function up(Schema $schema): void
    {
			$this->addSql(
				"
							ALTER TABLE accounts
							MODIFY COLUMN perm_role
								enum('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BAALCIE','R_BESTUUR','R_PUBCIE','R_FISCAAT','R_FORUM_MOD')
					"
			);
    }

    public function down(Schema $schema): void
    {
			// Verander BaalCie in BASFCie
			$this->addSql("
			UPDATE accounts
			SET perm_role = 'R_BASF'
			WHERE perm_role = 'R_BAALCIE'
				");

			// Haal BAALCIE weg
			$this->addSql(
				"
							ALTER TABLE accounts
							MODIFY COLUMN perm_role
								enum('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE','R_FISCAAT','R_FORUM_MOD')
					"
			);
    }
}
