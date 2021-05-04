<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210417170443 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Verplaats groepen data naar groep en groep_lid tabellen.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, aanmeld_limiet, aanmelden_vanaf, aanmelden_tot, bewerken_tot, afmelden_tot, soort, rechten_aanmelden, locatie, in_agenda, naam, familie, begin_moment, eind_moment, STATUS, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2)
SELECT 'activiteit', id, maker_uid, aanmeld_limiet, aanmelden_vanaf, aanmelden_tot, bewerken_tot, afmelden_tot, soort, rechten_aanmelden, locatie, in_agenda, naam, familie, begin_moment, eind_moment, STATUS, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM activiteiten");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM activiteit_deelnemers AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'activiteit'");
		$this->addSql('DELETE FROM activiteit_deelnemers');
		$this->addSql('DELETE FROM activiteiten');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, bijbeltekst, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2 )
SELECT 'bestuur', id, maker_uid, bijbeltekst, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM besturen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM bestuurs_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'bestuur'");
		$this->addSql('DELETE FROM bestuurs_leden');
		$this->addSql('DELETE FROM besturen');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, soort, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2)
SELECT 'commissie', id, maker_uid, soort, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM commissies");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM commissie_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'commissie'");
		$this->addSql('DELETE FROM commissie_leden');
		$this->addSql('DELETE FROM commissies');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, rechten_aanmelden, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2)
SELECT 'rechtengroep', id, maker_uid, rechten_aanmelden, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM groepen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM groep_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'rechtengroep'");
		$this->addSql('DELETE FROM groep_leden');
		$this->addSql('DELETE FROM groepen');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, aanmeld_limiet, aanmelden_vanaf, aanmelden_tot, bewerken_tot, afmelden_tot, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2)
SELECT 'ketzer', id, maker_uid, aanmeld_limiet, aanmelden_vanaf, aanmelden_tot, bewerken_tot, afmelden_tot, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM ketzers");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM ketzer_deelnemers AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'ketzer'");
		$this->addSql('DELETE FROM ketzer_deelnemers');
		$this->addSql('DELETE FROM ketzers');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, verticale, kring_nummer, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2 )
SELECT 'kring', id, maker_uid, verticale, kring_nummer, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM kringen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM kring_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'kring'");
		$this->addSql('DELETE FROM kring_leden');
		$this->addSql('DELETE FROM kringen');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, lidjaar, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2)
SELECT 'lichting', id, maker_uid, lidjaar, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM lichtingen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM lichting_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'lichting'");
		$this->addSql('DELETE FROM lichting_leden');
		$this->addSql('DELETE FROM lichtingen');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, letter, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2 )
SELECT 'verticale', id, maker_uid, letter, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM verticalen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM verticale_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'verticale'");
		$this->addSql('DELETE FROM verticale_leden');
		$this->addSql('DELETE FROM verticalen');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, aanmeld_limiet, aanmelden_vanaf, aanmelden_tot, bewerken_tot, afmelden_tot, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2 )
SELECT 'werkgroep', id, maker_uid, aanmeld_limiet, aanmelden_vanaf, aanmelden_tot, bewerken_tot, afmelden_tot, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM werkgroepen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM werkgroep_deelnemers AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'werkgroep'");
		$this->addSql('DELETE FROM werkgroep_deelnemers');
		$this->addSql('DELETE FROM werkgroepen');

		$this->addSql('ALTER TABLE eetplan DROP FOREIGN KEY FK_EC97E0BBF0C31BC7');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, soort, eetplan, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2 )
SELECT 'woonoord', id, maker_uid, soort, eetplan, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM woonoorden");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM bewoners AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'woonoord'");
		$this->addSql('DELETE FROM bewoners');
		$this->addSql('DELETE FROM woonoorden');

		$this->addSql("
INSERT INTO groep (groep_type, oud_id, maker_uid, soort, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2 )
SELECT 'ondervereniging', id, maker_uid, soort, naam, familie, begin_moment, eind_moment, status, samenvatting, omschrijving, keuzelijst, versie, keuzelijst2
FROM onderverenigingen");
		$this->addSql("
INSERT INTO groep_lid (groep_id, uid, door_uid, opmerking, opmerking2, lid_sinds)
SELECT a.id, uid, door_uid, opmerking, opmerking2, lid_sinds FROM ondervereniging_leden AS d
INNER JOIN groep AS a ON a.oud_id = d.groep_id AND a.groep_type = 'ondervereniging'");
		$this->addSql('DELETE FROM ondervereniging_leden');
		$this->addSql('DELETE FROM onderverenigingen');

		$this->addSql("UPDATE eetplan SET woonoord_id = (SELECT id FROM groep WHERE groep_type = 'woonoord' AND oud_id = woonoord_id)");
		$this->addSql('ALTER TABLE eetplan ADD CONSTRAINT FK_EC97E0BBF0C31BC7 FOREIGN KEY (woonoord_id) REFERENCES groep (id)');
	}

	public function down(Schema $schema): void
	{
		$this->throwIrreversibleMigrationException();
	}
}
