<?php

require_once 'model/InstellingenModel.class.php';

/**
 * LidInstellingenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de instellingen bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 */
class LidInstellingen extends Instellingen {

	const orm = 'LidInstelling';

	protected static $instance;
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
	protected static $defaults = array(
		'algemeen'		 => array(
			'bijbel' => array('Bijbelvertaling', T::Enumeration, array('De Nieuwe Bijbelvertaling' => 'NBV', 'Bijbel in Gewone Taal' => 'BGT', 'Groot Nieuws Bijbel' => 'GNB96', 'Nije Fryske Bibeloersetting' => 'NFB', 'NBG-vertaling 1951' => 'NBG51', 'Statenvertaling (Jongbloed-editie)' => 'SVJ', 'Herziene Statenvertaling' => 'HSVI', 'Contemporary English Version' => 'CEVD', 'Good News Translation' => 'GNTD'), 'NBV')
		),
		'agenda'		 => array(
			'toonBijbelrooster'	 => array('Bijbelrooster weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonVerjaardagen'	 => array('Verjaardagen weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonMaaltijden'	 => array('Maaltijden weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonCorvee'		 => array('Corvee weergeven', T::Enumeration, array('iedereen', 'eigen', 'nee'), 'eigen')
		),
		'layout'		 => array(
			'toegankelijk'	 => array('Leesbaarheid', T::Enumeration, array('standaard', 'bredere letters'), 'standaard'),
			'opmaak'		 => array('Opmaak', T::Enumeration, array('normaal', 'owee', 'dies', 'sineregno', 'roze'), 'normaal'),
			'fx'			 => array('Effect', T::Enumeration, array('nee', 'sneeuw', 'space', 'wolken'), 'nee'),
			'visitekaartjes' => array('Civikaartjes', T::Enumeration, array('ja', 'nee'), 'ja'),
			'neuzen'		 => array('Neuzen', T::Enumeration, array('2013', 'nee'), '2013'),
			'minion'		 => array('Minion', T::Enumeration, array('ja', 'nee'), 'nee'),
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
		'googleContacts' => array(
			'groepnaam'	 => array('Naam van groep voor contacten in Google contacts', T::String, array(1, 100), 'C.S.R.-leden'),
			'extended'	 => array('Uitgebreide export (nickname, duckname, voorletters, adres/tel ouders, website, chataccounts, eetwens) ', T::Enumeration, array('ja', 'nee'), 'ja')
		),
		'mededelingen'	 => array(
			'aantalPerPagina' => array('Aantal mededeling per pagina', T::Integer, array(5, 50), 10)
		),
		'voorpagina'	 => array(
			'bijbelroosterblokje'	 => array('Bijbelrooster weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'maaltijdblokje'		 => array('Eerstvolgende maaltijd weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'laatstefotoalbum'		 => array('Laatste fotoalbum weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'twitterblokje'			 => array('Twitter-feed weergeven', T::Enumeration, array('ja', 'nee'), 'nee')
		),
		'zijbalk'		 => array(
			'scrollen'				 => array('Scrollen', T::Enumeration, array('met pagina mee', 'apart scrollen', 'pauper/desktop'), 'met pagina mee'),
			'scrollbalk'			 => array('Scrollbalk tonen', T::Enumeration, array('ja', 'nee'), 'ja'),
			'ishetal'				 => array('Is het al… weergeven', T::Enumeration, array('niet weergeven', 'willekeurig', 'wist u dat', 'weekend', 'kring', 'lezing', 'borrel', 'jarig', 'dies', 'lunch', 'studeren'), 'willekeurig'),
			'favorieten'			 => array('Favorieten menu weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
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
		)
	);

	/**
	 * Functie getInstelling aanvullen met uid.
	 * 
	 * @param array $primary_key_values
	 * @return LidInstelling
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$primary_key_values[] = LoginModel::getUid();
		return parent::retrieveByPrimaryKey($primary_key_values);
	}

	protected function newInstelling($module, $id) {
		$instelling = new LidInstelling();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$instelling->uid = LoginModel::getUid();
		$this->create($instelling);
		return $instelling;
	}

	public function getDescription($module, $id) {
		return static::$defaults[$module][$id][0];
	}

	public function getType($module, $id) {
		if (static::has($module, $id)) {
			return static::$defaults[$module][$id][1];
		} else {
			return null;
		}
	}

	public function getTypeOptions($module, $id) {
		return static::$defaults[$module][$id][2];
	}

	public function getDefault($module, $id) {
		return static::$defaults[$module][$id][3];
	}

	public function getTechnicalValue($module, $id) {
		$waarde = static::get($module, $id);
		$index = array_search($waarde, static::$defaults[$module][$id][2]);
		if (isset(static::$defaults[$module][$id][4]) AND isset(static::$defaults[$module][$id][4][$index])) {
			return static::$defaults[$module][$id][4][$index];
		}
		return $waarde;
	}

	public function isValidValue($module, $id, $waarde) {
		$options = $this->getTypeOptions($module, $id);
		switch ($this->getType($module, $id)) {
			case T::Enumeration:
				if (in_array($waarde, $options)) {
					return true;
				}
				break;

			case T::Integer:
				if ($waarde >= $options[0] AND $waarde <= $options[1]) {
					return true;
				}
				break;

			case T::String:
				if (strlen($waarde) >= $options[0] AND strlen($waarde) <= $options[1] AND preg_match('/^[\w\-_\. ]*$/', $waarde)) {
					return true;
				}
				break;
		}
		return false;
	}

	public function save() {
		// create matrix for sqlInsertMultiple
		$properties[] = $this->orm->getAttributes();

		foreach (static::$defaults as $module => $instellingen) {
			foreach ($instellingen as $id => $waarde) {
				if ($this->getType($module, $id) === T::Integer) {
					$filter = FILTER_SANITIZE_NUMBER_INT;
				} else {
					$filter = FILTER_SANITIZE_STRING;
				}
				$waarde = filter_input(INPUT_POST, $module . '_' . $id, $filter);
				if (!$this->isValidValue($module, $id, $waarde)) {
					$waarde = $this->getDefault($module, $id);
				}
				$properties[] = array($module, $id, $waarde, LoginModel::getUid());
			}
		}
		Database::sqlInsertMultiple($this->orm->getTableName(), $properties, true);
		$this->flushCache(true);
	}

	public function resetForAll($module, $id) {
		Database::sqlDelete($this->orm->getTableName(), 'module = ? AND instelling_id = ?', array($module, $id));
		$this->flushCache(true);
	}

}
