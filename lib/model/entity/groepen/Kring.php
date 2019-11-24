<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\groepen\leden\KringLedenModel;
use CsrDelft\Orm\Entity\T;

/**
 * Kring.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Kring extends AbstractGroep {

	const LEDEN = KringLedenModel::class;

	/**
	 * Verticaleletter
	 * @var string
	 */
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 */
	public $kring_nummer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'verticale' => [T::Char],
		'kring_nummer' => [T::Integer]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'kringen';

	public function getUrl() {
		return '/groepen/kringen/' . $this->verticale . '.' . $this->kring_nummer;
	}

}
