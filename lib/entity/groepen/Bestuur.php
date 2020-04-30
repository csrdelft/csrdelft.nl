<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\repository\groepen\leden\BestuursLedenModel;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * Bestuur.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\BesturenModel")
 * @ORM\Table("besturen")
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
