<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\repository\eetplan\EetplanBekendenRepository;
use CsrDelft\repository\eetplan\EetplanRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenForm;
use CsrDelft\view\eetplan\EetplanBekendeHuizenTable;
use CsrDelft\view\eetplan\EetplanBekendenForm;
use CsrDelft\view\eetplan\EetplanBekendenTable;
use CsrDelft\view\eetplan\EetplanHuizenResponse;
use CsrDelft\view\eetplan\EetplanHuizenTable;
use CsrDelft\view\eetplan\EetplanHuizenZoekenResponse;
use CsrDelft\view\eetplan\NieuwEetplanForm;
use CsrDelft\view\eetplan\VerwijderEetplanForm;
use CsrDelft\view\renderer\TemplateView;
use CsrDelft\view\View;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor eetplan.
 */
class EetplanController extends AbstractController {
	/** @var string */
	private $lichting;
	/** @var EetplanRepository */
	private $eetplanRepository;
	/** @var EetplanBekendenRepository */
	private $eetplanBekendenRepository;
	/** @var WoonoordenRepository */
	private $woonoordenRepository;

	public function __construct(
		EetplanRepository $eetplanRepository, EetplanBekendenRepository $eetplanBekendenRepository, WoonoordenRepository $woonoordenRepository
	) {
		$this->eetplanRepository = $eetplanRepository;
		$this->eetplanBekendenRepository = $eetplanBekendenRepository;
		$this->woonoordenRepository = $woonoordenRepository;
		$this->lichting = substr((string)LichtingenRepository::getJongsteLidjaar(), 2, 2);
	}

