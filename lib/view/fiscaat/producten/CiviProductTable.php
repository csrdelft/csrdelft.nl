<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\fiscaat\enum\CiviSaldoCommissieEnum;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;
use CsrDelft\view\Icon;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 04/04/2017
 */
class CiviProductTable extends DataTable
{
	public function __construct()
	{
		parent::__construct(
			CiviProduct::class,
			'/fiscaat/producten',
			'Productenbeheer'
		);

		$this->selectEnabled = false;

		$this->addColumn('id');
		$this->addColumn(
			'prijs',
			null,
			null,
			CellRender::Bedrag(),
			null,
			CellType::FormattedNumber()
		);
		$this->addColumn('beheer', 'prijs', null, CellRender::Check());
		$this->addColumn('categorie', 'prijs');
		$this->hideColumn('prioriteit');
		$this->deleteColumn('categorie_id');

		$this->searchColumn('beschrijving');
		$this->searchColumn('categorie');

		$sources = new CollectionDataTableKnop(
			Multiplicity::Any(),
			'Commissie',
			'Selecteer commissie'
		);
		$sources->addKnop(
			new SourceChangeDataTableKnop(
				'/fiscaat/producten',
				'Alle',
				'Laat alle producten zien'
			)
		);
		foreach (CiviSaldoCommissieEnum::all() as $val) {
			$sources->addKnop(
				new SourceChangeDataTableKnop(
					'/fiscaat/producten/' . $val->getValue(),
					$val->getDescription(),
					'Laat alleen ' . $val->getDescription() . ' producten zien'
				)
			);
		}

		$this->addKnop($sources);
		$this->addKnop(
			new DataTableKnop(
				Multiplicity::Zero(),
				$this->dataUrl . '/bewerken',
				'Nieuw',
				'Nieuw product toevoegen',
				'toevoegen'
			)
		);
		$this->addRowKnop(
			new DataTableRowKnop(
				$this->dataUrl . '/bewerken',
				'Product bewerken',
				'bewerken'
			)
		);
		$this->addRowKnop(
			new DataTableRowKnop(
				$this->dataUrl . '/verwijderen',
				'Product verwijderen',
				'verwijderen',
				'confirm'
			)
		);
	}

	public function getBreadcrumbs()
	{
		return '<a href="/" title="Startpagina">' .
			Icon::getTag('home', null, 'Voorpagina', 'module-icon') .
			'</a> » <a href="/fiscaat">' .
			Icon::getTag('money-bill', null, 'Fiscaat', 'module-icon') .
			'</a> » <span class="active">Producten</span>';
	}
}
