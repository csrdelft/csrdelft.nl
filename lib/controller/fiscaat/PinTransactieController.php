<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use CsrDelft\entity\fiscaat\enum\CiviSaldoCommissieEnum;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\fiscaat\pin\PinBestellingAanmakenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingInfoForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVeranderenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVerwijderenForm;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchTable;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchTableResponse;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2017
 */
class PinTransactieController extends AbstractController {
	/** @var CiviBestellingRepository */
	private $civiBestellingModel;
	/** @var CiviSaldoRepository */
	private $civiSaldoRepository;
	/** @var PinTransactieMatchRepository */
	private $pinTransactieMatchRepository;
	/** @var PinTransactieRepository */
	private $pinTransactieRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(
		EntityManagerInterface $em,
		CiviBestellingRepository $civiBestellingRepository,
		CiviSaldoRepository $civiSaldoRepository,
		PinTransactieMatchRepository $pinTransactieMatchRepository,
		PinTransactieRepository $pinTransactieRepository
	) {
		$this->civiBestellingModel = $civiBestellingRepository;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->pinTransactieMatchRepository = $pinTransactieMatchRepository;
		$this->pinTransactieRepository = $pinTransactieRepository;
		$this->em = $em;
	}

	/**
	 * @return TemplateView
	 * @Route("/fiscaat/pin", methods={"GET"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function overzicht() {
		return view('fiscaat.pagina', [
			'titel' => 'Pin transacties beheer',
			'view' => new PinTransactieMatchTable(),
		]);
	}

	/**
	 * @param Request $request
	 * @return PinTransactieMatchTableResponse
	 * @Route("/fiscaat/pin", methods={"POST"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function lijst(Request $request) {
		$filter = $request->query->get('filter', '');

		switch ($filter) {
			case 'metFout':
				$data = $this->pinTransactieMatchRepository->metFout();
				break;

			case 'alles':
			default:
				$data = $this->pinTransactieMatchRepository->findAll();
				break;
		}

		return new PinTransactieMatchTableResponse($data);
	}

	/**
	 * @throws CsrException
	 * @Route("/fiscaat/pin/verwerk", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function verwerk() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($selection[0]);

			switch ($pinTransactieMatch->status) {
				case PinTransactieMatchStatusEnum::STATUS_MATCH:
					throw new CsrGebruikerException('Er is geen fout om op te lossen.');
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING:
					// Maak een nieuwe bestelling met bedrag en uid.
					return new PinBestellingAanmakenForm($pinTransactieMatch);
					break;
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE:
					// Verwijder de bestelling met een confirm.
					return new PinBestellingVerwijderenForm($pinTransactieMatch);
					break;
				case PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG:
					// Update bestelling met bedrag.
					return new PinBestellingVeranderenForm($pinTransactieMatch);
					break;
				default:
					throw new CsrException('Onbekende PinTransactieMatchStatusEnum: ' . $pinTransactieMatch->status);
			}
		}
	}

	/**
	 * @throws CsrGebruikerException
	 * @throws CsrException
	 * @Route("/fiscaat/ipin/aanmaken", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function aanmaken() {
		$form = new PinBestellingAanmakenForm();

		if ($form->validate()) {
			$values = $form->getValues();

			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($values['pinTransactieId']);

			if ($pinTransactieMatch->transactie !== null) {
				throw new CsrGebruikerException('Er bestaat al een bestelling.');
			}

			if ($pinTransactieMatch->transactie === null) {
				throw new CsrGebruikerException('Geen transactie gevonden om een bestelling voor aan te maken');
			}

			$nieuwePinTransactieMatch = $this->em->transactional(function () use ($pinTransactieMatch, $values) {
				$pinTransactie = $pinTransactieMatch->transactie;

				$bestelling = new CiviBestelling();
				$bestelling->moment = $pinTransactie->datetime;
				$bestelling->uid = $values['uid'];
				$bestelling->profiel = ProfielRepository::get($values['uid']);
				$bestelling->totaal = $pinTransactie->getBedragInCenten() * -1;
				$bestelling->cie = CiviSaldoCommissieEnum::SOCCIE;
				$bestelling->deleted = false;
				$bestelling->comment = sprintf('Aangemaakt door de fiscus op %s.', getDateTime());

				$bestellingInhoud = new CiviBestellingInhoud();
				$bestellingInhoud->product_id = CiviProductTypeEnum::PINTRANSACTIE;
				$bestellingInhoud->aantal = $pinTransactie->getBedragInCenten();

				$bestelling->inhoud[] = $bestellingInhoud;

				$bestelling->id = $this->civiBestellingModel->create($bestelling);

				$this->civiSaldoRepository->ophogen($values['uid'], $pinTransactie->getBedragInCenten());

				$manager = $this->getDoctrine()->getManager();

				$manager->remove($pinTransactieMatch);

				$nieuwePinTransactieMatch = PinTransactieMatch::match($pinTransactie, $bestelling);

				$manager->persist($nieuwePinTransactieMatch);
				$manager->flush();

				return $nieuwePinTransactieMatch;
			});

			return new PinTransactieMatchTableResponse([
				[
					'UUID' => $pinTransactieMatch->getUUID(),
					'remove' => true,
				],
				$nieuwePinTransactieMatch,
			]);

		} else {
			return $form;
		}
	}

	/**
	 * @throws CsrGebruikerException
	 * @Route("/fiscaat/pin/ontkoppel", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function ontkoppel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($selection[0]);

			if ($pinTransactieMatch->bestelling === null) {
				throw new CsrGebruikerException('Ontoppelen niet mogelijk, geen bestelling gevonden.');
			} elseif ($pinTransactieMatch->transactie === null) {
				throw new CsrGebruikerException('Ontkoppelen niet mogelijk, geen transactie gevonden.');
			} else {

				$nieuweMatches = $this->em->transactional(function () use ($pinTransactieMatch) {
					$missendeBestelling = PinTransactieMatch::missendeBestelling($pinTransactieMatch->transactie);
					$missendeTransactie = PinTransactieMatch::missendeTransactie($pinTransactieMatch->bestelling);

					$manager = $this->getDoctrine()->getManager();

					$manager->remove($pinTransactieMatch);
					$manager->persist($missendeTransactie);
					$manager->persist($missendeBestelling);
					$manager->flush();

					return [$missendeBestelling, $missendeTransactie];
				});

				return new PinTransactieMatchTableResponse(array_merge($nieuweMatches, [
					[
						'UUID' => $pinTransactieMatch->getUUID(),
						'remove' => true
					],
				]));
			}
		}
	}

	/**
	 * @throws CsrException
	 * @Route("/fiscaat/pin/koppel", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function koppel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 2) {
			throw new CsrGebruikerException('Selecteer twee regels om te koppelen.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch1 */
			$pinTransactieMatch1 = $this->pinTransactieMatchRepository->retrieveByUUID($selection[0]);
			/** @var PinTransactieMatch $pinTransactieMatch2 */
			$pinTransactieMatch2 = $this->pinTransactieMatchRepository->retrieveByUUID($selection[1]);

