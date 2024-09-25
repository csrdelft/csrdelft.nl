<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925192739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE accounts CHANGE blocked_reason blocked_reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE agenda CHANGE beschrijving beschrijving LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE biebbeschrijving CHANGE beschrijving beschrijving LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE biebexemplaar CHANGE opmerking opmerking LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE changelog CHANGE old_value old_value LONGTEXT DEFAULT NULL, CHANGE new_value new_value LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE civi_product CHANGE beschrijving beschrijving LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE civi_saldo CHANGE naam naam LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE civi_saldo_log CHANGE data data LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE courant CHANGE inhoud inhoud LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE courantbericht CHANGE bericht bericht LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE crv_functies CHANGE email_bericht email_bericht LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE crv_taken CHANGE wanneer_gemaild wanneer_gemaild LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE debug_log CHANGE call_trace call_trace LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE forum_delen CHANGE omschrijving omschrijving LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE forum_draden_reageren CHANGE concept concept LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_posts CHANGE tekst tekst LONGTEXT NOT NULL, CHANGE bewerkt_tekst bewerkt_tekst LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE forumplaatjes CHANGE source_url source_url LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE instellingen CHANGE waarde waarde LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE lidinstellingen CHANGE waarde waarde LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE lidtoestemmingen CHANGE waarde waarde LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE mlt_archief CHANGE aanmeldingen aanmeldingen LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE mlt_maaltijden CHANGE omschrijving omschrijving LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE peiling CHANGE beschrijving beschrijving LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE peiling_optie CHANGE beschrijving beschrijving LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE profielen CHANGE kgb kgb LONGTEXT DEFAULT NULL, CHANGE novitiaat novitiaat LONGTEXT DEFAULT NULL, CHANGE vrienden vrienden LONGTEXT DEFAULT NULL, CHANGE medisch medisch LONGTEXT DEFAULT NULL, CHANGE novitiaatBijz novitiaatBijz LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE savedquery CHANGE savedquery savedquery LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE voorkeur_opmerking CHANGE lidOpmerking lidOpmerking LONGTEXT DEFAULT NULL, CHANGE praesesOpmerking praesesOpmerking LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE accounts CHANGE blocked_reason blocked_reason MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE agenda CHANGE beschrijving beschrijving MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE biebbeschrijving CHANGE beschrijving beschrijving MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE biebexemplaar CHANGE opmerking opmerking MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE changelog CHANGE old_value old_value MEDIUMTEXT DEFAULT NULL, CHANGE new_value new_value MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE civi_product CHANGE beschrijving beschrijving MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE civi_saldo CHANGE naam naam MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE civi_saldo_log CHANGE data data MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE courant CHANGE inhoud inhoud MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE courantbericht CHANGE bericht bericht MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE crv_functies CHANGE email_bericht email_bericht MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE crv_taken CHANGE wanneer_gemaild wanneer_gemaild MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE debug_log CHANGE call_trace call_trace MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE forum_delen CHANGE omschrijving omschrijving MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE forum_draden_reageren CHANGE concept concept MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_posts CHANGE tekst tekst MEDIUMTEXT NOT NULL, CHANGE bewerkt_tekst bewerkt_tekst MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE forumplaatjes CHANGE source_url source_url MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE instellingen CHANGE waarde waarde MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE lidinstellingen CHANGE waarde waarde MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE lidtoestemmingen CHANGE waarde waarde MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE mlt_archief CHANGE aanmeldingen aanmeldingen MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE mlt_maaltijden CHANGE omschrijving omschrijving MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE peiling CHANGE beschrijving beschrijving MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE peiling_optie CHANGE beschrijving beschrijving MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE profielen CHANGE novitiaat novitiaat MEDIUMTEXT DEFAULT NULL, CHANGE novitiaatBijz novitiaatBijz MEDIUMTEXT DEFAULT NULL, CHANGE medisch medisch MEDIUMTEXT DEFAULT NULL, CHANGE kgb kgb MEDIUMTEXT DEFAULT NULL, CHANGE vrienden vrienden MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE savedquery CHANGE savedquery savedquery MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE voorkeur_opmerking CHANGE lidOpmerking lidOpmerking MEDIUMTEXT DEFAULT NULL, CHANGE praesesOpmerking praesesOpmerking MEDIUMTEXT DEFAULT NULL');
    }
}
