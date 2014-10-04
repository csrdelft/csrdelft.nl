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
			'bijbel' => array('Bijbelvertaling', T::Enumeration, array('NBV', 'NBG', 'Herziene Statenvertaling', 'Statenvertaling (Jongbloed)', 'Groot Nieuws Bijbel', 'Willibrordvertaling'), 'NBV')
		),
		'layout'		 => array(
			'toegankelijk'	 => array('Leesbaarheid', T::Enumeration, array('standaard', 'bredere letters'), 'standaard'),
			'opmaak'		 => array('Opmaak', T::Enumeration, array('normaal', 'owee', 'lustrum', 'sineregno', 'roze'), 'normaal'),
			'visitekaartjes' => array('Visitekaartjes', T::Enumeration, array('ja', 'nee'), 'nee'),
			'sneeuw'		 => array('Sneeuw', T::Enumeration, array('ja', 'freeze!', 'nee'), 'nee'),
			'neuzen'		 => array('Neuzen', T::Enumeration, array('overal', '2013', 'nee'), '2013'),
			'minion'		 => array('Minion', T::Enumeration, array('ja', 'nee'), 'nee')
		),
		'zijbalk'		 => array(
			'scrollen'				 => array('Scrollen', T::Enumeration, array('met pagina mee', 'apart scrollen'), 'met pagina mee'),
			'quicknav'				 => array('Navigatie knopjes weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'ishetal'				 => array('Is het al… weergeven', T::Enumeration, array('niet weergeven', 'willekeurig', 'wist u dat', 'weekend', 'kring', 'lezing', 'borrel', 'jarig', 'dies', 'happie', 'lunch', 'studeren'), 'willekeurig'),
			'agendaweken'			 => array('Aantal weken in agenda weergeven', T::Integer, array(0, 10), 2),
			'agenda_max'			 => array('Maximaal aantal agenda-items', T::Integer, array(0, 50), 15),
			'mededelingen'			 => array('Aantal mededelingen', T::Integer, array(0, 50), 5),
			'forum_belangrijk'		 => array('Aantal belangrijke forumberichten', T::Integer, array(0, 50), 5),
			'forum'					 => array('Aantal forumberichten', T::Integer, array(0, 50), 10),
			'forum_zelf'			 => array('Aantal zelf geposte forumberichten', T::Integer, array(0, 50), 0),
			'fotoalbum'				 => array('Laatste fotoalbum weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'fotos'					 => array('Aantal foto\'s weergeven', T::Integer, array(0, 50), 6),
			'verjaardagen'			 => array('Aantal verjaardagen weergeven', T::Integer, array(0, 50), 9),
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
		'forum'			 => array(
			'draden_per_pagina'		 => array('Draadjes per pagina', T::Integer, array(5, 100), 20),
			'posts_per_pagina'		 => array('Berichten per pagina', T::Integer, array(5, 100), 20),
			'zoekresultaten'		 => array('Zoekresultaten per pagina', T::Integer, array(10, 50), 20),
			'naamWeergave'			 => array('Naamweergave', T::Enumeration, array('civitas', 'volledig', 'bijnaam', 'aaidrom', 'Duckstad'), 'civitas'),
			'datumWeergave'			 => array('Datumweergave', T::Enumeration, array('relatief', 'vast'), 'relatief'),
			'ongelezenWeergave'		 => array('Ongelezenweergave', T::Enumeration, array('cursief', 'dikgedrukt', 'onderstreept', 'alsof-gelezen'), 'cursief'),
			'open_draad_op_pagina'	 => array('Open onderwerp op pagina', T::Enumeration, array('1', 'ongelezen', 'laatste'), 'ongelezen'),
			'toonpasfotos'			 => array('Pasfoto\'s standaard weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'fotoWeergave'			 => array('Toon groter formaat foto\'s', T::Enumeration, array('nee', 'boven bericht', 'in bericht'), 'boven bericht'),
			'filter2008'			 => array('Berichten van 2008 eerst verbergen', T::Enumeration, array('ja', 'nee'), 'nee')
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
			return false;
		}
		if ($this->getType($module, $key) === T::Integer) {
			$value = (int) $value;
		}
		if ($this->isValidValue($module, $key, $value)) {
			Instellingen::setTemp($module, 'lid_' . $key, $value);
			return true;
		} else {
			$this->setDefaultValue($module, $key);
			return false;
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
			$ok = $this->setValue($instelling->module, $instelling->instelling_id, $instelling->waarde); // validate
			if (!$ok) {
				$rowcount = $this->delete($instelling);
				if ($rowcount !== 1) {
					throw new Exception('Niet bestaande instelling verwijderen mislukt');
				}
			}
		}
	}

	public function save() {
		$properties[] = array('uid', 'module', 'instelling_id', 'waarde');
		foreach ($this->instellingen as $module => $instellingen) {
			foreach ($instellingen as $key => $value) {
				$value = filter_input(INPUT_POST, $module . '_' . $key, FILTER_SANITIZE_STRING);
				$ok = $this->setValue($module, $key, $value); // validate
				if ($ok) {
					$properties[] = array(LoginModel::getUid(), $module, $key, $this->getValue($module, $key));
				}
			}
		}
		$orm = self::orm;
		Database::sqlInsertMultiple($orm::getTableName(), $properties);
	}

	public function resetForAll($module, $key) {
		$orm = self::orm;
		Database::sqlDelete($orm::getTableName(), 'module = ? AND instelling_id = ?', array($module, $key));
	}

	public function setForAll($module, $key, $value) {
		$properties[] = array('uid', 'module', 'instelling_id', 'waarde');
		$ok = $this->setValue($module, $key, $value); // validate
		if (!$ok) {
			return;
		}
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
