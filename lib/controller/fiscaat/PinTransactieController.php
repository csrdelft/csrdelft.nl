<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\fiscaat\CiviProductTypeEnum;
use CsrDelft\model\entity\fiscaat\CiviSaldoCommissieEnum;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\view\fiscaat\pin\PinBestellingAanmakenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingInfoForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVeranderenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVerwijderenForm;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchTable;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchTableResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieController extends AbstractController {
	/** @var CiviBestellingInhoudModel */
	private $civiBestellingInhoudModel;
	/** @var CiviBestellingModel */
	private $civiBestellingModel;
	/** @var CiviSaldoModel */
	private $civiSaldoModel;
	/** @var PinTransactieMatchRepository */
	private $pinTransactieMatchRepository;
	/** @var PinTransactieRepository */
	private $pinTransactieRepository;

	public function __construct(
		CiviBestellingInhoudModel $civiBestellingInhoudModel,
		CiviBestellingModel $civiBestellingModel,
		CiviSaldoModel $civiSaldoModel,
		PinTransactieMatchRepository $pinTransactieMatchRepository,
		PinTransactieRepository $pinTransactieRepository
	) {
		$this->civiBestellingInhoudModel = $civiBestellingInhoudModel;
		$this->civiBestellingModel = $civiBestellingModel;
		$this->civiSaldoModel = $civiSaldoModel;
		$this->pinTransactieMatchRepository = $pinTransactieMatchRepository;
		$this->pinTransactieRepository = $pinTransactieRepository;
	}

	public function overzicht() {
		return view('fiscaat.pagina', [
			'titel' => 'Pin transacties beheer',
			'view' => new PinTransactieMatchTable(),
		]);
	}

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
	 */
	public function aanmaken() {
		$form = new PinBestellingAanmakenForm();

		if ($form->validate()) {
			$values = $form->getValues();

			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($values['pinTransactieId']);

			if ($pinTransactieMatch->bestelling_id !== null) {
				throw new CsrGebruikerException('Er bestaat al een bestelling.');
			}

			if ($pinTransactieMatch->transactie_id === null) {
				throw new CsrGebruikerException('Geen transactie gevonden om een bestelling voor aan te maken');
			}

			$nieuwePinTransactieMatch = Database::transaction(function () use ($pinTransactieMatch, $values) {
				$pinTransactie = $this->pinTransactieRepository->get($pinTransactieMatch->transactie_id);

				$bestelling = new CiviBestelling();
				$bestelling->moment = $pinTransactie->datetime;
				$bestelling->uid = $values['uid'];
				$bestelling->totaal = $pinTransactie->getBedragInCenten() * -1;
				$bestelling->cie = CiviSaldoCommissieEnum::SOCCIE;
				$bestelling->deleted = false;
				$bestelling->comment = sprintf('Aangemaakt door de fiscus op %s.', getDateTime());

				$bestellingInhoud = new CiviBestellingInhoud();
				$bestellingInhoud->product_id = CiviProductTypeEnum::PINTRANSACTIE;
				$bestellingInhoud->aantal = $pinTransactie->getBedragInCenten();

				$bestelling->inhoud[] = $bestellingInhoud;

				$bestelling->id = $this->civiBestellingModel->create($bestelling);

				$this->civiSaldoModel->ophogen($values['uid'], $pinTransactie->getBedragInCenten());

				$manager = $this->getDoctrine()->getManager();

				$manager->remove($pinTransactieMatch);

				$nieuwePinTransactieMatch = PinTransactieMatch::match($pinTransactie, $bestellingInhoud);

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
	 */
	public function ontkoppel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($selection[0]);

			if ($pinTransactieMatch->bestelling_id === null) {
				throw new CsrGebruikerException('Ontoppelen niet mogelijk, geen bestelling gevonden.');
			} elseif ($pinTransactieMatch->transactie_id === null) {
				throw new CsrGebruikerException('Ontkoppelen niet mogelijk, geen transactie gevonden.');
			} else {

				$nieuweMatches = Database::transaction(function () use ($pinTransactieMatch) {
					$missendeBestelling = PinTransactieMatch::missendeBestelling($this->pinTransactieRepository->get($pinTransactieMatch->transactie_id));
					$missendeTransactie = PinTransactieMatch::missendeTransactie($this->civiBestellingInhoudModel->getVoorBestellingEnProduct($pinTransactieMatch->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE));

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

			$nieuwePinTransactieMatch = Database::transaction(function () use ($pinTransactieMatch1, $pinTransactieMatch2) {
				if ($pinTransactieMatch1->bestelling_id === null && $pinTransactieMatch2->transactie_id === null) {
					$nieuwePinTransactieMatch = $this->koppelMatches($pinTransactieMatch2, $pinTransactieMatch1);
				} elseif ($pinTransactieMatch2->bestelling_id === null && $pinTransactieMatch1->transactie_id === null) {
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
		return Database::transaction(function () use ($missendeTransactie, $missendeBestelling) {
			/** @var CiviBestellingInhoud $bestelling */
			$bestelling = $this->civiBestellingInhoudModel->getVoorBestellingEnProduct($missendeTransactie->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);
			$transactie = $this->pinTransactieRepository->get($missendeBestelling->transactie_id);

			if ($bestelling->aantal === $transactie->getBedragInCenten()) {
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
	 */
	public function verwijder() {
		$form = new PinBestellingVerwijderenForm(new PinTransactieMatch());

		if ($form->validate()) {
			$pinTransactieMatch = $form->getModel();
			$pinTransactieMatch = Database::transaction(function () use ($pinTransactieMatch) {
				$pinTransactieMatch = $this->pinTransactieMatchRepository->find($pinTransactieMatch->id);

				$oudeBestelling = $this->civiBestellingModel->get($pinTransactieMatch->bestelling_id);
				$oudeBestelling->deleted = true;
				$this->civiBestellingModel->update($oudeBestelling);

				/** @var CiviBestellingInhoud[] $bestellingInhoud */
				$bestellingInhoud = $oudeBestelling->getInhoud();

				if (count($bestellingInhoud) === 1) {
					$this->civiSaldoModel->verlagen($oudeBestelling->uid, $oudeBestelling->totaal * -1);
				} else {
					/** @var CiviBestellingInhoud $pinBestellingInhoud */
					$pinBestellingInhoud = $this->civiBestellingInhoudModel->getVoorBestellingEnProduct($oudeBestelling->id, CiviProductTypeEnum::PINTRANSACTIE);
					$this->civiSaldoModel->verlagen($oudeBestelling->uid, $pinBestellingInhoud->aantal);

					$nieuweBestellingInhoud = [];

					foreach ($bestellingInhoud as $inhoud) {
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
	 */
	public function update() {
		$form = new PinBestellingVeranderenForm(new PinTransactieMatch());

		if ($form->validate()) {
			$pinTransactieMatch = $form->getModel();

			Database::transaction(function () use ($pinTransactieMatch) {
				$pinTransactieMatch = $this->pinTransactieMatchRepository->find($pinTransactieMatch->id);

				$transactie = $this->pinTransactieRepository->get($pinTransactieMatch->transactie_id);

				$bestelling = $this->civiBestellingModel->get($pinTransactieMatch->bestelling_id);
				$bestellingInhoud = $this->civiBestellingInhoudModel->getVoorBestellingEnProduct($bestelling->id, CiviProductTypeEnum::PINTRANSACTIE);

				$oudAantal = $bestellingInhoud->aantal;
				$nieuwAantal = $transactie->getBedragInCenten();

				$bestellingInhoud->aantal = $transactie->getBedragInCenten();
				$bestelling->totaal += $oudAantal - $nieuwAantal;
				$bestelling->comment = sprintf('Veranderd door de fiscus op %s.', getDateTime());

				if ($oudAantal < $nieuwAantal) {
					// Is nu meer gepind
					$this->civiSaldoModel->ophogen($bestelling->uid, $nieuwAantal - $oudAantal);
				} else {
					// Is nu minder gepind
					$this->civiSaldoModel->verlagen($bestelling->uid, $oudAantal - $nieuwAantal);
				}

				$this->civiBestellingModel->update($bestelling);
				$this->civiBestellingInhoudModel->update($bestellingInhoud);

				$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;

				$this->getDoctrine()->getManager()->persist($pinTransactieMatch);
				$this->getDoctrine()->getManager()->flush();
			});

			return new PinTransactieMatchTableResponse([$pinTransactieMatch]);
		} else {
			return $form;
		}
	}

	/**
	 * @throws CsrGebruikerException
	 */
	public function info() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID($selection[0]);

			if ($pinTransactieMatch->bestelling_id === null) {
				throw new CsrGebruikerException('Geen bestelling gevonden');
			} else {
				$pinBestelling = $this->civiBestellingModel->get($pinTransactieMatch->bestelling_id);
				return new PinBestellingInfoForm($pinBestelling);
			}
		}
	}

	/**
	 * Markeer een match als verwijderd, deze transactie is niet relevant en al op een andere manier verwerkt.
	 */
	public function verwijder_transactie() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$model = $this->pinTransactieMatchRepository;

		$updated = Database::transaction(function () use ($selection, $model) {
			$updated = [];

			$manager = $this->getDoctrine()->getManager();

			foreach ($selection as $uuid) {
				/** @var PinTransactieMatch $pinTransactieMatch */
				$pinTransactieMatch = $model->retrieveByUUID($uuid);

				$bestelling = $this->civiBestellingInhoudModel->getVoorBestellingEnProduct($pinTransactieMatch->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);

				if ($bestelling != false) {
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
	 */
	public function heroverweeg() {
		$model = $this->pinTransactieMatchRepository;

		$deleted = Database::transaction(function () use ($model) {
			/** @var PinTransactieMatch[] $alleMatches */
			$alleMatches = $model->findAll();
			$deleted = [];
			$manager = $this->getDoctrine()->getManager();

			foreach ($alleMatches as $match) {
				$bestelling = $this->civiBestellingInhoudModel->getVoorBestellingEnProduct($match->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);
				if ($bestelling === false && $match->transactie_id == null) {
					$manager->remove($match);
					$deleted[] = [
						'UUID' => $match->getUUID(),
						'remove' => true,
					];
				}
			}

			$manager->flush();

			return $deleted;
		});

		return new PinTransactieMatchTableResponse($deleted);
	}
}
