<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeVrijstelling;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\maalcie\forms\VrijstellingForm;
use CsrDelft\view\PlainView;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVrijstellingenController {
	/**
	 * @var CorveeVrijstellingenModel
	 */
	private $corveeVrijstellingenModel;

	public function __construct(CorveeVrijstellingenModel $corveeVrijstellingenModel) {
		$this->corveeVrijstellingenModel = $corveeVrijstellingenModel;
	}

	public function beheer() {
		return view('maaltijden.vrijstelling.beheer_vrijstellingen', ['vrijstellingen' => $this->corveeVrijstellingenModel->find()]);
	}

	public function nieuw() {
		return new VrijstellingForm($this->corveeVrijstellingenModel->nieuw()); // fetches POST values itself
	}

	public function bewerk($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		return new VrijstellingForm($this->corveeVrijstellingenModel->getVrijstelling($uid)); // fetches POST values itself
	}

	public function opslaan($uid = null) {
		if ($uid !== null) {
			$view = $this->bewerk($uid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			$values = $view->getValues();
			return view('maaltijden.vrijstelling.beheer_vrijstelling_lijst', [
				'vrijstelling' => $this->corveeVrijstellingenModel->saveVrijstelling($values['uid'], $values['begin_datum'], $values['eind_datum'], $values['percentage'])
			]);
		}

		return $view;
	}

	public function verwijder($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$this->corveeVrijstellingenModel->verwijderVrijstelling($uid);
		return new PlainView('<tr id="vrijstelling-row-' . $uid . '" class="remove"></tr>');
	}

}
