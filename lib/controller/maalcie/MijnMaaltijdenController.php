<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnMaaltijdenController {
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/** @var CorveeTakenRepository */
	private $corveeTakenRepository;
	/** @var MaaltijdBeoordelingenRepository */
	private $maaltijdBeoordelingenRepository;
	/** @var MaaltijdAanmeldingenRepository */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdBeoordelingenRepository = $maaltijdBeoordelingenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	/**
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden", methods={"GET"})
	 * @Route("/maaltijden/ketzer", methods={"GET"})
	 * @Auth(P_MAAL_IK)
	 */
	public function ketzer() {
		$maaltijden = $this->maaltijdenRepository->getKomendeMaaltijdenVoorLid(LoginService::getUid());
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid($maaltijden, LoginService::getUid());
		$timestamp = date_create_immutable(instelling('maaltijden', 'beoordeling_periode'));
		$recent = $this->maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid(LoginService::getUid(), $timestamp);
		$beoordelen = [];
		$kwantiteit_forms = [];
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
			$beoordeling = $this->maaltijdBeoordelingenRepository->find(['maaltijd_id' => $maaltijd_id, 'uid' => LoginService::getUid()]);
			if (!$beoordeling) {
				$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw($maaltijd);
			}
			$kwantiteit_forms[$maaltijd_id] = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
			$kwaliteit_forms[$maaltijd_id] = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
		return view('maaltijden.maaltijd.mijn_maaltijden', [
			'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs')),
			'maaltijden' => $maaltijden,
			'aanmeldingen' => $aanmeldingen,
			'beoordelen' => $beoordelen,
			'kwantiteit' => $kwantiteit_forms,
			'kwaliteit' => $kwaliteit_forms,
		]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return TemplateView
	 * @Route("/maaltijden/lijst/{maaltijd_id}", methods={"GET"})
	 * @Auth(P_MAAL_IK)
	 */
	public function lijst(Maaltijd $maaltijd) {
		if (!$maaltijd->magSluiten(LoginService::getUid()) AND !LoginService::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorMaaltijd($maaltijd);
		for ($i = $maaltijd->getMarge(); $i > 0; $i--) { // ruimte voor marge eters
			$aanmeldingen[] = new MaaltijdAanmelding();
		}

		return view('maaltijden.maaltijd.maaltijd_lijst', [
			'titel' => $maaltijd->getTitel(),
			'aanmeldingen' => $aanmeldingen,
			'eterstotaal' => $maaltijd->getAantalAanmeldingen() + $maaltijd->getMarge(),
			'corveetaken' => $this->corveeTakenRepository->getTakenVoorMaaltijd($maaltijd->maaltijd_id),
			'maaltijd' => $maaltijd,
			'prijs' => sprintf("%.2f", $maaltijd->getPrijsFloat()),
		]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/lijst/sluit/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function sluit(Maaltijd $maaltijd) {
		if ($maaltijd->verwijderd) throw new CsrToegangException();
		if (!$maaltijd->magSluiten(LoginService::getUid()) AND !LoginService::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
		echo '<h3 id="gesloten-melding" class="remove"></div>';
		exit;
	}

	/**
	 * @param Request $request
	 * @param Maaltijd $maaltijd
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/aanmelden/{maaltijd_id}", methods={"GET","POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function aanmelden(Request $request, Maaltijd $maaltijd) {
		if ($maaltijd->verwijderd) throw new CsrToegangException();
		$aanmelding = $this->maaltijdAanmeldingenRepository->aanmeldenVoorMaaltijd($maaltijd, LoginService::getUid(), LoginService::getUid());
		if ($request->getMethod() == 'POST') {
			return view('maaltijden.maaltijd.mijn_maaltijd_lijst', [
				'maaltijd' => $aanmelding->maaltijd,
				'aanmelding' => $aanmelding,
				'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs'))
			]);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
		}
	}

	/**
	 * @param Request $request
	 * @param Maaltijd $maaltijd
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/afmelden/{maaltijd_id}", methods={"GET","POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function afmelden(Request $request, Maaltijd $maaltijd) {
		if ($maaltijd->verwijderd) throw new CsrToegangException();
		$this->maaltijdAanmeldingenRepository->afmeldenDoorLid($maaltijd, LoginService::getUid());
		if ($request->getMethod() == 'POST') {
			return view('maaltijden.maaltijd.mijn_maaltijd_lijst', [
				'maaltijd' => $maaltijd,
				'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs'))
			]);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $maaltijd]);
		}
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/gasten/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function gasten(Maaltijd $maaltijd) {
		if ($maaltijd->verwijderd) throw new CsrToegangException();
		$gasten = (int)filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = $this->maaltijdAanmeldingenRepository->saveGasten($maaltijd->maaltijd_id, LoginService::getUid(), $gasten);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	/**
	 * @param int $maaltijd_id
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/opmerking/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function opmerking($maaltijd_id) {
		$opmerking = filter_input(INPUT_POST, 'gasten_eetwens', FILTER_SANITIZE_STRING);
		$aanmelding = $this->maaltijdAanmeldingenRepository->saveGastenEetwens($maaltijd_id, LoginService::getUid(), $opmerking);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/ketzer/beoordeling/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function beoordeling(Maaltijd $maaltijd) {
		if ($maaltijd->verwijderd) throw new CsrToegangException();
		$beoordeling = $this->maaltijdBeoordelingenRepository->find(['maaltijd_id' => $maaltijd->maaltijd_id, 'uid' => LoginService::getUid()]);
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
