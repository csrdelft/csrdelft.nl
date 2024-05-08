<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\repository\eetplan\EetplanBekendenRepository;
use CsrDelft\repository\eetplan\EetplanRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\EetplanService;
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
class EetplanController extends AbstractController
{
	/** @var string */
	private $lidjaar;
	/** @var EetplanService */
	private $eetplanService;
	/** @var EetplanRepository */
	private $eetplanRepository;
	/** @var EetplanBekendenRepository */
	private $eetplanBekendenRepository;
	/** @var WoonoordenRepository */
	private $woonoordenRepository;

	public function __construct(
		EetplanService $eetplanService,
		EetplanRepository $eetplanRepository,
		EetplanBekendenRepository $eetplanBekendenRepository,
		WoonoordenRepository $woonoordenRepository
	) {
		$this->eetplanService = $eetplanService;
		$this->eetplanRepository = $eetplanRepository;
		$this->eetplanBekendenRepository = $eetplanBekendenRepository;
		$this->woonoordenRepository = $woonoordenRepository;
		$this->lidjaar = LichtingenRepository::getJongsteLidjaar();
	}

	/**
  * @return Response
  * @Auth(P_LEDEN_READ)
  */
 #[Route(path: '/eetplan', methods: ['GET'])]
 public function view(): Response
	{
		return $this->render('eetplan/overzicht.html.twig', [
			'eetplan' => $this->eetplanRepository->getEetplan($this->lidjaar),
		]);
	}

	/**
  * @param string $uid
  * @return Response
  * @Auth(P_LEDEN_READ)
  */
 #[Route(path: '/eetplan/noviet/{uid}', methods: ['GET'], requirements: ['uid' => '.{4}'])]
 public function noviet(string $uid): Response
	{
		$eetplan = $this->eetplanRepository->getEetplanVoorNoviet($uid);
		if (!$eetplan) {
			throw new NotFoundHttpException('Geen eetplan gevonden voor deze noviet');
		}

		return $this->render('eetplan/noviet.html.twig', [
			'noviet' => ProfielRepository::get($uid),
			'eetplan' => $eetplan,
		]);
	}

	/**
  * @param integer $id
  * @return Response
  * @Auth(P_LEDEN_READ)
  */
 #[Route(path: '/eetplan/huis/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
 public function huis(int $id): Response
	{
		$eetplan = $this->eetplanRepository->getEetplanVoorHuis(
			$id,
			$this->lidjaar
		);
		if ($eetplan == []) {
			throw new CsrGebruikerException('Huis niet gevonden');
		}

		return $this->render('eetplan/huis.html.twig', [
			'woonoord' => $this->woonoordenRepository->get($id),
			'eetplan' => $eetplan,
		]);
	}

