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
		$this->addSql("UPDATE cms_paginas SET rechten_bewerken = REPLACE(rechten_bewerken, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE cms_paginas SET rechten_bekijken = REPLACE(rechten_bekijken, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE document SET leesrechten = REPLACE(leesrechten, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET schrijfrechten = REPLACE(schrijfrechten, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET leesrechten = REPLACE(leesrechten, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE agenda SET rechten_bekijken = REPLACE(rechten_bekijken, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_lezen = REPLACE(rechten_lezen, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_modereren = REPLACE(rechten_modereren, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_posten = REPLACE(rechten_posten, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE forum_categorien SET rechten_lezen = REPLACE(rechten_lezen, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE menus SET rechten_bekijken = REPLACE(rechten_bekijken, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE groep SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE declaratie_wachtrij SET rechten = REPLACE(rechten, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE mlt_maaltijden SET aanmeld_filter = REPLACE(aanmeld_filter, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE mlt_repetities SET abonnement_filter = REPLACE(abonnement_filter, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'P_PUBLIC', 'PUBLIC_ACCESS') WHERE TRUE;");
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
		$this->addSql("UPDATE mlt_maaltijden SET aanmeld_filter = REPLACE(aanmeld_filter, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE mlt_repetities SET abonnement_filter = REPLACE(abonnement_filter, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'P_', 'ROLE_') WHERE TRUE;");
		$this->addSql('ALTER TABLE savedquery CHANGE permissie permissie VARCHAR(255) DEFAULT \'ROLE_LOGGED_IN\' NOT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql("UPDATE cms_paginas SET rechten_bewerken = REPLACE(rechten_bewerken, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE cms_paginas SET rechten_bekijken = REPLACE(rechten_bekijken, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE document SET leesrechten = REPLACE(leesrechten, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET schrijfrechten = REPLACE(schrijfrechten, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE document_categorie SET leesrechten = REPLACE(leesrechten, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE agenda SET rechten_bekijken = REPLACE(rechten_bekijken, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_lezen = REPLACE(rechten_lezen, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_modereren = REPLACE(rechten_modereren, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE forum_delen SET rechten_posten = REPLACE(rechten_posten, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE forum_categorien SET rechten_lezen = REPLACE(rechten_lezen, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE menus SET rechten_bekijken = REPLACE(rechten_bekijken, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE groep SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE declaratie_wachtrij SET rechten = REPLACE(rechten, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE mlt_maaltijden SET aanmeld_filter = REPLACE(aanmeld_filter, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE mlt_repetities SET abonnement_filter = REPLACE(abonnement_filter, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'PUBLIC_ACCESS', 'P_PUBLIC') WHERE TRUE;");
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
		$this->addSql("UPDATE mlt_maaltijden SET aanmeld_filter = REPLACE(aanmeld_filter, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE mlt_repetities SET abonnement_filter = REPLACE(abonnement_filter, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_activiteit SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_aanmelden = REPLACE(rechten_aanmelden, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_beheren = REPLACE(rechten_lijst_beheren, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql("UPDATE aanmelder_reeks SET rechten_lijst_bekijken = REPLACE(rechten_lijst_bekijken, 'ROLE_', 'P_') WHERE TRUE;");
		$this->addSql('ALTER TABLE savedquery CHANGE permissie permissie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'P_LOGGED_IN\' NOT NULL COLLATE `utf8mb4_general_ci`');
	}
}
