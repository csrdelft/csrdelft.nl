<?php

/**
 * InstellingenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Instellingen extends CachedPersistenceModel {

	const orm = 'Instelling';

	protected static $instance;

	public static function has($module, $id) {
		return isset(static::$defaults[$module][$id]);
	}

	public static function get($module, $id) {
		return static::instance()->getValue($module, $id);
	}

	protected static $defaults = array(
		'stek'		 => array(
			'homepage'		 => 'thuis',
			'beschrijving'	 => 'De Civitas Studiosorum Reformatorum is een bruisende, actieve, christelijke studentenvereniging in Delft, rijk aan tradities die zijn ontstaan in haar 50-jarig bestaan. Het is een breed gezelschap van zo&lsquo;n 270 leden met een zeer gevarieerde (kerkelijke) achtergrond, maar met een duidelijke eenheid door het christelijk geloof. C.S.R. is de plek waar al tientallen jaren studenten goede vrienden van elkaar worden, op intellectueel en geestelijk gebied groeien en goede studentengrappen uithalen.'
		),
		'forum'		 => array(
			'reageren_tijd' => '-2 minutes'
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
	 * Remove cached instellingen from memcache and clear runtime cache.
	 */
	protected function clearCache() {
		$key = get_class($this);
		$this->unsetCache($key, true);
		$this->flushCache(false);
	}

	protected function memcacheKey() {
		return get_class($this);
	}

	public function prefetch($criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		// use memcache
		$key = $this->memcacheKey();
		if ($this->isCached($key, true)) {
			return $this->getCached($key, true);
		}
		$result = parent::prefetch($criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		if ($result) {
			$this->setCache($key, $result, true);
		}
		return $result;
	}

	public function getModules() {
		return array_keys(static::$defaults);
	}

	public function getModuleInstellingen($module) {
		return array_keys(static::$defaults[$module]);
	}

	public function getInstellingen() {
		$instellingen = array();
		foreach ($this->getModules() as $module) {
			$instellingen[$module] = $this->getModuleInstellingen($module);
		}
		return $instellingen;
	}

	public function getValue($module, $id) {
		return $this->getInstelling($module, $id)->waarde;
	}

	public function getDefault($module, $id) {
		return static::$defaults[$module][$id];
	}

	/**
	 * Haal een instelling op uit het cache of de database.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 * 
	 * @param string $module
	 * @param string $id
	 * @return Instelling
	 * @throws Exception indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	protected function getInstelling($module, $id) {
		$instelling = $this->retrieveByPrimaryKey(array($module, $id));
		if (static::has($module, $id)) {
			if (!$instelling) {
				$instelling = $this->newInstelling($module, $id);
			}
			return $instelling;
		} else {
			if ($instelling) {
				// Haal niet-bestaande instelling uit de database
				$this->delete($instelling);
			}
			throw new Exception('Instelling bestaat niet: ' . $id . ' module: ' . $module);
		}
		return $instelling;
	}

	protected function newInstelling($module, $id) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$this->create($instelling);
		$this->clearCache();
		return $instelling;
	}

	public function wijzigInstelling($module, $id, $waarde) {
		$instelling = $this->getInstelling($module, $id);
		$instelling->waarde = $waarde;
		$this->update($instelling);
		$this->clearCache();
		return $instelling;
	}

	public function opschonen() {
		foreach ($this->find() as $instelling) {
			if (!static::has($instelling->module, $instelling->instelling_id)) {
				$this->delete($instelling);
			}
		}
	}

}
