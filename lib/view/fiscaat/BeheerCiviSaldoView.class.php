<?php
require_once 'model/entity/fiscaat/CiviSaldo.class.php';

/**
 * BeheerCiviSaldoView.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class BeheerCiviSaldoView extends DataTable {
	public function __construct() {
		parent::__construct(CiviSaldo::class, '/fiscaat/saldo', 'Saldobeheer');

		$this->addColumn('naam', 'saldo');
		$this->addColumn('lichting', 'saldo');
		$this->setOrder(array('saldo' => 'asc'));

		$nieuw = new DataTableKnop('== 0', $this->dataTableId, '', null, 'Registreren', null, 'add', 'defaultCollection');
		$nieuw->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/saldo/registreren/lid', 'post', 'Lid', 'Lid registreren'));
		$nieuw->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/saldo/registreren/lichting', 'post', 'Lichting', 'Lichting registreren'));

		$this->addKnop($nieuw);
	}

	public function getBreadcrumbs() {
		return '<a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » Saldo';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
function truefalse (data) {
    return '<span class="ico '+(data?'tick':'cross')+'"></span>';
}
JS;
	}
}

class LidRegistratieForm extends ModalForm {
	public function __construct(CiviSaldo $model) {
		parent::__construct($model, '/fiscaat/saldo/registreren/lid', false, true);

		$fields[] = new LidField('uid', $model->uid, 'Lid');
		$fields[] = new IntField('saldo', $model->saldo, 'Initieel saldo');
		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}

class BeheerSaldoResponse extends DataTableResponse {
	/**
	 * @param CiviSaldo $entity
	 * @return string
	 */
	public function getJson($entity) {
		$data = array(
			'uid' => $entity->uid,
			'naam' => ProfielModel::getNaam($entity->uid, 'volledig'),
			'lichting' => substr($entity->uid, 0, 2),
			'saldo' => $entity->saldo,
			'laatst_veranderd' => $entity->laatst_veranderd
		);
		return json_encode($data);
	}
}
