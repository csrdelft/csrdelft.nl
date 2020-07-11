<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200711093836 extends AbstractMigration {
	public function getDescription(): string {
		return 'Van eigen remember tokens naar Symfony';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<SQL
CREATE TABLE `rememberme_token` (
    `series`   char(88)     UNIQUE PRIMARY KEY NOT NULL,
    `value`    char(88)     NOT NULL,
    `lastUsed` datetime     NOT NULL,
    `class`    varchar(100) NOT NULL,
    `username` varchar(200) NOT NULL
);
SQL
		);
		$this->addSql('DROP TABLE login_remember');

	}

	public function down(Schema $schema): void {
		$this->addSql('DROP TABLE rememberme_token');
		$this->addSql(<<<SQL
CREATE TABLE `login_remember` (
	`id`             INT(11)      NOT NULL AUTO_INCREMENT,
	`uid`            VARCHAR(4)   NOT NULL COMMENT '(DC2Type:uid)' COLLATE 'utf8mb4_unicode_ci',
	`token`          VARCHAR(255) NOT NULL                         COLLATE 'utf8mb4_unicode_ci',
	`remember_since` DATETIME     NOT NULL,
	`device_name`    VARCHAR(255) NOT NULL                         COLLATE 'utf8mb4_unicode_ci',
	`ip`             VARCHAR(255) NOT NULL                         COLLATE 'utf8mb4_unicode_ci',
	`lock_ip`        TINYINT(1)   NOT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `IDX_BD5B5182539B0606` (`uid`) USING BTREE,
	CONSTRAINT `FK_BD5B5182539B0606` FOREIGN KEY (`uid`) REFERENCES profielen (`uid`) ON UPDATE RESTRICT ON DELETE RESTRICT
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
		);

	}
}
