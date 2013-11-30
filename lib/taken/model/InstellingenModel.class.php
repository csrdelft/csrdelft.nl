<?php
namespace Taken\MLT;

require_once 'taken/model/entity/Instelling.class.php';

/**
 * InstellingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class InstellingenModel {

	private static $_instellingen = null;
	private static $_defaults = array(
		'corveepunten_per_jaar' => '11',
		'herinnering_aantal_mails' => '2',
		'herinnering_1e_mail' => '-5 weeks',
		'herinnering_1e_mail_uiterlijk' => '-4 weeks',
		'herinnering_2e_mail' => '-14 days',
		'herinnering_2e_mail_uiterlijk' => '-7 days',
		'maaltijden_ketzer_vooraf' => '+1 month',
		'maaltijden_recent_lidprofiel' => '-2 months',
		'standaard_repetitie_weekdag' => '4',
		'standaard_repetitie_periode' => '7',
		'standaard_abonneerbaar' => '1',
		'standaard_voorkeurbaar' => '1',
		'standaard_kwalificatie' => '0',
		'standaard_functie_punten' => '1',
		'standaard_aantal_corveers' => '1',
		'standaard_vrijstelling_percentage' => '100',
		'standaard_maaltijdaanvang' => '18:00',
		'standaard_maaltijdprijs' => '3.00',
		'standaard_maaltijdlimiet' => '0',
		'marge_gasten_verhouding' => '10',
		'marge_gasten_min' => '3',
		'marge_gasten_max' => '6',
		'vrijstelling_percentage_max' => '200',
		'vrijstelling_percentage_min' => '0',
		'weergave_ledennamen_beheer' => 'volledig',
		'weergave_ledennamen_corveerooster' => 'civitas',
		'weergave_ledennamen_maaltijdlijst' => 'civitas',
		'waarschuwing_taaktoewijzing_vooraf' => '+14 days',
		'waarschuwing_puntentoewijzing_achteraf' => '-1 days',
		'maaltijdlijst_tekst' => '<p>Regels omtrent het betalen van de maaltijden op Confide:</p>
<ul>
<li>Maaltijdprijs: &euro; MAALTIJDPRIJS</li>
<li>Niet betaald = nb</li>
<li>Betaald met machtiging = omcirkel "m" en vul bedrag in.</li>
<li>Contant betaald = bedrag invullen.</li>
<li>Schrijf duidelijk in het hokje hoeveel je in de helm hebt gegooid.</li>
<li>Bevat derde kolom "ok"? Dan hebt u nog voldoende tegoed voor deze maaltijd.</li>
<li>Als u onvoldoende saldo hebt bij de MaalCie en u betaalt niet voor deze maaltijd dan krijgt u een boete van 20 cent, 1 euro of 2 euro, afhankelijk van hoe negatief uw saldo is!</li>
</ul>'
	);
	
	/**
	 * Laad alle instellingen uit de database.
	 * Als default instellingen ontbreken worden deze aangemaakt en opgeslagen.
	 * 
	 * @return Instelling[]
	 */
	public static function getAlleInstellingen() {
		if (self::$_instellingen === null) { // laad maar 1x
			self::$_instellingen = self::loadInstellingen();
		}
		foreach (self::$_instellingen as $instelling) { // zet publieke toegang
			$GLOBALS[$instelling->getInstellingId()] = $instelling->getWaarde();
		}
		foreach (self::$_defaults as $key => $value) {
			if (!array_key_exists($key, $GLOBALS)) {
				$GLOBALS[$key] = $value;
				self::$_instellingen[] = self::newInstelling($key, $value);
			}
		}
		return self::$_instellingen;
	}
	
	/**
	 * Zoek een instelling voor bewerken of na verwijderen.
	 * Als een default instelling ontbreekt wordt deze aangemaakt en opgeslagen.
	 * 
	 * @return Instelling
	 */
	public static function getInstelling($key) {
		foreach (self::$_instellingen as $instelling) {
			if ($key === $instelling->getInstellingId()) {
				return $instelling;
			}
		}
		if (!array_key_exists($key, self::$_defaults)) { // geen default instelling
			throw new \Exception('Get instelling faalt: Not found $key ='. $key);
		}
		$instelling = self::newInstelling($key, self::$_defaults[$key]);
		return $instelling;
	}
	
	private static function loadInstellingen($where=null, $values=array(), $limit=null) {
		$sql = 'SELECT instelling_id, waarde';
		$sql.= ' FROM mlt_instellingen';
		if ($where !== null) {
			$sql.= ' WHERE '. $where;
		}
		$sql.= ' ORDER BY instelling_id ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT '. $limit;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Taken\MLT\Instelling');
		return $result;
	}
	
	public static function saveInstelling($key, $value) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$instelling = self::getInstelling($key);
			if ($instelling === null) {
				$instelling = self::newInstelling($key, $value);
			}
			else {
				$instelling->setWaarde($value);
				self::updateInstelling($instelling);
			}
			$db->commit();
			return $instelling;
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	private static function newInstelling($key, $value) {
		$sql = 'INSERT INTO mlt_instellingen';
		$sql.= ' (instelling_id, waarde)';
		$sql.= ' VALUES (?, ?)';
		$values = array($key, $value);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New instelling faalt: $query->rowCount() ='. $query->rowCount());
		}
		return new Instelling($key, $value);
	}
	
	private static function updateInstelling(Instelling $instelling) {
		$sql = 'UPDATE mlt_instellingen';
		$sql.= ' SET waarde = ?';
		$sql.= ' WHERE instelling_id = ?';
		$values = array(
			$instelling->getWaarde(),
			$instelling->getInstellingId()
		);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('Update instelling faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	public static function verwijderInstelling($key) {
		self::deleteInstelling($key);
		unset($GLOBALS[$key]);
		self::$_instellingen = array();
	}
	
	private static function deleteInstelling($key) {
		$sql = 'DELETE FROM mlt_instellingen';
		$sql.= ' WHERE instelling_id = ?';
		$values = array($key);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('Delete instelling faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
}