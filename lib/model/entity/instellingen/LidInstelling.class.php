<?php

namespace CsrDelft\model\entity\instellingen;

use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Een LidInstelling beschrijft een Instelling per Lid.
 */
class LidInstelling extends PersistentEntity {

	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 */
	public $uid;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $module;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 */
	public $waarde;
	/**
	 * Database table columns
	 * @var array
	 */
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid' => array(T::UID),
		'module' => array(T::StringKey),
		'instelling_id' => array(T::StringKey),
		'waarde' => array(T::Text)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('module', 'instelling_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lidinstellingen';

	public function __construct($cast = false, array $attributes_retrieved = null) {
		parent::__construct($cast, $attributes_retrieved);

		if ($cast) {
			$this->castWaarde();
		}
	}

	public function onAttributesRetrieved(array $attributes) {
		parent::onAttributesRetrieved($attributes);

		$this->castWaarde();
	}

	protected function castWaarde() {
		if (LidInstellingenModel::instance()->getType($this->module, $this->instelling_id) === T::Integer) {
			$this->waarde = (int)$this->waarde;
		}
	}

}
