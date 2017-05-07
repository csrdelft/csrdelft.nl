<?php
/**
 * LidRegistratieForm.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\fiscaat;
use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Class LidRegistratieForm
 *
 * Maak het mogelijk om een lid te registreren, wordt uiteindelijk samengetrokken met het aanmaken van een lid.
 */
class LidRegistratieForm extends ModalForm {
	public function __construct(CiviSaldo $model) {
		parent::__construct($model, '/fiscaat/saldo/registreren/lid', false, true);

		$fields[] = new LidField('uid', $model->uid, 'Lid');
		$fields[] = new IntField('saldo', $model->saldo, 'Initieel saldo');
		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}