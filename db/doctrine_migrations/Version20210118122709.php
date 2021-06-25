<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210118122709 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Engels extern menu';
	}

	public function up(Schema $schema): void
	{
		$this->addSql(<<<SQL
-- Selecteer maximale item id uit db
SELECT MAX(item_id) + 1 INTO @max_menu_id FROM menus;
-- Selecteer de item id van het externe menu (noot, er zijn meerdere menus met tekst = 'extern')
SELECT item_id INTO @extern_menu_id FROM menus WHERE tekst = 'extern' AND parent_id IS null LIMIT 1;

INSERT INTO menus
-- Lees het externe menu met een Recursive Common Table Expression
WITH RECURSIVE menus_cte
AS (
	SELECT *
	FROM menus
	WHERE parent_id IS NULL AND tekst = 'extern'
	UNION ALL
	SELECT m.* FROM menus AS m, menus_cte AS c
	WHERE m.parent_id = c.item_id
)
SELECT
  -- Zet item id relatief aan einde van bestaande menu items, bewaar offset
	@max_menu_id + item_id - @extern_menu_id AS item_id,
	@max_menu_id + parent_id - @extern_menu_id AS parent_id,
	volgorde,
	CASE
	  -- Vertaal menu item waardes
		WHEN tekst = 'extern' THEN 'extern_en'
		WHEN tekst = 'Vereniging' THEN 'Association'
		WHEN tekst = 'Foto\'s' THEN 'Photos'
		WHEN tekst = 'Forum' THEN 'Forum'
		WHEN tekst = 'Lid worden?' THEN 'Want to join?'
		WHEN tekst = 'Contact' THEN 'Contact'
		WHEN tekst = 'Geloof' THEN 'Faith'
		WHEN tekst = 'Vorming' THEN 'Education'
		WHEN tekst = 'Gezelligheid' THEN 'Social'
		WHEN tekst = 'Onder verenigingen' THEN 'Sub-associations'
		WHEN tekst = 'Ontspanning' THEN 'Leisure'
		WHEN tekst = 'Kamers Zoeken en Aanbieden' THEN 'Room search and offer'
		WHEN tekst = 'Bedrijven' THEN 'Business'
		ELSE ''
	END AS tekst,
  -- Voeg /en toe aan het begin van de link om locale en te forceren.
	CONCAT('/en', link) AS link,
	rechten_bekijken,
	zichtbaar
FROM menus_cte
SQL
		);
	}

	public function down(Schema $schema): void
	{
		$this->addSql('SET FOREIGN_KEY_CHECKS = 0;');
		$this->addSql('DELETE FROM menus WHERE link LIKE \'/en/%\' AND rechten_bekijken = \'P_PUBLIC\'');
		$this->addSql('SET FOREIGN_KEY_CHECKS = 1;');
	}
}
