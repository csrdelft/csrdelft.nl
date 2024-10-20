<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\PopupDataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\knoppen\UrlDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class BeheerMaaltijdenTable extends DataTable
{
	/**
	 * BeheerMaaltijdenView constructor.
	 *
	 * @param $repetities MaaltijdRepetitie[]
	 */
	public function __construct($repetities)
	{
		parent::__construct(Maaltijd::class, '/maaltijden/beheer');

		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');
		$this->hideColumn('mlt_repetitie_id');

		$this->addcolumn('maaltijd_id', 'product_id');
		$this->addColumn('repetitie_naam', 'titel');
		$this->addColumn('aanmeld_filter', null, null, CellRender::AanmeldFilter());
		$this->addColumn('gesloten', null, null, CellRender::Check());
		$this->addColumn('verwerkt', null, null, CellRender::Check());
		$this->addColumn(
			'aanmeldingen',
			'aanmeld_limiet',
			null,
			CellRender::Aanmeldingen()
		);
		$this->addColumn(
			'prijs',
			null,
			null,
			CellRender::Bedrag(),
			null,
			CellType::FormattedNumber()
		);

		$this->setOrder(['datum' => 'asc']);

		$this->searchColumn('titel');
		$this->searchColumn('prijs');
		$this->searchColumn('aanmeld_filter');

		$weergave = new CollectionDataTableKnop(
			Multiplicity::None(),
			'Weergave',
			'Weergave van tabel',
			''
		);
		$weergave->addKnop(
			new SourceChangeDataTableKnop(
				$this->dataUrl,
				'Toekomst',
				'Toekomst weergeven',
				'arrow-rotate-right'
			)
		);
		$weergave->addKnop(
			new SourceChangeDataTableKnop(
				$this->dataUrl . '?filter=alles',
				'Alles',
				'Alles weergeven',
				'clock'
			)
		);
		$this->addKnop($weergave);

		$nieuw = new CollectionDataTableKnop(
			Multiplicity::None(),
			'Nieuw',
			'Nieuwe maaltijd aanmaken',
			'toevoegen'
		);

		foreach ($repetities as $repetitie) {
			$nieuw->addKnop(
				new DataTableKnop(
					Multiplicity::None(),
					$this->dataUrl . '/nieuw?mrid=' . $repetitie->mlt_repetitie_id,
					$repetitie->standaard_titel,
					"Nieuwe $repetitie->standaard_titel aanmaken"
				)
			);
		}

		$nieuw->addKnop(
			new DataTableKnop(
				Multiplicity::None(),
				$this->dataUrl . '/nieuw',
				'Anders',
				'Maaltijd zonder repetitie aanmaken',
				'file-pen'
			)
		);
		$this->addKnop($nieuw);

		$this->addKnop(
			new DataTableKnop(
				Multiplicity::One(),
				$this->dataUrl . '/toggle/:maaltijd_id',
				'Open/Sluit',
				'Maaltijd openen of sluiten'
			)
		);

		$aanmeldingen = new CollectionDataTableKnop(
			Multiplicity::One(),
			'Aanmeldingen',
			'Aanmeldingen bewerken',
			'user-pen'
		);
		$aanmeldingen->addKnop(
			new DataTableKnop(
				Multiplicity::None(),
				$this->dataUrl . '/aanmelden',
				'Toevoegen',
				'Aanmelding toevoegen',
				'user-plus'
			)
		);
		$aanmeldingen->addKnop(
			new DataTableKnop(
				Multiplicity::None(),
				$this->dataUrl . '/afmelden',
				'Verwijderen',
				'Aanmelding verwijderen',
				'user-minus'
			)
		);

		$this->addKnop($aanmeldingen);

		$this->addKnop(
			new DataTableKnop(
				Multiplicity::One(),
				$this->dataUrl . '/bewerk',
				'Bewerken',
				'Maaltijd bewerken',
				'bewerken'
			)
		);
		$this->addKnop(
			new UrlDataTableKnop(
				Multiplicity::One(),
				'/corvee/beheer/maaltijd/:maaltijd_id',
				'Corvee bewerken',
				'Gekoppelde corveetaken bewerken',
				'folder-tree'
			)
		);
		$this->addKnop(
			new ConfirmDataTableKnop(
				Multiplicity::One(),
				$this->dataUrl . '/verwijder',
				'Verwijderen',
				'Maaltijd verwijderen',
				'verwijderen'
			)
		);

		$this->addKnop(
			new PopupDataTableKnop(
				Multiplicity::One(),
				'/maaltijden/lijst/:maaltijd_id',
				'Maaltijdlijst',
				'Maaltijdlijst bekijken',
				'tabel'
			)
		);
	}

	public function getBreadcrumbs()
	{
		return 'Maaltijden / Beheer';
	}
}
