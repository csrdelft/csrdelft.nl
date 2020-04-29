<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\groepen\KetzerKeuzesModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * KetzerOptie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een keuzemogelijkheid van een ketzer kan gekozen worden door een groeplid.
 *
 */
class KetzerOptie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $optie_id;
	/**
	 * Optie van deze KetzerSelector
	 * @var int
	 */
	public $select_id;
	/**
	 * Keuzewaarde
	 * @var string
	 */
	public $waarde;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'optie_id' => [T::Integer, false, 'auto_increment'],
		'select_id' => [T::Integer],
		'waarde' => [T::String]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_opties';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = ['optie_id'];

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return KetzerKeuze[]
	 */
	public function getKeuzes() {
		return ContainerFacade::getContainer()->get(KetzerKeuzesModel::class)->getKeuzesVoorOptie($this);
	}

}
