<?php

declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261028120000 extends AbstractMigration
{
 public function getDescription(): string{return 'Dagelijkse Panorama-fotoquiz en archief voor gewijzigde spelregels';}
 public function up(Schema $schema):void
 {
  $this->addSql("CREATE TABLE panorama_daily_questions (id INT UNSIGNED AUTO_INCREMENT NOT NULL, day DATE NOT NULL, prompt VARCHAR(180) NOT NULL, answers TEXT NOT NULL, image VARCHAR(80) DEFAULT NULL, credit VARCHAR(255) NOT NULL DEFAULT '', published TINYINT(1) NOT NULL DEFAULT 0, created_by VARCHAR(32) NOT NULL, revision INT UNSIGNED NOT NULL DEFAULT 0, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_PANORAMA_DAY (day), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");
  $this->addSql("CREATE TABLE panorama_daily_answers (question_id INT UNSIGNED NOT NULL, uid VARCHAR(32) NOT NULL, display_name VARCHAR(160) NOT NULL, guesses TEXT NOT NULL, solved TINYINT(1) NOT NULL DEFAULT 0, points SMALLINT UNSIGNED NOT NULL DEFAULT 0, answered_at DATETIME DEFAULT NULL, revision INT UNSIGNED NOT NULL DEFAULT 0, INDEX IDX_PANORAMA_DAILY_UID (uid), PRIMARY KEY(question_id,uid), CONSTRAINT FK_PANORAMA_DAILY_QUESTION FOREIGN KEY (question_id) REFERENCES panorama_daily_questions(id) ON DELETE CASCADE) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");
  foreach($this->connection->fetchAllAssociative('SELECT uid,state FROM panorama_players') as $row){$state=json_decode($row['state'],true);if(($state['version']??0)!==2)continue;foreach(['route','spot','stars'] as $kind){if(isset($state['records'][$kind])){$state['retiredRecords']['v1'][$kind]=$state['records'][$kind];unset($state['records'][$kind]);}}if(in_array($state['active']['kind']??null,['route','spot','stars'],true))$state['active']=null;unset($state['lastResult']);$state['revision']++;$this->addSql('UPDATE panorama_players SET state=?,score=?,stage=?,revision=? WHERE uid=?',[json_encode($state,JSON_THROW_ON_ERROR),array_sum(array_column($state['records'],'best')),count($state['records']),$state['revision'],$row['uid']]);}
 }
 public function down(Schema $schema):void{$this->addSql('DROP TABLE panorama_daily_answers');$this->addSql('DROP TABLE panorama_daily_questions');}
}
