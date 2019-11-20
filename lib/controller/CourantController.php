<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\common\Ini;
use CsrDelft\model\CourantBerichtModel;
use CsrDelft\model\CourantModel;
use CsrDelft\model\entity\courant\CourantBericht;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\courant\CourantBerichtFormulier;
use CsrDelft\view\courant\CourantView;
use CsrDelft\view\PlainView;
use Symfony\Component\HttpFoundation\Response;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de courant.
 */
class CourantController extends AbstractController {

	private $courantModel;
	private $courantBerichtModel;

	public function __construct() {
		$this->courantModel = CourantModel::instance();
		$this->courantBerichtModel = CourantBerichtModel::instance();
	}

	public function archief() {
		return view('courant.archief', ['couranten' => $this->courantModel->find()]);
	}

	public function bekijken($id) {
		$courant = $this->courantModel->get($id);
		return new Response($courant->inhoud);
	}

	public function voorbeeld() {
		return new CourantView($this->courantModel->nieuwCourant(), $this->courantBerichtModel->getBerichten());
	}

	public function toevoegen() {
		$bericht = new CourantBericht();
		$bericht->datumTijd = getDateTime();
		$bericht->uid = LoginModel::getUid();

		$form = new CourantBerichtFormulier($bericht, '/courant');

		if ($form->isPosted() && $form->validate()) {
			$bericht->setVolgorde();
			$this->courantBerichtModel->create($bericht);
			setMelding('Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);

			return $this->redirectToRoute('courant-toevoegen');
		}

		return view('courant.beheer', [
			'courantModel' => $this->courantModel,
			'berichten' => $this->courantBerichtModel->getBerichtenVoorGebruiker(),
			'form' => $form,
		]);
	}

	public function bewerken($id) {
		$bericht = $this->courantBerichtModel->get($id);
		$form = new CourantBerichtFormulier($bericht, '/courant/bewerken/' . $id);

		if ($form->isPosted() && $form->validate()) {
			$this->courantBerichtModel->update($bericht);
			setMelding('Bericht is bewerkt', 1);
			return $this->redirectToRoute('courant-toevoegen');
		}

		return view('courant.beheer', [
			'courantModel' => $this->courantModel,
			'berichten' => $this->courantBerichtModel->getBerichtenVoorGebruiker(),
			'form' => $form,
		]);
	}

	public function verwijderen($id) {
		$bericht = $this->courantBerichtModel->get($id);
		if (!$bericht || !$this->courantModel->magBeheren($bericht->uid)) {
			throw new CsrToegangException();
		}
		if ($this->courantBerichtModel->delete($bericht)) {
			setMelding('Uw bericht is verwijderd.', 1);
		} else {
			setMelding('Uw bericht is niet verwijderd.', -1);
		}
		return $this->redirectToRoute('courant-toevoegen');
	}

	public function verzenden($iedereen = null) {
		if (count($this->courantBerichtModel->getBerichten()) < 1) {
			setMelding('Lege courant kan niet worden verzonden', 0);
			return $this->redirectToRoute('courant-toevoegen');
		}

		$courant = $this->courantModel->nieuwCourant();

		$courantView = new CourantView($courant, $this->courantBerichtModel->getBerichten());
		$courant->inhoud = $courantView->getHtml(false);
		if ($iedereen === 'iedereen') {
			$this->courantModel->verzenden(Ini::lees(Ini::EMAILS, 'leden'), $courantView);

			Database::transaction(function () use ($courant) {
				$this->courantModel->create($courant);
				$berichten = $this->courantBerichtModel->getBerichten();

				foreach ($berichten as $bericht) {
					$this->courantBerichtModel->delete($bericht);
				}

				setMelding('De courant is verzonden naar iedereen', 1);
			});

			return new PlainView('<div id="courantKnoppenContainer">' . getMelding() . '<strong>Aan iedereen verzonden</strong></div>');
		} else {
			$this->courantModel->verzenden(Ini::lees(Ini::EMAILS, 'pubcie'), $courantView);
			setMelding('Verzonden naar de PubCie', 1);
			return new PlainView('<div id="courantKnoppenContainer">'. getMelding() . '<a class="btn btn-primary post confirm" title="Courant aan iedereen verzenden" href="/courant/verzenden/iedereen">Aan iedereen verzenden</a></div>');
		}
	}
}
