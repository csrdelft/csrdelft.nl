<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\LidInstelling;
use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\Database;


/**
 * LidInstellingenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de toestemming bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 */
class LidToestemmingModel extends InstellingenModel {

	const ORM = LidToestemming::class;

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
			'foto_intern' => ['Mijn foto\'s mogen op de interne stek geplaatst worden.', T::Enumeration, ['', 'ja', 'nee'], ''],
			'foto_extern' => ['Mijn foto\'s mogen op de externe stek geplaatst worden en voor promotiedoeleinden gebruikt worden.', T::Enumeration, ['', 'ja', 'nee'], ''],
			'vereniging' => ['Het bestuur en commissies van C.S.R. mogen mijn persoonsgegevens gebruiken om hun taken uit te voeren.', T::Enumeration, ['', 'ja', 'nee'], ''],
			'bijzonder' => ['Het bestuur en commissies van C.S.R. mogen mijn bijzondere persoonsgegevens gebruiken om hun taken uit te voeren.', T::Enumeration, ['', 'ja', 'nee'], ''],
		],
		'intern' => [ // Gegevens die niet directe velden in Profiel zijn
			// C.S.R. Groepen
			'commissies' => ['Commissies', T::Enumeration, ['', 'ja', 'nee'], ''],
			'werkgroepen' => ['Werkgroepen', T::Enumeration, ['', 'ja', 'nee'], ''],
			'ondervereniging' => ['Ondervereniging', T::Enumeration, ['', 'ja', 'nee'], ''],
			'groepen' => ['Groepen', T::Enumeration, ['', 'ja', 'nee'], ''],
			'kring' => ['Kring', T::Enumeration, ['', 'ja', 'nee'], ''],
			'verticale' => ['Verticale', T::Enumeration, ['', 'ja', 'nee'], ''],

			// C.S.R. overig
			'forum_posts' => ['Forumbijdragen', T::Enumeration, ['', 'ja', 'nee'], ''],
			'kinderen' => ['Verenigingskinderen', T::Enumeration, ['', 'ja', 'nee'], ''],

			// Gegevens
			'naam' => ['Naam', T::Enumeration, ['', 'ja', 'nee'], ''],
			'profielfoto' => ['Profielfoto', T::Enumeration, ['', 'ja', 'nee'], ''],
			'fotos' => ['Getagde foto\'s', T::Enumeration, ['', 'ja', 'nee'], ''],
		],
		'profiel' => [ // Matcht velden in Profiel
			// naam
			'voorletters' => ['Voorletters', T::Enumeration, ['', 'ja', 'nee'], ''],
			'nickname' => ['Bijnaam', T::Enumeration, ['', 'ja', 'nee'], ''],
			// fysiek
			'geslacht' => ['Geslacht', T::Enumeration, ['', 'ja', 'nee'], ''],
			'gebdatum' => ['Geboortedatum', T::Enumeration, ['', 'ja', 'nee'], ''],
			// adres
			'adres' => ['Straat + Huisnummer', T::Enumeration, ['', 'ja', 'nee'], ''],
			'postcode' => ['Postcode', T::Enumeration, ['', 'ja', 'nee'], ''],
			'woonplaats' => ['Woonplaats', T::Enumeration, ['', 'ja', 'nee'], ''],
			'land' => ['Land', T::Enumeration, ['', 'ja', 'nee'], ''],
			'mobiel' => ['Mobiel nummer', T::Enumeration, ['', 'ja', 'nee'], ''],
			'telefoon' => ['Telefoonnummer', T::Enumeration, ['', 'ja', 'nee'], ''],
			// Ouders
			'o_adres' => ['Straat + Huisnummer ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
			'o_postcode' => ['Postcode ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
			'o_woonplaats' => ['Woonplaats ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
			'o_land' => ['Land ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
			'o_telefoon' => ['Telefoonnummer ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
			// studie
			'studie' => ['Studie', T::Enumeration, ['', 'ja', 'nee'], ''],
			'studienr' => ['Studie nummer', T::Enumeration, ['', 'ja', 'nee'], ''],
			'studiejaar' => ['Studiejaar', T::Enumeration, ['', 'ja', 'nee'], ''],
			// contact
			'email' => ['Email', T::Enumeration, ['', 'ja', 'nee'], ''],
			// lidmaatschap
			'status' => ['Status', T::Enumeration, ['', 'ja', 'nee'], ''], // Willen we dit?
			// civi-gegevens
			'patroon' => ['Patroon/Matroon', T::Enumeration, ['', 'ja', 'nee'], ''],
			// Persoonlijk
			'eetwens' => ['Allergie/dieet', T::Enumeration, ['', 'ja', 'nee'], ''],
		],
		'profiel_lid' => [
			'bankrekening' => ['Bankrekeningnummer', T::Enumeration, ['', 'ja', 'nee'], ''],
		],
		'profiel_oudlid' => [
			'echtgenoot' => ['Echtgenoot', T::Enumeration, ['', 'ja', 'nee'], ''],
		],
	];

	/**
	 * Geef de categorien waar een lid toestemming voor kan geven. Oudleden hebben minder gegevens dan leden.
	 *
	 * @param boolean $islid
	 * @return array
	 */
	public function getRelevantToestemmingCategories($islid) {
		$instellingen = [];

		if ($islid) {
			$instellingen['profiel_lid'] = array_merge($instellingen, $this->getModuleInstellingen('profiel_lid'));
		}

		$instellingen['profiel_oudlid'] = $this->getModuleInstellingen('profiel_oudlid');
		$instellingen['profiel'] = $this->getModuleInstellingen('profiel');
		$instellingen['intern'] = $this->getModuleInstellingen('intern');

		return $instellingen;
	}

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

	protected function newInstelling($module, $id, $uid = null) {
		$instelling = new LidToestemming();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$instelling->uid = $uid ?? LoginModel::getUid();
		$this->create($instelling);
		return $instelling;
	}

	public static function toestemmingGegeven() {
		if ($_SERVER['REQUEST_URI'] == '/privacy') // Doe niet naggen op de privacy info pagina.
			return true;

		if (startsWith($_SERVER['REQUEST_URI'], '/wachtwoord')) // Voorkom problemen tijdens opnieuw instellen wachtwoord
			return true;

		if (isset($_SESSION['stop_nag']) && $_SESSION['stop_nag'] > time() - 3600) // Doe niet naggen voor een uur als een lid op annuleren heeft geklikt.
			return true;

		$uid = LoginModel::getUid();

		$modules = ['algemeen', 'intern', 'profiel'];
		$placeholdersModule = implode(', ', array_fill(0, count($modules), '?'));

		if (static::instance()->count('uid = ? AND waarde = \'\' AND module IN (' . $placeholdersModule . ')', array_merge([$uid], $modules)) != 0) // Er zijn nog opties
			return false;

		if (static::instance()->count('uid = ?', [$uid]) == 0) // Er is geen enkele selectie gemaakt
			return false;

		return true;
	}

	public function toestemming($profiel, $id, $cat = 'profiel', $except = 'P_LEDEN_MOD') {
		if ($profiel->uid == LoginModel::getUid())
			return true;

		if (LoginModel::mag($except))
			return true;

		/** @var LidToestemming $toestemming */
		$toestemming = parent::retrieveByPrimaryKey([$cat, $id, $profiel->uid]);

		if (!$toestemming)
			return false;

		return $toestemming->waarde == "ja";
	}

	public function toestemmingUid($uid, $id, $except = 'P_LEDEN_MOD') {
		if ($uid == LoginModel::getUid())
			return true;

		if (LoginModel::mag($except))
			return true;

		/** @var LidToestemming $toestemming */
		$toestemming = parent::retrieveByPrimaryKey(['toestemming', $id, $uid]);

		if (!$toestemming)
			return false;

		return $toestemming->waarde == "ja";
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
		}
		return false;
	}

	public function getToestemmingForIds($ids, $waardes = ['ja', 'nee']) {
		$placeholdersModule = implode(', ', array_fill(0, count($ids), '?'));
		$placeholdersWaarde = implode(', ', array_fill(0, count($waardes), '?'));

		return $this->find('instelling_id IN (' . $placeholdersModule . ') AND waarde IN (' . $placeholdersWaarde . ')', array_merge($ids, $waardes), null, 'uid');
	}

	/**
	 * @param null $uid Sla op voor uid
	 * @throws \Exception
	 */
	public function save($uid = null) {
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
					continue;
				}
				$properties[] = array($module, $id, $waarde, $uid ?? LoginModel::getUid());
			}
		}
		Database::instance()->sqlInsertMultiple($this->getTableName(), $properties, true);
		$this->flushCache(true);
	}
}
