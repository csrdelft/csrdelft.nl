<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrNotFoundException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\repository\CourantBerichtRepository;
use CsrDelft\repository\CourantRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\courant\CourantBerichtFormulier;
use CsrDelft\view\courant\CourantView;
use CsrDelft\view\PlainView;
use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\HttpFoundation\Response;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de courant.
 */
class CourantController extends AbstractController {
	/**
	 * @var CourantRepository
	 */
	private $courantRepository;
	/**
	 * @var CourantBerichtRepository
	 */
	private $courantBerichtRepository;

	public function __construct(CourantRepository $courantRepository, CourantBerichtRepository $courantBerichtRepository) {
		$this->courantRepository = $courantRepository;
		$this->courantBerichtRepository = $courantBerichtRepository;
	}

	public function archief() {
		return view('courant.archief', ['couranten' => $this->courantRepository->findAll()]);
	}

	public function bekijken($id) {
		$courant = $this->courantRepository->find($id);
		if (!$courant) {
			throw new CsrNotFoundException("Courant niet gevonden");
		}
		return new Response($courant->inhoud);
	}

	public function voorbeeld() {
		return new CourantView($this->courantRepository->nieuwCourant(), $this->courantBerichtRepository->findAll());
	}

	public function toevoegen() {
		$bericht = new CourantBericht();
		$bericht->datumTijd = new DateTime();
		$bericht->uid = LoginService::getUid();
		$bericht->schrijver = LoginService::getProfiel();

		$form = new CourantBerichtFormulier($bericht, '/courant');

		if ($form->isPosted() && $form->validate()) {
			$bericht->setVolgorde();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($bericht);
			$manager->flush();
			setMelding('Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);

			return $this->redirectToRoute('courant-toevoegen');
		}

		return view('courant.beheer', [
			'magVerzenden' => $this->courantRepository->magVerzenden(),
			'magBeheren' => $this->courantRepository->magBeheren(),
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form,
		]);
	}

	public function bewerken($id) {
		$bericht = $this->courantBerichtRepository->find($id);
		$form = new CourantBerichtFormulier($bericht, '/courant/bewerken/' . $id);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->flush();
			setMelding('Bericht is bewerkt', 1);
			return $this->redirectToRoute('courant-toevoegen');
		}

		return view('courant.beheer', [
			'magVerzenden' => $this->courantRepository->magVerzenden(),
			'magBeheren' => $this->courantRepository->magBeheren(),
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form,
		]);
	}

	public function verwijderen($id) {
		$bericht = $this->courantBerichtRepository->find($id);
		if (!$bericht || !$bericht->magBeheren()) {
			throw new CsrToegangException();
		}
		try {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($bericht);
			$manager->flush();

			setMelding('Uw bericht is verwijderd.', 1);
		} catch (Exception $exception) {
			setMelding('Uw bericht is niet verwijderd.', -1);
		}
		return $this->redirectToRoute('courant-toevoegen');
	}

	public function verzenden($iedereen = null) {
		if (count($this->courantBerichtRepository->findAll()) < 1) {
			setMelding('Lege courant kan niet worden verzonden', 0);
			return $this->redirectToRoute('courant-toevoegen');
		}

		$courant = $this->courantRepository->nieuwCourant();

		$courantView = new CourantView($courant, $this->courantBerichtRepository->findAll());
		$courant->inhoud = $courantView->getHtml(false);
		if ($iedereen === 'iedereen') {
			$this->courantRepository->verzenden(env('EMAIL_LEDEN'), $courantView);
			/** @var Connection $conn */
			$conn = $this->getDoctrine()->getConnection();
			$conn->beginTransaction();

			try {
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($courant);

				$berichten = $this->courantBerichtRepository->findAll();

				foreach ($berichten as $bericht) {
					$manager->remove($bericht);
				}

				$manager->flush();
				$conn->commit();

				setMelding('De courant is verzonden naar iedereen', 1);
			} catch (Exception $exception) {
				$conn->rollBack();
				setMelding('Courant niet verzonden', -1);
			}

			return new PlainView('<div id="courantKnoppenContainer">' . getMelding() . '<strong>Aan iedereen verzonden</strong></div>');
		} else {
			$this->courantRepository->verzenden(env('EMAIL_PUBCIE'), $courantView);
			setMelding('Verzonden naar de PubCie', 1);
			return new PlainView('<div id="courantKnoppenContainer">' . getMelding() . '<a class="btn btn-primary post confirm" title="Courant aan iedereen verzenden" href="/courant/verzenden/iedereen">Aan iedereen verzenden</a></div>');
		}
	}
}
