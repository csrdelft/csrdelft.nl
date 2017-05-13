<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Class CiviPrijs
 *
 * Prijs van een @see CiviProduct van en tot zorgen ervoor dat altijd terug te vinden is wat de prijs van een product
 * was op een bepaald moment.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviPrijs extends PersistentEntity {
	public $van;
	public $tot;
	public $product_id;
	public $prijs;

	protected static $table_name = 'CiviPrijs';
	protected static $persistent_attributes = array(
		'van' => array(T::Timestamp),
		'tot' => array(T::Timestamp, true),
		'product_id' => array(T::Integer),
		'prijs' => array(T::Integer)
	);
	protected static $primary_key = array('van', 'product_id');
}
