<?php
namespace CsrDelft\view\groepen\leden;
use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

/**
 * GroepLedenTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GroepLedenTable extends DataTable {

	public function __construct(AbstractGroepLedenModel $model, AbstractGroep $groep) {
		parent::__construct($model::ORM, $groep->getUrl() . 'leden', 'Leden van ' . $groep->naam, 'status');
		$this->hideColumn('uid', false);
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Lidnaam');
		$this->setColumnTitle('door_uid', 'Aangemeld door');

		if ($groep->mag(AccessAction::Beheren)) {

			$create = new DataTableKnop('== 0', $this->dataTableId, $groep->getUrl() . 'aanmelden', 'post popup', 'Aanmelden', 'Lid toevoegen', 'user_add');
			$this->addKnop($create);

			$update = new DataTableKnop('== 1', $this->dataTableId, $groep->getUrl() . 'bewerken', 'post popup', 'Bewerken', 'Lidmaatschap bewerken', 'user_edit');
			$this->addKnop($update);

			$delete = new DataTableKnop('>= 1', $this->dataTableId, $groep->getUrl() . 'afmelden', 'post confirm', 'Afmelden', 'Leden verwijderen', 'user_delete');
			$this->addKnop($delete);
		}
	}

}
