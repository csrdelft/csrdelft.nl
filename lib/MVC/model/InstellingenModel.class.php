<?php

/**
 * InstellingenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Instellingen extends PersistenceModel {

	const orm = 'Instelling';

	protected static $instance;

	public static function has($module, $key) {
		return array_key_exists($module, static::instance()->instellingen) AND is_array(static::instance()->instellingen[$module]) AND array_key_exists($key, static::instance()->instellingen[$module]);
	}

	public static function get($module, $key) {
		return static::instance()->instellingen[$module][$key];
	}

	/**
	 * Temporary setting (behaves like $GLOBAL).
	 * 
	 * @param string $module
	 * @param string $key
	 * @param mixed $value
	 */
	public static function setTemp($module, $key, $value) {
		static::instance()->instellingen[$module][$key] = $value;
	}

	private $defaults = array(
		'stek'		 => array(
			'homepage'		 => 'thuis',
			'beschrijving'	 => 'De Civitas Studiosorum Reformatorum is een bruisende, actieve, christelijke studentenvereniging in Delft, rijk aan tradities die zijn ontstaan in haar 50-jarig bestaan. Het is een breed gezelschap van zo&lsquo;n 270 leden met een zeer gevarieerde (kerkelijke) achtergrond, maar met een duidelijke eenheid door het christelijk geloof. C.S.R. is de plek waar al tientallen jaren studenten goede vrienden van elkaar worden, op intellectueel en geestelijk gebied groeien en goede studentengrappen uithalen.'
		),
		'agenda'	 => array(
			'standaard_zichtbaar_rechten'	 => 'P_LEDEN_READ',
			'standaard_tijden'				 => '1,Hele dag,2,Kring,3,Lezing,4,Borrel',
			'standaard_tijd_1'				 => '00:00-23:59',
			'standaard_tijd_2'				 => '18:30-22:30',
			'standaard_tijd_3'				 => '20:00-22:00',
			'standaard_tijd_4'				 => '20:00-23:59'
		),
		'corvee'	 => array(
			'punten_per_jaar'						 => '11',
			'herinnering_aantal_mails'				 => '2',
			'herinnering_1e_mail'					 => '-4 weeks',
			'herinnering_1e_mail_uiterlijk'			 => '-3 weeks',
			'herinnering_2e_mail'					 => '-2 weeks',
			'herinnering_2e_mail_uiterlijk'			 => '-1 weeks',
			'suggesties_recent_verbergen'			 => '-2 months',
			'suggesties_recent_filter'				 => '1',
			'suggesties_recent_kwali_filter'		 => '0',
			'suggesties_voorkeur_filter'			 => '1',
			'suggesties_voorkeur_kwali_filter'		 => '1',
			'standaard_repetitie_weekdag'			 => '4',
			'standaard_repetitie_periode'			 => '7',
			'standaard_voorkeurbaar'				 => '1',
			'standaard_kwalificatie'				 => '0',
			'standaard_aantal_corveers'				 => '1',
			'standaard_vrijstelling_percentage'		 => '100',
			'vrijstelling_percentage_max'			 => '100',
			'vrijstelling_percentage_min'			 => '0',
			'weergave_link_ledennamen'				 => 'visitekaartje',
			'weergave_ledennamen_beheer'			 => 'volledig',
			'weergave_ledennamen_corveerooster'		 => 'civitas',
			'waarschuwing_taaktoewijzing_vooraf'	 => '+14 days',
			'waarschuwing_puntentoewijzing_achteraf' => '-1 days',
			'mail_wel_meeeten'						 => 'P.S.: U eet WEL mee met de maaltijd.',
			'mail_niet_meeeten'						 => 'P.S.: U eet NIET mee met de maaltijd.'
		),
		'maaltijden' => array(
			'budget_maalcie'					 => '100',
			'toon_ketzer_vooraf'				 => '+1 month',
			'recent_lidprofiel'					 => '-2 months',
			'standaard_repetitie_weekdag'		 => '4',
			'standaard_repetitie_periode'		 => '7',
			'standaard_abonneerbaar'			 => '1',
			'standaard_aanvang'					 => '18:00',
			'standaard_prijs'					 => '300',
			'standaard_limiet'					 => '0',
			'marge_gasten_verhouding'			 => '10',
			'marge_gasten_min'					 => '3',
			'marge_gasten_max'					 => '6',
			'weergave_link_ledennamen'			 => 'visitekaartje',
			'weergave_ledennamen_beheer'		 => 'volledig',
			'weergave_ledennamen_maaltijdlijst'	 => 'streeplijst',
			'maaltijdlijst_tekst'				 => '<p>Regels omtrent het betalen van de maaltijden op Confide:</p>
<ul>
<li>Maaltijdprijs: &euro; MAALTIJDPRIJS</li>
<li>Niet betaald = nb</li>
<li>Betaald met machtiging = omcirkel "m" en vul bedrag in.</li>
<li>Contant betaald = bedrag invullen.</li>
<li>Schrijf duidelijk in het hokje hoeveel je in de helm hebt gegooid.</li>
<li>Bevat derde kolom "ok"? Dan heeft u nog voldoende tegoed voor deze maaltijd.</li>
<li>Als u onvoldoende saldo hebt bij de MaalCie en u betaalt niet voor deze maaltijd dan krijgt u een boete van 20 cent, 1 euro of 2 euro, afhankelijk van hoe negatief uw saldo is!</li>
</ul>'
		)
	);
	/**
	 * Instellingen array like $defaults
	 * @var array
	 */
	private $instellingen = array();

	/**
	 * Laad alle instellingen uit de database.
	 * Als default instellingen ontbreken worden deze aangemaakt en opgeslagen.
	 */
	protected function __construct() {
		parent::__construct();
		$instellingen = $this->find(); // load all from db
		foreach ($instellingen as $instelling) {
			// haal verwijderde instellingen uit de database
			if (!array_key_exists($instelling->module, $this->defaults) OR ! array_key_exists($instelling->instelling_id, $this->defaults[$instelling->module])) {
				$rowcount = $this->delete($instelling);
				if ($rowcount !== 1) {
					throw new Exception('Niet bestaande instelling verwijderen mislukt');
				}
			}
			$this->instellingen[$instelling->module][$instelling->instelling_id] = $instelling->waarde;
		}
		foreach ($this->defaults as $module => $instellingen) {
			foreach ($instellingen as $key => $value) {
				// maak missende instellingen opnieuw aan met default waarde
				if (!array_key_exists($module, $this->instellingen) OR ! array_key_exists($key, $this->instellingen[$module])) {
					$this->instellingen[$module][$key] = $value;
					$this->newInstelling($module, $key, $value); // save to db
				}
			}
		}
	}

	/**
	 * Lijst van alle modules.
	 * 
	 * @return PDOStatement
	 */
	public function getAlleModules() {
		$sql = 'SELECT DISTINCT module FROM instellingen';
		$query = Database::instance()->prepare($sql);
		$query->execute();
		$query->setFetchMode(PDO::FETCH_COLUMN, 0);
		return $query;
	}

	/**
	 * Haalt alle instellingen op voor een module.
	 * 
	 * @param string $module
	 * @return Instelling[]
	 */
	public function getModuleInstellingen($module) {
		$where = 'module = ?';
		$params = array($module);
		return $this->find($where, $params);
	}

	/**
	 * Zoek een instelling voor bewerken of na verwijderen.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 * 
	 * @param string $module
	 * @param string $key
	 * @return Instelling
	 * @throws Exception indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	private function getInstelling($module, $key) {
		if (!array_key_exists($module, $this->instellingen) OR ! array_key_exists($key, $this->instellingen[$module])) {
			// get default for missing instelling
			if (array_key_exists($module, $this->defaults) AND array_key_exists($key, $this->defaults[$module])) {
				$this->instellingen[$module][$key] = $this->defaults[$module][$key];
				return $this->newInstelling($module, $key, $this->defaults[$module][$key]); // save to db
			} else { // geen default instelling
				throw new Exception('Instelling default not found: ' . $key . ' module: ' . $module);
			}
		}
		return $this->retrieveByPrimaryKey(array($module, $key));
	}

	private function newInstelling($module, $key, $value) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $key;
		$instelling->waarde = $value;
		$this->create($instelling);
		return $instelling;
	}

	public function wijzigInstelling($module, $key, $value) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $key;
		$instelling->waarde = $value;
		$this->update($instelling);
		return $instelling;
	}

	public function resetInstelling($module, $key) {
		$rowcount = $this->deleteByPrimaryKey(array($module, $key));
		if ($rowcount !== 1) {
			throw new Exception('Instelling resetten mislukt');
		}
		unset($this->instellingen[$module][$key]);
		return $this->getInstelling($module, $key); // creates new with default value
	}

}
