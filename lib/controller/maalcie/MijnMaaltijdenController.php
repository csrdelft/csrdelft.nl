<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\maalcie\MaaltijdAanmeldingenService;
use CsrDelft\service\maalcie\MaaltijdenService;
use CsrDelft\service\maalcie\MaaltijdGastAanmeldingenService;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnMaaltijdenController extends AbstractController
{
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/** @var CorveeTakenRepository */
	private $corveeTakenRepository;
	/** @var MaaltijdBeoordelingenRepository */
	private $maaltijdBeoordelingenRepository;
	/** @var MaaltijdAanmeldingenRepository */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var MaaltijdAanmeldingenService
	 */
	private $maaltijdAanmeldingenService;
	/**
	 * @var MaaltijdGastAanmeldingenService
	 */
	private $maaltijdGastAanmeldingenService;
	/**
	 * @var MaaltijdenService
	 */
	private $maaltijdenService;

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdenService $maaltijdenService,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository,
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		MaaltijdGastAanmeldingenService $maaltijdGastAanmeldingenService,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdBeoordelingenRepository = $maaltijdBeoordelingenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdAanmeldingenService = $maaltijdAanmeldingenService;
		$this->maaltijdGastAanmeldingenService = $maaltijdGastAanmeldingenService;
		$this->maaltijdenService = $maaltijdenService;
	}

	/**
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden", methods={"GET"})
	 * @Route("/maaltijden/ketzer", methods={"GET"})
	 * @Auth(P_MAAL_IK)
	 */
	public function ketzer()
	{
		$maaltijden = $this->maaltijdenService->getKomendeMaaltijdenVoorLid(
			$this->getProfiel()
		);
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid(
			$maaltijden,
			$this->getUid()
		);
		$timestamp = date_create_immutable(
			InstellingUtil::instelling('maaltijden', 'beoordeling_periode')
		);
		$recent = $this->maaltijdAanmeldingenService->getRecenteAanmeldingenVoorLid(
			$this->getUid(),
			$timestamp
		);
		/** @var Maaltijd[] $beoordelen */
		$beoordelen = [];
		/** @var MaaltijdKwantiteitBeoordelingForm[] $kwantiteit_forms */
		$kwantiteit_forms = [];
		/** @var MaaltijdKwaliteitBeoordelingForm[] $kwaliteit_forms */
		$kwaliteit_forms = [];
		foreach ($maaltijden as $maaltijd) {
			$maaltijd_id = $maaltijd->maaltijd_id;
			if (!array_key_exists($maaltijd_id, $aanmeldingen)) {
				$aanmeldingen[$maaltijd_id] = false;
			}
		}
		foreach ($recent as $aanmelding) {
			$maaltijd = $aanmelding->maaltijd;
			$maaltijd_id = $aanmelding->maaltijd_id;
			$beoordelen[$maaltijd_id] = $maaltijd;
			$beoordeling = $this->maaltijdBeoordelingenRepository->find([
				'maaltijd_id' => $maaltijd_id,
				'uid' => $this->getUid(),
			]);
			if (!$beoordeling) {
				$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw($maaltijd);
			}
			$kwantiteit_forms[$maaltijd_id] = new MaaltijdKwantiteitBeoordelingForm(
				$maaltijd,
				$beoordeling
			);
			$kwaliteit_forms[$maaltijd_id] = new MaaltijdKwaliteitBeoordelingForm(
				$maaltijd,
				$beoordeling
			);
		}
		return $this->render('maaltijden/maaltijd/mijn_maaltijden.html.twig', [
			'standaardprijs' => intval(
				InstellingUtil::instelling('maaltijden', 'standaard_prijs')
			),
			'maaltijden' => $maaltijden,
			'aanmeldingen' => $aanmeldingen,
			'beoordelen' => $beoordelen,
			'kwantiteit' => $kwantiteit_forms,
			'kwaliteit' => $kwaliteit_forms,
		]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return Response
	 * @Route("/maaltijden/lijst/{maaltijd_id}", methods={"GET"})
	 * @Auth(P_MAAL_IK)
	 */
	public function lijst(Maaltijd $maaltijd)
	{
		if (!$maaltijd->magSluiten($this->getUid()) && !$this->mag(P_MAAL_MOD)) {
			throw $this->createAccessDeniedException();
		}
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorMaaltijd(
			$maaltijd
		);
		for ($i = $maaltijd->getMarge(); $i > 0; $i--) {
			// ruimte voor marge eters
			$aanmeldingen[] = new MaaltijdAanmelding();
		}

		return $this->render('maaltijden/maaltijd/maaltijd_lijst.html.twig', [
			'titel' => $maaltijd->getTitel(),
			'aanmeldingen' => $aanmeldingen,
			'eterstotaal' =>
				$maaltijd->getAantalAanmeldingen() + $maaltijd->getMarge(),
			'corveetaken' => $this->corveeTakenRepository->getTakenVoorMaaltijd(
				$maaltijd->maaltijd_id
			),
			'maaltijd' => $maaltijd,
			'prijs' => sprintf('%.2f', $maaltijd->getPrijsFloat()),
		]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/lijst/sluit/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function sluit(Maaltijd $maaltijd)
	{
		if ($maaltijd->verwijderd) {
			throw $this->createAccessDeniedException();
		}
		if (!$maaltijd->magSluiten($this->getUid()) && !$this->mag(P_MAAL_MOD)) {
			throw $this->createAccessDeniedException();
		}
		$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
		echo '<h3 id="gesloten-melding" class="remove"></div>';
		exit();
	}

	/**
	 * @param Request $request
	 * @param Maaltijd $maaltijd
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/aanmelden/{maaltijd_id}", methods={"GET","POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function aanmelden(Request $request, Maaltijd $maaltijd)
	{
		if ($maaltijd->verwijderd) {
			throw $this->createAccessDeniedException();
		}
		$aanmelding = $this->maaltijdAanmeldingenService->aanmeldenVoorMaaltijd(
			$maaltijd,
			$this->getProfiel(),
			$this->getProfiel()
		);
		if ($request->getMethod() == 'POST') {
			return $this->render(
				'maaltijden/maaltijd/mijn_maaltijd_lijst.html.twig',
				[
					'maaltijd' => $aanmelding->maaltijd,
					'aanmelding' => $aanmelding,
					'standaardprijs' => intval(
						InstellingUtil::instelling('maaltijden', 'standaard_prijs')
					),
				]
			);
		} elseif ($request->query->get('size') == 'klein') {
			// Aparte ketzer voor de agenda op de voorpagina
			return $this->render('voorpagina/agenda_maaltijd_ketzer.html.twig', [
				'maaltijd' => $aanmelding->maaltijd,
				'aanmelding' => $aanmelding,
			]);
		} else {
			return $this->render('maaltijden/bb.html.twig', [
				'maaltijd' => $aanmelding->maaltijd,
				'aanmelding' => $aanmelding,
			]);
		}
	}

	/**
	 * @param Request $request
	 * @param Maaltijd $maaltijd
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/afmelden/{maaltijd_id}", methods={"GET","POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function afmelden(Request $request, Maaltijd $maaltijd)
	{
		if ($maaltijd->verwijderd) {
			throw $this->createAccessDeniedException();
		}
		$this->maaltijdAanmeldingenService->afmeldenDoorLid(
			$maaltijd,
			$this->getProfiel()
		);
		if ($request->getMethod() == 'POST') {
			return $this->render(
				'maaltijden/maaltijd/mijn_maaltijd_lijst.html.twig',
				[
					'maaltijd' => $maaltijd,
					'standaardprijs' => intval(
						InstellingUtil::instelling('maaltijden', 'standaard_prijs')
					),
				]
			);
		} elseif ($request->query->get('size') == 'klein') {
			// Aparte ketzer voor de agenda op de voorpagina
			return $this->render('voorpagina/agenda_maaltijd_ketzer.html.twig', [
				'maaltijd' => $maaltijd,
			]);
		} else {
			return $this->render('maaltijden/bb.html.twig', [
				'maaltijd' => $maaltijd,
			]);
		}
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/gasten/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function gasten(Maaltijd $maaltijd)
	{
		if ($maaltijd->verwijderd) {
			throw $this->createAccessDeniedException();
		}
		$gasten = (int) filter_input(
			INPUT_POST,
			'aantal_gasten',
			FILTER_SANITIZE_NUMBER_INT
		);
		$aanmelding = $this->maaltijdGastAanmeldingenService->saveGasten(
			$maaltijd->maaltijd_id,
			$this->getUid(),
			$gasten
		);
		return $this->render('maaltijden/bb.html.twig', [
			'maaltijd' => $aanmelding->maaltijd,
			'aanmelding' => $aanmelding,
		]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/mijn/gasten/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function gasten_mijn(Maaltijd $maaltijd)
	{
		if ($maaltijd->verwijderd) {
			throw $this->createAccessDeniedException();
		}
		$gasten = (int) filter_input(
			INPUT_POST,
			'aantal_gasten',
			FILTER_SANITIZE_NUMBER_INT
		);
		$aanmelding = $this->maaltijdGastAanmeldingenService->saveGasten(
			$maaltijd->maaltijd_id,
			$this->getUid(),
			$gasten
		);
		return $this->render('maaltijden/maaltijd/mijn_maaltijd_lijst.html.twig', [
			'maaltijd' => $aanmelding->maaltijd,
			'aanmelding' => $aanmelding,
			'standaardprijs' => intval(
				InstellingUtil::instelling('maaltijden', 'standaard_prijs')
			),
		]);
	}

	/**
	 * @param int $maaltijd_id
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/opmerking/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function opmerking(Maaltijd $maaltijd)
	{
		$opmerking = filter_input(
			INPUT_POST,
			'gasten_eetwens',
			FILTER_SANITIZE_STRING
		);
		$aanmelding = $this->maaltijdGastAanmeldingenService->saveGastenEetwens(
			$maaltijd,
			$this->getProfiel(),
			$opmerking
		);
		return $this->render('maaltijden/bb.html.twig', [
			'maaltijd' => $aanmelding->maaltijd,
			'aanmelding' => $aanmelding,
		]);
	}

	/**
	 * @param int $maaltijd_id
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/mijn/opmerking/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function opmerking_mijn(Maaltijd $maaltijd)
	{
		$opmerking = filter_input(
			INPUT_POST,
			'gasten_eetwens',
			FILTER_SANITIZE_STRING
		);
		$aanmelding = $this->maaltijdGastAanmeldingenService->saveGastenEetwens(
			$maaltijd,
			$this->getProfiel(),
			$opmerking
		);
		return $this->render('maaltijden/maaltijd/mijn_maaltijd_lijst.html.twig', [
			'maaltijd' => $aanmelding->maaltijd,
			'aanmelding' => $aanmelding,
			'standaardprijs' => intval(
				InstellingUtil::instelling('maaltijden', 'standaard_prijs')
			),
		]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/beoordeling/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function beoordeling(Maaltijd $maaltijd)
	{
		if ($maaltijd->verwijderd) {
			throw $this->createAccessDeniedException();
		}
		$beoordeling = $this->maaltijdBeoordelingenRepository->find([
			'maaltijd_id' => $maaltijd->maaltijd_id,
			'uid' => $this->getUid(),
		]);
		if (!$beoordeling) {
			$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw($maaltijd);
		}
		$form = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
		if (!$form->validate()) {
			$form = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
		if ($form->validate()) {
			$this->maaltijdBeoordelingenRepository->update($beoordeling);
		}
		return new JsonResponse(null);
	}
}
