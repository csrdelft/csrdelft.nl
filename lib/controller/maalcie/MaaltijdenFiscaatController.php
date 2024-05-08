<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\maalcie\MaaltijdAanmeldingenService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\maalcie\beheer\FiscaatMaaltijdenOverzichtTable;
use CsrDelft\view\maalcie\beheer\OnverwerkteMaaltijdenTable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MaaltijdenFiscaatController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaaltijdenFiscaatController extends AbstractController
{
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var CiviBestellingRepository
	 */
	private $civiBestellingRepository;
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;
	/**
	 * @var MaaltijdAanmeldingenService
	 */
	private $maaltijdAanmeldingenService;

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		CiviBestellingRepository $civiBestellingRepository,
		CiviSaldoRepository $civiSaldoRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->civiBestellingRepository = $civiBestellingRepository;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->maaltijdAanmeldingenService = $maaltijdAanmeldingenService;
	}

	/**
	 * @return Response
	 * @Route("/maaltijden/fiscaat", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function GET_overzicht()
	{
		return $this->render('maaltijden/pagina.html.twig', [
			'titel' => 'Overzicht verwerkte maaltijden',
			'content' => new FiscaatMaaltijdenOverzichtTable(),
		]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/maaltijden/fiscaat", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function POST_overzicht()
	{
		$data = $this->maaltijdenRepository->findBy(['verwerkt' => true]);

		return $this->tableData($data, ['datatable', 'datatable-fiscaat']);
	}

	/**
	 * @return Response
	 * @Route("/maaltijden/fiscaat/onverwerkt", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function GET_onverwerkt()
	{
		return $this->render('maaltijden/pagina.html.twig', [
			'titel' => 'Onverwerkte Maaltijden',
			'content' => new OnverwerkteMaaltijdenTable(),
		]);
	}

	/**
	 * @param EntityManagerInterface $em
	 * @return GenericDataTableResponse
	 * @Route("/maaltijden/fiscaat/verwerk", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function POST_verwerk(EntityManagerInterface $em)
	{
		// Haal maaltijd op
		$selection = $this->getDataTableSelection();
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);

		// Controleer of de maaltijd gesloten is en geweest is
		if (
			!$maaltijd->gesloten ||
			$maaltijd->getMoment() >= date_create_immutable('now')
		) {
			throw new CsrGebruikerException('Maaltijd nog niet geweest');
		}

		// Controleer of maaltijd niet al verwerkt is
		if ($maaltijd->verwerkt) {
			throw new CsrGebruikerException('Maaltijd is al verwerkt');
		}

		$maaltijden = $em->wrapInTransaction(function () use ($maaltijd) {
			// Ga alle personen in de maaltijd af
			$aanmeldingen = $this->maaltijdAanmeldingenRepository->findBy([
				'maaltijd_id' => $maaltijd->maaltijd_id,
			]);

			/** @var Civibestelling[] $bestellingen */
			$bestellingen = [];
			// Maak een bestelling voor deze persoon
			foreach ($aanmeldingen as $aanmelding) {
				$bestellingen[] = $this->maaltijdAanmeldingenService->maakCiviBestelling(
					$aanmelding
				);
			}

			// Reken de bestelling af
			foreach ($bestellingen as $bestelling) {
				$this->civiBestellingRepository->create($bestelling);
				$this->civiSaldoRepository->verlagen(
					$bestelling->uid,
					$bestelling->totaal
				);
			}

			// Zet de maaltijd op verwerkt
			$maaltijd->verwerkt = true;

			$this->maaltijdenRepository->update($maaltijd);

			$verwijderd = new RemoveDataTableEntry(
				$maaltijd->maaltijd_id,
				Maaltijd::class
			);

			return [$verwijderd];
		});

		return $this->tableData($maaltijden);
	}
}
