<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220608182201 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Converteer rechten van P_ naar ROLE_';
	}

	public function up(Schema $schema): void
	{
		$this->addSql("UPDATE cms_paginas SET rechten_bewerken = REPLACE(rechten_bewerken, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE cms_paginas SET rechten_bekijken = REPLACE(rechten_bekijken, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE document SET leesrechten = REPLACE(leesrechten, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET schrijfrechten = REPLACE(schrijfrechten, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET leesrechten = REPLACE(leesrechten, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE agenda SET rechten_bekijken = REPLACE(rechten_bekijken, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_lezen = REPLACE(rechten_lezen, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_modereren = REPLACE(rechten_modereren, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_posten = REPLACE(rechten_posten, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE forum_categorien SET rechten_lezen = REPLACE(rechten_lezen, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE menus SET rechten_bekijken = REPLACE(rechten_bekijken, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE groep SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE declaratie_wachtrij SET rechten = REPLACE(rechten, 'P_', 'ROLE_') WHERE TRUE;");

	}

	public function down(Schema $schema): void
	{
		$this->addSql("UPDATE cms_paginas SET rechten_bewerken = REPLACE(rechten_bewerken, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE cms_paginas SET rechten_bekijken = REPLACE(rechten_bekijken, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE document SET leesrechten = REPLACE(leesrechten, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET schrijfrechten = REPLACE(schrijfrechten, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET leesrechten = REPLACE(leesrechten, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE agenda SET rechten_bekijken = REPLACE(rechten_bekijken, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_lezen = REPLACE(rechten_lezen, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_modereren = REPLACE(rechten_modereren, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_posten = REPLACE(rechten_posten, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE forum_categorien SET rechten_lezen = REPLACE(rechten_lezen, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE menus SET rechten_bekijken = REPLACE(rechten_bekijken, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE groep SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE declaratie_wachtrij SET rechten = REPLACE(rechten, 'ROLE_', 'P_') WHERE TRUE;");
	}
}