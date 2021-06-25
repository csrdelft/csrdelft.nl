<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210420110647 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_270256949B1A6152 ON groep (in_agenda)');
        $this->addSql('CREATE INDEX IDX_2702569459040656 ON groep (familie)');
        $this->addSql('CREATE INDEX IDX_27025694C97624A2 ON groep (begin_moment)');
        $this->addSql('CREATE INDEX IDX_270256945CB07420 ON groep (huis_status)');
        $this->addSql('CREATE INDEX IDX_270256941CC8AEA4 ON groep (ondervereniging_status)');
        $this->addSql('CREATE INDEX IDX_27025694A54A531D ON groep (activiteit_soort)');
        $this->addSql('CREATE INDEX IDX_27025694611D94A4 ON groep (commissie_soort)');
        $this->addSql('CREATE INDEX IDX_27025694EC97E0BB ON groep (eetplan)');
        $this->addSql('CREATE INDEX IDX_270256941EB559E1 ON groep (kring_nummer)');
        $this->addSql('CREATE INDEX IDX_2702569416CBD341 ON groep (verticale)');
        $this->addSql('CREATE INDEX IDX_27025694C25A9097 ON groep (groep_type)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_270256949B1A6152 ON groep');
        $this->addSql('DROP INDEX IDX_2702569459040656 ON groep');
        $this->addSql('DROP INDEX IDX_27025694C97624A2 ON groep');
        $this->addSql('DROP INDEX IDX_270256945CB07420 ON groep');
        $this->addSql('DROP INDEX IDX_270256941CC8AEA4 ON groep');
        $this->addSql('DROP INDEX IDX_27025694A54A531D ON groep');
        $this->addSql('DROP INDEX IDX_27025694611D94A4 ON groep');
        $this->addSql('DROP INDEX IDX_27025694EC97E0BB ON groep');
        $this->addSql('DROP INDEX IDX_270256941EB559E1 ON groep');
        $this->addSql('DROP INDEX IDX_2702569416CBD341 ON groep');
        $this->addSql('DROP INDEX IDX_27025694C25A9097 ON groep');
    }
}
