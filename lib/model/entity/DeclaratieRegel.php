<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 *
 * @property-read $btw_bedrag
 */
class DeclaratieRegel extends PersistentEntity {
	/** @var integer */
	public $id;
	/** @var string */
	public $datum;
	/** @var string */
	public $omschrijving;
	/** @var integer */
	public $bedrag;
	/** @var string */
	public $btw_tarief;

	public function getBtwBedrag() {
		$percentage = BtwTarieven::getPercentage($this->btw_tarief);

		return round($this->bedrag / (100 + $percentage) * $percentage);
	}

	protected static $table_name = 'declaratie_regel';
	protected static $primary_key = ['id'];

	protected static $computed_attributes = [
		'btw_bedrag' => [T::Integer]
	];

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'datum' => [T::Date],
		'omschrijving' => [T::String],
		'bedrag' => [T::String],
		'btw_tarief' => [T::Enumeration, false, BtwTarieven::class],
	];
}
