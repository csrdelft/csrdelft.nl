<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\AbstractGroepLid;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\ActiviteitSoort;
use CsrDelft\entity\groepen\GroepStatus;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\repository\AbstractGroepLedenRepository;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\groepen\formulier\GroepConverteerForm;
use CsrDelft\view\groepen\formulier\GroepForm;
use CsrDelft\view\groepen\formulier\GroepLidBeheerForm;
use CsrDelft\view\groepen\formulier\GroepLogboekForm;
use CsrDelft\view\groepen\formulier\GroepOpvolgingForm;
use CsrDelft\view\groepen\formulier\GroepPreviewForm;
use CsrDelft\view\groepen\GroepenBeheerTable;
use CsrDelft\view\groepen\GroepenDeelnameGrafiek;
use CsrDelft\view\groepen\GroepenView;
use CsrDelft\view\groepen\GroepView;
use CsrDelft\view\groepen\leden\GroepEetwensView;
use CsrDelft\view\groepen\leden\GroepEmailsView;
use CsrDelft\view\groepen\leden\GroepLedenTable;
use CsrDelft\view\groepen\leden\GroepLijstView;
use CsrDelft\view\groepen\leden\GroepOmschrijvingView;
use CsrDelft\view\groepen\leden\GroepPasfotosView;
use CsrDelft\view\groepen\leden\GroepStatistiekView;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * AbstractGroepenController.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
abstract class AbstractGroepenController extends AbstractController implements RouteLoaderInterface {
	/** @var DataTable */
	protected $table;
	/** @var AbstractGroepenRepository */
	protected $model;
	/**
	 * @var ChangeLogRepository
	 */
	private $changeLogRepository;

	public function __construct($repository = null) {
		$this->model = $repository;
		$this->changeLogRepository = ContainerFacade::getContainer()->get(ChangeLogRepository::class);
	}

