<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Mail;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\MailService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\fiscaat\pin\PinBestellingAanmakenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingCrediterenForm;
use CsrDelft\view\fiscaat\pin\PinBestellingInfoForm;
use CsrDelft\view\fiscaat\pin\PinBestellingVeranderenForm;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchNegerenForm;
use CsrDelft\view\formulier\FoutmeldingForm;
use CsrDelft\view\table\PinTransactieMatchTableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2017
 */
class PinTransactieController extends AbstractController
{
	/** @var CiviBestellingRepository */
	private $civiBestellingModel;
	/** @var CiviSaldoRepository */
	private $civiSaldoRepository;
	/** @var CiviProductRepository */
	private $civiProductRepository;
	/** @var PinTransactieMatchRepository */
	private $pinTransactieMatchRepository;
	/** @var PinTransactieRepository */
	private $pinTransactieRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(
		EntityManagerInterface $em,
		CiviBestellingRepository $civiBestellingRepository,
		CiviSaldoRepository $civiSaldoRepository,
		CiviProductRepository $civiProductRepository,
		PinTransactieMatchRepository $pinTransactieMatchRepository,
		PinTransactieRepository $pinTransactieRepository,
		MailService $mailService
	) {
		$this->civiBestellingModel = $civiBestellingRepository;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->civiProductRepository = $civiProductRepository;
		$this->pinTransactieMatchRepository = $pinTransactieMatchRepository;
		$this->pinTransactieRepository = $pinTransactieRepository;
		$this->em = $em;
		$this->mailService = $mailService;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @throws ExceptionInterface
	 * @Route("/fiscaat/pin", methods={"GET", "POST"})
	 * @Auth(P_FISCAAT_READ)
	 * @return GenericDataTableResponse
	 */
	public function overzicht(Request $request)
	{
		$table = $this->createDataTable(PinTransactieMatchTableType::class);

		if ($request->isMethod('POST')) {
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

			return $table->createData($data);
		}

		return $this->render('fiscaat/pin.html.twig', [
			'titel' => 'Pin transacties beheer',
			'table' => $table->createView(),
		]);
	}

	/**
	 * @throws CsrException
	 * @Route("/fiscaat/pin/verwerk", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function verwerk()
	{
		$selection = $this->getDataTableSelection();

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk');
		} else {
			/** @var PinTransactieMatch $pinTransactieMatch */
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID(
				$selection[0]
			);

			if ($pinTransactieMatch->bestelling !== null) {
				$account = $this->civiSaldoRepository->getSaldo(
					$pinTransactieMatch->bestelling->uid,
					true
				);
				if (!$account) {
					return new FoutmeldingForm(
						'Account verwijderd.',
						'Dit account is verwijderd, dus deze bestelling kan niet gecorrigeerd worden.'
					);
				}
			}

			switch ($pinTransactieMatch->status) {
				case PinTransactieMatchStatusEnum::STATUS_MATCH:
					throw new CsrGebruikerException('Er is geen fout om op te lossen.');
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING:
					// Maak een nieuwe bestelling met bedrag en uid.
					return new PinBestellingAanmakenForm($pinTransactieMatch);
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE:
					// Crediteer de bestelling met een confirm.
					return new PinBestellingCrediterenForm($pinTransactieMatch);
				case PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG:
					// Update bestelling met bedrag.
					return new PinBestellingVeranderenForm($pinTransactieMatch);
				default:
					throw new CsrException(
						'Onbekende PinTransactieMatchStatusEnum: ' .
							$pinTransactieMatch->status
					);
			}
		}
	}

	/**
	 * @throws CsrGebruikerException
	 * @Route("/fiscaat/pin/aanmaken", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function aanmaken()
	{
		$form = new PinBestellingAanmakenForm();

		if ($form->validate()) {
			$values = $form->getValues();

			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID(
				$values['pinTransactieId']
			);
			$form = new PinBestellingAanmakenForm($pinTransactieMatch);
			$values = $form->getValues();
			$removePinTransactieMatch = new RemoveDataTableEntry(
				$pinTransactieMatch->id,
				PinTransactieMatch::class
			);

			if ($pinTransactieMatch->bestelling !== null) {
				throw new CsrGebruikerException('Er bestaat al een bestelling.');
			}

			if ($pinTransactieMatch->transactie === null) {
				throw new CsrGebruikerException(
					'Geen transactie gevonden om een bestelling voor aan te maken'
				);
			}

			$account = $this->civiSaldoRepository->getSaldo($values['uid'], true);
			if (!$account) {
				throw new CsrGebruikerException(
					'Er is geen CiviSaldo voor dit lid gevonden.'
				);
			}

			/** @var PinTransactieMatch $nieuwePinTransactieMatch */
			$nieuwePinTransactieMatch = $this->em->transactional(function () use (
				$account,
				$pinTransactieMatch,
				$values
			) {
				$pinTransactie = $pinTransactieMatch->transactie;

				$bestelling = $pinTransactieMatch->bouwBestelling(
					$this->civiProductRepository,
					$values['comment'] ?: null,
					$account->uid
				);
				$bestelling->id = $this->civiBestellingModel->create($bestelling);
				$this->civiSaldoRepository->ophogen(
					$values['uid'],
					$pinTransactie->getBedragInCenten()
				);

				$manager = $this->getDoctrine()->getManager();
				$manager->remove($pinTransactieMatch);
				$manager->flush();

				$nieuwePinTransactieMatch = PinTransactieMatch::match(
					$pinTransactie,
					$bestelling
				);
				$nieuwePinTransactieMatch->notitie = $values['intern'] ?: null;
				$manager->persist($nieuwePinTransactieMatch);

				return $nieuwePinTransactieMatch;
			});

			if ($values['stuurMail']) {
				$datum = date_format_intl(
					$nieuwePinTransactieMatch->transactie->datetime,
					'cccc d MMMM y H:mm'
				);
				$bedrag = format_bedrag_kaal(
					$nieuwePinTransactieMatch->bestelling->totaal / -1
				);
				$this->stuurMail(
					$account->uid,
					'Uw CiviSaldo is verhoogd',
					"Uit mijn administratie bleek dat uw pinbetaling van {$datum} nog niet verwerkt was in uw CiviSaldo. " .
						"Dit is nu wel gebeurd, waardoor uw saldo met € {$bedrag} opgehoogd is. " .
						'Mocht dit een vergissing zijn, wilt u dan reageren op dit bericht?'
				);
			}

			return $this->tableData([
				$removePinTransactieMatch,
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
	public function ontkoppel()
	{
		$selection = $this->getDataTableSelection();

		if (count($selection) !== 1) {
			throw new CsrGebruikerException('Selecteer één regel tegelijk.');
		} else {
			$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID(
				$selection[0]
			);
			$removePinTransactieMatch = new RemoveDataTableEntry(
				$pinTransactieMatch->id,
				PinTransactieMatch::class
			);

			if ($pinTransactieMatch->bestelling === null) {
				throw new CsrGebruikerException(
					'Ontoppelen niet mogelijk, geen bestelling gevonden.'
				);
			} elseif ($pinTransactieMatch->transactie === null) {
				throw new CsrGebruikerException(
					'Ontkoppelen niet mogelijk, geen transactie gevonden.'
				);
			} else {
				$nieuweMatches = $this->em->transactional(function () use (
					$pinTransactieMatch
				) {
					$missendeBestelling = PinTransactieMatch::missendeBestelling(
						$pinTransactieMatch->transactie
					);
					$missendeTransactie = PinTransactieMatch::missendeTransactie(
						$pinTransactieMatch->bestelling
					);

					$manager = $this->getDoctrine()->getManager();

					$manager->remove($pinTransactieMatch);
					$manager->flush();
					$manager->persist($missendeTransactie);
					$manager->persist($missendeBestelling);
					$manager->flush();

					return [$missendeBestelling, $missendeTransactie];
				});

				return $this->tableData(
					array_merge($nieuweMatches, [$removePinTransactieMatch])
				);
			}
		}
	}

	/**
	 * @throws CsrException
	 * @Route("/fiscaat/pin/koppel", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function koppel()
	{
		$selection = $this->getDataTableSelection();

		if (count($selection) !== 2) {
			throw new CsrGebruikerException('Selecteer twee regels om te koppelen.');
		} else {
			$pinTransactieMatch1 = $this->pinTransactieMatchRepository->retrieveByUUID(
				$selection[0]
			);
			$removePinTransactieMatch1 = new RemoveDataTableEntry(
				$pinTransactieMatch1->id,
				PinTransactieMatch::class
			);
			$pinTransactieMatch2 = $this->pinTransactieMatchRepository->retrieveByUUID(
				$selection[1]
			);
			$removePinTransactieMatch2 = new RemoveDataTableEntry(
				$pinTransactieMatch2->id,
				PinTransactieMatch::class
			);

			$nieuwePinTransactieMatch = $this->em->transactional(function () use (
				$pinTransactieMatch1,
				$pinTransactieMatch2
			) {
				if (
					$pinTransactieMatch1->bestelling === null &&
					$pinTransactieMatch2->transactie === null
				) {
					$nieuwePinTransactieMatch = $this->koppelMatches(
						$pinTransactieMatch2,
						$pinTransactieMatch1
					);
				} elseif (
					$pinTransactieMatch2->bestelling === null &&
					$pinTransactieMatch1->transactie === null
				) {
					$nieuwePinTransactieMatch = $this->koppelMatches(
						$pinTransactieMatch1,
						$pinTransactieMatch2
					);
				} else {
					throw new CsrGebruikerException(
						'Een van de regels is niet incompleet'
					);
				}

				return $nieuwePinTransactieMatch;
			});

			return $this->tableData([
				$removePinTransactieMatch1,
				$removePinTransactieMatch2,
				$nieuwePinTransactieMatch,
			]);
		}
	}

	/**
	 * @param PinTransactieMatch $missendeTransactie
	 * @param PinTransactieMatch $missendeBestelling
	 * @return PinTransactieMatch
	 */
	private function koppelMatches($missendeTransactie, $missendeBestelling)
	{
		return $this->em->transactional(function () use (
			$missendeTransactie,
			$missendeBestelling
		) {
			$bestelling = $missendeTransactie->bestelling;
			$bestellingInhoud = $bestelling->getProduct(
				CiviProductTypeEnum::PINTRANSACTIE
			);
			$transactie = $missendeBestelling->transactie;

			if ($bestellingInhoud->aantal === $transactie->getBedragInCenten()) {
				$pinTransactieMatch = PinTransactieMatch::match(
					$transactie,
					$bestelling
				);
			} else {
				$pinTransactieMatch = PinTransactieMatch::verkeerdBedrag(
					$transactie,
					$bestelling
				);
			}

			$manager = $this->getDoctrine()->getManager();
			$manager->remove($missendeBestelling);
			$manager->remove($missendeTransactie);
			$manager->flush();
			$manager->persist($pinTransactieMatch);
			$manager->flush();

			return $pinTransactieMatch;
		});
	}

	/**
	 * Crediteer pingedeelte van deze bestelling.
	 * @Route("/fiscaat/pin/crediteer", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function crediteer()
	{
		$form = new PinBestellingCrediterenForm();

		if ($form->validate()) {
			$values = $form->getValues();

			$oudePinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID(
				$values['pinTransactieId']
			);
			$form = new PinBestellingCrediterenForm($oudePinTransactieMatch);
			$values = $form->getValues();

			if ($oudePinTransactieMatch->transactie !== null) {
				throw new CsrGebruikerException('Er bestaat wel een transactie.');
			}

			if ($oudePinTransactieMatch->bestelling === null) {
				throw new CsrGebruikerException(
					'Geen bestelling gevonden om een creditbestelling voor aan te maken'
				);
			}

			$account = $this->civiSaldoRepository->getSaldo(
				$oudePinTransactieMatch->bestelling->uid,
				true
			);
			if (!$account) {
				throw new CsrGebruikerException(
					'Er is geen CiviSaldo voor dit lid gevonden.'
				);
			}

			/** @var PinTransactieMatch $nieuwePinTransactieMatch */
			$nieuwePinTransactieMatch = $this->em->transactional(function () use (
				$account,
				$oudePinTransactieMatch,
				$values
			) {
				$bestelling = $oudePinTransactieMatch->bestelling;
				$bestelling->comment = $values['commentOud'];

				$creditBestelling = $oudePinTransactieMatch->bouwBestelling(
					$this->civiProductRepository,
					$values['commentNieuw'] ?: null,
					$account->uid
				);
				$creditBestelling->id = $this->civiBestellingModel->create(
					$creditBestelling
				);
				$this->civiSaldoRepository->verlagen(
					$account->uid,
					abs($creditBestelling->totaal)
				);

				$manager = $this->getDoctrine()->getManager();
				$manager->flush();

				$oudePinTransactieMatch->status =
					PinTransactieMatchStatusEnum::STATUS_GENEGEERD;
				$oudePinTransactieMatch->notitie = $values['internOud'] ?: null;
				$nieuwePinTransactieMatch = PinTransactieMatch::negeer(
					null,
					$creditBestelling
				);
				$nieuwePinTransactieMatch->notitie = $values['internNieuw'] ?: null;
				$manager->persist($nieuwePinTransactieMatch);

				return $nieuwePinTransactieMatch;
			});

			if ($values['stuurMail']) {
				$datum = date_format_intl(
					$oudePinTransactieMatch->bestelling->moment,
					'cccc d MMMM y H:mm'
				);
				$bedrag = format_bedrag_kaal(
					$nieuwePinTransactieMatch->bestelling->totaal
				);
				$this->stuurMail(
					$account->uid,
					'Uw CiviSaldo is verlaagd',
					"Uit mijn administratie bleek dat uw pinbetaling van {$datum} niet binnengekomen is. " .
						'Dit kan voorkomen als een betaling mislukt of niet goed ingevoerd is. ' .
						"De pinbetaling is teruggedraaid, waardoor uw saldo met € {$bedrag} verlaagd is. " .
						'Mocht dit een vergissing zijn, wilt u dan op dit bericht reageren met een schermafbeelding van de pintransactie?'
				);
			}

			return $this->tableData([
				$oudePinTransactieMatch,
				$nieuwePinTransactieMatch,
			]);
		} else {
			return $form;
		}
	}

	/**
	 * Verander het bedrag in de bestelling.
	 * @Route("/fiscaat/pin/update", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function update()
	{
		$form = new PinBestellingVeranderenForm();

		if ($form->validate()) {
			$values = $form->getValues();

			$oudePinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID(
				$values['pinTransactieId']
			);
			$form = new PinBestellingVeranderenForm($oudePinTransactieMatch);
			$values = $form->getValues();

			if ($oudePinTransactieMatch->transactie === null) {
				throw new CsrGebruikerException(
					'Geen transactie gevonden voor verkeerd bedrag'
				);
			}

			if ($oudePinTransactieMatch->bestelling === null) {
				throw new CsrGebruikerException(
					'Geen bestelling gevonden voor verkeerd bedrag'
				);
			}

			$account = $this->civiSaldoRepository->getSaldo(
				$oudePinTransactieMatch->bestelling->uid,
				true
			);
			if (!$account) {
				throw new CsrGebruikerException(
					'Er is geen CiviSaldo voor dit lid gevonden.'
				);
			}

			/** @var PinTransactieMatch $nieuwePinTransactieMatch */
			$nieuwePinTransactieMatch = $this->em->transactional(function () use (
				$account,
				$oudePinTransactieMatch,
				$values
			) {
				$bestelling = $oudePinTransactieMatch->bestelling;
				$bestelling->comment = $values['commentOud'];

				$correctieBestelling = $oudePinTransactieMatch->bouwBestelling(
					$this->civiProductRepository,
					$values['commentNieuw'] ?: null,
					$account->uid
				);
				$correctieBestelling->id = $this->civiBestellingModel->create(
					$correctieBestelling
				);

				if ($correctieBestelling->totaal > 0) {
					$this->civiSaldoRepository->verlagen(
						$account->uid,
						$correctieBestelling->totaal
					);
				} else {
					$this->civiSaldoRepository->ophogen(
						$account->uid,
						abs($correctieBestelling->totaal)
					);
				}

				$manager = $this->getDoctrine()->getManager();
				$manager->flush();

				$nieuwePinTransactieMatch = PinTransactieMatch::negeer(
					null,
					$correctieBestelling
				);
				$nieuwePinTransactieMatch->notitie = $values['internNieuw'] ?: null;
				$oudePinTransactieMatch->status =
					PinTransactieMatchStatusEnum::STATUS_GENEGEERD;
				$oudePinTransactieMatch->notitie = $values['internOud'] ?: null;
				$manager->persist($nieuwePinTransactieMatch);

				return $nieuwePinTransactieMatch;
			});

			if ($values['stuurMail']) {
				$datum = date_format_intl(
					$oudePinTransactieMatch->transactie->datetime,
					'cccc d MMMM y H:mm'
				);
				$foutBedrag = format_bedrag_kaal(
					$oudePinTransactieMatch->bestelling->getProduct(
						CiviProductTypeEnum::PINTRANSACTIE
					)->aantal
				);
				$correctBedrag = format_bedrag_kaal(
					$oudePinTransactieMatch->transactie->getBedragInCenten()
				);
				$bedrag = format_bedrag_kaal(
					abs($nieuwePinTransactieMatch->bestelling->totaal)
				);
				$actie =
					$nieuwePinTransactieMatch->bestelling->totaal > 0
						? 'verlaagd'
						: 'verhoogd';
				$this->stuurMail(
					$account->uid,
					"Uw CiviSaldo is {$actie}",
					"Op {$datum} is er een pinbetaling van {$foutBedrag} ingevoerd, terwijl u € {$correctBedrag} had gepind. " .
						"De pinbetaling is gecorrigeerd, waardoor uw saldo met € {$bedrag} {$actie} is. " .
						'Mocht dit een vergissing zijn, wilt u dan op dit bericht reageren met een schermafbeelding van de pintransactie?'
				);
			}

			return $this->tableData([
				$oudePinTransactieMatch,
				$nieuwePinTransactieMatch,
			]);
		} else {
			return $form;
		}
	}

	/**
	 * @throws CsrGebruikerException
	 * @Route("/fiscaat/pin/info", methods={"POST"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function info()
	{
		$form = new PinBestellingInfoForm(new PinTransactieMatch());

		if ($form->validate()) {
			// Find match
			$values = $form->getValues();
			$pinTransactieMatch = $this->pinTransactieMatchRepository->find(
				$values['id']
			);
			$form = new PinBestellingInfoForm($pinTransactieMatch);
			$values = $form->getValues();
			$pinTransactieMatch->notitie = $values['intern'] ?: null;
			if ($pinTransactieMatch->bestelling !== null) {
				$pinTransactieMatch->bestelling->comment = $values['comment'] ?: null;
			}
			$this->em->flush();
			return $this->tableData([$pinTransactieMatch]);
		} else {
			$selection = $this->getDataTableSelection();

			if (count($selection) !== 1) {
				throw new CsrGebruikerException('Selecteer één regel tegelijk.');
			} else {
				/** @var PinTransactieMatch $pinTransactieMatch */
				$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUUID(
					$selection[0]
				);
				return new PinBestellingInfoForm($pinTransactieMatch);
			}
		}
	}

	/**
	 * @Route("/fiscaat/pin/negeer", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function negeer()
	{
		$selection = $this->getDataTableSelection();
		$form = new PinTransactieMatchNegerenForm($selection);

		if ($form->validate()) {
			$values = $form->getValues();

			$updated = $this->em->transactional(function () use ($values) {
				$updated = [];
				foreach (explode(',', $values['ids']) as $uuid) {
					$pinTransactieMatch = $this->pinTransactieMatchRepository->retrieveByUuid(
						$uuid
					);

					if (!$pinTransactieMatch) {
						throw new CsrGebruikerException('Match niet gevonden');
					}

					if (
						$pinTransactieMatch->status ===
						PinTransactieMatchStatusEnum::STATUS_GENEGEERD
					) {
						$pinTransactieMatch->status = $pinTransactieMatch->logischeStatus();
					} else {
						$pinTransactieMatch->status =
							PinTransactieMatchStatusEnum::STATUS_GENEGEERD;
						$pinTransactieMatch->notitie = $values['intern'] ?: null;
					}
					$updated[] = $pinTransactieMatch;
				}
				return $updated;
			});

			return $this->tableData($updated);
		} else {
			return $form;
		}
	}

	/**
	 * Verwijder matches die geen bestelling en transactie hebben. Dit kan gebeuren als een probleem binnen het
	 * socciesysteem wordt opgelost.
	 * @Route("/fiscaat/pin/heroverweeg", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function heroverweeg()
	{
		$deleted = $this->em->transactional(function () {
			$alleMatches = $this->pinTransactieMatchRepository->findAll();
			$deleted = [];
			$manager = $this->getDoctrine()->getManager();

			foreach ($alleMatches as $match) {
				if (!$match->bestelling || $match->transactie) {
					continue;
				}
				$pin = $match->bestelling->getProduct(
					CiviProductTypeEnum::PINTRANSACTIE
				);
				$pinCorrectie = $match->bestelling->getProduct(
					CiviProductTypeEnum::PINCORRECTIE
				);
				if ($match->bestelling->deleted || (!$pin && !$pinCorrectie)) {
					$deleted[] = new RemoveDataTableEntry(
						$match->id,
						PinTransactieMatch::class
					);
					$manager->remove($match);
				}
			}

			$manager->flush();

			return $deleted;
		});

		return $this->tableData($deleted === true ? [] : $deleted);
	}

	private function stuurMail($uid, $onderwerp, $melding)
	{
		$ontvanger = ProfielRepository::get($uid);
		if (!$ontvanger) {
			return;
		}
		$bcc = $this->getProfiel();
		$civiSaldo = $ontvanger->getCiviSaldo() * 100;
		$saldo = format_bedrag_kaal($civiSaldo);
		$saldoMelding = $civiSaldo < 0 ? ' Leg a.u.b. in.' : '';

		$bericht = "Beste {$ontvanger->getNaam('civitas')},

{$melding}

Uw CiviSaldo is nu € {$saldo}.{$saldoMelding}

Met vriendelijke groet,
h.t. Fiscus";

		$mail = new Mail($ontvanger->getEmailOntvanger(), $onderwerp, $bericht);
		$mail->setFrom($_ENV['EMAIL_FISCUS'], 'Fiscus C.S.R. Delft');
		$mail->addBcc($bcc->getEmailOntvanger());
		$this->mailService->send($mail);
	}
}
