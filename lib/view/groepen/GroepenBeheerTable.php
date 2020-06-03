<?php

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;
use Doctrine\ORM\EntityManagerInterface;


/**
 * GroepenBeheerTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property-read Groep $model
 */
class GroepenBeheerTable extends AbstractDataTableType {

	private $naam;
	private $pagina;
	/** @var CmsPaginaRepository */
	private $cmsPaginaRepository;
	/** @var EntityManagerInterface */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager, CmsPaginaRepository $cmsPaginaRepository) {
		$this->cmsPaginaRepository = $cmsPaginaRepository;
		$this->entityManager = $entityManager;
	}

	public function getBreadcrumbs() {
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>'
			. '<li class="breadcrumb-item"><a href="' . $this->model->getUrl() . '">' . ucfirst($this->naam) . '</a></li>'
			. '<li class="breadcrumb-item active">Beheren</li></ul>';
	}

	public function __toString()
	{
		$view = new CmsPaginaView($this->pagina);
		return $view->__toString() . parent::__toString();
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void {
		/** @var AbstractGroepenRepository $repository */
		$repository = $options['repository'];
		$builder->selectEnabled = false;

		$this->naam = $repository->getNaam();
		$builder->setTitel('Beheer' . $this->naam);
		$builder->setDataUrl($repository->getUrl() . '/beheren');

		$this->pagina = $this->cmsPaginaRepository->find($this->naam);
		if (!$this->pagina) {
			$this->pagina = $this->cmsPaginaRepository->find('');
		}

		$builder->loadFromMetadata($this->entityManager->getClassMetadata($repository->entityClass));

		$builder->hideColumn('id', false);
		$builder->deleteColumn('samenvatting');
		$builder->deleteColumn('omschrijving');
		$builder->deleteColumn('maker');
		$builder->deleteColumn('keuzelijst');
		$builder->deleteColumn('rechten_aanmelden');
		$builder->deleteColumn('status_historie');
		$builder->searchColumn('naam');
		$builder->searchColumn('status');
		$builder->searchColumn('soort');

		$builder->hideColumn('versie');
		$builder->hideColumn('afmelden_tot');
		$builder->hideColumn('bewerken_tot');
		$builder->hideColumn('eind_moment');

		$builder->deleteColumn('keuzelijst2');

		$builder->setOrder(['id' => 'desc']);

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/voorbeeld', 'Voorbeeldweergave van de ketzer', 'show'));

		$builder->addKnop(new DataTableKnop(Multiplicity::Zero(), $repository->getUrl() . '/nieuw', 'Nieuw', 'Nieuwe toevoegen', 'toevoegen'));

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/aanmaken', 'Nieuwe toevoegen die de huidige opvolgt', 'toevoegen'));

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/wijzigen', 'Wijzig eigenschappen', 'bewerken'));

		if (property_exists($repository->entityClass, 'aanmelden_vanaf')) {
			$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/sluiten', 'Inschrijvingen nu sluiten', 'lock'));
		}

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/opvolging', 'Familienaam en groepstatus instellen', 'timeline_marker'));

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/converteren', 'Converteer naar ander soort groep', 'lightning'));

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/verwijderen', 'Definitief verwijderen (groep moet hier voor leeg zijn)', 'delete', 'confirm'));

		$builder->addRowKnop(new DataTableRowKnop($repository->getUrl() . '/:id/logboek', 'Logboek bekijken', 'log', '', 'get'));
	}
}
