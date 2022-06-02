<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\courant\Courant;
use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\entity\courant\CourantCategorie;
use CsrDelft\repository\CourantBerichtRepository;
use CsrDelft\repository\CourantRepository;
use CsrDelft\view\courant\CourantBerichtFormulier;
use CsrDelft\view\PlainView;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de courant.
 */
class CourantController extends AbstractController
{
	/**
	 * @var CourantRepository
	 */
	private $courantRepository;
	/**
	 * @var CourantBerichtRepository
	 */
	private $courantBerichtRepository;

	public function __construct(CourantRepository $courantRepository, CourantBerichtRepository $courantBerichtRepository)
	{
		$this->courantRepository = $courantRepository;
		$this->courantBerichtRepository = $courantBerichtRepository;
	}

	/**
	 * @return Response
	 * @Route("/courant/archief", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 * @throws Exception
	 */
	public function archief(): Response
	{
		return $this->render('courant/archief.html.twig', [
			'couranten' => group_by('getJaar', $this->courantRepository->findAll())
		]);
	}

	/**
	 * @param Courant $courant
	 * @return Response
	 * @Route("/courant/bekijken/{id}", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function bekijken(Courant $courant): Response
	{
		return new Response($courant->inhoud);
	}

	/**
	 * @return Response
	 * @Route("/courant/voorbeeld", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function voorbeeld(): Response
	{
		return $this->render('courant/mail.html.twig', [
			'berichten' => $this->courantBerichtRepository->findAll(),
			'catNames' => CourantCategorie::getEnumDescriptions(),
		]);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/courant", methods={"GET", "POST"})
	 * @Auth(P_MAIL_POST)
	 */
	public function toevoegen(Request $request): Response
	{
		$bericht = new CourantBericht();
		$bericht->datumTijd = new DateTime();
		$bericht->uid = $this->getUid();
		$bericht->schrijver = $this->getProfiel();

		$form = $this->createFormulier(CourantBerichtFormulier::class, $bericht, [
			'action' => $this->generateUrl('csrdelft_courant_toevoegen')
		]);

		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$bericht->setVolgorde();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($bericht);
			$manager->flush();
			setMelding('Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);

			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		return $this->render('courant/beheer.html.twig', [
			'magVerzenden' => $this->courantRepository->magVerzenden(),
			'magBeheren' => $this->courantRepository->magBeheren(),
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form->createView(),
		]);
	}

	/**
	 * @param Request $request
	 * @param CourantBericht $bericht
	 * @return Response
	 * @Route("/courant/bewerken/{id}", methods={"GET", "POST"})
	 * @Auth(P_MAIL_POST)
	 */
	public function bewerken(Request $request, CourantBericht $bericht): Response
	{
		$form = $this->createFormulier(CourantBerichtFormulier::class, $bericht, [
			'action' => $this->generateUrl('csrdelft_courant_bewerken', ['id' => $bericht->id]),
		]);

		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->flush();
			setMelding('Bericht is bewerkt', 1);
			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		return $this->render('courant/beheer.html.twig', [
			'magVerzenden' => $this->courantRepository->magVerzenden(),
			'magBeheren' => $this->courantRepository->magBeheren(),
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form->createView(),
		]);
	}

	/**
	 * @param CourantBericht $bericht
	 * @return RedirectResponse
	 * @Route("/courant/verwijderen/{id}", methods={"POST"})
	 * @Auth(P_MAIL_POST)
	 */
	public function verwijderen(CourantBericht $bericht): RedirectResponse
	{
		if (!$bericht->magBeheren()) {
			throw $this->createAccessDeniedException();
		}

		try {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($bericht);
			$manager->flush();

			setMelding('Uw bericht is verwijderd.', 1);
		} catch (Exception $exception) {
			setMelding('Uw bericht is niet verwijderd.', -1);
		}
		return $this->redirectToRoute('csrdelft_courant_toevoegen');
	}

	/**
	 * @param null $iedereen
	 * @return PlainView|RedirectResponse
	 * @throws ConnectionException
	 * @Route("/courant/verzenden/{iedereen}", methods={"POST"}, defaults={"iedereen": null})
	 * @Auth(P_MAIL_SEND)
	 */
	public function verzenden($iedereen = null)
	{
		if (count($this->courantBerichtRepository->findAll()) < 1) {
			setMelding('Lege courant kan niet worden verzonden', 0);
			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		$courant = $this->courantRepository->nieuwCourant();

		$courant->inhoud = $this->renderView('courant/mail.html.twig', [
			'berichten' => $this->courantBerichtRepository->findAll(),
			'catNames' => CourantCategorie::getEnumDescriptions(),
		]);
		if ($iedereen === 'iedereen') {
			$response = $this->courantRepository->verzenden($_ENV['EMAIL_LEDEN'], $courant->inhoud);
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

			return new PlainView('<div id="courantKnoppenContainer">' . $response . getMelding() . '<strong>Aan iedereen verzonden</strong></div>');
		} else {
			$response = $this->courantRepository->verzenden($_ENV['EMAIL_PUBCIE'], $courant->inhoud);
			setMelding('Verzonden naar de PubCie', 1);
			return new PlainView('<div id="courantKnoppenContainer">' . $response . getMelding() . '<a class="btn btn-primary post confirm" title="Courant aan iedereen verzenden" href="/courant/verzenden/iedereen">Aan iedereen verzenden</a></div>');
		}
	}
}
