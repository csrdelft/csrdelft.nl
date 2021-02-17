<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210216164714 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Zet [bb] en [/bb] voor en na alle bb velden, om ze goed te laten werken met Prosemirror.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql("UPDATE cms_paginas SET inhoud = concat('[bb]', concat(inhoud, '[/bb]')) WHERE inhoud <> ''");
		$this->addSql("UPDATE forum_posts SET tekst = concat('[bb]', concat(tekst, '[/bb]')) WHERE tekst <> ''");
		$this->addSql("UPDATE forum_posts SET bewerkt_tekst = concat('[bb]', concat(bewerkt_tekst, '[/bb]')) WHERE bewerkt_tekst <> ''");

		$this->addSql("UPDATE commissies SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE commissies SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE activiteiten SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE activiteiten SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE ketzers SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE ketzers SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE besturen SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE besturen SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE onderverenigingen SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE onderverenigingen SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE groepen SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE groepen SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE verticalen SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE verticalen SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE werkgroepen SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE werkgroepen SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE woonoorden SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE woonoorden SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE kringen SET samenvatting = concat('[bb]', concat(samenvatting, '[/bb]')) WHERE samenvatting <> ''");
		$this->addSql("UPDATE kringen SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");

		$this->addSql("UPDATE mlt_maaltijden SET omschrijving = concat('[bb]', concat(omschrijving, '[/bb]')) WHERE omschrijving <> ''");
		$this->addSql("UPDATE courantbericht SET bericht = concat('[bb]', concat(bericht, '[/bb]')) WHERE bericht <> ''");
		$this->addSql("UPDATE peiling SET beschrijving = concat('[bb]', concat(beschrijving, '[/bb]')) WHERE beschrijving <> ''");
		$this->addSql("UPDATE peiling_optie SET beschrijving = concat('[bb]', concat(beschrijving, '[/bb]')) WHERE beschrijving <> ''");
	}

	public function down(Schema $schema): void
	{
		$this->addSql("UPDATE cms_paginas SET inhoud = substr(inhoud, 5) WHERE inhoud LIKE '[bb]%'");
		$this->addSql("UPDATE cms_paginas SET inhoud = substr(inhoud, 1, length(inhoud) - 5) WHERE inhoud LIKE '%[/bb]'");
		$this->addSql("UPDATE forum_posts SET tekst = substr(tekst, 5) WHERE tekst LIKE '[bb]%'");
		$this->addSql("UPDATE forum_posts SET tekst = substr(tekst, 1, length(tekst) - 5) WHERE tekst LIKE '%[/bb]'");
		$this->addSql("UPDATE forum_posts SET bewerkt_tekst = substr(bewerkt_tekst, 5) WHERE bewerkt_tekst LIKE '[bb]%'");
		$this->addSql("UPDATE forum_posts SET bewerkt_tekst = substr(bewerkt_tekst, 1, length(bewerkt_tekst) - 5) WHERE bewerkt_tekst LIKE '%[/bb]'");

		$this->addSql("UPDATE commissies SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE commissies SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE commissies SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE commissies SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE activiteiten SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE activiteiten SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE activiteiten SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE activiteiten SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE ketzers SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE ketzers SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE ketzers SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE ketzers SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE besturen SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE besturen SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE besturen SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE besturen SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE onderverenigingen SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE onderverenigingen SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE onderverenigingen SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE onderverenigingen SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE groepen SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE groepen SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE groepen SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE groepen SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE verticalen SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE verticalen SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE verticalen SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE verticalen SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE werkgroepen SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE werkgroepen SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE werkgroepen SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE werkgroepen SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE woonoorden SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE woonoorden SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE woonoorden SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE woonoorden SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");
		$this->addSql("UPDATE kringen SET samenvatting = substr(samenvatting, 5) WHERE samenvatting LIKE '[bb]%'");
		$this->addSql("UPDATE kringen SET samenvatting = substr(samenvatting, 1, length(samenvatting) - 5) WHERE samenvatting LIKE '%[/bb]'");
		$this->addSql("UPDATE kringen SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE kringen SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");

		$this->addSql("UPDATE mlt_maaltijden SET omschrijving = substr(omschrijving, 5) WHERE omschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE mlt_maaltijden SET omschrijving = substr(omschrijving, 1, length(omschrijving) - 5) WHERE omschrijving LIKE '%[/bb]'");

		$this->addSql("UPDATE courantbericht SET bericht = substr(bericht, 5) WHERE bericht LIKE '[bb]%'");
		$this->addSql("UPDATE courantbericht SET bericht = substr(bericht, 1, length(bericht) - 5) WHERE bericht LIKE '%[/bb]'");

		$this->addSql("UPDATE peiling SET beschrijving = substr(beschrijving, 5) WHERE beschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE peiling SET beschrijving = substr(beschrijving, 1, length(beschrijving) - 5) WHERE beschrijving LIKE '%[/bb]'");

		$this->addSql("UPDATE peiling_optie SET beschrijving = substr(beschrijving, 5) WHERE beschrijving LIKE '[bb]%'");
		$this->addSql("UPDATE peiling_optie SET beschrijving = substr(beschrijving, 1, length(beschrijving) - 5) WHERE beschrijving LIKE '%[/bb]'");
	}
}
