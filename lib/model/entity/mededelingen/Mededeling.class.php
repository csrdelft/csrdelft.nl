<?php

namespace CsrDelft\model\entity\mededelingen;

use CsrDelft\model\mededelingen\MededelingCategorieenModel;
use CsrDelft\model\mededelingen\MededelingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Mededeling.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Mededeling extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Datum van plaatsen
	 * @var string
	 */
	public $datum;
	/**
	 * Datum van vervallen
	 * @var string
	 */
	public $vervaltijd;
	/**
	 * Titel van de mededeling
	 * @var string
	 */
	public $titel;
	/**
	 * Textuele inhoud met eventueel bbcode
	 * @var string
	 */
	public $tekst;
	public $categorie;
	public $prive;
	public $zichtbaarheid;
	public $prioriteit = 255;
	/**
	 * Lidnummer van auteur
	 * @var string
	 */
	public $uid;
	public $doelgroep;
	public $verborgen = false;
	public $verwijderd = false;
	/**
	 * Url naar afbeelding van 200x200
	 * @var string
	 */
	public $plaatje;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'datum' => array(T::DateTime),
		'vervaltijd' => array(T::DateTime, true),
		'titel' => array(T::String),
		'tekst' => array(T::Text),
		'categorie' => array(T::Integer),
		'zichtbaarheid' => array(T::String),
		'prioriteit' => array(T::Integer),
		'uid' => array(T::UID),
		'doelgroep' => array(T::String),
		'verborgen' => array(T::Boolean),
		'verwijderd' => array(T::Boolean),
		'plaatje' => array(T::String, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'mededelingen';

	public function getTitelVoorZijbalk() {
		$resultaat = $this->titel;
		if (strlen($resultaat) > 21) { //TODO: constante van maken?
			$resultaat = trim(substr($resultaat, 0, 18)) . '…'; //TODO: constanten van maken?
		}
		return $resultaat;
	}

	public function getTekstVoorZijbalk() {
		$tijdelijk = preg_replace('/(\[(|\/)\w+\])/', '|', $this->tekst);
		$resultaat = substr(str_replace(array("\n", "\r", ' '), ' ', $tijdelijk), 0, 40); //TODO: constanten van maken?
		return $resultaat;
	}

	public function getCategorie() {
		return MededelingCategorieenModel::get($this->categorie);
	}

	public function isModerator() {
		return MededelingenModel::isModerator();
	}

	public function getProfiel() {
		return ProfielModel::get($this->uid);
	}

	//	// function magBewerken()
//	// post: geeft true terug als het huidige lid deze Mededeling mag bewerken of verwijderen. Anders, false.
	public function magBewerken() {
		// het huidige lid mag dit bericht alleen bewerken als hij moderator is of als dit zijn eigen bericht
		// is (en hij dus het toevoeg-recht heeft).
		return MededelingenModel::isModerator() OR (MededelingenModel::magToevoegen() AND $this->uid == LoginModel::getUid());
	}

}
