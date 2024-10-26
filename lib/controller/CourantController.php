<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\common\Security\Voter\Entity\CourantBerichtVoter;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\FlashUtil;
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
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de courant.
 */
class CourantController extends AbstractController
{
	public function __construct(
		private readonly CourantRepository $courantRepository,
		private readonly CourantBerichtRepository $courantBerichtRepository
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_LEDEN_READ)
	 * @throws Exception
	 */
	#[Route(path: '/courant/archief', methods: ['GET'])]
	public function archief(): Response
	{
		return $this->render('courant/archief.html.twig', [
			'couranten' => ArrayUtil::group_by(
				'getJaar',
				$this->courantRepository->findAll()
			),
		]);
	}

	/**
	 * @param Courant $courant
	 * @return Response
	 * @Auth(P_LEDEN_READ)
	 */
	#[Route(path: '/courant/bekijken/{id}', methods: ['GET'])]
	public function bekijken(Courant $courant): Response
	{
		return new Response($courant->inhoud);
	}

	/**
	 * @return Response
	 * @Auth(P_LEDEN_READ)
	 */
	#[Route(path: '/courant/voorbeeld', methods: ['GET'])]
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
	 * @Auth(P_MAIL_POST)
	 */
	#[Route(path: '/courant', methods: ['GET', 'POST'])]
	public function toevoegen(Request $request): Response
	{
		$bericht = new CourantBericht();
		$bericht->datumTijd = new DateTime();
		$bericht->uid = $this->getUid();
		$bericht->schrijver = $this->getProfiel();

		$form = $this->createFormulier(CourantBerichtFormulier::class, $bericht, [
			'action' => $this->generateUrl('csrdelft_courant_toevoegen'),
		]);

		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$bericht->setVolgorde();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($bericht);
			$manager->flush();
			$this->addFlash(
				FlashType::SUCCESS,
				'Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.'
			);

			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		return $this->render('courant/beheer.html.twig', [
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form->createView(),
		]);
	}

	/**
	 * @param Request $request
	 * @param CourantBericht $bericht
	 * @return Response
	 * @Auth(P_MAIL_POST)
	 */
	#[Route(path: '/courant/bewerken/{id}', methods: ['GET', 'POST'])]
	public function bewerken(Request $request, CourantBericht $bericht): Response
	{
		$form = $this->createFormulier(CourantBerichtFormulier::class, $bericht, [
			'action' => $this->generateUrl('csrdelft_courant_bewerken', [
				'id' => $bericht->id,
			]),
		]);

		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->flush();
			$this->addFlash(FlashType::SUCCESS, 'Bericht is bewerkt');
			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		return $this->render('courant/beheer.html.twig', [
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form->createView(),
		]);
	}

	/**
	 * @param CourantBericht $bericht
	 * @return RedirectResponse
	 * @Auth(P_MAIL_POST)
	 */
	#[Route(path: '/courant/verwijderen/{id}', methods: ['POST'])]
	public function verwijderen(CourantBericht $bericht): RedirectResponse
	{
		$this->denyAccessUnlessGranted(CourantBerichtVoter::BEHEREN, $bericht);

		try {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($bericht);
			$manager->flush();

			$this->addFlash(FlashType::SUCCESS, 'Uw bericht is verwijderd.');
		} catch (Exception) {
			$this->addFlash(FlashType::ERROR, 'Uw bericht is niet verwijderd.');
		}
		return $this->redirectToRoute('csrdelft_courant_toevoegen');
	}

	/**
	 * @param null $iedereen
	 * @return PlainView|RedirectResponse
	 * @throws ConnectionException
	 * @Auth(P_MAIL_SEND)
	 */
	#[
		Route(
			path: '/courant/verzenden/{iedereen}',
			methods: ['POST'],
			defaults: ['iedereen' => null]
		)
	]
	public function verzenden($iedereen = null)
	{
		if (count($this->courantBerichtRepository->findAll()) < 1) {
			$this->addFlash(
				FlashType::INFO,
				'Lege courant kan niet worden verzonden'
			);
			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		$courant = $this->courantRepository->nieuwCourant();

		$courant->inhoud = $this->renderView('courant/mail.html.twig', [
			'berichten' => $this->courantBerichtRepository->findAll(),
			'catNames' => CourantCategorie::getEnumDescriptions(),
		]);
		if ($iedereen === 'iedereen') {
			$response = $this->courantRepository->verzenden(
				$_ENV['EMAIL_LEDEN'],
				$courant->inhoud
			);
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

				$this->addFlash(
					FlashType::SUCCESS,
					'De courant is verzonden naar iedereen'
				);
			} catch (Exception) {
				$conn->rollBack();
				$this->addFlash(FlashType::ERROR, 'Courant niet verzonden');
			}

			return new PlainView(
				'<div id="courantKnoppenContainer">' .
					$response .
					FlashUtil::getFlashUsingContainerFacade() .
					'<strong>Aan iedereen verzonden</strong></div>'
			);
		} else {
			$response = $this->courantRepository->verzenden(
				$_ENV['EMAIL_PUBCIE'],
				$courant->inhoud
			);
			$this->addFlash(FlashType::SUCCESS, 'Verzonden naar de PubCie');
			return new PlainView(
				'<div id="courantKnoppenContainer">' .
					$response .
					FlashUtil::getFlashUsingContainerFacade() .
					'<a class="btn btn-primary post confirm" title="Courant aan iedereen verzenden" href="/courant/verzenden/iedereen">Aan iedereen verzenden</a></div>'
			);
		}
	}
}
