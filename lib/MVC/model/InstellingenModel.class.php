<?php

require_once 'MVC/model/entity/Instelling.class.php';

/**
 * InstellingenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class InstellingenModel extends PersistenceModel {

	/**
	 * Singleton instance
	 * @var InstellingenModel
	 */
	private static $_instance;

	/**
	 * Get singleton InstellingenModel instance.
	 * 
	 * @return InstellingenModel
	 */
	public static function instance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new InstellingenModel();
		}
		return self::$_instance;
	}

	private $defaults = array(
		'corvee' => array(
			'corveepunten_per_jaar' => '11',
			'herinnering_aantal_mails' => '2',
			'herinnering_1e_mail' => '-5 weeks',
			'herinnering_1e_mail_uiterlijk' => '-4 weeks',
			'herinnering_2e_mail' => '-3 weeks',
			'herinnering_2e_mail_uiterlijk' => '-2 weeks',
			'suggesties_recent_verbergen' => '-2 months',
			'suggesties_recent_filter' => '1',
			'suggesties_recent_kwali_filter' => '0',
			'suggesties_voorkeur_filter' => '1',
			'suggesties_voorkeur_kwali_filter' => '1',
			'standaard_repetitie_weekdag' => '4',
			'standaard_repetitie_periode' => '7',
			'standaard_voorkeurbaar' => '1',
			'standaard_kwalificatie' => '0',
			'standaard_aantal_corveers' => '1',
			'standaard_vrijstelling_percentage' => '100',
			'vrijstelling_percentage_max' => '200',
			'vrijstelling_percentage_min' => '0',
			'weergave_ledennamen' => 'visitekaartje',
			'weergave_ledennamen_beheer' => 'volledig',
			'weergave_ledennamen_corveerooster' => 'civitas',
			'waarschuwing_taaktoewijzing_vooraf' => '+14 days',
			'waarschuwing_puntentoewijzing_achteraf' => '-1 days'
		),
		'maaltijden' => array(
			'maaltijd_budget_maalcie' => '1.00',
			'maaltijden_ketzer_vooraf' => '+1 month',
			'maaltijden_recent_lidprofiel' => '-2 months',
			'standaard_repetitie_weekdag' => '4',
			'standaard_repetitie_periode' => '7',
			'standaard_abonneerbaar' => '1',
			'standaard_maaltijdaanvang' => '18:00',
			'standaard_maaltijdprijs' => '3.00',
			'standaard_maaltijdlimiet' => '0',
			'marge_gasten_verhouding' => '10',
			'marge_gasten_min' => '3',
			'marge_gasten_max' => '6',
			'weergave_ledennamen' => 'visitekaartje',
			'weergave_ledennamen_beheer' => 'volledig',
			'weergave_ledennamen_maaltijdlijst' => 'streeplijst',
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
		)
	);
	private $instellingen = null;

	/**
	 * Laad alle instellingen uit de database.
	 * Als default instellingen ontbreken worden deze aangemaakt en opgeslagen.
	 */
	private function __construct() {
		$instellingen = $this->find(new Instelling()); // load all from db
		foreach ($instellingen as $instelling) {
			$this->instellingen[$instelling->module][$instelling->instelling_id] = $instelling;
			$GLOBALS[$instelling->module][$instelling->instelling_id] = $instelling->waarde; // zet publieke toegang
		}
		// zet missende instellingen op default waarde
		foreach ($this->defaults as $module => $instellingen) {
			foreach ($instellingen as $key => $value) {
				if (!array_key_exists($key, $this->instellingen[$module])) {
					$this->instellingen[$module][$key] = self::newInstelling($module, $key, $value); // save to db
					$GLOBALS[$module][$key] = $value;
				}
			}
		}
	}

	/**
	 * Geeft alle instellingen op voor een module.
	 * 
	 * @return Instellingen[]
	 */
	public function getModuleInstellingen($module) {
		if (!array_key_exists($module, $this->instellingen)) {
			return null;
		}
		return $this->instellingen[$module];
	}

	/**
	 * Zoek een instelling voor bewerken of na verwijderen.
	 * Als een default instelling ontbreekt wordt deze aangemaakt en opgeslagen.
	 * 
	 * @return LidInstellingen
	 */
	public function getInstelling($module, $key) {
		if (!array_key_exists($module, $this->instellingen) OR !array_key_exists($key, $this->instellingen[$module])) {
			// get default for missing instelling
			if (array_key_exists($module, $this->defaults) AND array_key_exists($key, $this->defaults[$module])) {
				$this->instellingen[$module][$key] = self::newInstelling($module, $key, $this->defaults[$key]);
			} else { // geen default instelling
				throw new Exception('Instelling default not found: ' . $key . ' module: ' . $module);
			}
		}
		return $this->instellingen[$module][$key];
	}

	public function wijzigInstelling($module, $key, $value) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $key;
		$instelling->waarde = $value;
		$this->update($instelling);
	}

	private function newInstelling($module, $key, $value) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $key;
		$instelling->waarde = $value;
		$this->create($instelling);
		return $instelling;
	}

	public function resetInstelling($module, $key) {
		self::deleteInstelling($module, $key);
		unset($GLOBALS[$module][$key]);
		unset($this->instellingen[$module][$key]);
	}

	private function deleteInstelling($module, $key) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $key;
		$this->delete($instelling);
	}

}
