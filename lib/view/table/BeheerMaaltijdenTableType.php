<?php

namespace CsrDelft\view\table;

use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\PopupDataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\knoppen\UrlDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BeheerMaaltijdenTableType extends AbstractDataTableType {
	const OPTION_REPETITIES = 'repetities';
	/** @var EntityManagerInterface */
	private $entityManager;
	/** @var UrlGeneratorInterface */
	private $urlGenerator;

	/**
	 * @param EntityManagerInterface $entityManager
	 * @param UrlGeneratorInterface $urlGenerator
	 */
	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator) {
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void {
		$builder->setDataUrl($this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_post_beheer'));
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
		$weergave->addKnop(new SourceChangeDataTableKnop($this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_post_beheer'), 'Toekomst', 'Toekomst weergeven', 'time_go'));
		$weergave->addKnop(new SourceChangeDataTableKnop($this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_post_beheer', ['filter' => 'alles']), 'Alles', 'Alles weergeven', 'time'));
		$builder->addKnop($weergave);

		$nieuw = new CollectionDataTableKnop(Multiplicity::None(),'Nieuw', 'Nieuwe maaltijd aanmaken', 'add');

		foreach ($options[self::OPTION_REPETITIES] as $repetitie) {
			$nieuw->addKnop(new DataTableKnop(Multiplicity::None(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_nieuw', ['mlt_repetitie_id' => $repetitie->mlt_repetitie_id]), $repetitie->standaard_titel, "Nieuwe $repetitie->standaard_titel aanmaken"));
		}

		$nieuw->addKnop(new DataTableKnop(Multiplicity::None(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_nieuw'), 'Anders', 'Maaltijd zonder repetitie aanmaken', 'calendar_edit'));
		$builder->addKnop($nieuw);

		$builder->addKnop(new DataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_toggle', ['maaltijd_id' => ':maaltijd_id']), 'Open/Sluit', 'Maaltijd openen of sluiten'));

		$aanmeldingen = new CollectionDataTableKnop(Multiplicity::One(), 'Aanmeldingen', 'Aanmeldingen bewerken', 'user');
		$aanmeldingen->addKnop(new DataTableKnop(Multiplicity::None(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_aanmelden'), 'Toevoegen', 'Aanmelding toevoegen', 'user_add'));
		$aanmeldingen->addKnop(new DataTableKnop(Multiplicity::None(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_afmelden'), 'Verwijderen', 'Aanmelding verwijderen', 'user_delete'));

		$builder->addKnop($aanmeldingen);

		$builder->addKnop(new DataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_bewerk', ['maaltijd_id' => ':maaltijd_id']), 'Bewerken', 'Maaltijd bewerken', 'pencil'));
		$builder->addKnop(new UrlDataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_maalcie_beheertaken_maaltijd', ['maaltijd_id' => ':maaltijd_id']), 'Corvee bewerken', 'Gekoppelde corveetaken bewerken', 'chart_organisation'));
		$builder->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_maalcie_beheermaaltijden_verwijder'), 'Verwijderen', 'Maaltijd verwijderen', 'cross'));

		$builder->addKnop(new PopupDataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_maalcie_mijnmaaltijden_lijst', ['maaltijd_id' => ':maaltijd_id']), 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal'));
	}
}
