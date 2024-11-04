<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Security\Voter\Entity\GroepLidVoter;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\groepen\enum\HuisStatus;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldRechten;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\GroepRepository;
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
use CsrDelft\view\groepen\leden\GroepPasfotosView;
use CsrDelft\view\groepen\leden\GroepStatistiekView;
use CsrDelft\view\Icon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Algemene controller voor groepen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
abstract class AbstractGroepenController extends AbstractController implements
	RouteLoaderInterface
{
	/** @var DataTable */
	protected $table;
	/** @var GroepRepository */
	protected $repository;
	/** @var ChangeLogRepository */
	private $changeLogRepository;
	/** @var GroepLidRepository */
	private $groepLidRepository;

	public function __construct(ManagerRegistry $registry)
	{
		$this->repository = $registry->getRepository($this->getGroepType());
		$this->changeLogRepository = $registry->getRepository(
			ChangeLogEntry::class
		);
		$this->groepLidRepository = $registry->getRepository(GroepLid::class);
	}

	/**
	 * @return Groep|string
	 */
	abstract public function getGroepType();

	/**
	 * @param Request $request
	 * @param null $id
	 * @param null $soort
	 * @return GenericDataTableResponse|GroepForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function nieuw(Request $request, $id = null, $soort = null)
	{
		return $this->aanmaken($request, $id, $soort);
	}

	/**
	 * @param Request $request
	 * @param null $id
	 * @param null $soort
	 * @return GenericDataTableResponse|GroepForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmaken(Request $request, $id = null, $soort = null)
	{
		if (!$id) {
			$vorige = null;
			$groep = $this->repository->nieuw($soort);
			// Rechtencheck op lege groep
			if (!$this->isGranted(AbstractGroepVoter::AANMAKEN, $groep)) {
				throw $this->createAccessDeniedException();
			}
			$profiel = $this->getProfiel();
			if ($groep instanceof Activiteit && empty($groep->rechtenAanmelden)) {
				switch ($groep->activiteitSoort) {
					case ActiviteitSoort::Lichting():
						$groep->rechtenAanmelden = 'Lichting:' . $profiel->lidjaar;
						break;

					case ActiviteitSoort::Verticale():
						$groep->rechtenAanmelden = 'Verticale:' . $profiel->verticale;
						break;

					case ActiviteitSoort::Kring():
						$kring = $profiel->getKring();
						if ($kring) {
							$groep->rechtenAanmelden =
								'Kring:' . $kring->verticale . '.' . $kring->kringNummer;
						}
						break;
					default:
						$groep->rechtenAanmelden = P_LOGGED_IN;
						break;
				}
			}
		}
		// opvolger
		else {
			$vorige = $this->repository->retrieveByUUID($id);
			if (!$vorige) {
				throw $this->createAccessDeniedException();
			}
			if ($vorige instanceof HeeftSoort) {
				$soort = $vorige->getSoort();
			}
			$groep = $this->repository->nieuw($soort);
			// Rechtencheck op lege groep
			if (!$this->isGranted(AbstractGroepVoter::AANMAKEN, $groep)) {
				throw $this->createAccessDeniedException();
			}
			$groep->naam = $vorige->naam;
			$groep->familie = $vorige->familie;
			$groep->samenvatting = $vorige->samenvatting;
			$groep->omschrijving = $vorige->omschrijving;
			if (
				$groep instanceof HeeftAanmeldRechten &&
				$vorige instanceof HeeftAanmeldRechten
			) {
				$groep->setAanmeldRechten($vorige->getAanmeldRechten());
			}
		}

		// checks rechten aanmaken
		$form = new GroepForm(
			$groep,
			$this->repository->getUrl() . '/aanmaken',
			$this->isGranted(AbstractGroepVoter::AANMAKEN, $groep),
			false
		);
		if ($request->getMethod() == 'GET') {
			$table = new GroepenBeheerTable($this->repository);
			$form->setDataTableId($table->getDataTableId());
			return $this->render('default.html.twig', [
				'content' => $table,
				'modal' => $form,
			]);
		} elseif ($form->validate()) {
			$this->changeLogRepository->log(
				$groep,
				'create',
				null,
				$this->changeLogRepository->serialize($groep)
			);
			$this->repository->create($groep);
			$response[] = $groep;
			if ($vorige) {
				$vorige->status = GroepStatus::OT();
				$this->repository->update($vorige);
				$response[] = $vorige;
			}
			$view = $this->tableData($response);
			$this->addFlash(
				FlashType::SUCCESS,
				$groep::class . ' succesvol aangemaakt!'
			);
			$form = new GroepPreviewForm($this->container->get('twig'), $groep);
			$view->modal = $form->__toString();
			return $view;
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse|Response
	 */
	public function beheren(Request $request, $soort = null)
	{
		if ($request->getMethod() == 'POST') {
			$soortEnum = $this->repository->parseSoort($soort);
			if ($soortEnum) {
				$groepen = $this->repository->findBy(['soort' => $soortEnum]);
			} else {
				$groepen = $this->repository->findAll();
			}
			return $this->tableData($groepen);
		} else {
			$this->table = new GroepenBeheerTable($this->repository);
			return $this->render('default.html.twig', ['content' => $this->table]);
		}
	}

	/*
	 * Voor groepen V2
	 */

}
