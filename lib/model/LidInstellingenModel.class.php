<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\LidInstelling;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\Database;


/**
 * LidInstellingenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de instellingen bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 */
class LidInstellingenModel extends InstellingenModel {

	const ORM = LidInstelling::class;

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
		'algemeen' => array(
			'bijbel' => array('Bijbelvertaling', T::Enumeration, array('NBV' => 'De Nieuwe Bijbelvertaling', 'BGT' => 'Bijbel in Gewone Taal', 'GNB96' => 'Groot Nieuws Bijbel', 'NFB' => 'Nije Fryske Bibeloersetting', 'NBG51' => 'NBG-vertaling 1951', 'SVJ' => 'Statenvertaling (Jongbloed-editie)', 'HSVI' => 'Herziene Statenvertaling', 'CEVD' => 'Contemporary English Version', 'GNTD' => 'Good News Translation'), 'NBV')
		),
		'agenda' => array(
			'toonVerjaardagen' => array('Verjaardagen weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonMaaltijden' => array('Maaltijden weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'toonCorvee' => array('Corvee weergeven', T::Enumeration, array('iedereen', 'eigen', 'nee'), 'eigen')
		),
		'layout' => array(
			'toegankelijk' => array('Leesbaarheid', T::Enumeration, array('standaard', 'bredere letters'), 'standaard'),
			'opmaak' => array('Opmaak', T::Enumeration, array('normaal', 'lustrum', 'owee', 'dies', 'sineregno', 'civitasia', 'roze'), 'normaal'),
			'fx' => array('Effect', T::Enumeration, array('nee', 'civisaldo', 'onontdekt', 'sneeuw', 'space', 'wolken'), 'nee'),
			'visitekaartjes' => array('Civikaartjes', T::Enumeration, array('ja', 'nee'), 'ja'),
			'neuzen' => array('Neuzen', T::Enumeration, array('2013', 'nee'), '2013'),
			'minion' => array('Minion', T::Enumeration, array('ja', 'nee'), 'nee'),
		),
		'forum' => array(
			'draden_per_pagina' => array('Draadjes per pagina', T::Integer, array(5, 100), 20),
			'posts_per_pagina' => array('Berichten per pagina', T::Integer, array(5, 100), 20),
			'zoekresultaten' => array('Zoekresultaten per pagina', T::Integer, array(10, 50), 20),
			'naamWeergave' => array('Naamweergave', T::Enumeration, array('civitas', 'volledig', 'bijnaam', 'aaidrom', 'Duckstad'), 'civitas'),
			'datumWeergave' => array('Datumweergave', T::Enumeration, array('relatief', 'vast'), 'relatief'),
			'ongelezenWeergave' => array('Ongelezenweergave', T::Enumeration, array('cursief', 'dikgedrukt', 'onderstreept', 'alsof-gelezen'), 'cursief'),
			'open_draad_op_pagina' => array('Open onderwerp op pagina', T::Enumeration, array('1', 'ongelezen', 'laatste'), 'ongelezen'),
			'toonpasfotos' => array('Pasfoto\'s standaard weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'fotoWeergave' => array('Toon groter formaat foto\'s', T::Enumeration, array('nee', 'boven bericht', 'in bericht'), 'boven bericht'),
			'filter2008' => array('Berichten van 2008 eerst verbergen', T::Enumeration, array('ja', 'nee'), 'nee')
		),
		'fotoalbum' => array(
			'tag_suggestions' => array('Etiket suggesties', T::Enumeration, array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'), 'leden')
		),
		'googleContacts' => array(
			'groepnaam' => array('Naam van groep voor contacten in Google contacts', T::String, array(1, 100), 'C.S.R.-leden'),
		),
		'mededelingen' => array(
			'aantalPerPagina' => array('Aantal mededeling per pagina', T::Integer, array(5, 50), 10)
		), /*
		  'voorpagina'	 => array(
		  'maaltijdblokje'		 => array('Eerstvolgende maaltijd weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
		  'laatstefotoalbum'		 => array('Laatste fotoalbum weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
		  'twitterblokje'			 => array('Twitter-feed weergeven', T::Enumeration, array('ja', 'nee'), 'nee')
		  ), */
		'zijbalk' => array(
			'scrollen' => array('Scrollen', T::Enumeration, array('met pagina mee', 'apart scrollen', 'pauper/desktop'), 'met pagina mee'),
			'scrollbalk' => array('Scrollbalk tonen', T::Enumeration, array('ja', 'nee'), 'ja'),
			'ishetal' => array('Is het al… weergeven', T::Enumeration, array('niet weergeven', 'willekeurig', 'wist u dat', 'weekend', 'kring', 'lezing', 'borrel', 'jarig', 'dies', 'lunch', 'studeren', 'foutmelding'), 'willekeurig'),
			'favorieten' => array('Favorieten menu weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'agendaweken' => array('Aantal weken in agenda weergeven', T::Integer, array(0, 10), 2),
			'agenda_max' => array('Maximaal aantal agenda-items', T::Integer, array(0, 50), 15),
			'mededelingen' => array('Aantal mededelingen', T::Integer, array(0, 50), 5),
			'forum_belangrijk' => array('Aantal belangrijke forumberichten', T::Integer, array(0, 50), 5),
			'forum' => array('Aantal forumberichten', T::Integer, array(0, 50), 10),
			'forum_zelf' => array('Aantal zelf geposte forumberichten', T::Integer, array(0, 50), 0),
			'ledenmemory_topscores' => array('Ledenmemory topscores weergeven', T::Integer, array(0, 10), 0),
			'fotoalbum' => array('Laatste fotoalbum weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
			'fotos' => array('Aantal foto\'s weergeven', T::Integer, array(0, 50), 6),
			'verjaardagen' => array('Aantal verjaardagen weergeven', T::Integer, array(0, 50), 9),
			'verjaardagen_pasfotos' => array('Pasfoto\'s bij verjaardagen', T::Enumeration, array('ja', 'nee'), 'ja')
		),
		'zoeken' => array(
			'favorieten' => array('Favorieten<span class="offtopic"> (telt niet mee voor max.)</span>', T::Enumeration, array('ja', 'nee'), 'ja'),
			'menu' => array('Menu<span class="offtopic"> (telt niet mee voor max.)</span>', T::Enumeration, array('ja', 'nee'), 'ja'),
			'leden' => array(
				'Leden',
				T::Enumeration,
				array(
					'LEDEN' => 'Huidige leden',
					'OUDLEDEN' => 'Alleen oudleden',
					'LEDEN|OUDLEDEN' => 'Leden en oudleden',
					'ALL' => 'Ook niet-leden',
					'NOVIET' => 'Alleen novieten',
					'GASTLID' => 'Alleen gastleden',
					'nee' => 'Nee'
				),
				'LEDEN'),
			'commissies' => array('Commissies', T::Enumeration, array('ja', 'nee'), 'ja'),
			'kringen' => array('Kringen', T::Enumeration, array('ja', 'nee'), 'nee'),
			'onderverenigingen' => array('Onderverenigingen', T::Enumeration, array('ja', 'nee'), 'nee'),
			'werkgroepen' => array('Werkgroepen', T::Enumeration, array('ja', 'nee'), 'nee'),
			'woonoorden' => array('Woonoorden', T::Enumeration, array('ja', 'nee'), 'ja'),
			'groepen' => array('Overige groepen', T::Enumeration, array('ja', 'nee'), 'nee'),
			'agenda' => array('Agenda', T::Enumeration, array('ja', 'nee'), 'ja'),
			'forum' => array('Forum', T::Enumeration, array('ja', 'nee'), 'ja'),
			'fotoalbum' => array('Fotoalbum', T::Enumeration, array('ja', 'nee'), 'nee'),
			'wiki' => array('Wiki', T::Enumeration, array('ja', 'nee'), 'ja'),
			'documenten' => array('Documenten', T::Enumeration, array('ja', 'nee'), 'nee'),
			'boeken' => array('Boeken', T::Enumeration, array('ja', 'nee'), 'nee')
		)
	);

	/**
	 * Functie getInstelling aanvullen met uid.
	 *
	 * @param array $primary_key_values
	 * @return LidInstelling|false
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
		$properties[] = $this->getAttributes();
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
		Database::instance()->sqlInsertMultiple($this->getTableName(), $properties, true);
		$this->flushCache(true);
	}

	public function resetForAll($module, $id) {
		Database::instance()->sqlDelete($this->getTableName(), 'module = ? AND instelling_id = ?', array($module, $id));
		$this->flushCache(true);
	}

}
