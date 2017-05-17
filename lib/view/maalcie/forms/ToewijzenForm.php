<?php
namespace CsrDelft\view\maalcie\forms;
use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use Exception;

/**
 * ToewijzenForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 *
 */
class ToewijzenForm extends ModalForm {

	public function __construct(CorveeTaak $taak, array $suggesties) {
		parent::__construct(null, maalcieUrl . '/toewijzen/' . $taak->taak_id);

		if (!is_int($taak->taak_id) || $taak->taak_id <= 0) {
			throw new Exception('invalid tid');
		}
		$this->titel = 'Taak toewijzen aan lid';
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('uid', $taak->uid, 'Naam of lidnummer', 'leden');
		$fields[] = new SuggestieLijst($suggesties, $taak);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}
