<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261028140000 extends AbstractMigration
{
    public function getDescription(): string { return 'Panorama-spelbeheer en eenvoudiger Bergroute'; }
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE panorama_game_settings (id INT NOT NULL, settings MEDIUMTEXT NOT NULL, updated_by VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");
        $this->addSql("INSERT INTO panorama_game_settings (id,settings,updated_by) VALUES (1,'{}','')");
        // De nieuwe Bergroute vergelijkt geen scores met de vorige kaartregels.
        $this->addSql("UPDATE panorama_players SET state=JSON_SET(JSON_SET(JSON_REMOVE(state,'$.records.route','$.lastResult'), '$.retiredRecords', COALESCE(JSON_EXTRACT(state,'$.retiredRecords'),JSON_OBJECT())), '$.retiredRecords.routeV2', COALESCE(JSON_EXTRACT(state,'$.records.route'),JSON_OBJECT()), '$.active', CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(state,'$.active.kind'))='route' THEN NULL ELSE JSON_EXTRACT(state,'$.active') END, '$.revision', revision+1), revision=revision+1 WHERE JSON_EXTRACT(state,'$.version')=2 AND (JSON_CONTAINS_PATH(state,'one','$.records.route') OR JSON_UNQUOTE(JSON_EXTRACT(state,'$.active.kind'))='route')");
        $this->addSql("UPDATE panorama_players SET score=COALESCE(JSON_EXTRACT(state,'$.records.puzzle.best'),0)+COALESCE(JSON_EXTRACT(state,'$.records.memory.best'),0)+COALESCE(JSON_EXTRACT(state,'$.records.spot.best'),0)+COALESCE(JSON_EXTRACT(state,'$.records.stars.best'),0), stage=JSON_LENGTH(JSON_EXTRACT(state,'$.records')) WHERE JSON_EXTRACT(state,'$.version')=2");
    }
    public function down(Schema $schema): void { $this->addSql('DROP TABLE panorama_game_settings'); }
}
