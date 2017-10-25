<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Class CiviProduct
 *
 * Bevat een @see CiviPrijs
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviProduct extends PersistentEntity {
	public $id;
	public $status;
	public $beschrijving;
	public $prioriteit;
	public $beheer;
	public $categorie_id;

	public $prijs;

	public function getBeschrijvingFormatted() {
		return sprintf("%s (€%.2f)", $this->beschrijving, $this->prijs / 100);
	}

	protected static $table_name = 'CiviProduct';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'status' => array(T::Integer),
		'beschrijving' => array(T::Text),
		'prioriteit' => array(T::Integer),
		'beheer' => array(T::Boolean),
		'categorie_id' => array(T::Integer)
	);
	protected static $primary_key = array('id');
}