	/**
	 * Alle routes die groepen controllers aan gaan @return RouteCollection
	 * @see config/routes/groepen.yaml
	 */
	public function loadRoutes() {
		$routes = new RouteCollection();
		$prefix = 'groep-' . $this->model::getNaam();

		$className = get_class($this);

		$route = function ($path, $func, $methods, $defaults = [], $requirements = [], $overrideName = null) use ($routes, $prefix, $className) {
			$routes->add(
				$prefix . '-' . ($overrideName ?? $func),
				(new Route($path))
					->setDefaults($defaults + [
							'_mag' => P_LOGGED_IN,
							'_controller' => $className . '::' . $func,
						])
					->setRequirements($requirements)
					->setMethods($methods)
			);
		};

		// Let op, als je meerdere routes naar dezelfde functie hebt, gebruik dan overrideName om de naam van de route goed te zetten.
		$route('', 'overzicht', ['GET'], [], [], 'main');
		$route('beheren/{soort}', 'beheren', ['GET', 'POST'], ['soort' => null]);
		$route('overzicht/{soort}', 'overzicht', ['GET'], ['soort' => null]);
		$route('{id}/verwijderen', 'verwijderen', ['POST']);
		$route('zoeken/{zoekterm}', 'zoeken', ['GET'], ['zoekterm' => null]);
		$route('nieuw/{soort}', 'nieuw', ['GET', 'POST'], ['soort' => null]);
		$route('{id}/ketzer/afmelden', 'ketzer_afmelden', ['POST']);
		$route('{id}/ketzer/aanmelden', 'ketzer_aanmelden', ['POST']);
		$route('{id}/ketzer/bewerken', 'ketzer_bewerken', ['POST']);
		$route('{id}/nieuw/{soort}', 'nieuw', ['GET', 'POST'], ['soort' => null], [], 'nieuw-met-id');
		$route('{id}/deelnamegrafiek', 'deelnamegrafiek', ['POST']);
		$route('{id}/omschrijving', 'omschrijving', ['POST']);
		$route('{id}/pasfotos', 'pasfotos', ['POST']);
		$route('{id}/lijst', 'lijst', ['POST']);
		$route('{id}/stats', 'stats', ['POST']);
		$route('{id}/emails', 'emails', ['POST']);
		$route('{id}/eetwens', 'eetwens', ['POST']);
		$route('{id}/aanmelden', 'aanmelden', ['POST'], [], ['uid' => '.{4}']);
		$route('{id}/aanmelden2/{uid}', 'aanmelden2', ['POST'], [], ['uid' => '.{4}']);
		$route('{id}/naar_ot/{uid}', 'naar_ot', ['POST'], ['uid' => null], ['uid' => '.{4}']);
		$route('{id}/bewerken/{uid}', 'bewerken', ['POST'], ['uid' => null], ['uid' => '.{4}']);
		$route('{id}/afmelden/{uid}', 'afmelden', ['POST'], [], ['uid' => '.{4}']);
		$route('{id}/leden', 'leden', ['GET', 'POST']);
		$route('{id}/wijzigen', 'wijzigen', ['GET', 'POST'], ['id' => null]);
		$route('{id}/logboek', 'logboek', ['GET', 'POST'], ['id' => null]);
		$route('aanmaken/{soort}', 'aanmaken', ['GET', 'POST'], ['id' => null, 'soort' => null]);
		$route('{id}/aanmaken/{soort}', 'aanmaken', ['GET', 'POST'], ['soort' => null], [], 'aanmaken-met-id');
		$route('{id}/opvolging', 'opvolging', ['POST']);
		$route('{id}/converteren', 'converteren', ['POST']);
		$route('{id}/sluiten', 'sluiten', ['POST']);
		$route('{id}/voorbeeld', 'voorbeeld', ['POST']);
		$route('{id}', 'bekijken', ['GET']);

		$routes->addPrefix('groepen/' . $this->model::getNaam());
		return $routes;
	}

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->findBy(['status' => GroepStatus::HT(), 'soort' => $soort]);
		} else {
			$groepen = $this->model->findBy(['status' => GroepStatus::HT()]);
		}
		$body = new GroepenView($this->model, $groepen, $soort); // controleert rechten bekijken per groep
		return view('default', ['content' => $body]);
	}

	public function bekijken($id) {
		$groep = $this->model->get($id);
		$groepen = $this->model->findBy(['familie' => $groep->familie], ['begin_moment' => 'DESC']);
		if ($groep instanceof HeeftSoort) {
			$soort = $groep->getSoort();
		} else {
			$soort = null;
		}
		$body = new GroepenView($this->model, $groepen, $soort, $groep->id); // controleert rechten bekijken per groep
		return view('default', ['content' => $body]);
	}

	public function deelnamegrafiek($id) {
		$groep = $this->model->get($id);
		/** @var AbstractGroep[] $groepen */
		$groepen = $this->model->findBy(['familie' => $groep->familie]);
		return new GroepenDeelnameGrafiek($groepen); // controleert GEEN rechten bekijken
	}

	public function omschrijving($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepOmschrijvingView($groep);
	}

	public function pasfotos($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepPasfotosView($groep);
	}

	public function lijst($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepLijstView($groep);
	}

	public function stats($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}

		$statistieken = $this->model->getStatistieken($groep);

		return new GroepStatistiekView($groep, $statistieken);
	}

	public function emails($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepEmailsView($groep);
	}

	public function eetwens($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepEetwensView($groep);
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$zoekterm = '%' . $zoekterm . '%';
		$limit = 5;
		if ($request->query->has('limit')) {
			$limit = $request->query->getInt('limit');
		}
		$result = [];
		/** @var AbstractGroep $groepen */
		$groepen = $this->model->createQueryBuilder('g')
			->where('g.familie LIKE :familie and (g.status = :ht or g.status = :ft)')
			->setParameter('familie', $zoekterm)
			->setParameter('ht', GroepStatus::HT())
			->setParameter('ft', GroepStatus::FT())
			->setMaxResults($limit)
			->getQuery()->getResult();
		foreach ($groepen as $groep) {
			/** @var AbstractGroep $groep */
			if (!isset($result[$groep->familie])) {
				$type = classNameZonderNamespace(get_class($groep));
				$result[$groep->familie] = [
					'url' => $groep->getUrl() . '#' . $groep->id,
					'label' => 'Groepen',
					'value' => $type . ': ' . $groep->familie,
					'icon' => Icon::getTag($type),
				];
			}
		}
		return new JsonResponse($result);
	}

	/**
	 * @param Request $request
	 * @param null $id
	 * @param null $soort
	 * @return GroepForm|GroepPreviewForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function nieuw(Request $request, $id = null, $soort = null) {
		return $this->aanmaken($request, $id, $soort);
	}

	/**
	 * @param Request $request
	 * @param null $id
	 * @param null $soort
	 * @return GenericDataTableResponse|GroepForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmaken(Request $request, $id = null, $soort = null) {
		if (!$id) {
			$old = null;
			$groep = $this->model->nieuw($soort);
			/**
			 * @var Profiel $profiel
			 */
			$profiel = LoginService::getProfiel();
			if ($groep instanceof Activiteit && empty($groep->rechten_aanmelden)) {
				switch ($groep->soort) {

					case ActiviteitSoort::Lichting:
						$groep->rechten_aanmelden = 'Lichting:' . $profiel->lidjaar;
						break;

					case ActiviteitSoort::Verticale:
						$groep->rechten_aanmelden = 'Verticale:' . $profiel->verticale;
						break;

					case ActiviteitSoort::Kring:
						$kring = $profiel->getKring();
						if ($kring) {
							$groep->rechten_aanmelden = 'Kring:' . $kring->verticale . '.' . $kring->kring_nummer;
						}
						break;
				}
			}
		} // opvolger
		else {
			/** @var AbstractGroep $old */
			$old = $this->model->retrieveByUUID($id);
			if (!$old) {
				throw new CsrToegangException();
			}
			if (property_exists($old, 'soort')) {
				$soort = $old->soort;
			}
			$groep = $this->model->nieuw($soort);
			$groep->naam = $old->naam;
			$groep->familie = $old->familie;
			$groep->samenvatting = $old->samenvatting;
			$groep->omschrijving = $old->omschrijving;
			if (property_exists($old, 'rechten_aanmelden')) {
				$groep->rechten_aanmelden = $old->rechten_aanmelden;
			}
		}
		$form = new GroepForm($groep, $this->model->getUrl() . '/aanmaken', AccessAction::Aanmaken); // checks rechten aanmaken
		if ($request->getMethod() == 'GET') {
			$this->beheren($request);
			$form->setDataTableId($this->table->getDataTableId());
			return view('default', ['content' => $this->table, 'modal' => $form]);
		} elseif ($form->validate()) {
			$this->changeLogRepository->log($groep, 'create', null, print_r($groep, true));
			$this->model->create($groep);
			$response[] = $groep;
			if ($old) {
				$old->status = GroepStatus::OT;
				$this->model->update($old);
				$response[] = $old;
			}
			$view = $this->tableData($response);
			setMelding(get_class($groep) . ' succesvol aangemaakt!', 1);
			$form = new GroepPreviewForm($groep);
			$view->modal = $form->getHtml();
			return $view;
		} else {
			return $form;
		}
	}

	public function beheren(Request $request, $soort = null) {
		if ($request->getMethod() == 'POST') {
			if ($soort) {
				$groepen = $this->model->findBy(['soort' => $soort], ['begin_moment' => 'DESC']);
			} else {
				$groepen = $this->model->findAll();
			}
			return $this->tableData($groepen);
		} else {
			$table = new GroepenBeheerTable($this->model);
			$this->table = $table;
			return view('default', ['content' => $table]);
		}
	}

	/**
	 * @param Request $request
	 * @param null $id
	 * @return GenericDataTableResponse|GroepForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function wijzigen(Request $request, $id = null) {
		if ($id) {
			$groep = $this->model->get($id);
			if (!$groep->mag(AccessAction::Wijzigen)) {
				throw new CsrToegangException();
			}
			$form = new GroepForm($groep, $groep->getUrl() . '/wijzigen', AccessAction::Wijzigen); // checks rechten wijzigen
			if ($request->getMethod() == 'GET') {
				$this->beheren($request);
				$this->table->filter = $groep->naam;
				$form->setDataTableId($this->table->getDataTableId());
				return view('default', ['content' => $this->table, 'modal' => $form]);
			} elseif ($form->validate()) {
				$this->changeLogRepository->logChanges($form->diff());
				$this->model->update($groep);
				return $this->tableData([$groep]);
			} else {
				return $form;
			}
		} // beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new CsrToegangException();
			}
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($selection[0]);
			if (!$groep || !$groep->mag(AccessAction::Wijzigen)) {
				throw new CsrToegangException();
			}
			$form = new GroepForm($groep, $groep->getUrl() . '/wijzigen', AccessAction::Wijzigen); // checks rechten wijzigen
			if ($form->validate()) {
				$this->changeLogRepository->logChanges($form->diff());
				$this->model->update($groep);
				return $this->tableData([$groep]);
			} else {
				return $form;
			}
		}
	}

	/**
	 * @param SerializerInterface $serializer
	 * @param $id
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderen(SerializerInterface $serializer, $id) {
		$response = [];
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($id);
		if ($groep && $groep->mag(AccessAction::Verwijderen) && count($groep->getLeden()) === 0) {
			$old = $serializer->serialize($groep, 'json', [
				AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($obj) { return $obj->id ?? ""; },
				AbstractNormalizer::IGNORED_ATTRIBUTES => ['familieSuggesties'],
			]);
			$this->changeLogRepository->log($groep, 'delete', $old, null);
			$response[] = new RemoveDataTableEntry($groep->id, get_class($groep));
			$this->model->delete($groep);
		}
		return $this->tableData($response);
	}

	/**
	 * @param $id
	 * @return GenericDataTableResponse|GroepOpvolgingForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function opvolging($id) {
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($id);
		$form = new GroepOpvolgingForm($groep, $this->model->getUrl() . '/opvolging');
		if ($form->validate()) {
			$values = $form->getValues();
			$response = [];
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($id);
			if ($groep and $groep->mag(AccessAction::Opvolging)) {
				$this->changeLogRepository->log($groep, 'familie', $groep->familie, $values['familie']);
				$this->changeLogRepository->log($groep, 'status', $groep->status, $values['status']);
				$groep->familie = $values['familie'];
				$groep->status = $values['status'];
				$this->model->update($groep);
				$response[] = $groep;
			}
			return $this->tableData($response);
		} else {
			return $form;
		}
	}

	/**
	 * @param $id
	 * @return GenericDataTableResponse|GroepConverteerForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function converteren($id) {
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($id);
		$form = new GroepConverteerForm($groep, $this->model);
		if ($form->validate()) {
			$values = $form->getValues();
			/** @var AbstractGroepenRepository $model */
			$model = ContainerFacade::getContainer()->get($values['model']);
			$converteer = get_class($model) !== get_class($this->model);
			$response = [];
			$groep = $this->model->retrieveByUUID($id);
			if ($groep and $groep->mag(AccessAction::Wijzigen)) {
				if ($converteer) {
					$this->changeLogRepository->log($groep, 'class', get_class($groep), $model->entityClass);
					$nieuw = $model->converteer($groep, $this->model, $values['soort']);
					if ($nieuw) {
						$response[] = new RemoveDataTableEntry($groep->id, get_class($groep));
						$response[] = $groep;
					}
				} elseif ($groep instanceof HeeftSoort) {
					$this->changeLogRepository->log($groep, 'soort', $groep->getSoort(), $values['soort']);
					$groep->setSoort($values['soort']);
					$this->model->update($groep);
					$response[] = $groep;
				}
			}
			return $this->tableData($response);
		} else {
			return $form;
		}
	}

	/**
	 * @param $id
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function sluiten($id) {
		$response = [];
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($id);
		if ($groep and property_exists($groep, 'aanmelden_tot') && date_create_immutable() <= $groep->aanmelden_tot && $groep->mag(AccessAction::Wijzigen)) {
			$this->changeLogRepository->log($groep, 'aanmelden_tot', $groep->aanmelden_tot, date_create_immutable());
			$groep->aanmelden_tot = date_create_immutable();
			$this->model->update($groep);
			$response[] = $groep;
		}
		return $this->tableData($response);
	}

	public function voorbeeld($id) {
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($id);
		if (!$groep or !$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepPreviewForm($groep);
	}

	/**
	 * @param Request $request
	 * @param $id
	 * @return GenericDataTableResponse|GroepLogboekForm
	 */
	public function logboek(Request $request, $id) {
		// data request
		if ($request->getMethod() == 'POST') {
			$groep = $this->model->get($id);
			if (!$groep->mag(AccessAction::Bekijken)) {
				throw new CsrToegangException();
			}
			$data = $this->changeLogRepository->findBy(['subject' => $groep->getUUID()]);
			return $this->tableData($data);
		} // popup request
		else {
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($id);
			if (!$groep || !$groep->mag(AccessAction::Bekijken)) {
				throw new CsrToegangException('Kan logboek niet vinden');
			}
			return new GroepLogboekForm($groep);
		}
	}

	public function leden(Request $request, $id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		if ($request->getMethod() == 'POST') {
			return $this->tableData($groep->getLeden());
		} else {
			return new GroepLedenTable($groep);
		}
	}

	/*
	 * Voor groepen V2
	 */
	public function aanmelden2(Request $request, EntityManagerInterface $em, $id, $uid) {
		$groep = $this->model->get($id);
		$model = $em->getRepository($groep->getLidType());

		if (!$groep->mag(AccessAction::Aanmelden)) {
			throw new CsrToegangException();
		}
		$lid = $model->nieuw($groep, $uid);

		$opmerking = $request->request->get('opmerking2');

		$keuzes = [];
		foreach ($opmerking as $keuze) {
			$keuzes[] = new GroepKeuzeSelectie($keuze['naam'], $keuze['selectie']);
		}

		if (!$groep->valideerOpmerking($keuzes)) {
			throw new CsrGebruikerException('');
		}

		$lid->opmerking2 = $keuzes;

		$this->changeLogRepository->log($groep, 'aanmelden', null, $lid->uid);
		$model->save($lid);

		return new JsonResponse(['success' => true]);
	}

	public function ketzer_aanmelden(EntityManagerInterface $em, $id) {
		$uid = LoginService::getUid();
		$groep = $this->model->get($id);

		if (!$groep->mag(AccessAction::Aanmelden)) {
			throw new CsrToegangException();
		}

		/** @var AbstractGroepLedenRepository $repository */
		$repository = $em->getRepository($groep->getLidType());
		$lid = $repository->nieuw($groep, $uid);

		$form = new GroepAanmeldenForm($lid, $groep);

		if ($form->validate()) {
			$this->changeLogRepository->log($groep, 'aanmelden', null, $lid->uid);
			$em->persist($lid);
			$em->flush();
			return new GroepPasfotosView($groep);
		} else {
			return $form;
		}
	}

	public function aanmelden(EntityManagerInterface $em, $id) {
		$groep = $this->model->get($id);
		/** @var AbstractGroepLedenRepository $model */
		$model = $em->getRepository($groep->getLidType());

		if (!$groep->mag(AccessAction::Beheren)) {
			throw new CsrToegangException();
		}

		/** @var AbstractGroepLid $lid */
		$lid = $model->nieuw($groep, null);
		$lid->groep = $groep;
		$lid->groep_id = $groep->id;
		$leden = group_by_distinct('uid', $groep->getLeden());
		$form = new GroepLidBeheerForm($lid, $groep->getUrl() . '/aanmelden', array_keys($leden));

		if ($form->validate()) {
			$this->changeLogRepository->log($groep, 'aanmelden', null, $lid->uid);
			$lid->profiel = ProfielRepository::get($lid->uid);
			$model->save($lid);
			return $this->tableData([$lid]);
		} else {
			return $form;
		}
	}

	public function ketzer_bewerken(EntityManagerInterface $em, $id) {
		$uid = LoginService::getUid();
		$groep = $this->model->get($id);

		if (!$groep->mag(AccessAction::Bewerken)) {
			throw new CsrToegangException();
		}
		$lid = $groep->getLid($uid);
		$form = new GroepBewerkenForm($lid, $groep);

		if ($form->validate()) {
			$this->changeLogRepository->logChanges($form->diff());
			$em->persist($lid);
			$em->flush();
		}

		return $form;
	}

	public function bewerken(EntityManagerInterface $em, $id, $uid = null) {
		$groep = $this->model->get($id);

		if (!$uid) {
			$uid = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);
		}

		$lid = $groep->getLid($uid);

		if (!$lid) {
			throw new CsrToegangException();
		}

		if (!$groep->mag(AccessAction::Beheren)) {
			throw new CsrToegangException();
		}

		$form = new GroepLidBeheerForm($lid, $groep->getUrl() . '/bewerken');

		if ($form->validate()) {
			$this->changeLogRepository->logChanges($form->diff());
			$em->persist($lid);
			$em->flush();
			return $this->tableData([$lid]);
		} else {
			return $form;
		}
	}

	public function ketzer_afmelden(EntityManagerInterface $em, $id) {
		$uid = LoginService::getUid();
		$groep = $this->model->get($id);

		if (!$groep->mag(AccessAction::Afmelden) && !$groep->mag(AccessAction::Beheren)) { // A::Beheren voor afmelden via context-menu
			throw new CsrToegangException();
		}

		$lid = $groep->getLid($uid);

		if (!$lid) {
			throw new CsrToegangException('Niet aangemeld');
		}

		$this->changeLogRepository->log($groep, 'afmelden', $lid->uid, null);
		$em->remove($lid);
		$em->flush();

		return new GroepView($groep);
	}

	public function afmelden(EntityManagerInterface $em, $id, $uid) {
		$groep = $this->model->get($id);

		if (!$groep->mag(AccessAction::Beheren)) {
			throw new CsrToegangException();
		}

		$lid = $groep->getLid($uid);
		$this->changeLogRepository->log($groep, 'afmelden', $lid->uid, null);
		$response = new RemoveDataTableEntry(['groep_id' => $id, 'uid' => $uid], get_class($lid));
		$em->remove($lid);
		$em->flush();

		return $this->tableData([$response]);
	}

	public function naar_ot(EntityManagerInterface $em, $id, $uid = null) {
		$groep = $this->model->get($id);

		// Vind de groep uit deze familie met het laatste eind_moment
		$ot_groep_statement = $this->model->findOneBy(["familie" => $groep->familie, 'status' => 'ot'], ['eind_moment', 'DESC']);

		if ($ot_groep_statement) {
			throw new CsrGebruikerException('Geen o.t. groep gevonden');
		}

		/** @var AbstractGroep $ot_groep */
		$ot_groep = $ot_groep_statement;

		if ($uid) {
			if ($ot_groep->getLid($uid)) {
				throw new CsrGebruikerException('Lid al onderdeel van o.t. groep');
			}
			if (!$groep->mag(AccessAction::Afmelden) and !$groep->mag(AccessAction::Beheren) and !$ot_groep->mag(AccessAction::Aanmelden)) { // A::Beheren voor afmelden via context-menu
				throw new CsrGebruikerException();
			}
			$em->transactional(function () use ($groep, $ot_groep, $uid, $em) {
				$lid = $groep->getLid($uid);
				$this->changeLogRepository->log($groep, 'afmelden', $lid->uid, null);
				$this->changeLogRepository->log($ot_groep, 'aanmelden', $lid->uid, null);
				$em->remove($lid);
				$em->flush();
				$lid->groep_id = $ot_groep->id;
				$em->persist($lid);
				$em->flush();
				$lid->groep_id = $groep->id; // Terugspelen naar gebruiker dat dit lid is verwijderd
			});
			return new GroepView($groep);
		} else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new CsrGebruikerException();
			}

			$response = $em->transactional(function () use ($selection, $groep, $ot_groep, $em) {
				$response = [];
				foreach ($selection as $UUID) {
					if (!$groep->mag(AccessAction::Beheren)) {
						throw new CsrGebruikerException();
					}

					[$id, $rest] = explode("@", $UUID);
					[$groep_id, $uid] = explode(".", $id);

					$lid = $groep->getLid($uid);

					if ($ot_groep->getLid($lid->uid)) {
						throw new CsrGebruikerException('Lid al onderdeel van o.t. groep');
					}

					$this->changeLogRepository->log($groep, 'afmelden', $lid->uid, null);
					$this->changeLogRepository->log($ot_groep, 'aanmelden', $lid->uid, null);
					$em->remove($lid);
					$em->flush();
					$lid->groep_id = $ot_groep->id;
					$lid->lid_sinds = getDateTime();
					$lid->door_uid = LoginService::getUid();
					$em->persist($lid);
					$em->flush();
					$lid->groep_id = $groep->id;

					$response[] = new RemoveDataTableEntry(['groep_id' => $groep->id, 'uid' => $lid->uid], get_class($lid));
				}

				return $response;
			});
			return $this->tableData($response);
		}

	}

}