			$nieuwePinTransactieMatch = $this->em->transactional(function () use ($pinTransactieMatch1, $pinTransactieMatch2) {
				if ($pinTransactieMatch1->bestelling === null && $pinTransactieMatch2->transactie === null) {
					$nieuwePinTransactieMatch = $this->koppelMatches($pinTransactieMatch2, $pinTransactieMatch1);
				} elseif ($pinTransactieMatch2->bestelling === null && $pinTransactieMatch1->transactie === null) {
					$nieuwePinTransactieMatch = $this->koppelMatches($pinTransactieMatch1, $pinTransactieMatch2);
				} else {
					throw new CsrGebruikerException('Een van de regels is niet incompleet');
				}

				return $nieuwePinTransactieMatch;
			});


			return new PinTransactieMatchTableResponse([
				[
					'UUID' => $pinTransactieMatch1->getUUID(),
					'remove' => true
				],
				[
					'UUID' => $pinTransactieMatch2->getUUID(),
					'remove' => true
				],
				$nieuwePinTransactieMatch,
			]);
		}
	}

	/**
	 * @param PinTransactieMatch $missendeTransactie
	 * @param PinTransactieMatch $missendeBestelling
	 * @return PinTransactieMatch
	 */
	private function koppelMatches($missendeTransactie, $missendeBestelling) {
		return $this->em->transactional(function () use ($missendeTransactie, $missendeBestelling) {
			$bestelling = $missendeTransactie->bestelling;
			$bestellingInhoud = $bestelling->getProduct(CiviProductTypeEnum::PINTRANSACTIE);
			$transactie = $missendeBestelling->transactie;

			if ($bestellingInhoud->aantal === $transactie->getBedragInCenten()) {
				$pinTransactieMatch = PinTransactieMatch::match($transactie, $bestelling);
			} else {
				$pinTransactieMatch = PinTransactieMatch::verkeerdBedrag($transactie, $bestelling);
			}

			$manager = $this->getDoctrine()->getManager();
			$manager->remove($missendeBestelling);
			$manager->remove($missendeTransactie);
			$manager->persist($pinTransactieMatch);
			$manager->flush();

			return $pinTransactieMatch;
		});
	}

	/**
	 * Verwijder een pin bestelling. Als er nog andere onderdelen aan deze bestelling zijn, maak dan een nieuwe
	 * bestelling aan hiervoor.
	 * @Route("/fiscaat/pin/verwijder", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function verwijder() {
		$form = new PinBestellingVerwijderenForm(new PinTransactieMatch());

		if ($form->validate()) {
			$pinTransactieMatch = $form->getModel();
			$pinTransactieMatch = $this->em->transactional(function () use ($pinTransactieMatch) {
				$pinTransactieMatch = $this->pinTransactieMatchRepository->find($pinTransactieMatch->id);

				$oudeBestelling = $pinTransactieMatch->bestelling;
				$oudeBestelling->deleted = true;
				$this->em->persist($oudeBestelling);
				$this->em->flush();

				if (count($oudeBestelling->inhoud) === 1) {
					$this->civiSaldoRepository->verlagen($oudeBestelling->uid, $oudeBestelling->totaal * -1);
				} else {
					$pinBestellingInhoud = $oudeBestelling->getProduct(CiviProductTypeEnum::PINTRANSACTIE);
					$this->civiSaldoRepository->verlagen($oudeBestelling->uid, $pinBestellingInhoud->aantal);

					$nieuweBestellingInhoud = [];

					foreach ($oudeBestelling->inhoud as $inhoud) {
						if ($inhoud !== $pinBestellingInhoud) {
							$nieuweInhoud = new CiviBestellingInhoud();
							$nieuweInhoud->product_id = $inhoud->product_id;
							$nieuweInhoud->aantal = $inhoud->aantal;

							$nieuweBestellingInhoud[] = $nieuweInhoud;
						}
					}

					$nieuweBestelling = new CiviBestelling();
					$nieuweBestelling->inhoud = $nieuweBestellingInhoud;
					$nieuweBestelling->uid = $oudeBestelling->uid;
					$nieuweBestelling->profiel = $oudeBestelling->profiel;
					$nieuweBestelling->moment = $oudeBestelling->moment;
					$nieuweBestelling->cie = $oudeBestelling->cie;
					$nieuweBestelling->totaal = $oudeBestelling->totaal - $pinBestellingInhoud->aantal;
					$nieuweBestelling->comment = sprintf('Veranderd door de fiscus op %s.', getDateTime());

					$this->civiBestellingModel->create($nieuweBestelling);
				}

				$this->getDoctrine()->getManager()->remove($pinTransactieMatch);
				$this->getDoctrine()->getManager()->flush();

				return $pinTransactieMatch;
			});

			return new PinTransactieMatchTableResponse([[
				'UUID' => $pinTransactieMatch->getUUID(),
				'remove' => true,
			]]);
		} else {
			return $form;
		}
	}

	/**
	 * Verander het bedrag in de bestelling.
	 * @Route("/fiscaat/pin/update", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function update() {
		$form = new PinBestellingVeranderenForm(new PinTransactieMatch());

		if ($form->validate()) {
			$pinTransactieMatch = $form->getModel();

			$this->em->transactional(function () use ($pinTransactieMatch) {
				$pinTransactieMatch = $this->pinTransactieMatchRepository->find($pinTransactieMatch->id);

				$transactie = $pinTransactieMatch->transactie;

				$bestelling = $pinTransactieMatch->bestelling;
				$bestellingInhoud = $bestelling->getProduct(CiviProductTypeEnum::PINTRANSACTIE);

				$oudAantal = $bestellingInhoud->aantal;
				$nieuwAantal = $transactie->getBedragInCenten();

				$bestellingInhoud->aantal = $transactie->getBedragInCenten();
				$bestelling->totaal += $oudAantal - $nieuwAantal;
				$bestelling->comment = sprintf('Veranderd door de fiscus op %s.', getDateTime());

				if ($oudAantal < $nieuwAantal) {
					// Is nu meer gepind
					$this->civiSaldoRepository->ophogen($bestelling->uid, $nieuwAantal - $oudAantal);
				} else {
					// Is nu minder gepind
					$this->civiSaldoRepository->verlagen($bestelling->uid, $oudAantal - $nieuwAantal);
				}

				$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;

				$this->em->persist($bestelling);
				$this->em->persist($bestellingInhoud);
				$this->em->persist($pinTransactieMatch);
				$this->em->flush();
			});

			return new PinTransactieMatchTableResponse([$pinTransactieMatch]);
		} else {
			return $form;
		}
	}

	/**
	 * @throws CsrGebruikerException
	 * @Route("/fiscaat/pin/info", methods={"POST"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function info() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($selection[0]);

			if ($pinTransactieMatch->bestelling === null) {
				throw new CsrGebruikerException('Geen bestelling gevonden');
			} else {
				$pinBestelling = $pinTransactieMatch->bestelling;
				return new PinBestellingInfoForm($pinBestelling);
			}
		}
	}

	/**
	 * Markeer een match als verwijderd, deze transactie is niet relevant en al op een andere manier verwerkt.
	 * @Route("/fiscaat/pin/verwijder_transactie", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function verwijder_transactie() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		$updated = $this->em->transactional(function () use ($selection) {
			$updated = [];

			$manager = $this->getDoctrine()->getManager();

			foreach ($selection as $uuid) {
				$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($uuid);

				if ($pinTransactieMatch->bestelling && $pinTransactieMatch->bestelling->getProduct(CiviProductTypeEnum::PINTRANSACTIE)) {
					throw new CsrGebruikerException("Match kan niet verwijderd worden, er hangt een bestelling aan.");
				}

				if ($pinTransactieMatch->status == PinTransactieMatchStatusEnum::STATUS_VERWIJDERD) {
					$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
				} else {
					$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_VERWIJDERD;
				}

				$manager->persist($pinTransactieMatch);
				$updated[] = $pinTransactieMatch;
			}

			$manager->flush();

			return $updated;
		});

		return new PinTransactieMatchTableResponse($updated);
	}

	/**
	 * Verwijder matches die geen bestelling en transactie hebben. Dit kan gebeuren als een probleem binnen het
	 * socciesysteem wordt opgelost.
	 * @Route("/fiscaat/pin/heroverweeg", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function heroverweeg() {
		$deleted = $this->em->transactional(function () {
			$alleMatches = $this->pinTransactieMatchRepository->findAll();
			$deleted = [];
			$manager = $this->getDoctrine()->getManager();

			foreach ($alleMatches as $match) {
				if (!$match->bestelling) {
					continue;
				}
				$bestelling = $match->bestelling->getProduct(CiviProductTypeEnum::PINTRANSACTIE);
				if (!$bestelling && $match->transactie == null) {
					$deleted[] = new RemoveDataTableEntry($match->id, PinTransactieMatch::class);
					$manager->remove($match);
				}
			}

			$manager->flush();

			return $deleted;
		});

		return $this->tableData($deleted === true ? [] : $deleted);
	}
}
