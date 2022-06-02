<?php

namespace CsrDelft\service\forum;

use CsrDelft\common\Mail;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\security\CsrSecurity;
use CsrDelft\service\security\SuService;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ForumMeldingenService
{
	/**
	 * @var SuService
	 */
	private $suService;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingRepository;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var MailService
	 */
	private $mailService;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var CsrSecurity
	 */
	private $security;

	public function __construct(
		Environment                  $twig,
		CsrSecurity                  $security,
		MailService                  $mailService,
		SuService                    $suService,
		ProfielRepository            $profielRepository,
		LidInstellingenRepository    $lidInstellingenRepository,
		ForumDradenMeldingRepository $forumDradenMeldingRepository,
		ForumDelenMeldingRepository  $forumDelenMeldingRepository
	)
	{
		$this->suService = $suService;
		$this->forumDradenMeldingRepository = $forumDradenMeldingRepository;
		$this->twig = $twig;
		$this->mailService = $mailService;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->profielRepository = $profielRepository;
		$this->security = $security;
	}

	public function stuurDraadMeldingen(ForumPost $post)
	{
		$this->stuurDraadMeldingenNaarVolgers($post);
		$this->stuurDraadMeldingenNaarGenoemden($post);
	}

	/**
	 * Stuurt meldingen van nieuw bericht naar leden met meldingsniveau op altijd
	 *
	 * @param ForumPost $post
	 */
	private function stuurDraadMeldingenNaarVolgers(ForumPost $post)
	{
		$auteur = $this->profielRepository->find($post->uid);
		// Laad meldingsbericht in
		foreach ($this->forumDradenMeldingRepository->getAltijdMeldingVoorDraad($post->draad) as $volger) {
			$volgerProfiel = $this->profielRepository->find($volger->uid);

			// Stuur geen meldingen als lid niet gevonden is of lid de auteur
			if (!$volgerProfiel || $volgerProfiel->uid === $post->uid) {
				continue;
			}

			$account = $volgerProfiel->account;

			if (!$account) {
				$this->forumDradenMeldingRepository->remove($volger);
			} else {
				$this->stuurDraadMelding($account, $auteur, $post, $post->draad, 'mail/bericht/forumaltijdmelding.mail.twig');
			}
		}
	}

	/**
	 * Stuurt meldingen van nieuw bericht naar leden die genoemd / geciteerd worden in bericht
	 *
	 * @param ForumPost $post
	 */
	public function stuurDraadMeldingenNaarGenoemden(ForumPost $post)
	{
		$auteur = $this->profielRepository->find($post->uid);
		$draad = $post->draad;

		// Laad meldingsbericht in
		$genoemden = $this->zoekGenoemdeLeden($post->tekst);
		foreach ($genoemden as $uid) {
			$genoemde = $this->profielRepository->find($uid);

			// Stuur geen meldingen als lid niet gevonden is, lid de auteur is of als lid geen meldingen wil voor draadje
			// Met laatste voorwaarde worden ook leden afgevangen die sowieso al een melding zouden ontvangen
			if (!$genoemde || !$genoemde->account || $genoemde->uid === $post->uid || !ForumDraadMeldingNiveau::isVERMELDING($this->getDraadMeldingNiveauVoorLid($post->draad, $genoemde->uid))) {
				continue;
			}

			$magMeldingKrijgen = $this->suService->alsLid($genoemde->account, function () use ($draad) {
				return $draad->magMeldingKrijgen();
			});

			if (!$magMeldingKrijgen) {
				continue;
			}

			$this->stuurDraadMelding($genoemde->account, $auteur, $post, $post->draad, 'mail/bericht/forumvermeldingmelding.mail.twig');
		}
	}

	/**
	 * Zoek genoemde leden in gegeven bericht
	 *
	 * @param string $bericht
	 * @return string[]
	 */
	public function zoekGenoemdeLeden($bericht)
	{
		$regex = "/\[(?:lid|citaat)=?\s*]?\s*([[:alnum:]]+)\s*[\[\]]/";
		preg_match_all($regex, $bericht, $leden);

		return array_unique($leden[1]);
	}

	public function getDraadMeldingNiveauVoorLid(ForumDraad $draad, $uid = null)
	{
		if ($uid === null) $uid = $this->security->getAccount()->getUserIdentifier();

		$voorkeur = $this->forumDradenMeldingRepository->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
		if ($voorkeur) {
			return $voorkeur->niveau;
		} else {
			$wilMeldingBijVermelding = $this->lidInstellingenRepository->getInstellingVoorLid('forum', 'meldingStandaard', $uid);
			return $wilMeldingBijVermelding === 'ja' ? ForumDraadMeldingNiveau::VERMELDING() : ForumDraadMeldingNiveau::NOOIT();
		}
	}

	/**
	 * Verzendt mail
	 *
	 * @param Account $ontvanger
	 * @param Profiel $auteur
	 * @param ForumPost $post
	 * @param ForumDraad $draad
	 * @param $template
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	private function stuurDraadMelding(Account $ontvanger, Profiel $auteur, ForumPost $post, ForumDraad $draad, $template)
	{
		// Stel huidig UID in op ontvanger om te voorkomen dat ontvanger privé of andere persoonlijke info te zien krijgt
		$this->suService->alsLid($ontvanger, function () use ($ontvanger, $auteur, $post, $draad, $template) {
			$bericht = $this->twig->render($template, [
				'naam' => $ontvanger->profiel->getNaam('civitas'),
				'auteur' => $auteur->getNaam('civitas'),
				'postlink' => $post->getLink(true),
				'titel' => $draad->titel,
				'tekst' => str_replace('\r\n', "\n", $post->tekst),
			]);

			$mail = new Mail($ontvanger->profiel->getEmailOntvanger(), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
			$this->mailService->send($mail);
		});
	}

	/**
	 * Stuur alle meldingen rondom forumdelen.
	 * @param ForumPost $post
	 */
	public function stuurDeelMeldingen(ForumPost $post)
	{
		$this->stuurDeelMeldingenNaarVolgers($post);
	}

	/**
	 * Verzendt mail
	 *
	 * @param Account $ontvanger
	 * @param Profiel $auteur
	 * @param ForumPost $post
	 * @param ForumDraad $draad
	 * @param ForumDeel $deel
	 */
	private function stuurDeelMelding(Account $ontvanger, Profiel $auteur, ForumPost $post, ForumDraad $draad, ForumDeel $deel)
	{

		// Stel huidig UID in op ontvanger om te voorkomen dat ontvanger privé of andere persoonlijke info te zien krijgt
		$this->suService->alsLid($ontvanger, function () use ($draad, $deel, $ontvanger, $auteur, $post) {
			$bericht = $this->twig->render('mail/bericht/forumdeelmelding.mail.twig', [
				'naam' => $ontvanger->profiel->getNaam('civitas'),
				'auteur' => $auteur->getNaam('civitas'),
				'postlink' => $post->getLink(true),
				'titel' => $draad->titel,
				'forumdeel' => $deel->titel,
				'tekst' => str_replace('\r\n', "\n", $post->tekst),
			]);
			if ($draad->magMeldingKrijgen()) {
				$mail = new Mail($ontvanger->profiel->getEmailOntvanger(), 'C.S.R. Forum: nieuw draadje in ' . $deel->titel . ': ' . $draad->titel, $bericht);
				$this->mailService->send($mail);
			}
		});
	}

	/**
	 * Stuurt meldingen van nieuw bericht naar leden die forumdeel volgen.
	 *
	 * @param ForumPost $post
	 */
	private function stuurDeelMeldingenNaarVolgers(ForumPost $post)
	{
		$auteur = ProfielRepository::get($post->uid);
		$draad = $post->draad;
		$deel = $draad->deel;

		foreach ($deel->meldingen as $volger) {
			$volgerProfiel = ProfielRepository::get($volger->uid);

			// Stuur geen meldingen als lid niet gevonden is of lid de auteur
			if (!$volgerProfiel || $volgerProfiel->uid === $post->uid) {
				continue;
			}

			$account = $volgerProfiel->account;

			// Als dit lid geen account meer heeft, volgt dit lid niet meer deze post
			if (!$account) {
				$this->forumDelenMeldingRepository->remove($volger);
			} else {
				$this->stuurDeelMelding($account, $auteur, $post, $draad, $deel);
			}
		}
	}
}
