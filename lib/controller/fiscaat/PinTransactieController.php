<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\fiscaat\CiviProductTypeEnum;
use CsrDelft\model\entity\fiscaat\CiviSaldoCommissieEnum;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatchStatusEnum;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fiscaat\pin\PinBestellingAanmakenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingInfoForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVeranderenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVerwijderenForm;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchTableResponse;
use CsrDelft\view\fiscaat\pin\PinTransactieOverzichtView;

/**
 * Class PinTransactieController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 * @property PinTransactieMatchModel $model
 */
class PinTransactieController extends AclController {

	/**
	 * @param string $query
	 */
	public function __construct($query) {
		parent::__construct($query, PinTransactieMatchModel::instance());

		if ($this->getMethod() == 'POST') {
			$this->acl = [
				'overzicht' => 'P_FISCAAT_READ',
				'verwerk' => 'P_FISCAAT_MOD',
				'ontkoppel' => 'P_FISCAAT_MOD',
				'koppel' => 'P_FISCAAT_MOD',
				'verwijder' => 'P_FISCAAT_MOD',
				'aanmaken' => 'P_FISCAAT_MOD',
				'update' => 'P_FISCAAT_MOD',
				'info' => 'P_FISCAAT_READ',
				'verwijder_transactie' => 'P_FISCAAT_MOD',
				'heroverweeg' => 'P_FISCAAT_MOD',
			];
		} else {
			$this->acl = [
				'overzicht' => 'P_FISCAAT_READ',
			];
		}
	}

	/**
	 * @param array $args
	 * @return mixed
	 * @throws CsrException
	 */
	public function performAction(array $args = array()) {
		$this->action = 'overzicht';

		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		return parent::performAction($args);
	}

	public function GET_overzicht() {
		$this->view = new CsrLayoutPage(new PinTransactieOverzichtView());
	}

	public function POST_overzicht() {
		$filter = $this->hasParam('filter') ? $this->getParam('filter') : '';

		switch ($filter) {
			case 'metFout':
				$data = $this->model->find('status <> \'match\' AND status <> \'verwijderd\'');
				break;

			case 'alles':
			default:
				$data = $this->model->find();
				break;
		}

		$this->view = new PinTransactieMatchTableResponse($data);
	}

	/**
	 * @throws CsrException
	 */
	public function POST_verwerk() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->model->retrieveByUUID($selection[0]);

