<?php

namespace CsrDelft\model;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\LidInstelling;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
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
	protected static $defaults = [
		'algemeen' => [
			'bijbel' => [
				'Bijbelvertaling',
				T::Enumeration,
				['NBV' => 'De Nieuwe Bijbelvertaling', 'BGT' => 'Bijbel in Gewone Taal', 'GNB96' => 'Groot Nieuws Bijbel', 'NFB' => 'Nije Fryske Bibeloersetting', 'NBG51' => 'NBG-vertaling 1951', 'SVJ' => 'Statenvertaling (Jongbloed-editie)', 'HSVI' => 'Herziene Statenvertaling', 'CEVD' => 'Contemporary English Version', 'GNTD' => 'Good News Translation'],
				'NBV',
				'Bijbelvertaling die wordt gebruikt als iemand naar een bijbeltekst verwijst op het forum.'
			]
		],
		'agenda' => [
			'toonVerjaardagen' => [
				'Verjaardagen weergeven',
				T::Enumeration,
				['ja', 'nee'],
				'ja',
				'Geef verjaardagen weer in de agenda, geldt ook voor de agenda geimporteerd in je eigen systeem.'
			],
			'toonMaaltijden' => [
				'Maaltijden weergeven',
				T::Enumeration,
				['ja', 'nee'],
				'ja',
				'Geef maaltijden weer in de agenda, geldt ook voor de agenda geimporteerd in je eigen systeem.'
			],
			'toonCorvee' => [
				'Corvee weergeven',
				T::Enumeration,
				['iedereen', 'eigen', 'nee'],
				'eigen',
				'Geef corvee weer in de agenda, geldt ook voor de agenda geimporteerd in je eigen systeem.'
			]
		],
		'layout' => [
			'toegankelijk' => [
				'Leesbaarheid',
				T::Enumeration,
				['standaard', 'bredere letters'],
				'standaard',
				'Maak de stek beter leesbaar door een breder lettertype in te stellen.'
			],
			'opmaak' => [
				'Opmaak',
				T::Enumeration,
				['normaal', 'lustrum', 'owee', 'dies', 'sineregno', 'civitasia', 'roze'],
				'normaal',
				'Ik daag je uit om de stek in de roze opmaak te gebruiken.'
			],
			'fx' => [
				'Effect',
				T::Enumeration,
				['nee', 'civisaldo', 'onontdekt', 'sneeuw', 'space', 'wolken'],
				'nee',
				'Het effect wat wordt weergegeven tijdens zoeken en als het menu geopend is.'
			],
			'visitekaartjes' => [
				'Civikaartjes',
				T::Enumeration,
				['ja', 'nee'],
				'ja',
				'Toon een foto en korte beschrijving van een persoon als je met je muis over iemands naam beweegt.'
			],
			'neuzen' => [
				'Neuzen',
				T::Enumeration,
				['2013', 'nee'],
				'2013',
				'Vervang voor leden van lichting 13 iedere "o" door een clownsneus.'
			],
			'minion' => [
				'Minion',
				T::Enumeration,
				['ja', 'nee'],
				'nee',
				'Zet de minion aan of uit.'
			],
			'trein' => [
				'Trein',
				T::Enumeration,
				['nee', 'willekeurig'],
				'nee',
				'Tjoek tjoek!'
			]
		],
		'forum' => [
			'draden_per_pagina' => [
				'Draadjes per pagina',
				T::Integer,
				[5, 100],
				20,
				'Aantal draadjes per pagina in het deel en recent overzicht.'
			],
			'posts_per_pagina' => [
				'Berichten per pagina',
				T::Integer,
				[5, 100],
				20,
				'Aantal berichten die per pagina zichtbaar zijn.'
			],
			'zoekresultaten' => [
				'Zoekresultaten per pagina',
				T::Integer,
				[10, 50],
				20,
				'Aantal zoekresultaten die per pagina zichtbaar zijn.'
			],
			'naamWeergave' => [
				'Naamweergave',
				T::Enumeration,
				['civitas', 'volledig', 'bijnaam', 'aaidrom', 'Duckstad'],
				'civitas',
				'Weergave van namen in het forum.'
			],
			'datumWeergave' => [
				'Datumweergave',
				T::Enumeration,
				['relatief', 'vast'],
				'relatief',
				'Datumweergave voor draadjes en berichten op het forum.'
			],
			'ongelezenWeergave' => [
				'Ongelezenweergave',
				T::Enumeration,
				['cursief', 'dikgedrukt', 'onderstreept', 'alsof-gelezen'],
				'cursief',
				'Hoe ongelezen draadjes eruit zien in de zijbalk en in het forum.'
			],
			'open_draad_op_pagina' => [
				'Open draad op pagina',
				T::Enumeration,
				['1', 'ongelezen', 'laatste'],
				'ongelezen',
				'Op welke pagina een draad moet openen.'
			],
			'fotoWeergave' => [
				'Toon groter formaat foto\'s',
				T::Enumeration,
				['nee', 'boven bericht', 'in bericht'],
				'boven bericht',
				'Weergave van fotos op het forum.'
			],
			'filter2008' => [
				'Berichten van 2008 eerst verbergen',
				T::Enumeration,
				['ja', 'nee'],
				'nee',
				'Verberg berichten van leden uit 2008'
			]
		],
		'fotoalbum' => [
			'tag_suggestions' => [
				'Etiket suggesties',
				T::Enumeration,
				['leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'],
				'leden',
				'De groep waaruit suggesties worden gedaan tijdens het etiketteren in het fotoalbum.'
			]
		],
		'googleContacts' => [
			'groepnaam' => [
				'Naam van groep voor contacten in Google contacts',
				T::String,
				[1, 100],
				'C.S.R.-leden',
				''
			],
		],
		'mededelingen' => [
			'aantalPerPagina' => [
				'Aantal mededelingen per pagina',
				T::Integer,
				[5, 50],
				10,
				''
			]
		], /*
		  'voorpagina'	 => array(
		  'maaltijdblokje'		 => array('Eerstvolgende maaltijd weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
		  'laatstefotoalbum'		 => array('Laatste fotoalbum weergeven', T::Enumeration, array('ja', 'nee'), 'ja'),
		  'twitterblokje'			 => array('Twitter-feed weergeven', T::Enumeration, array('ja', 'nee'), 'nee')
		  ), */
		'zijbalk' => [
			'ishetal' => ['Is het al… weergeven', T::Enumeration, ['niet weergeven', 'willekeurig', 'wist u dat', 'weekend', 'kring', 'lezing', 'borrel', 'jarig', 'dies', 'lunch', 'studeren', 'foutmelding', 'sponsorkliks'], 'willekeurig', ''],
			'favorieten' => ['Favorieten menu weergeven', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'agendaweken' => ['Aantal weken in agenda weergeven', T::Integer, [0, 10], 2, ''],
			'agenda_max' => ['Maximaal aantal agenda-items', T::Integer, [0, 50], 15, ''],
			'mededelingen' => ['Aantal mededelingen', T::Integer, [0, 50], 5, ''],
			'forum_belangrijk' => ['Aantal belangrijke forumberichten', T::Integer, [0, 50], 5, ''],
			'forum' => ['Aantal forumberichten', T::Integer, [0, 50], 10, ''],
			'forum_zelf' => ['Aantal zelf geposte forumberichten', T::Integer, [0, 50], 0, ''],
			'ledenmemory_topscores' => ['Ledenmemory topscores weergeven', T::Integer, [0, 10], 0, ''],
			'fotoalbum' => ['Laatste fotoalbum weergeven', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'fotos' => ['Aantal foto\'s weergeven', T::Integer, [0, 50], 6, ''],
			'verjaardagen' => ['Aantal verjaardagen weergeven', T::Integer, [0, 50], 9, ''],
			'verjaardagen_pasfotos' => ['Pasfoto\'s bij verjaardagen', T::Enumeration, ['ja', 'nee'], 'ja', '']
		],
		'zoeken' => [
			'favorieten' => ['Favorieten<span class="offtopic"> (telt niet mee voor max.)</span>', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'menu' => ['Menu<span class="offtopic"> (telt niet mee voor max.)</span>', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'leden' => [
				'Leden',
				T::Enumeration,
				[
					'LEDEN' => 'Huidige leden',
					'OUDLEDEN' => 'Alleen oudleden',
					'LEDEN|OUDLEDEN' => 'Leden en oudleden',
					'ALL' => 'Ook niet-leden',
					'NOVIET' => 'Alleen novieten',
					'GASTLID' => 'Alleen gastleden',
					'nee' => 'Nee'
				],
				'LEDEN', ''],
			'commissies' => ['Commissies', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'kringen' => ['Kringen', T::Enumeration, ['ja', 'nee'], 'nee', ''],
			'onderverenigingen' => ['Onderverenigingen', T::Enumeration, ['ja', 'nee'], 'nee', ''],
			'werkgroepen' => ['Werkgroepen', T::Enumeration, ['ja', 'nee'], 'nee', ''],
			'woonoorden' => ['Woonoorden', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'groepen' => ['Overige groepen', T::Enumeration, ['ja', 'nee'], 'nee', ''],
			'agenda' => ['Agenda', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'forum' => ['Forum', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'fotoalbum' => ['Fotoalbum', T::Enumeration, ['ja', 'nee'], 'nee', ''],
			'wiki' => ['Wiki', T::Enumeration, ['ja', 'nee'], 'ja', ''],
			'documenten' => ['Documenten', T::Enumeration, ['ja', 'nee'], 'nee', ''],
			'boeken' => ['Boeken', T::Enumeration, ['ja', 'nee'], 'nee', '']
		]
	];


	/**
	 * Geeft een array terug van dezelfde vorm als de instellingen, maar gevuld met gekozen instellingen.
	 *
	 * Let op, kan minder bevatten dan de instellingen array.
	 *
	 * @param string $uid
	 * @return string[]
	 */
	public function getAllForLid(string $uid) {
		return array_reduce($this->find('uid = ?', [$uid])->fetchAll(), function ($carry, LidInstelling $instelling) {
			if (!isset($carry[$instelling->module])) $carry[$instelling->module] = [];

			$carry[$instelling->module][$instelling->instelling_id] = $instelling->waarde;

			return $carry;
		}, []);
	}

	/**
	 * Functie getInstelling aanvullen met uid.
	 *
	 * @param array $primary_key_values
	 * @return LidInstelling|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$primary_key_values[] = LoginModel::getUid();
		/** @var LidInstelling|false */
		$value = parent::retrieveByPrimaryKey($primary_key_values);
		return $value;
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
				if (isset($options[$waarde])) {
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

	/**
	 * @param LidInstelling|PersistentEntity $entity
	 * @return int
	 * @throws CsrGebruikerException
	 */
	public function update(PersistentEntity $entity) {
		if (!isset(static::$defaults[$entity->module][$entity->instelling_id])) {
			throw new CsrGebruikerException("Instelling '{$entity->instelling_id}' uit module '{$entity->module}' niet gevonden.");
		}

		$type = $this->getTypeOptions($entity->module, $entity->instelling_id);
		$typeOptions = $this->getTypeOptions($entity->module, $entity->instelling_id);

		if ($type === T::Enumeration
			&& !in_array($entity->waarde, $typeOptions)) {
			throw new CsrGebruikerException("Waarde is geen geldige optie");
		}

		if ($type === T::String) {
			if (strlen($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException("Waarde is te lang");
			}

			if (strlen($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException("Waarde is te kort");
			}
		}

		if ($type === T::Integer) {
			if (intval($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException("Waarde is te lang");
			}

			if (intval($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException("Waarde is te kort");
			}
		}

		return parent::update($entity);
	}

}
