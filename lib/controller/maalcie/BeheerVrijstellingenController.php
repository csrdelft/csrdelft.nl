<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeVrijstelling;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\maalcie\forms\VrijstellingForm;
use CsrDelft\view\PlainView;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVrijstellingenController {
	/**
	 * @var CorveeVrijstellingenRepository
	 */
	private $corveeVrijstellingenRepository;

	public function __construct(CorveeVrijstellingenRepository $corveeVrijstellingenRepository) {
		$this->corveeVrijstellingenRepository = $corveeVrijstellingenRepository;
	}

	public function beheer() {
		return view('maaltijden.vrijstelling.beheer_vrijstellingen', ['vrijstellingen' => $this->corveeVrijstellingenRepository->findAll()]);
	}

	public function nieuw() {
		return new VrijstellingForm($this->corveeVrijstellingenRepository->nieuw()); // fetches POST values itself
	}

	public function bewerk($uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		return new VrijstellingForm($this->corveeVrijstellingenRepository->getVrijstelling($uid)); // fetches POST values itself
	}

	public function opslaan($uid = null) {
		if ($uid !== null) {
			$view = $this->bewerk($uid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			/** @var CorveeVrijstelling $values */
			$values = $view->getModel();
			return view('maaltijden.vrijstelling.beheer_vrijstelling_lijst', [
				'vrijstelling' => $this->corveeVrijstellingenRepository->saveVrijstelling($values->profiel, $values->begin_datum, $values->eind_datum, $values->percentage)
			]);
		}

		return $view;
	}

	public function verwijder($uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$this->corveeVrijstellingenRepository->verwijderVrijstelling($uid);
		return new PlainView('<tr id="vrijstelling-row-' . $uid . '" class="remove"></tr>');
	}

}