	/**
  * @return EetplanHuizenResponse
  * @throws ORMException
  * @throws OptimisticLockException
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/woonoorden/toggle', methods: ['POST'])]
 public function woonoorden_toggle(): EetplanHuizenResponse
	{
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
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/woonoorden', methods: ['POST'])]
 public function woonoorden(): EetplanHuizenResponse
	{
		$woonoorden = $this->woonoordenRepository->findBy([
			'status' => GroepStatus::HT(),
		]);
		return new EetplanHuizenResponse($woonoorden);
	}

	/**
  * @return GenericDataTableResponse
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/bekendehuizen', methods: ['POST'])]
 public function bekendehuizen(): GenericDataTableResponse
	{
		return $this->tableData(
			$this->eetplanRepository->getBekendeHuizen($this->lidjaar)
		);
	}

	/**
  * @param Request $request
  * @return GenericDataTableResponse|Response
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/bekendehuizen/toevoegen', methods: ['POST'])]
 public function bekendehuizen_toevoegen(Request $request)
	{
		$eetplan = new Eetplan();
		$form = $this->createFormulier(EetplanBekendeHuizenForm::class, $eetplan, [
			'action' => $this->generateUrl(
				'csrdelft_eetplan_bekendehuizen_toevoegen'
			),
			'update' => false,
		]);
		$form->handleRequest($request);
		if (!$form->validate()) {
			return new Response($form->createModalView());
		} elseif (
			$this->eetplanRepository->findOneBy([
				'noviet' => $eetplan->noviet,
				'woonoord' => $eetplan->woonoord,
			]) != null
		) {
			$this->addFlash(
				FlashType::ERROR,
				'Deze noviet is al eens op dit huis geweest'
			);
			return new Response($form->createModalView());
		} else {
			$this->eetplanRepository->save($eetplan);
			return $this->tableData(
				$this->eetplanRepository->getBekendeHuizen($this->lidjaar)
			);
		}
	}

	/**
  * @param Request $request
  * @param string|null $uuid
  * @return GenericDataTableResponse|Response
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/bekendehuizen/bewerken/{uuid}', methods: ['POST'])]
 public function bekendehuizen_bewerken(Request $request, $uuid = null)
	{
		if (!$uuid) {
			$uuid = $this->getDataTableSelection()[0];
		}

		$eetplan = $this->eetplanRepository->retrieveByUUID($uuid);
		$form = $this->createFormulier(EetplanBekendeHuizenForm::class, $eetplan, [
			'action' => $this->generateUrl(
				'csrdelft_eetplan_bekendehuizen_bewerken',
				['uuid' => $uuid]
			),
			'update' => true,
		]);
		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanRepository->save($eetplan);
			return $this->tableData(
				$this->eetplanRepository->getBekendeHuizen($this->lidjaar)
			);
		} else {
			return new Response($form->createModalView());
		}
	}

	/**
  * @return GenericDataTableResponse
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/bekendehuizen/verwijderen', methods: ['POST'])]
 public function bekendehuizen_verwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$verwijderd = [];
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
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/bekendehuizen/zoeken', methods: ['GET'])]
 public function bekendehuizen_zoeken(
		Request $request
	): EetplanHuizenZoekenResponse {
		$huisnaam = $request->query->get('q');
		$huisnaam = '%' . $huisnaam . '%';
		/** @var Woonoord[] $woonoorden */
		$woonoorden = $this->woonoordenRepository
			->createQueryBuilder('w')
			->where('w.status = :status and w.naam LIKE :naam')
			->setParameter('status', GroepStatus::HT)
			->setParameter('naam', $huisnaam)
			->getQuery()
			->getResult();
		return new EetplanHuizenZoekenResponse($woonoorden);
	}

	/**
  * @return GenericDataTableResponse
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/novietrelatie', methods: ['POST'])]
 public function novietrelatie(): GenericDataTableResponse
	{
		return $this->tableData(
			$this->eetplanBekendenRepository->getBekendenVoorLidjaar($this->lidjaar)
		);
	}

	/**
  * @param Request $request
  * @return GenericDataTableResponse|Response
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/novietrelatie/toevoegen', methods: ['POST'])]
 public function novietrelatie_toevoegen(Request $request)
	{
		$eetplanbekenden = new EetplanBekenden();
		$form = $this->createFormulier(
			EetplanBekendenForm::class,
			$eetplanbekenden,
			[
				'action' => $this->generateUrl(
					'csrdelft_eetplan_novietrelatie_toevoegen'
				),
				'update' => false,
			]
		);
		$form->handleRequest($request);
		if (!$form->validate()) {
			return new Response($form->createModalView());
		} elseif ($this->eetplanBekendenRepository->exists($eetplanbekenden)) {
			$this->addFlash(FlashType::ERROR, 'Bekenden bestaan al');
			return new Response($form->createModalView());
		} else {
			$this->eetplanBekendenRepository->save($eetplanbekenden);
			return $this->tableData(
				$this->eetplanBekendenRepository->getBekendenVoorLidjaar($this->lidjaar)
			);
		}
	}

	/**
  * @param Request $request
  * @param $uuid
  * @return GenericDataTableResponse|Response
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/novietrelatie/bewerken/{uuid}', methods: ['POST'], defaults: ['uuid' => null])]
 public function novietrelatie_bewerken(Request $request, $uuid)
	{
		if (!$uuid) {
			$uuid = $this->getDataTableSelection()[0];
		}

		$eetplanbekenden = $this->eetplanBekendenRepository->retrieveByUUID($uuid);
		$form = $this->createFormulier(
			EetplanBekendenForm::class,
			$eetplanbekenden,
			[
				'action' => $this->generateUrl(
					'csrdelft_eetplan_novietrelatie_bewerken',
					['uuid' => $uuid]
				),
				'update' => true,
			]
		);
		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanBekendenRepository->save($eetplanbekenden);
			return $this->tableData(
				$this->eetplanBekendenRepository->getBekendenVoorLidjaar($this->lidjaar)
			);
		} else {
			return new Response($form->createModalView());
		}
	}

	/**
  * @return GenericDataTableResponse
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/novietrelatie/verwijderen', methods: ['POST'])]
 public function novietrelatie_verwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$verwijderd = [];
		foreach ($selection as $uuid) {
			$bekenden = $this->eetplanBekendenRepository->retrieveByUUID($uuid);
			$verwijderd[] = new RemoveDataTableEntry(
				$bekenden->id,
				EetplanBekenden::class
			);
			$this->eetplanBekendenRepository->remove($bekenden);
		}
		return $this->tableData($verwijderd);
	}

	/**
  * Beheerpagina.
  *
  * POST een json body om dingen te doen.
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/beheer', methods: ['GET', 'POST'])]
 public function beheer(): Response
	{
		return $this->render('eetplan/beheer.html.twig', [
			'bekendentable' => new EetplanBekendenTable(),
			'huizentable' => $this->createDataTable(
				EetplanHuizenTable::class
			)->createView(),
			'bekendehuizentable' => new EetplanBekendeHuizenTable(),
			'eetplan' => $this->eetplanRepository->getEetplan($this->lidjaar),
		]);
	}

	/**
  * @return NieuwEetplanForm|Response
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/nieuw', methods: ['POST'])]
 public function nieuw()
	{
		$form = new NieuwEetplanForm();

		if (!$form->validate()) {
			return $form;
		} elseif (
			$this->eetplanRepository->avondHasEetplan(
				date_create_immutable($form->getValues()['avond'])
			)
		) {
			$this->addFlash(
				FlashType::ERROR,
				'Er bestaat al een eetplan met deze datum'
			);
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$eetplan = $this->eetplanService->maakEetplan($avond, $this->lidjaar);

			foreach ($eetplan as $sessie) {
				$this->eetplanRepository->save($sessie);
			}

			return $this->render('eetplan/table.html.twig', [
				'eetplan' => $this->eetplanRepository->getEetplan($this->lidjaar),
			]);
		}
	}

	/**
  * @return VerwijderEetplanForm|Response
  * @Auth({P_ADMIN,"commissie:NovCie"})
  */
 #[Route(path: '/eetplan/verwijderen', methods: ['POST'])]
 public function verwijderen()
	{
		$avonden = $this->eetplanRepository->getAvonden($this->lidjaar);
		$form = new VerwijderEetplanForm($avonden);

		if (!$form->validate()) {
			return $form;
		} else {
			$avond = date_create_immutable($form->getValues()['avond']);
			$this->eetplanRepository->verwijderEetplan($avond, $this->lidjaar);

			return $this->render('eetplan/table.html.twig', [
				'eetplan' => $this->eetplanRepository->getEetplan($this->lidjaar),
			]);
		}
	}
}