			switch ($pinTransactieMatch->status) {
				case PinTransactieMatchStatusEnum::STATUS_MATCH:
					throw new CsrGebruikerException('Er is geen fout om op te lossen.');
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING:
					// Maak een nieuwe bestelling met bedrag en uid.
					$this->view = new PinBestellingAanmakenForm($pinTransactieMatch);
					break;
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE:
					// Verwijder de bestelling met een confirm.
					$this->view = new PinBestellingVerwijderenForm($pinTransactieMatch);
					break;
				case PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG:
					// Update bestelling met bedrag.
					$this->view = new PinBestellingVeranderenForm($pinTransactieMatch);
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
	public function POST_aanmaken() {
		$form = new PinBestellingAanmakenForm();

		if ($form->validate()) {
			$values = $form->getValues();

			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->model->retrieveByUUID($values['pinTransactieId']);

			if ($pinTransactieMatch->bestelling_id !== null) {
				throw new CsrGebruikerException('Er bestaat al een bestelling.');
			}

			if ($pinTransactieMatch->transactie_id === null) {
				throw new CsrGebruikerException('Geen transactie gevonden om een bestelling voor aan te maken');
			}

			$nieuwePinTransactieMatch = Database::transaction(function () use ($pinTransactieMatch, $values) {
				$pinTransactie = PinTransactieModel::get($pinTransactieMatch->transactie_id);

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

				$bestelling->id = CiviBestellingModel::instance()->create($bestelling);

				CiviSaldoModel::instance()->ophogen($values['uid'], $pinTransactie->getBedragInCenten());

				PinTransactieMatchModel::instance()->delete($pinTransactieMatch);

				$nieuwePinTransactieMatch = PinTransactieMatch::match($pinTransactie, $bestellingInhoud);
				$nieuwePinTransactieMatch->id = PinTransactieMatchModel::instance()->create($nieuwePinTransactieMatch);

				return $nieuwePinTransactieMatch;
			});

			$this->view = new PinTransactieMatchTableResponse([
				[
					'UUID' => $pinTransactieMatch->getUUID(),
					'remove' => true,
				],
				$nieuwePinTransactieMatch,
			]);

		} else {
			$this->view = $form;
		}
	}

	/**
	 * @throws CsrGebruikerException
	 */
	public function POST_ontkoppel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->model->retrieveByUUID($selection[0]);

			if ($pinTransactieMatch->bestelling_id === null) {
				throw new CsrGebruikerException('Ontoppelen niet mogelijk, geen bestelling gevonden.');
			} elseif ($pinTransactieMatch->transactie_id === null) {
				throw new CsrGebruikerException('Ontkoppelen niet mogelijk, geen transactie gevonden.');
			} else {

				$nieuweMatches = Database::transaction(function () use ($pinTransactieMatch) {
					$missendeBestelling = PinTransactieMatch::missendeBestelling(PinTransactieModel::get($pinTransactieMatch->transactie_id));
					$missendeTransactie = PinTransactieMatch::missendeTransactie(CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($pinTransactieMatch->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE));

					PinTransactieMatchModel::instance()->delete($pinTransactieMatch);
					$missendeTransactie->id = PinTransactieMatchModel::instance()->create($missendeTransactie);
					$missendeBestelling->id = PinTransactieMatchModel::instance()->create($missendeBestelling);

					return [$missendeBestelling, $missendeTransactie];
				});

				$this->view = new PinTransactieMatchTableResponse(array_merge($nieuweMatches, [
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
	public function POST_koppel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 2) {
			throw new CsrGebruikerException('Selecteer twee regels om te koppelen.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch1 */
			$pinTransactieMatch1 = $this->model->retrieveByUUID($selection[0]);
			/** @var PinTransactieMatch $pinTransactieMatch2 */
			$pinTransactieMatch2 = $this->model->retrieveByUUID($selection[1]);

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


			$this->view = new PinTransactieMatchTableResponse([
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
	 * Verwijder een pin bestelling. Als er nog andere onderdelen aan deze bestelling zijn, maak dan een nieuwe
	 * bestelling aan hiervoor.
	 */
	public function POST_verwijder() {
		$form = new PinBestellingVerwijderenForm(new PinTransactieMatch());

		if ($form->validate()) {
			$pinTransactieMatch = $form->getModel();
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = Database::transaction(function () use ($pinTransactieMatch) {
				/** @var PinTransactieMatch $pinTransactieMatch */
				$pinTransactieMatch = PinTransactieMatchModel::instance()->retrieve($pinTransactieMatch);

				$oudeBestelling = CiviBestellingModel::get($pinTransactieMatch->bestelling_id);
				$oudeBestelling->deleted = true;
				CiviBestellingModel::instance()->update($oudeBestelling);

				/** @var CiviBestellingInhoud[] $bestellingInhoud */
				$bestellingInhoud = $oudeBestelling->getInhoud();

				if (count($bestellingInhoud) === 1) {
					CiviSaldoModel::instance()->verlagen($oudeBestelling->uid, $oudeBestelling->totaal * -1);
				} else {
					/** @var CiviBestellingInhoud $pinBestellingInhoud */
					$pinBestellingInhoud = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($oudeBestelling->id, CiviProductTypeEnum::PINTRANSACTIE);
					CiviSaldoModel::instance()->verlagen($oudeBestelling->uid, $pinBestellingInhoud->aantal);

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

					CiviBestellingModel::instance()->create($nieuweBestelling);
				}

				PinTransactieMatchModel::instance()->delete($pinTransactieMatch);

				return $pinTransactieMatch;
			});

			$this->view = new PinTransactieMatchTableResponse([[
				'UUID' => $pinTransactieMatch->getUUID(),
				'remove' => true,
			]]);
		} else {
			$this->view = $form;
		}
	}

	/**
	 * Verander het bedrag in de bestelling.
	 */
	public function POST_update() {
		$form = new PinBestellingVeranderenForm(new PinTransactieMatch());

		if ($form->validate()) {
			$pinTransactieMatch = $form->getModel();

			Database::transaction(function () use ($pinTransactieMatch) {
				/** @var PinTransactieMatch $pinTransactieMatch */
				$pinTransactieMatch = PinTransactieMatchModel::instance()->retrieve($pinTransactieMatch);

				$transactie = PinTransactieModel::get($pinTransactieMatch->transactie_id);

				$bestelling = CiviBestellingModel::get($pinTransactieMatch->bestelling_id);
				$bestellingInhoud = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($bestelling->id, CiviProductTypeEnum::PINTRANSACTIE);

				$oudAantal = $bestellingInhoud->aantal;
				$nieuwAantal = $transactie->getBedragInCenten();

				$bestellingInhoud->aantal = $transactie->getBedragInCenten();
				$bestelling->totaal += $oudAantal - $nieuwAantal;
				$bestelling->comment = sprintf('Veranderd door de fiscus op %s.', getDateTime());

				if ($oudAantal < $nieuwAantal) {
					// Is nu meer gepind
					CiviSaldoModel::instance()->ophogen($bestelling->uid, $nieuwAantal - $oudAantal);
				} else {
					// Is nu minder gepind
					CiviSaldoModel::instance()->verlagen($bestelling->uid, $oudAantal - $nieuwAantal);
				}

				CiviBestellingModel::instance()->update($bestelling);
				CiviBestellingInhoudModel::instance()->update($bestellingInhoud);

				$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;

				PinTransactieMatchModel::instance()->update($pinTransactieMatch);
			});

			$this->view = new PinTransactieMatchTableResponse([
				$pinTransactieMatch
			]);
		} else {
			$this->view = $form;
		}
	}

	/**
	 * @throws CsrGebruikerException
	 */
	public function POST_info() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->model->retrieveByUUID($selection[0]);

			if ($pinTransactieMatch->bestelling_id === null) {
				throw new CsrGebruikerException('Geen bestelling gevonden');
			} else {
				$pinBestelling = CiviBestellingModel::get($pinTransactieMatch->bestelling_id);
				$this->view = new PinBestellingInfoForm($pinBestelling);
			}
		}
	}

	/**
	 * Markeer een match als verwijderd, deze transactie is niet relevant en al op een andere manier verwerkt.
	 */
	public function POST_verwijder_transactie() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$model = $this->model;

		$updated = Database::transaction(function () use ($selection, $model) {
			$updated = [];

			foreach ($selection as $uuid) {
				/** @var PinTransactieMatch $pinTransactieMatch */
				$pinTransactieMatch = $model->retrieveByUUID($uuid);

				$bestelling = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($pinTransactieMatch->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);

				if ($bestelling != false) {
					throw new CsrGebruikerException("Match kan niet verwijderd worden, er hangt een bestelling aan.");
				}

				if ($pinTransactieMatch->status == PinTransactieMatchStatusEnum::STATUS_VERWIJDERD) {
					$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
				} else {
					$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_VERWIJDERD;
				}

				$model->update($pinTransactieMatch);
				$updated[] = $pinTransactieMatch;
			}

			return $updated;
		});

		$this->view = new PinTransactieMatchTableResponse($updated);
	}

	/**
	 * Verwijder matches die geen bestelling en transactie hebben. Dit kan gebeuren als een probleem binnen het
	 * socciesysteem wordt opgelost.
	 */
	public function POST_heroverweeg() {
		$model = $this->model;

		$deleted = Database::transaction(function () use ($model) {
			/** @var PinTransactieMatch[] $alleMatches */
			$alleMatches = $model->find();
			$deleted = [];

			foreach ($alleMatches as $match) {
				$bestelling = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($match->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);
				if ($bestelling === false && $match->transactie_id == null) {
					$model->delete($match);
					$deleted[] = [
						'UUID' => $match->getUUID(),
						'remove' => true,
					];
				}
			}

			return $deleted;
		});

		$this->view = new PinTransactieMatchTableResponse($deleted);
	}

	/**
	 * @param PinTransactieMatch $missendeTransactie
	 * @param PinTransactieMatch $missendeBestelling
	 * @return PinTransactieMatch
	 */
	private function koppelMatches($missendeTransactie, $missendeBestelling) {
		return Database::transaction(function () use ($missendeTransactie, $missendeBestelling) {
			/** @var CiviBestellingInhoud $bestelling */
			$bestelling = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($missendeTransactie->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);
			$transactie = PinTransactieModel::get($missendeBestelling->transactie_id);

			if ($bestelling->aantal === $transactie->getBedragInCenten()) {
				$pinTransactieMatch = PinTransactieMatch::match($transactie, $bestelling);
			} else {
				$pinTransactieMatch = PinTransactieMatch::verkeerdBedrag($transactie, $bestelling);
			}

			PinTransactieMatchModel::instance()->delete($missendeBestelling);
			PinTransactieMatchModel::instance()->delete($missendeTransactie);
			$pinTransactieMatch->id = PinTransactieMatchModel::instance()->create($pinTransactieMatch);

			return $pinTransactieMatch;
		});
	}
}
