<?php

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * GroepenBeheerTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property-read Groep $model
 */
class GroepenBeheerTable extends DataTable
{
	private $naam;
	private $pagina;

	public function __construct(GroepRepository $repository)
	{
		parent::__construct(
			$repository->entityClass,
			$repository->getUrl() . '/beheren',
			null
		);

		$this->selectEnabled = false;

		$this->naam = $repository->getNaam();
		$this->titel = 'Beheer ' . $this->naam;

		$cmsPaginaRepository = ContainerFacade::getContainer()->get(
			CmsPaginaRepository::class
		);
		$this->pagina = $cmsPaginaRepository->find($this->naam);
		if (!$this->pagina) {
			$this->pagina = $cmsPaginaRepository->find('');
		}

		$this->hideColumn('id', false);
		$this->deleteColumn('samenvatting');
		$this->deleteColumn('omschrijving');
		$this->deleteColumn('maker');
		$this->deleteColumn('keuzelijst');
		$this->deleteColumn('rechten_aanmelden');
		$this->deleteColumn('status_historie');
		$this->searchColumn('naam');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$this->hideColumn('versie');
		$this->hideColumn('afmelden_tot');
		$this->hideColumn('bewerken_tot');
		$this->hideColumn('eind_moment');

		$this->deleteColumn('keuzelijst2');

		$this->setOrder(['id' => 'desc']);

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/voorbeeld',
				'Voorbeeldweergave van de ketzer',
				'show'
			)
		);

		$this->addKnop(
			new DataTableKnop(
				Multiplicity::Zero(),
				$repository->getUrl() . '/nieuw',
				'Nieuw',
				'Nieuwe toevoegen',
				'toevoegen'
			)
		);

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/aanmaken',
				'Nieuwe toevoegen die de huidige opvolgt',
				'toevoegen'
			)
		);

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/wijzigen',
				'Wijzig eigenschappen',
				'bewerken'
			)
		);

		if (property_exists($repository->entityClass, 'aanmelden_vanaf')) {
			$this->addRowKnop(
				new DataTableRowKnop(
					$repository->getUrl() . '/:id/sluiten',
					'Inschrijvingen nu sluiten',
					'lock'
				)
			);
		}

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/opvolging',
				'Familienaam en groepstatus instellen',
				'timeline_marker'
			)
		);

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/converteren',
				'Converteer naar ander soort groep',
				'lightning'
			)
		);

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/verwijderen',
				'Definitief verwijderen (groep moet hier voor leeg zijn)',
				'delete',
				'confirm'
			)
		);

		$this->addRowKnop(
			new DataTableRowKnop(
				$repository->getUrl() . '/:id/logboek',
				'Logboek bekijken',
				'log',
				'',
				'get'
			)
		);
	}

	public function getBreadcrumbs()
	{
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>' .
			'<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>' .
			'<li class="breadcrumb-item"><a href="' .
			$this->model->getUrl() .
			'">' .
			ucfirst($this->naam) .
			'</a></li>' .
			'<li class="breadcrumb-item active">Beheren</li></ul>';
	}

	public function __toString()
	{
		$view = new CmsPaginaView($this->pagina);
		return $view->__toString() . parent::__toString();
	}
}