	/**
	 * @return TemplateView
	 * @Route("/eetplan", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function view() {
		return view('eetplan.overzicht', [
			'eetplan' => $this->eetplanRepository->getEetplan($this->lichting)
		]);
	}

	/**
	 * @param string $uid
	 * @return View
	 * @Route("/eetplan/noviet/{uid}", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function noviet($uid) {
		$eetplan = $this->eetplanRepository->getEetplanVoorNoviet($uid);
		if (!$eetplan) {
			throw new NotFoundHttpException("Geen eetplan gevonden voor deze noviet");
		}

		return view('eetplan.noviet', [
			'noviet' => ProfielRepository::get($uid),
			'eetplan' => $eetplan,
		]);
	}

	/**
	 * @param integer $id
	 * @return TemplateView
	 * @Route("/eetplan/huis/{id}", methods={"GET"}, requirements={"id": "\d+"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function huis($id) {
		$eetplan = $this->eetplanRepository->getEetplanVoorHuis($id, $this->lichting);
		if ($eetplan == []) {
			throw new CsrGebruikerException('Huis niet gevonden');
		}

		return view('eetplan.huis', [
			'woonoord' => $this->woonoordenRepository->get($id),
			'eetplan' => $eetplan,
		]);
	}

	/**
	 * @return EetplanHuizenResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/eetplan/woonoorden/toggle", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function woonoorden_toggle() {
		$selection = $this->getDataTableSelection();
		$woonoorden = [];
		foreach ($selection as $woonoord) {
			/** @var Woonoord $woonoord */
			$woonoord = $this->woonoordenRepository->retrieveByUUID($woonoord);
			$woonoord->eetplan = !$woonoord->eetplan;
			$this->woonoordenRepository->update($woonoord);
			$woonoorden[] = $woonoord;
		}
		return new EetplanHuizenResponse($woonoorden);
	}

	/**
	 * @return EetplanHuizenResponse
	 * @Route("/eetplan/woonoorden", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function woonoorden() {
		$woonoorden = $this->woonoordenRepository->findBy(['status' => GroepStatus::HT()]);
		return new EetplanHuizenResponse($woonoorden);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/eetplan/bekendehuizen", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function bekendehuizen() {
		return $this->tableData($this->eetplanRepository->getBekendeHuizen($this->lichting));
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Route("/eetplan/bekendehuizen/toevoegen", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function bekendehuizen_toevoegen(Request $request) {
		$eetplan = new Eetplan();
		$form = $this->createFormulier(
			EetplanBekendeHuizenForm::class,
			$eetplan,
			['action' => $this->generateUrl('csrdelft_eetplan_bekendehuizen_toevoegen'), 'update' => false]
		);
		$form->handleRequest($request);
		if (!$form->validate()) {
			return $form->createModalView();
		} elseif ($this->eetplanRepository->findOneBy(['noviet' => $eetplan->noviet, 'woonoord' => $eetplan->woonoord]) != null) {
			setMelding('Deze noviet is al eens op dit huis geweest', -1);
			return $form->createModalView();
		} else {
			$this->eetplanRepository->save($eetplan);
			return $this->tableData($this->eetplanRepository->getBekendeHuizen($this->lichting));
		}
	}

	/**
	 * @param Request $request
	 * @param string|null $uuid
	 * @return GenericDataTableResponse|Response
	 * @Route("/eetplan/bekendehuizen/bewerken/{uuid}", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function bekendehuizen_bewerken(Request $request, $uuid = null) {
		if (!$uuid) {
			$uuid = $this->getDataTableSelection()[0];
		}

		$eetplan = $this->eetplanRepository->retrieveByUUID($uuid);
		$form = $this->createFormulier(
			EetplanBekendeHuizenForm::class,
			$eetplan,
			['action' => $this->generateUrl('csrdelft_eetplan_bekendehuizen_bewerken', ['uuid' => $uuid]), 'update' => true],
		);
		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanRepository->save($eetplan);
			return $this->tableData($this->eetplanRepository->getBekendeHuizen($this->lichting));
		} else {
			return $form->createModalView();
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/eetplan/bekendehuizen/verwijderen", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function bekendehuizen_verwijderen() {
		$selection = $this->getDataTableSelection();
		$verwijderd = array();
		if ($selection !== false) {
			foreach ($selection as $uuid) {
				$eetplan = $this->eetplanRepository->retrieveByUUID($uuid);
				if (!$eetplan) {
					continue;
				}
				$verwijderd[] = new RemoveDataTableEntry($eetplan->id, Eetplan::class);
				$this->eetplanRepository->remove($eetplan);
			}
		}
		return $this->tableData($verwijderd);
	}

	/**
	 * @param Request $request
	 * @return EetplanHuizenZoekenResponse
	 * @Route("/eetplan/bekendehuizen/zoeken", methods={"GET"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function bekendehuizen_zoeken(Request $request) {
		$huisnaam = $request->query->get('q');
		$huisnaam = '%' . $huisnaam . '%';
		/** @var Woonoord[] $woonoorden */
		$woonoorden = $this->woonoordenRepository->createQueryBuilder('w')
			->where('w.status = :status and w.naam LIKE :naam')
			->setParameter('status', GroepStatus::HT)
			->setParameter('naam', $huisnaam)
			->getQuery()->getResult();
		return new EetplanHuizenZoekenResponse($woonoorden);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/eetplan/novietrelatie", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novietrelatie() {
		return $this->tableData($this->eetplanBekendenRepository->getBekenden($this->lichting));
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Route("/eetplan/novietrelatie/toevoegen", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novietrelatie_toevoegen(Request $request) {
		$eetplanbekenden = new EetplanBekenden();
		$form = $this->createFormulier(
			EetplanBekendenForm::class,
			$eetplanbekenden,
			['action' => $this->generateUrl('csrdelft_eetplan_novietrelatie_toevoegen'), 'update' => false]
		);
		$form->handleRequest($request);
		if (!$form->validate()) {
			return $form->createModalView();
		} elseif ($this->eetplanBekendenRepository->exists($eetplanbekenden)) {
			setMelding('Bekenden bestaan al', -1);
			return $form->createModalView();
		} else {
			$this->eetplanBekendenRepository->save($eetplanbekenden);
			return $this->tableData($this->eetplanBekendenRepository->getBekenden($this->lichting));
		}
	}

	/**
	 * @param Request $request
	 * @param $uuid
	 * @return GenericDataTableResponse|Response
	 * @Route("/eetplan/novietrelatie/bewerken/{uuid}", methods={"POST"}, defaults={"uuid": null})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novietrelatie_bewerken(Request $request, $uuid) {
		if (!$uuid) {
			$uuid = $this->getDataTableSelection()[0];
		}

		$eetplanbekenden = $this->eetplanBekendenRepository->retrieveByUUID($uuid);
		$form = $this->createFormulier(
			EetplanBekendenForm::class,
			$eetplanbekenden,
			['action' => $this->generateUrl('csrdelft_eetplan_novietrelatie_bewerken', ['uuid' => $uuid]), 'update' => true]
		);
		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanBekendenRepository->save($eetplanbekenden);
			return $this->tableData($this->eetplanBekendenRepository->getBekenden($this->lichting));
		} else {
			return $form->createModalView();
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/eetplan/novietrelatie/verwijderen", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novietrelatie_verwijderen() {
		$selection = $this->getDataTableSelection();
		$verwijderd = [];
		foreach ($selection as $uuid) {
			$bekenden = $this->eetplanBekendenRepository->retrieveByUUID($uuid);
			$verwijderd[] = new RemoveDataTableEntry($bekenden->id, EetplanBekenden::class);
			$this->eetplanBekendenRepository->remove($bekenden);
		}
		return $this->tableData($verwijderd);
	}

	/**
	 * Beheerpagina.
	 *
	 * POST een json body om dingen te doen.
	 * @Route("/eetplan/beheer", methods={"GET", "POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function beheer() {
		return view('eetplan.beheer', [
			'bekendentable' => new EetplanBekendenTable(),
			'huizentable' => new EetplanHuizenTable(),
			'bekendehuizentable' => new EetplanBekendeHuizenTable(),
			'eetplan' => $this->eetplanRepository->getEetplan($this->lichting)
		]);
	}

	/**
	 * @return NieuwEetplanForm|TemplateView
	 * @Route("/eetplan/nieuw", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function nieuw() {
		$form = new NieuwEetplanForm();

		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanRepository->avondHasEetplan(date_create_immutable($form->getValues()['avond']))) {
			setMelding('Er bestaat al een eetplan met deze datum', -1);
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$eetplan = $this->eetplanRepository->maakEetplan($avond, $this->lichting);

			foreach ($eetplan as $sessie) {
				$this->eetplanRepository->save($sessie);
			}

			return view('eetplan.table', ['eetplan' => $this->eetplanRepository->getEetplan($this->lichting)]);
		}
	}

	/**
	 * @return VerwijderEetplanForm|TemplateView
	 * @Route("/eetplan/verwijderen", methods={"POST"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function verwijderen() {
		$avonden = $this->eetplanRepository->getAvonden($this->lichting);
		$form = new VerwijderEetplanForm($avonden);

		if (!$form->validate()) {
			return $form;
		} else {
			$avond = date_create_immutable($form->getValues()['avond']);
			$this->eetplanRepository->verwijderEetplan($avond, $this->lichting);

			return view('eetplan.table', ['eetplan' => $this->eetplanRepository->getEetplan($this->lichting)]);
		}
	}
}
