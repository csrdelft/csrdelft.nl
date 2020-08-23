<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200823183357 extends AbstractMigration {
	public function getDescription(): string {
		return 'Geef instelling entities een single primary key.';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<'SQL'
ALTER TABLE instellingen
 ADD id INT AUTO_INCREMENT NOT NULL,
 CHANGE instelling_id instelling VARCHAR(255) NOT NULL,
 CHANGE module module VARCHAR(255) NOT NULL,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY (id)
SQL
		);
		$this->addSql('CREATE UNIQUE INDEX module_instelling ON instellingen (module, instelling)');
		$this->addSql(<<<'SQL'
ALTER TABLE lidinstellingen
 ADD id INT AUTO_INCREMENT NOT NULL,
 CHANGE instelling_id instelling VARCHAR(255) NOT NULL,
 CHANGE module module VARCHAR(255) NOT NULL,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY (id)
SQL
		);
		$this->addSql('CREATE UNIQUE INDEX uid_module_instelling ON lidinstellingen (uid, module, instelling)');
		$this->addSql(<<<'SQL'
ALTER TABLE lidtoestemmingen
 ADD id INT AUTO_INCREMENT NOT NULL,
 CHANGE instelling_id instelling VARCHAR(255) NOT NULL,
 CHANGE module module VARCHAR(255) NOT NULL,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY (id)
SQL
		);
		$this->addSql('CREATE UNIQUE INDEX uid_module_instelling ON lidtoestemmingen (uid, module, instelling)');
	}

	public function down(Schema $schema): void {
		$this->addSql('ALTER TABLE instellingen MODIFY id INT NOT NULL');
		$this->addSql('DROP INDEX module_instelling ON instellingen');
		$this->addSql('ALTER TABLE instellingen DROP PRIMARY KEY');
		$this->addSql(<<<'SQL'
ALTER TABLE instellingen
 CHANGE instelling instelling_id varchar(191) COMMENT '(DC2Type:stringkey)' NOT NULL,
 DROP id,
 CHANGE module module VARCHAR(191) COMMENT '(DC2Type:stringkey)' NOT NULL
SQL
		);
		$this->addSql('ALTER TABLE instellingen ADD PRIMARY KEY (module, instelling_id)');
		$this->addSql('ALTER TABLE lidinstellingen MODIFY id INT NOT NULL');
		$this->addSql('DROP INDEX uid_module_instelling ON lidinstellingen');
		$this->addSql('ALTER TABLE lidinstellingen DROP PRIMARY KEY');
		$this->addSql(<<<'SQL'
ALTER TABLE lidinstellingen
 CHANGE instelling instelling_id varchar(191) COMMENT '(DC2Type:stringkey)' NOT NULL,
 DROP id,
 CHANGE module module VARCHAR(191) COMMENT '(DC2Type:stringkey)' NOT NULL
SQL
		);
		$this->addSql('ALTER TABLE lidinstellingen ADD PRIMARY KEY (uid, module, instelling_id)');
		$this->addSql('ALTER TABLE lidtoestemmingen MODIFY id INT NOT NULL');
		$this->addSql('DROP INDEX uid_module_instelling ON lidtoestemmingen');
		$this->addSql('ALTER TABLE lidtoestemmingen DROP PRIMARY KEY');
		$this->addSql(<<<'SQL'
ALTER TABLE lidtoestemmingen
 CHANGE instelling instelling_id varchar(191) COMMENT '(DC2Type:stringkey)' NOT NULL,
 DROP id,
 CHANGE module module VARCHAR(191) COMMENT '(DC2Type:stringkey)' NOT NULL
SQL
		);
		$this->addSql('ALTER TABLE lidtoestemmingen ADD PRIMARY KEY (uid, module, instelling_id)');
	}
}
