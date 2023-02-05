<?php

namespace CsrDelft\view\table;

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

class PinTransactieMatchTableType extends AbstractDataTableType
{
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	public function createDataTable(
		DataTableBuilder $builder,
		array $options
	): void {
		$builder->setDataUrl(
			$this->urlGenerator->generate(
				'csrdelft_fiscaat_pintransactie_overzicht',
				['filter' => 'metFout']
			)
		);
		$builder->setTitel('Overzicht van pintransacties matches');

		$builder->loadFromClass(PinTransactieMatch::class);

		$weergave = new CollectionDataTableKnop(
			Multiplicity::None(),
			'Weergave',
			'Weergave van de tabel',
			'cart-shopping'
		);
		$weergave->addKnop(
			new SourceChangeDataTableKnop(
				$this->urlGenerator->generate(
					'csrdelft_fiscaat_pintransactie_overzicht',
					['filter' => 'metFout']
				),
				'Met fouten',
				'Fouten weergeven',
				'xmark'
			)
		);
		$weergave->addKnop(
			new SourceChangeDataTableKnop(
				$this->urlGenerator->generate(
					'csrdelft_fiscaat_pintransactie_overzicht',
					['filter' => 'alles']
				),
				'Alles',
				'Alles weergeven',
				'tonen'
			)
		);
		$builder->addKnop($weergave);

		$builder->addKnop(
			new DataTableKnop(
				Multiplicity::One(),
				$this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_verwerk'),
				'Verwerk',
				'Dit probleem verwerken',
				'cart-arrow-down'
			)
		);
		$builder->addKnop(
			new ConfirmDataTableKnop(
				Multiplicity::One(),
				$this->urlGenerator->generate(
					'csrdelft_fiscaat_pintransactie_ontkoppel'
				),
				'Ontkoppel',
				'Ontkoppel bestelling en transactie',
				'code-branch'
			)
		);
		$builder->addKnop(
			new DataTableKnop(
				Multiplicity::Two(),
				$this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_koppel'),
				'Koppel',
				'Koppel een bestelling en transactie',
				'code-merge'
			)
		);
		$builder->addKnop(
			new DataTableKnop(
				Multiplicity::One(),
				$this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_info'),
				'Info',
				'Bekijk informatie over de gekoppelde bestelling',
				'search'
			)
		);
		$builder->addKnop(
			new DataTableKnop(
				Multiplicity::Any(),
				$this->urlGenerator->generate('csrdelft_fiscaat_pintransactie_negeer'),
				'Negeer',
				'Negeer match(es)',
				'verwijderen'
			)
		);
		$builder->addKnop(
			new DataTableKnop(
				Multiplicity::None(),
				$this->urlGenerator->generate(
					'csrdelft_fiscaat_pintransactie_heroverweeg'
				),
				'Heroverweeg',
				'Controleer op veranderingen in andere systemen',
				'arrow-right'
			)
		);

		$builder->addColumn('moment');
		$builder->addColumn('transactie_tijd');
		$builder->addColumn('bestelling_tijd');
		$builder->addColumn('tijdsverschil');
		$builder->addColumn('transactie');
		$builder->addColumn('bestelling');

		$builder->hideColumn('transactie_id');
		$builder->hideColumn('bestelling_id');

		$builder->setOrder(['moment' => 'desc']);

		$builder->searchColumn('status');
		$builder->searchColumn('bestelling_tijd');
		$builder->searchColumn('transactie_tijd');
		$builder->searchColumn('transactie');
		$builder->searchColumn('bestelling');
	}
}
