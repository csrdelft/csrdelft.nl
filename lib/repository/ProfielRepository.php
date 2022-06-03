<?php

namespace CsrDelft\repository;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\LDAP;
use CsrDelft\common\Mail;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\AbstractProfielLogEntry;
use CsrDelft\model\entity\profiel\ProfielCreateLogGroup;
use CsrDelft\model\entity\profiel\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use CsrDelft\model\entity\profiel\ProfielLogValueChange;
use CsrDelft\model\entity\profiel\ProfielLogVeldenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\security\LoginService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;


/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method Profiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profiel[]    findAll()
 * @method Profiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfielRepository extends AbstractRepository {
	/**
	 * @var MaaltijdAbonnementenRepository
	 */
	private $maaltijdAbonnementenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var BoekExemplaarRepository
	 */
	private $boekExemplaarModel;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(
		ManagerRegistry $registry,
		Security $security,
		Environment $twig,
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		CorveeTakenRepository $corveeTakenRepository,
		BoekExemplaarRepository $boekExemplaarModel,
		MailService $mailService
	) {
		parent::__construct($registry, Profiel::class);

		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->boekExemplaarModel = $boekExemplaarModel;
		$this->security = $security;
		$this->twig = $twig;
		$this->mailService = $mailService;
	}

	public static function changelog(array $diff, $uid) {
		if (empty($diff)) {
			return null;
		}
		$changes = [];
		foreach ($diff as $change) {
			$changes[] = new ProfielLogValueChange($change->property, $change->old_value, $change->new_value);
		}
		return new ProfielUpdateLogGroup($uid, date_create_immutable(), $changes);
	}

	/**
	 * @param string $uid
	 * @return Profiel|false
	 * @deprecated Gebruik ProfielRepository::find($uid)
	 */
	public static function get($uid) {
		if ($uid == null || !ctype_alnum($uid) || strlen($uid) != 4) {
			return null;
		}

		$model = ContainerFacade::getContainer()->get(ProfielRepository::class);

		return $model->find($uid);
	}

	/**
	 * @param $uid
	 * @param $vorm
	 * @return string|null
	 * @deprecated Gebruik Profiel::getNaam($vorm)
	 */
	public static function getNaam($uid, $vorm = 'civitas') {
		$profiel = static::get($uid);
		if (!$profiel) {
			return null;
		}
		return $profiel->getNaam($vorm);
	}

	/**
	 * @param $uid
	 * @param $vorm
	 * @return string|null
	 * @deprecated Gebruik Profiel::getLink($vorm)
	 */
	public static function getLink($uid, $vorm = 'civitas') {
		$profiel = static::get($uid);
		if (!$profiel) {
			return null;
		}
		return $profiel->getLink($vorm);
	}

	/**
	 * @param $uid
	 * @return bool
	 * @deprecated Doe een null check op ProfielRepository::find($uid)
	 */
	public static function existsUid($uid) {
		if (!ctype_alnum($uid) || strlen($uid) != 4) {
			return false;
		}
		$model = ContainerFacade::getContainer()->get(ProfielRepository::class);
		return $model->find($uid) !== null;
	}

	public function existsDuck($duck) {
		return count($this->findBy(['duckname' => $duck])) !== 0;
	}

	public function nieuw($lidjaar, $lidstatus) {
		$user = $this->security->getUser();

		// Create kan door x999 gedaan worden
		$logUsername = $user == null ? LoginService::UID_EXTERN : $user->getUsername();
		$profiel = new Profiel();
		$profiel->lidjaar = $lidjaar;
		$profiel->status = $lidstatus;
		$profiel->ontvangtcontactueel = OntvangtContactueel::Nee();
		$profiel->changelog = [new ProfielCreateLogGroup($logUsername, new DateTime())];
		return $profiel;
	}

	/**
	 * @param Profiel $profiel
	 * @throws NonUniqueResultException
	 */
	public function create(Profiel $profiel) {
		// Lichting zijn de laatste 2 cijfers van lidjaar
		$jj = substr($profiel->lidjaar, 2, 2);
		try {
			$laatste_uid = $this->createQueryBuilder('p')
				->select('MAX(p.uid)')
				->where('p.uid LIKE :jj')
				->setParameter('jj', $jj . "__")
				->getQuery()
				->getSingleScalarResult();
			$volgnummer = intval(substr($laatste_uid, 2, 2)) + 1;
		} catch (NoResultException $exception) {
			$volgnummer = 1;
		}
		$profiel->uid = $jj . sprintf('%02d', $volgnummer);

		$this->save($profiel);
	}

	/**
	 * @param Profiel $profiel
	 */
	public function update(Profiel $profiel) {
		try {
			$this->save_ldap($profiel);
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1); //TODO: logging
		}
		$this->save($profiel);
	}

	/**
	 * Sla huidige objectstatus op in LDAP.
	 *
	 * @param Profiel $profiel
	 * @param LDAP $ldap persistent connection
	 * @return bool success
	 */
	public function save_ldap(Profiel $profiel, LDAP $ldap = null) {
		$success = true;

		if ($ldap === null) {
			$ldap = new LDAP();
			$persistent = false;
		} else {
			$persistent = true;
		}

		// Alleen leden, gastleden, novieten en kringels staan in LDAP (en Knorrie öO~ en Gerrit Uitslag)
		if (preg_match('/^S_(LID|GASTLID|NOVIET|KRINGEL|CIE)$/', $profiel->status) or $profiel->uid == '9808' or $profiel->uid == '0431') {

			// LDAP entry in elkaar zetten
			$entry = array();
			$entry['uid'] = $profiel->uid;
			$entry['givenname'] = $profiel->voornaam;
			$entry['sn'] = $profiel->achternaam;
			if (substr($entry['uid'], 0, 2) == 'x2') {
				$entry['cn'] = $entry['sn'];
			} else {
				$entry['cn'] = $profiel->getNaam();
			}
			$entry['mail'] = $profiel->getPrimaryEmail();
			$entry['homephone'] = $profiel->telefoon;
			$entry['mobile'] = $profiel->mobiel;
			$entry['homepostaladdress'] = implode('', array($profiel->adres, $profiel->postcode, $profiel->woonplaats));
			$entry['o'] = 'C.S.R. Delft';
			$entry['mozillanickname'] = $profiel->nickname;
			$entry['mozillausehtmlmail'] = 'FALSE';
			$entry['mozillahomestreet'] = $profiel->adres;
			$entry['mozillahomelocalityname'] = $profiel->woonplaats;
			$entry['mozillahomepostalcode'] = $profiel->postcode;
			$entry['mozillahomecountryname'] = $profiel->land;
			$entry['mozillahomeurl'] = $profiel->website;
			$entry['description'] = 'Ledenlijst C.S.R. Delft';
			if ($profiel->account) {
				$entry['userPassword'] = $profiel->account->pass_hash;
			}

			$woonoord = $profiel->getWoonoord();
			if ($woonoord) {
				$entry['ou'] = $woonoord->naam;
			}

			# lege velden er uit gooien
			foreach ($entry as $i => $e) {
				if ($e == '') {
					unset($entry[$i]);
				}
			}

			// Bestaat deze uid al in LDAP? dan wijzigen, anders aanmaken
			if ($ldap->isLid($entry['uid'])) {
				$success = $ldap->modifyLid($entry['uid'], $entry);
			} else {
				$success = $ldap->addLid($entry['uid'], $entry);
			}
		} else {
			// Als het een andere status is even kijken of de uid in LDAP voorkomt, zo ja wissen
			if ($ldap->isLid($profiel->uid)) {
				$success = $ldap->removeLid($profiel->uid);
			}
		}

		if (!$persistent) {
			$ldap->disconnect();
		}

		return $success;
	}

	public function wijzig_lidstatus(Profiel $profiel, $oudestatus) {
		$changes = [];
		// Maaltijd en corvee bijwerken
		$geenAboEnCorveeVoor = array(LidStatus::Oudlid, LidStatus::Erelid, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Commissie, LidStatus::Overleden);
		if (in_array($profiel->status, $geenAboEnCorveeVoor)) {
			//maaltijdabo's uitzetten (R_ETER is een S_NOBODY die toch een abo mag hebben)
			$account = $profiel->account;
			if (!$account || $account->perm_role !== AccessRole::Eter) {
				$removedabos = $this->disableMaaltijdabos($profiel, $oudestatus);
				$changes = array_merge($changes, $removedabos);
			}
			// Toekomstige corveetaken verwijderen
			$removedcorvee = $this->removeToekomstigeCorvee($profiel, $oudestatus);
			$changes = array_merge($changes, $removedcorvee);
		}
		// Mailen naar fisci,bibliothecaris...
		$wordtinactief = array(LidStatus::Oudlid, LidStatus::Erelid, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Overleden);
		$wasactief = array(LidStatus::Noviet, LidStatus::Gastlid, LidStatus::Lid, LidStatus::Kringel);
		if (in_array($profiel->status, $wordtinactief) && in_array($oudestatus, $wasactief)) {
			$this->notifyFisci($profiel, $oudestatus);
			$this->notifyBibliothecaris($profiel, $oudestatus);
		}
		return array_merge($changes, $this->verwijderVelden($profiel));
	}

	/**
	 * Zet alle abo's uit en geeft een changelog-regel terug.
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return AbstractProfielLogEntry[] wijzigingen
	 */
	private function disableMaaltijdabos(Profiel $profiel, $oudestatus) {
		$aantal = $this->maaltijdAbonnementenRepository->verwijderAbonnementenVoorLid($profiel->uid);
		if ($aantal > 0) {
			return [new ProfielLogTextEntry('Afmelden abo\'s: ' . $aantal . ' uitgezet.')];
		}
		return [];
	}

	/**
	 * Verwijder toekomstige corveetaken en geef changelog-regel terug.
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return AbstractProfielLogEntry[] wijzigingen
	 */
	private function removeToekomstigeCorvee(Profiel $profiel, $oudestatus) {
		$taken = $this->corveeTakenRepository->getKomendeTakenVoorLid($profiel);
		$aantal = $this->corveeTakenRepository->verwijderTakenVoorLid($profiel->uid);
		if (sizeof($taken) !== $aantal) {
			setMelding('Niet alle toekomstige corveetaken zijn verwijderd!', -1);
		}
		$changes = [];
		if ($aantal > 0) {
			$change = new ProfielLogCoveeTakenVerwijderChange([]);
			foreach ($taken as $taak) {
				$change->corveetaken[] = strftime('%a %e-%m-%Y', $taak->getBeginMoment()) . ' ' . $taak->corveeFunctie->naam;
			}
			$changes[] = $change;

			// Corveeceasar mailen over vrijvallende corveetaken.
			$bericht = $this->twig->render('mail/bericht/toekomstigcorveeverwijderd.mail.twig', [
				'aantal' => $aantal,
				'naam' => $profiel->getNaam('volledig'),
				'uid' => $profiel->uid,
				'oud' => $oudestatus,
				'nieuw' => $profiel->status,
				'change' => $change->toHtml(),
				'admin' => $this->security->getUser()->profiel->getNaam()
			]);
			$mail = new Mail(array('corvee@csrdelft.nl' => 'CorveeCaesar'), 'Lid-af: toekomstig corvee verwijderd', $bericht);
			$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));
			$this->mailService->send($mail);
		}
		return $changes;
	}

	/**
	 * Mail naar fisci over statuswijzigingen. Kunnen zij hun systemen weer mee updaten.
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyFisci(Profiel $profiel, $oudestatus) {
		// Saldi ophalen
		$saldi = '';
		$saldi .= 'CiviSaldo: ' . $profiel->getCiviSaldo() . "\n";

		$bericht = $this->twig->render('mail/bericht/lidafmeldingfisci.mail.twig', [
			'naam' => $profiel->getNaam('volledig'),
			'uid' => $profiel->uid,
			'oud' => $oudestatus,
			'nieuw' => $profiel->status,
			'saldi' => $saldi,
			'admin' => $this->security->getUser()->profiel->getNaam()
		]);
		$to = array(
			'fiscus@csrdelft.nl' => 'Fiscus C.S.R.',
			'maalcie-fiscus@csrdelft.nl' => 'MaalCie fiscus C.S.R.',
			'soccie@csrdelft.nl' => 'SocCie C.S.R.'
		);

		$mail = new Mail($to, 'Melding lid-af worden', $bericht);
		$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));

		return $this->mailService->send($mail);
	}

	/**
	 * Mail naar bibliothecaris en leden over geleende boeken
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyBibliothecaris(Profiel $profiel, $oudestatus) {
		$geleend = $this->boekExemplaarModel->getGeleend($profiel);
		if (!is_array($geleend)) {
			$geleend = array();
		}
		// Lijst van boeken genereren
		$bknleden = $bkncsr = array(
			'kopje' => '',
			'lijst' => '',
			'aantal' => 0
		);
		foreach ($geleend as $exemplaar) {
			$boek = $exemplaar->boek;
			if ($exemplaar->isBiebBoek()) {
				$bkncsr['aantal']++;
				$bkncsr['lijst'] .= "{$boek->titel} door {$boek->auteur}\n";
				$bkncsr['lijst'] .= " - " . getCsrRoot() . "/bibliotheek/boek/{$boek->id}\n";
			} else {
				$bknleden['aantal']++;
				$bknleden['lijst'] .= "{$boek->titel} door {$boek->auteur}\n";
				$bknleden['lijst'] .= " - " . getCsrRoot() . "/bibliotheek/boek/{$boek->id}\n";
				$naam = $exemplaar->eigenaar->getNaam('volledig');
				$bknleden['lijst'] .= " - boek is geleend van: $naam\n";
			}
		}
		// Kopjes
		$mv = ($profiel->geslacht->getValue() === Geslacht::Man ? 'hem' : 'haar');
		$enkelvoud = "Het volgende boek is nog door {$mv} geleend";
		$meervoud = "De volgende boeken zijn nog door {$mv} geleend";
		if ($bkncsr['aantal']) {
			$bkncsr['kopje'] = ($bkncsr['aantal'] > 1 ? $meervoud : $enkelvoud) . " van de C.S.R.-bibliotheek:";
		}
		if ($bknleden['aantal']) {
			$bknleden['kopje'] = ($bknleden['aantal'] > 1 ? $meervoud : $enkelvoud) . " van leden:";
		}

		// Alleen mailen als er C.S.R.boeken zijn
		if ($bkncsr['aantal'] == 0) {
			return false;
		}

		$to = array(
			'bibliothecaris@csrdelft.nl' => 'Bibliothecaris C.S.R.',
			$profiel->getPrimaryEmail() => $profiel->getNaam('civitas')
		);
		$bericht = $this->twig->render('mail/bericht/lidafgeleendebiebboeken.mail.twig', [
			'naam' => $profiel->getNaam('volledig'),
			'uid' => $profiel->uid,
			'oud' => substr($oudestatus, 2),
			'nieuw' => ($profiel->status === LidStatus::Nobody ? 'GEEN LID' : substr($profiel->status, 2)),
			'csrlijst' => $bkncsr['kopje'] . "\n" . $bkncsr['lijst'],
			'ledenlijst' => ($bkncsr['aantal'] > 0 ? "Verder ter informatie: " . $bknleden['kopje'] . "\n" . $bknleden['lijst'] : ''),
			'admin' => $this->security->getUser()->profiel->getNaam()
		]);
		$mail = new Mail($to, 'Geleende boeken - Melding lid-af worden', $bericht);
		$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));

		return $this->mailService->send($mail);
	}

	/**
	 * Verwijdert overbodige velden van het profiel.
	 * @param Profiel $profiel
	 * @return AbstractProfielLogEntry[]  Een logentry als er wijzigingen zijn.
	 */
	private function verwijderVelden(Profiel $profiel) {
		$velden_verwijderd = [];
		foreach (Profiel::$properties_lidstatus as $key => $status_allowed) {
			if (!$profiel->propertyMogelijk($key)) {
				$was_gevuld = $profiel->$key !== null;
				$profiel->$key = null;
				foreach ($profiel->changelog as $logGroup) {
					$was_gevuld |= $logGroup->censureerVeld($key);
				}
				if ($was_gevuld) {
					$velden_verwijderd[] = $key;
				}
			}
		}
		if (empty($velden_verwijderd)) {
			return [];
		} else {
			return [new ProfielLogVeldenVerwijderChange($velden_verwijderd)];
		}
	}

	/**
	 * Verwijder onnodige velden van het profiel. Slaat wijzigingen op in database.
	 * @param Profiel $profiel
	 */
	public function verwijderVeldenUpdate(Profiel $profiel) {
		$changes = $this->verwijderVelden($profiel);
		if (empty($changes)) {
			return false;
		}
		$profiel->changelog[] = new ProfielUpdateLogGroup($this->security->getUser()->getUsername(), new DateTime(), $changes);
		$this->update($profiel);
		return true;
	}

	/**
	 * Geef een lidjaar mee om alleen novieten van een specifiek lidjaar op te halen.
	 *
	 * @param null $lidjaar
	 * @return int|mixed|string
	 */
	public function getNovietenVanLaatsteLidjaar($lidjaar = null) {
		if (empty($lidjaar)) {
			return $this->createQueryBuilder('p')
				->where('p.status = :status')
				->setParameter('status', LidStatus::Noviet)
				->getQuery()->getResult();
		}

		return $this->createQueryBuilder('p')
			->where('p.status = :status and p.lidjaar = :lidjaar')
			->setParameter('lidjaar', $lidjaar)
			->setParameter('status', LidStatus::Noviet)
			->getQuery()->getResult();
	}

	/**
	 * @param $toegestaan
	 * @return Profiel[]
	 */
	public function findByLidStatus($toegestaan) {
		return $this->createQueryBuilder('p')
			->where('p.status in (:toegestaan)')
			->setParameter('toegestaan', $toegestaan)
			->getQuery()->getResult();
	}

	public function setEetwens(Profiel $profiel, $eetwens) {
		if ($profiel->eetwens === $eetwens) {
			return;
		}
		$profiel->eetwens = $eetwens;
		$this->update($profiel);
	}

}
