<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeVrijstelling;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\vrijstellingen\BeheerVrijstellingenView;
use CsrDelft\view\maalcie\corvee\vrijstellingen\BeheerVrijstellingView;
use CsrDelft\view\maalcie\forms\VrijstellingForm;
use CsrDelft\view\PlainView;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVrijstellingenController {
	private $model;

	public function __construct() {
		$this->model = CorveeVrijstellingenModel::instance();
	}

	public function beheer() {
		/** @var CorveeVrijstelling[] $vrijstellingen */
		$vrijstellingen = $this->model->find();
		$view = new BeheerVrijstellingenView($vrijstellingen);
		return new CsrLayoutPage($view);
	}

	public function nieuw() {
		$vrijstelling = $this->model->nieuw();
		return new VrijstellingForm($vrijstelling); // fetches POST values itself
	}

	public function bewerk($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$vrijstelling = $this->model->getVrijstelling($uid);
		return new VrijstellingForm($vrijstelling); // fetches POST values itself
	}

	public function opslaan($uid = null) {
		if ($uid !== null) {
			$view = $this->bewerk($uid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			$values = $view->getValues();
			$vrijstelling = $this->model->saveVrijstelling($values['uid'], $values['begin_datum'], $values['eind_datum'], $values['percentage']);
			return new BeheerVrijstellingView($vrijstelling);
		}

		return $view;
	}

	public function verwijder($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$this->model->verwijderVrijstelling($uid);
		return new PlainView('<tr id="vrijstelling-row-' . $uid . '" class="remove"></tr>');
	}

}
