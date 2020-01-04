<?php

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\AbstractGroepenModel;
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
 */
class GroepenBeheerTable extends DataTable {

	private $naam;
	private $pagina;

	public function __construct(AbstractGroepenModel $model) {
		parent::__construct($model::ORM, $model->getUrl() . '/beheren', null);

		$this->selectEnabled = false;

		$this->naam = $model->getNaam();
		$this->titel = 'Beheer ' . $this->naam;

		$cmsPaginaRepository = ContainerFacade::getContainer()->get(CmsPaginaRepository::class);
		$this->pagina = $cmsPaginaRepository->find($this->naam);
		if (!$this->pagina) {
			$this->pagina = $cmsPaginaRepository->find('');
		}

		$this->hideColumn('id', false);
		$this->hideColumn('samenvatting');
		$this->hideColumn('omschrijving');
		$this->hideColumn('maker_uid');
		$this->hideColumn('keuzelijst');
		$this->hideColumn('rechten_aanmelden');
		$this->hideColumn('status_historie');
		$this->searchColumn('naam');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$this->deleteColumn('keuzelijst2');

		$this->setOrder(['id' => 'desc']);

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/voorbeeld', 'Voorbeeldweergave van de ketzer', 'show'));

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $model->getUrl() . '/:id/nieuw', 'Nieuw', 'Nieuwe toevoegen', 'toevoegen'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/aanmaken', 'Nieuwe toevoegen die de huidige opvolgt', 'toevoegen'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/wijzigen', 'Wijzig eigenschappen', 'bewerken'));

		if (property_exists($model::ORM, 'aanmelden_vanaf')) {
			$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/sluiten', 'Inschrijvingen nu sluiten', 'lock'));
		}

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/opvolging', 'Familienaam en groepstatus instellen', 'timeline_marker'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/converteren', 'Converteer naar ander soort groep', 'lightning'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/verwijderen', 'Definitief verwijderen (groep moet hier voor leeg zijn)', 'delete', 'confirm'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . '/:id/logboek', 'Logboek bekijken', 'log', '', 'get'));
	}

	public function getBreadcrumbs() {
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>'
			. '<li class="breadcrumb-item"><a href="' . $this->model->getUrl() . '">' . ucfirst($this->naam) . '</a></li>'
			. '<li class="breadcrumb-item active">Beheren</li></ul>';
	}

	public function view() {
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		parent::view();
	}

}
