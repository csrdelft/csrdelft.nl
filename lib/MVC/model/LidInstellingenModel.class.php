<?php

require_once 'MVC/model/InstellingenModel.class.php';

/**
 * LidInstellingenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de instellingen bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 */
class LidInstellingen extends PersistenceModel {

	const orm = 'LidInstelling';

	protected static $instance;

	public static function get($module, $key = null) {
		return static::instance()->getValue($module, $key);
	}

	/**
	 * 'module' => array( 'key' => array('beschrijving', 'type', type-opties, 'default value', technical-values) )
	 *
	 * type-opties:
	 * enum: array
	 * int: array( min, max )
	 * string: array( min-lenght, max-lenght )
	 *
	 * technical-values:
	 * array(type-optie-1 => technical-value-1, ...)
	 *
	 * @var array
	 */
	private $instellingen = array(
		'algemeen'		 => array(
			'bijbel' => array('Bijbelvertaling voor bijbelrooster', T::Enumeration, array('NBV', 'NBG', 'Herziene Statenvertaling', 'Statenvertaling (Jongbloed)', 'Groot Nieuws Bijbel', 'Willibrordvertaling'), 'NBV')
		),
		'layout'		 => array(
			'layout'		 => array('Websteklayout', T::Enumeration, array('normaal', 'owee', 'lustrum', 'sineregno', 'roze'), 'normaal'),
			'beeld'			 => array('Schermmodus', T::Enumeration, array('normaal', 'breedbeeld'), 'normaal'),
			'fixed'			 => array('Zijbalk scrollen', T::Enumeration, array('normaal', 'vast'), 'normaal'),
			'visitekaartjes' => array('Visitekaartjes', T::Enumeration, array('ja', 'nee'), 'ja'),
			'sneeuw'		 => array('Sneeuw', T::Enumeration, array('ja', 'freeze!', 'nee'), 'nee'),
			'neuzen'		 => array('Neuzen', T::Enumeration, array('overal', '2013', 'nee'), '2013'),
			'minion'		 => array('Minion', T::Enumeration, array('ja', 'nee'), 'nee')
		),
		'forum'			 => array(
			'draden_per_pagina'		 => array('Draadjes per pagina', T::Integer, array(5, 100), 20),
			'posts_per_pagina'		 => array('Berichten per pagina', T::Integer, array(5, 100), 20),
			'zoekresultaten'		 => array('Zoekresultaten per pagina', T::Integer, array(10, 50), 20),
			'naamWeergave'			 => array('Naamweergave', T::Enumeration, array('civitas', 'volledig', 'bijnaam', 'aaidrom', 'Duckstad'), 'civitas'),
			'datumWeergave'			 => array('Datumweergave', T::Enumeration, array('relatief', 'vast'), 'relatief'),
			'ongelezenWeergave'		 => array('Ongelezenweergave', T::Enumeration, array('schuingedrukt', 'dikgedrukt', 'onderstreept', 'alsof gelezen'), 'schuingedrukt', array('schuingedrukt' => 'font-style: italic;', 'dikgedrukt' => 'font-weight: bold;', 'onderstreept' => 'text-decoration: underline;', 'alsof gelezen' => '')),
			'open_draad_op_pagina'	 => array('Open onderwerp op pagina', T::Enumeration, array('1', 'ongelezen', 'laatste'), 'ongelezen'),
			'toonpasfotos'			 => array('Pasfoto\'s standaard weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'filter2008'			 => array('Berichten van 2008 eerst verbergen', T::Enumeration, array('ja', 'nee'), 'nee'),
			'fotoWeergave'			 => array('Toon groter formaat afbeeldingen', T::Enumeration, array('nee', 'hoverIntent', 'altijd'), 'nee')
		),
		'zijbalk'		 => array(
			'ishetal'				 => array('Is het al… weergeven', T::Enumeration, array('niet weergeven', 'willekeurig', 'wist u dat', 'weekend', 'kring', 'werkgroep', 'lezing', 'borrel', 'jarig', 'dies', 'happie', 'lunch', 'studeren'), 'willekeurig'),
			'agendaweken'			 => array('Aantal weken in agenda weergeven', T::Integer, array(0, 10), 2),
			'agenda_max'			 => array('Maximaal aantal agenda-items', T::Integer, array(0, 50), 15),
			'mededelingen'			 => array('Aantal mededelingen', T::Integer, array(0, 50), 5),
			'forum_belangrijk'		 => array('Aantal belangrijke forumberichten', T::Integer, array(0, 50), 5),
			'forum'					 => array('Aantal forumberichten', T::Integer, array(0, 50), 10),
			'forum_zelf'			 => array('Aantal zelf geposte forumberichten', T::Integer, array(0, 50), 0),
			//'forum_preview' => array('Laatste forumbericht weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'fotoalbum'				 => array('Laatste fotoalbum weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'verjaardagen'			 => array('Aantal verjaardagen in zijbalk', T::Integer, array(0, 50), 9),
			'verjaardagen_pasfotos'	 => array('Pasfoto\'s bij verjaardagen', T::Enumeration, array('ja', 'nee'), 'ja')
		),
		'voorpagina'	 => array(
			'maaltijdblokje'		 => array('Eerstvolgende maaltijd weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'twitterblokje'			 => array('Twitter-blokje weergeven', T::Enumeration, array('ja', 'nee'), 'nee'),
			'bijbelroosterblokje'	 => array('Bijbelroosterblokje weergeven', T::Enumeration, array('ja', 'nee'), 'ja')
		),
		'agenda'		 => array(
			'toonBijbelrooster'	 => array('Bijbelrooster weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonVerjaardagen'	 => array('Verjaardagen weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonMaaltijden'	 => array('Maaltijden weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonCorvee'		 => array('Corvee weergeven', T::Enumeration, array('iedereen', 'eigen', 'nee'), 'eigen')
		),
		'mededelingen'	 => array(
			'aantalPerPagina' => array('Aantal mededeling per pagina', T::Integer, array(5, 50), 10)
		),
		'googleContacts' => array(
			'groepnaam'	 => array('Naam van groep voor contacten in Google contacts', T::String, array(1, 100), 'C.S.R.-leden'),
			'extended'	 => array('Uitgebreide export (nickname, duckname, voorletters, adres/tel ouders, website, chataccounts, eetwens) ', T::Enumeration, array('ja', 'nee'), 'ja')
		)
	);

	protected function __construct() {
		parent::__construct();
		$this->reload();
	}

	public function has($module, $key) {
		return array_key_exists($module, $this->instellingen) AND is_array($this->instellingen[$module]) AND array_key_exists($key, $this->instellingen[$module]);
	}

	public function getInstellingen() {
		return $this->instellingen;
	}

	public function getModules() {
		return array_keys($this->instellingen);
	}

	public function getDescription($module, $key) {
		return $this->instellingen[$module][$key][0];
	}

	public function getType($module, $key) {
		return $this->instellingen[$module][$key][1];
	}

	public function getTypeOptions($module, $key) {
		return $this->instellingen[$module][$key][2];
	}

	public function getDefault($module, $key) {
		return $this->instellingen[$module][$key][3];
	}

	public function isValidValue($module, $key, $value) {
		switch ($this->getType($module, $key)) {
			case T::Enumeration:
				if (in_array($value, $this->getTypeOptions($module, $key))) {
					return true;
				}
				break;

			case T::Integer:
				if ($value >= $this->instellingen[$module][$key][2][0] AND
						$value <= $this->instellingen[$module][$key][2][1]
				) {
					return true;
				}
				break;

			case T::String:
				if (strlen($value) >= $this->instellingen[$module][$key][2][0] AND
						strlen($value) <= $this->instellingen[$module][$key][2][1] AND
						preg_match('/^[\w\-_\. ]*$/', $value)
				) {
					return true;
				}
				break;
		}
		return false;
	}

	public function getTechnicalValue($module, $key) {
		$value = $this->getValue($module, $key);
		if (array_key_exists(4, $this->instellingen[$module][$key]) AND array_key_exists($value, $this->instellingen[$module][$key][4])) {
			return $this->instellingen[$module][$key][4][$value];
		}
		return $value;
	}

	public function getValue($module, $key) {
		if (!Instellingen::instance()->has($module, 'lid_' . $key) AND $this->has($module, $key)) {
			// als deze instelling wel bestaat maar niet is gezet door de gebruiker
			$this->setDefaultValue($module, $key);
		}
		return Instellingen::get($module, 'lid_' . $key);
	}

	public function setValue($module, $key, $value) {
		if (!$this->has($module, $key)) {
			throw new Exception('Deze instelling  bestaat niet: ' . $module . '->' . $key);
		}
		if ($this->getType($module, $key) === T::Integer) {
			$value = (int) $value;
		}
		if ($this->isValidValue($module, $key, $value)) {
			Instellingen::setTemp($module, 'lid_' . $key, $value);
		} else {
			// als waarde van deze instelling ongeldig is
			$this->setDefaultValue($module, $key);
		}
	}

	public function setDefaultValue($module, $key) {
		Instellingen::setTemp($module, 'lid_' . $key, $this->getDefault($module, $key));
	}

	public function reload() {
		foreach ($this->instellingen as $module => $instellingen) {
			foreach ($instellingen as $key => $value) {
				$this->setDefaultValue($module, $key);
			}
		}
		$instellingen = $this->find('uid = ?', array(LoginModel::getUid()));
		foreach ($instellingen as $instelling) {
			try {
				$this->setValue($instelling->module, $instelling->instelling_id, $instelling->waarde);
			} catch (Exception $e) {
				if (startsWith($e->getMessage(), 'Deze instelling  bestaat niet')) {
					$rowcount = $this->deleteByPrimaryKey($instelling->getValues(true));
					if ($rowcount !== 1) {
						throw new Exception('Niet bestaande instelling verwijderen mislukt');
					}
				} else {
					SimpleHTML::setMelding($e->getMessage(), -1);
					DebugLogModel::instance()->log(get_called_class(), 'reload', func_get_args(), $e);
				}
			}
		}
	}

	public function save() {
		$properties[] = array('uid', 'module', 'instelling_id', 'waarde');
		foreach ($this->instellingen as $module => $instellingen) {
			foreach ($instellingen as $key => $value) {
				$value = filter_input(INPUT_POST, $module . '_' . $key, FILTER_SANITIZE_STRING);
				$this->setValue($module, $key, $value); // sanatize value
				$properties[] = array(LoginModel::getUid(), $module, $key, $this->getValue($module, $key));
			}
		}
		$orm = self::orm;
		Database::sqlInsertMultiple($orm::getTableName(), $properties);
	}

	public function setForAll($module, $key, $value) {
		$properties[] = array('uid', 'module', 'instelling_id', 'waarde');
		$this->setValue($module, $key, $value); // sanatize value
		$value = $this->getValue($module, $key);
		$leden = Database::sqlSelect(array('uid'), 'lid');
		$leden->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($leden as $uid) {
			$properties[] = array($uid, $module, $key, $value);
		}
		$orm = self::orm;
		Database::sqlInsertMultiple($orm::getTableName(), $properties);
	}

}
