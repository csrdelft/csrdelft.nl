<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027190000 extends AbstractMigration
{
 public function getDescription(): string { return 'Expeditie Panorama: persoonlijke voortgang, teams en eenmalige teamhulp'; }
 public function up(Schema $schema): void
 {
  $this->addSql("CREATE TABLE panorama_teams (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, code CHAR(8) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_PANORAMA_CODE (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");
  $this->addSql("CREATE TABLE panorama_players (uid VARCHAR(32) NOT NULL, display_name VARCHAR(160) NOT NULL, team_id INT UNSIGNED DEFAULT NULL, state LONGTEXT NOT NULL, score INT UNSIGNED NOT NULL DEFAULT 0, stage SMALLINT UNSIGNED NOT NULL DEFAULT 0, revision INT UNSIGNED NOT NULL DEFAULT 0, updated_at DATETIME NOT NULL, INDEX IDX_PANORAMA_TEAM (team_id), INDEX IDX_PANORAMA_SCORE (score, stage), PRIMARY KEY(uid), CONSTRAINT FK_PANORAMA_PLAYER_TEAM FOREIGN KEY (team_id) REFERENCES panorama_teams (id) ON DELETE SET NULL) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");
  $this->addSql("CREATE TABLE panorama_help (recipient VARCHAR(32) NOT NULL, stage SMALLINT UNSIGNED NOT NULL, helper VARCHAR(32) NOT NULL, team_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(recipient, stage, helper), INDEX IDX_PANORAMA_HELP_TEAM (team_id), INDEX IDX_PANORAMA_HELP_HELPER (helper), CONSTRAINT FK_PANORAMA_HELP_RECIPIENT FOREIGN KEY (recipient) REFERENCES panorama_players (uid) ON DELETE CASCADE, CONSTRAINT FK_PANORAMA_HELP_HELPER FOREIGN KEY (helper) REFERENCES panorama_players (uid) ON DELETE CASCADE, CONSTRAINT FK_PANORAMA_HELP_TEAM FOREIGN KEY (team_id) REFERENCES panorama_teams (id) ON DELETE CASCADE) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");
 }
 public function down(Schema $schema): void
 {
  $this->addSql('DROP TABLE panorama_help');
  $this->addSql('DROP TABLE panorama_players');
  $this->addSql('DROP TABLE panorama_teams');
 }
}
