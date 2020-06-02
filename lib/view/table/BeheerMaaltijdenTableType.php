<?php

namespace CsrDelft\view\table;

use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
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
use Doctrine\ORM\EntityManagerInterface;

class BeheerMaaltijdenTableType extends AbstractDataTableType {
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * BeheerMaaltijdenView constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void {
		$builder->setDataUrl('/maaltijden/beheer');
		$builder->loadFromMetadata($this->entityManager->getClassMetadata(Maaltijd::class));

		$builder->hideColumn('verwijderd');
		$builder->hideColumn('aanmeld_limiet');
		$builder->hideColumn('omschrijving');
		$builder->hideColumn('mlt_repetitie_id');

		$builder->addColumn('datum', null, null, CellRender::Date());
		$builder->addColumn('tijd', null, null, CellRender::Time());

		$builder->addcolumn('maaltijd_id', 'product_id');
		$builder->addColumn('repetitie_naam', 'titel');
		$builder->addColumn('aanmeld_filter', null, null, CellRender::AanmeldFilter());
		$builder->addColumn('gesloten', null, null, CellRender::Check());
		$builder->addColumn('verwerkt', null, null, CellRender::Check());
		$builder->addColumn('aantal_aanmeldingen', 'aanmeld_limiet', null, CellRender::Aanmeldingen());
		$builder->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());

		$builder->setOrder(array('datum' => 'asc'));

		$builder->searchColumn('titel');
		$builder->searchColumn('prijs');
		$builder->searchColumn('aanmeld_filter');

		$weergave = new CollectionDataTableKnop(Multiplicity::None(), 'Weergave', 'Weergave van tabel', '');
		$weergave->addKnop(new SourceChangeDataTableKnop('/maaltijden/beheer', 'Toekomst', 'Toekomst weergeven', 'time_go'));
		$weergave->addKnop(new SourceChangeDataTableKnop('/maaltijden/beheer?filter=alles', 'Alles', 'Alles weergeven', 'time'));
		$builder->addKnop($weergave);

		$nieuw = new CollectionDataTableKnop(Multiplicity::None(),'Nieuw', 'Nieuwe maaltijd aanmaken', 'add');

		foreach ($options['repetities'] as $repetitie) {
			$nieuw->addKnop(new DataTableKnop(Multiplicity::None(), '/maaltijden/beheer/nieuw?mrid=' . $repetitie->mlt_repetitie_id, $repetitie->standaard_titel, "Nieuwe $repetitie->standaard_titel aanmaken"));
		}

		$nieuw->addKnop(new DataTableKnop(Multiplicity::None(), '/maaltijden/beheer/nieuw', 'Anders', 'Maaltijd zonder repetitie aanmaken', 'calendar_edit'));
		$builder->addKnop($nieuw);

		$builder->addKnop(new DataTableKnop(Multiplicity::One(), '/maaltijden/beheer/toggle/:maaltijd_id', 'Open/Sluit', 'Maaltijd openen of sluiten'));

		$aanmeldingen = new CollectionDataTableKnop(Multiplicity::One(), 'Aanmeldingen', 'Aanmeldingen bewerken', 'user');
		$aanmeldingen->addKnop(new DataTableKnop(Multiplicity::None(), '/maaltijden/beheer/aanmelden', 'Toevoegen', 'Aanmelding toevoegen', 'user_add'));
		$aanmeldingen->addKnop(new DataTableKnop(Multiplicity::None(), '/maaltijden/beheer/afmelden', 'Verwijderen', 'Aanmelding verwijderen', 'user_delete'));

		$builder->addKnop($aanmeldingen);

		$builder->addKnop(new DataTableKnop(Multiplicity::One(), '/maaltijden/beheer/bewerk', 'Bewerken', 'Maaltijd bewerken', 'pencil'));
		$builder->addKnop(new UrlDataTableKnop(Multiplicity::One(), '/corvee/beheer/maaltijd/:maaltijd_id', 'Corvee bewerken', 'Gekoppelde corveetaken bewerken', 'chart_organisation'));
		$builder->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/maaltijden/beheer/verwijder', 'Verwijderen', 'Maaltijd verwijderen', 'cross'));

		$builder->addKnop(new PopupDataTableKnop(Multiplicity::One(), '/maaltijden/lijst/:maaltijd_id', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal'));
	}

	public function getBreadcrumbs() {
		return "Maaltijden / Beheer";
	}
}
