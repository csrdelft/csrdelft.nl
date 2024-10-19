<?php

namespace CsrDelft\service\corvee;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Mail;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\MailService;
use DateInterval;
use Twig\Environment;
use DateTime;

/**
 * CorveeHerinneringenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class CorveeHerinneringService
{
	public function __construct(
		private readonly Environment $twig,
		private readonly MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		private readonly CorveeTakenRepository $corveeTakenRepository,
		private readonly ProfielRepository $profielRepository,
		private readonly MailService $mailService
	) {
	}

	public function stuurHerinnering(CorveeTaak $taak)
	{
		$datumCorvee = DateUtil::dateFormatIntl(
			$taak->datum,
			DateUtil::DATE_FORMAT
		);
		$datumAfmelden = DateUtil::dateFormatIntl(
			$taak->datum->add(DateInterval::createFromDateString('-22 days')),
			DateUtil::DATE_FORMAT
		);
		if (!$taak->profiel) {
			throw new CsrGebruikerException(
				$datumCorvee . ' ' . $taak->corveeFunctie->naam . ' niet toegewezen!'
			);
		}
		$lidnaam = $taak->profiel->getNaam('civitas');
		$to = $taak->profiel->getEmailOntvanger();
		$from = $_ENV['EMAIL_CC'];
		$onderwerp = 'C.S.R. Delft corvee ' . $datumCorvee;
		$eten = '';
		if ($taak->maaltijd !== null) {
			$aangemeld = $this->maaltijdAanmeldingenRepository->getIsAangemeld(
				$taak->maaltijd->maaltijd_id,
				$taak->profiel->uid
			);
			if ($aangemeld) {
				$eten = InstellingUtil::instelling('corvee', 'mail_wel_meeeten');
			} else {
				$eten = InstellingUtil::instelling('corvee', 'mail_niet_meeeten');
			}
		}
		$bericht = str_replace(
			['LIDNAAM', 'DATUM_CORVEE', 'MEEETEN', 'DATUM_AFMELDEN'],
			[$lidnaam, $datumCorvee, $eten, $datumAfmelden],
			$taak->corveeFunctie->email_bericht
		);
		$mail = new Mail($to, $onderwerp, $bericht);
		$mail->setFrom($from);
		if ($this->mailService->send($mail)) {
			// false if failed
			if (!$mail->inDebugMode()) {
				$this->corveeTakenRepository->updateGemaild($taak);
			}
			return $datumCorvee .
				' ' .
				$taak->corveeFunctie->naam .
				' verstuurd! (' .
				$lidnaam .
				')';
		} else {
			throw new CsrGebruikerException(
				$datumCorvee .
					' ' .
					$taak->corveeFunctie->naam .
					' faalt! (' .
					$lidnaam .
					')'
			);
		}
	}

	public function stuurHerinneringen()
	{
		$vooraf = str_replace(
			'-',
			'+',
			InstellingUtil::instelling('corvee', 'herinnering_1e_mail')
		);
		$van = date_create();
		$tot = date_create_immutable()->add(
			DateInterval::createFromDateString($vooraf)
		);
		$taken = $this->corveeTakenRepository->getTakenVoorAgenda($van, $tot, true);
		$verzonden = [];
		$errors = [];
		foreach ($taken as $taak) {
			if ($taak->getMoetHerinneren()) {
				try {
					$verzonden[] = $this->stuurHerinnering($taak);
				} catch (CsrGebruikerException $e) {
					$errors[] = $e;
				}
			}
		}
		return [$verzonden, $errors];
	}
}
