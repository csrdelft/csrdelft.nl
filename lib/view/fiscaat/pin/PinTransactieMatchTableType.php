<?php


namespace CsrDelft\view\fiscaat\pin;


use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PinTransactieMatchTableType extends AbstractDataTableType {
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator) {
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void {
		$builder->setDataUrl($this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_lijst', ['filter' => 'metFout']));
		$builder->setTitel('Overzicht van pintransacties matches');

		$builder->loadFromMetadata($this->entityManager->getClassMetadata(PinTransactieMatch::class));

		$weergave = new CollectionDataTableKnop(Multiplicity::None(), 'Weergave', 'Weergave van de tabel', 'cart');
		$weergave->addKnop(new SourceChangeDataTableKnop($this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_lijst', ['filter' => 'metFout']), 'Met fouten', 'Fouten weergeven', 'cart_error'));
		$weergave->addKnop(new SourceChangeDataTableKnop($this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_lijst', ['filter' => 'alles']), 'Alles', 'Alles weergeven', 'cart'));
		$builder->addKnop($weergave);

		$builder->addKnop(new DataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_verwerk'),  'Verwerk', 'Dit probleem verwerken', 'cart_edit'));
		$builder->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_ontkoppel'), 'Ontkoppel', 'Ontkoppel bestelling en transactie', 'arrow_divide'));
		$builder->addKnop(new DataTableKnop(Multiplicity::Two(), $this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_koppel'), 'Koppel', 'Koppel een bestelling en transactie', 'arrow_join'));
		$builder->addKnop(new DataTableKnop(Multiplicity::One(), $this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_info'), 'Info', 'Bekijk informatie over de gekoppelde bestelling', 'magnifier'));
		$builder->addKnop(new DataTableKnop(Multiplicity::Any(), $this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_verwijder_transactie'), 'Verwijder', 'Verwijder matches', 'delete'));
		$builder->addKnop(new DataTableKnop(Multiplicity::None(), $this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_heroverweeg'), 'Heroverweeg', 'Controleer op veranderingen in andere systemen', 'cart_go'));

		$builder->addColumn('moment');
		$builder->addColumn('transactie');
		$builder->addColumn('bestelling');

		$builder->hideColumn('transactie_id');
		$builder->hideColumn('bestelling_id');

		$builder->setOrder(['moment' => 'desc']);

		$builder->searchColumn('status');
		$builder->searchColumn('moment');
		$builder->searchColumn('transactie');
		$builder->searchColumn('bestelling');
	}
}
