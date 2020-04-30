<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\groepen\KetzerOptiesModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;


/**
 * KetzerSelector.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een soort selector (AND/XOR) heeft keuzemogelijkheden.
 *
 */
class KetzerSelector extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $select_id;
	/**
	 * Selector van deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Single selection (KeuzeRondje) / Multiple selection (Vinkje)
	 * @var KetzerSelectorSoort
	 */
	public $keuze_soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'select_id' => [T::Integer, false, 'auto_increment'],
		'ketzer_id' => [T::Integer],
		'keuze_soort' => [T::Enumeration, false, KetzerSelectorSoort::class]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_selectors';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = ['select_id'];

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return KetzerOptie[]
	 */
	public function getOpties() {
		return ContainerFacade::getContainer()->get(KetzerOptiesModel::class)->getOptiesVoorSelect($this);
	}

}
