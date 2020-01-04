<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\groepen\leden\BestuursLedenModel;
use CsrDelft\Orm\Entity\T;

/**
 * Bestuur.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Bestuur extends AbstractGroep {

	const LEDEN = BestuursLedenModel::class;

	/**
	 * Bestuurstekst
	 * @var string
	 */
	public $bijbeltekst;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'bijbeltekst' => [T::Text]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'besturen';

	public function getUrl() {
		return '/groepen/besturen/' . $this->id;
	}

}
