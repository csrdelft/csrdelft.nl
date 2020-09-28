<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200927164704 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Verwijder login_sessions tabel, deze wordt nu door Symfony bijgehouden.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('DROP TABLE login_sessions');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('CREATE TABLE login_sessions (session_hash VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL COLLATE `utf8mb4_general_ci`, uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL COLLATE `utf8mb4_general_ci`, login_moment DATETIME NOT NULL, expire DATETIME NOT NULL, user_agent VARCHAR(255) NOT NULL COLLATE `utf8mb4_general_ci`, ip VARCHAR(255) NOT NULL COLLATE `utf8mb4_general_ci`, lock_ip TINYINT(1) NOT NULL, authentication_method VARCHAR(255) NOT NULL COLLATE `utf8mb4_general_ci`, INDEX IDX_B4C4BD8C539B0606 (uid), PRIMARY KEY(session_hash))');
		$this->addSql('ALTER TABLE login_sessions ADD CONSTRAINT login_sessions_ibfk_1 FOREIGN KEY (uid) REFERENCES accounts (uid)');
	}
}
