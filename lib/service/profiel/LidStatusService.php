<?php

namespace CsrDelft\service\profiel;

use CsrDelft\common\Mail;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\HostUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\AbstractProfielLogEntry;
use CsrDelft\model\entity\profiel\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use CsrDelft\model\entity\profiel\ProfielLogVeldenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\maalcie\MaaltijdAbonnementenService;
use CsrDelft\service\MailService;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

/**
 * Operaties die te doen hebben met het veranderen van de lidstatus van een lid.
 */
class LidStatusService
{
	public function __construct(
		private readonly Security $security,
		private readonly ProfielRepository $profielRepository,
		private readonly MailService $mailService,
		private readonly Environment $twig,
		private readonly MaaltijdAbonnementenService $maaltijdAbonnementenService,
		private readonly CorveeTakenRepository $corveeTakenRepository,
		private readonly BoekExemplaarRepository $boekExemplaarRepository
	) {
	}

	public function wijzig_lidstatus(Profiel $profiel, $oudestatus)
	{
		$changes = [];
		// Maaltijd en corvee bijwerken
		$geenAboEnCorveeVoor = [
			LidStatus::Oudlid,
			LidStatus::Erelid,
			LidStatus::Nobody,
			LidStatus::Exlid,
			LidStatus::Commissie,
			LidStatus::Overleden,
		];
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
		$wordtinactief = [
			LidStatus::Oudlid,
			LidStatus::Erelid,
			LidStatus::Nobody,
			LidStatus::Exlid,
			LidStatus::Overleden,
		];
		$wasactief = [
			LidStatus::Noviet,
			LidStatus::Gastlid,
			LidStatus::Lid,
			LidStatus::Kringel,
		];
		if (
			in_array($profiel->status, $wordtinactief) &&
			in_array($oudestatus, $wasactief)
		) {
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
	private function disableMaaltijdabos(Profiel $profiel, $oudestatus)
	{
		$aantal = $this->maaltijdAbonnementenService->verwijderAbonnementenVoorLid(
			$profiel
		);
		if ($aantal > 0) {
			return [
				new ProfielLogTextEntry('Afmelden abo\'s: ' . $aantal . ' uitgezet.'),
			];
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
	private function removeToekomstigeCorvee(Profiel $profiel, $oudestatus)
	{
		$taken = $this->corveeTakenRepository->getKomendeTakenVoorLid($profiel);
		$aantal = $this->corveeTakenRepository->verwijderTakenVoorLid(
			$profiel->uid
		);
		if (sizeof($taken) !== $aantal) {
			FlashUtil::setFlashWithContainerFacade(
				'Niet alle toekomstige corveetaken zijn verwijderd!',
				-1
			);
		}
		$changes = [];
		if ($aantal > 0) {
			$change = new ProfielLogCoveeTakenVerwijderChange([]);
			foreach ($taken as $taak) {
				$change->corveetaken[] =
					DateUtil::dateFormatIntl($taak->getBeginMoment(), 'E d-MM yyyy') .
					' ' .
					$taak->corveeFunctie->naam;
			}
			$changes[] = $change;

			// Corveeceasar mailen over vrijvallende corveetaken.
			$bericht = $this->twig->render(
				'mail/bericht/toekomstigcorveeverwijderd.mail.twig',
				[
					'aantal' => $aantal,
					'naam' => $profiel->getNaam('volledig'),
					'uid' => $profiel->uid,
					'oud' => $oudestatus,
					'nieuw' => $profiel->status,
					'change' => $change->toHtml(),
					'admin' => $this->security->getUser()->profiel->getNaam(),
				]
			);
			$mail = new Mail(
				['corvee@csrdelft.nl' => 'CorveeCaesar'],
				'Lid-af: toekomstig corvee verwijderd',
				$bericht
			);
			$mail->addBcc(['pubcie@csrdelft.nl' => 'PubCie C.S.R.']);
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
	private function notifyFisci(Profiel $profiel, $oudestatus)
	{
		// Saldi ophalen
		$saldi = '';
		$saldi .= 'CiviSaldo: ' . $profiel->getCiviSaldo() . "\n";

		$bericht = $this->twig->render('mail/bericht/lidafmeldingfisci.mail.twig', [
			'naam' => $profiel->getNaam('volledig'),
			'uid' => $profiel->uid,
			'oud' => $oudestatus,
			'nieuw' => $profiel->status,
			'saldi' => $saldi,
			'admin' => $this->security->getUser()->profiel->getNaam(),
		]);
		$to = [
			'fiscus@csrdelft.nl' => 'Fiscus C.S.R.',
			'maalcie-fiscus@csrdelft.nl' => 'MaalCie fiscus C.S.R.',
			'soccie@csrdelft.nl' => 'SocCie C.S.R.',
		];

		$mail = new Mail($to, 'Melding lid-af worden', $bericht);
		$mail->addBcc(['pubcie@csrdelft.nl' => 'PubCie C.S.R.']);

		return $this->mailService->send($mail);
	}

	/**
	 * Mail naar bibliothecaris en leden over geleende boeken
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyBibliothecaris(Profiel $profiel, $oudestatus)
	{
		$geleend = $this->boekExemplaarRepository->getGeleend($profiel);
		if (!is_array($geleend)) {
			$geleend = [];
		}
		// Lijst van boeken genereren
		$bknleden = $bkncsr = [
			'kopje' => '',
			'lijst' => '',
			'aantal' => 0,
		];
		foreach ($geleend as $exemplaar) {
			$boek = $exemplaar->boek;
			if ($exemplaar->isBiebBoek()) {
				$bkncsr['aantal']++;
				$bkncsr['lijst'] .= "{$boek->titel} door {$boek->auteur}\n";
				$bkncsr['lijst'] .=
					' - ' . HostUtil::getCsrRoot() . "/bibliotheek/boek/{$boek->id}\n";
			} else {
				$bknleden['aantal']++;
				$bknleden['lijst'] .= "{$boek->titel} door {$boek->auteur}\n";
				$bknleden['lijst'] .=
					' - ' . HostUtil::getCsrRoot() . "/bibliotheek/boek/{$boek->id}\n";
				$naam = $exemplaar->eigenaar->getNaam('volledig');
				$bknleden['lijst'] .= " - boek is geleend van: $naam\n";
			}
		}
		// Kopjes
		$mv = $profiel->geslacht->getValue() === Geslacht::Man ? 'hem' : 'haar';
		$enkelvoud = "Het volgende boek is nog door {$mv} geleend";
		$meervoud = "De volgende boeken zijn nog door {$mv} geleend";
		if ($bkncsr['aantal']) {
			$bkncsr['kopje'] =
				($bkncsr['aantal'] > 1 ? $meervoud : $enkelvoud) .
				' van de C.S.R.-bibliotheek:';
		}
		if ($bknleden['aantal']) {
			$bknleden['kopje'] =
				($bknleden['aantal'] > 1 ? $meervoud : $enkelvoud) . ' van leden:';
		}

		// Alleen mailen als er C.S.R.boeken zijn
		if ($bkncsr['aantal'] == 0) {
			return false;
		}

		$to = [
			'bibliothecaris@csrdelft.nl' => 'Bibliothecaris C.S.R.',
			$profiel->getPrimaryEmail() => $profiel->getNaam('civitas'),
		];
		$bericht = $this->twig->render(
			'mail/bericht/lidafgeleendebiebboeken.mail.twig',
			[
				'naam' => $profiel->getNaam('volledig'),
				'uid' => $profiel->uid,
				'oud' => substr((string) $oudestatus, 2),
				'nieuw' =>
					$profiel->status === LidStatus::Nobody
						? 'GEEN LID'
						: substr($profiel->status, 2),
				'csrlijst' => $bkncsr['kopje'] . "\n" . $bkncsr['lijst'],
				'ledenlijst' =>
					$bkncsr['aantal'] > 0
						? 'Verder ter informatie: ' .
							$bknleden['kopje'] .
							"\n" .
							$bknleden['lijst']
						: '',
				'admin' => $this->security->getUser()->profiel->getNaam(),
			]
		);
		$mail = new Mail($to, 'Geleende boeken - Melding lid-af worden', $bericht);
		$mail->addBcc(['pubcie@csrdelft.nl' => 'PubCie C.S.R.']);

		return $this->mailService->send($mail);
	}

	/**
	 * Verwijdert overbodige velden van het profiel.
	 * @param Profiel $profiel
	 * @return AbstractProfielLogEntry[]  Een logentry als er wijzigingen zijn.
	 */
	private function verwijderVelden(Profiel $profiel)
	{
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
	public function verwijderVeldenUpdate(Profiel $profiel)
	{
		$changes = $this->verwijderVelden($profiel);
		if (empty($changes)) {
			return false;
		}
		$profiel->changelog[] = new ProfielUpdateLogGroup(
			$this->security->getUser()?->getUserIdentifier(),
			new DateTime(),
			$changes
		);
		$this->profielRepository->update($profiel);
		return true;
	}
}
