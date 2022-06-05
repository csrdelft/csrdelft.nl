<?php

namespace CsrDelft\view\fiscaat\bestellingen;

use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\view\datatable\DataTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/02/2018
 */
class CiviBestellingInhoudTable extends DataTable
{
	/**
	 * @param CiviBestelling $civiBestelling
	 */
	public function __construct($civiBestelling)
	{
		parent::__construct(
			CiviBestellingInhoud::class,
			'/fiscaat/bestellingen/inhoud/' . $civiBestelling->id
		);

		$this->defaultLength = -1;
		$this->settings['buttons'] = [];
		$this->settings['dom'] = 'Brtpli';
		$this->settings['select'] = false;

		$this->addColumn('product');
		$this->addColumn('aantal');
		$this->addColumn('stukprijs');
		$this->addColumn('totaalprijs');
		$this->hideColumn('bestelling_id');
		$this->hideColumn('product_id');

		$this->setOrder(['product_id' => 'asc']);
	}

	/**
	 * Print niet de JS, dat wordt door formulier gedaan.
	 */
	public function __toString()
	{
		return $this->getHtml();
	}
}
