<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\groepen\leden\OnderverenigingsLedenModel;
use CsrDelft\Orm\Entity\T;


/**
 * Ondervereniging.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Ondervereniging extends AbstractGroep {

	const leden = OnderverenigingsLedenModel::class;

	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 */
	public $soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, OnderverenigingStatus::class),
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'onderverenigingen';

	public function getUrl() {
		return '/groepen/onderverenigingen/' . $this->id . '/';
	}

}
