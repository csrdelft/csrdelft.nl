<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Mail;
use CsrDelft\common\SimpleSpamFilter;
use CsrDelft\service\MailService;
use CsrDelft\view\PlainView;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/12/2018
 */
class ContactFormulierController extends AbstractController
{
	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(MailService $mailService)
	{
		$this->mailService = $mailService;
	}

	/**
	 * @return PlainView
	 * @Route("/contactformulier/interesse", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function interesse()
	{
		$resp = $this->checkCaptcha(filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING));

		if (!$resp['success']) {
			throw $this->createAccessDeniedException("Geen toegang");
		}

		$naam = filter_input(INPUT_POST, "naam", FILTER_SANITIZE_STRING);
		$achternaam = filter_input(INPUT_POST, "achternaam", FILTER_SANITIZE_STRING);
		$email = filter_input(INPUT_POST, "submit_by", FILTER_SANITIZE_STRING);
		$adres = filter_input(INPUT_POST, "straat", FILTER_SANITIZE_STRING);
		$postcode = filter_input(INPUT_POST, "postcode", FILTER_SANITIZE_STRING);
		$woonplaats = filter_input(INPUT_POST, "plaats", FILTER_SANITIZE_STRING);
		$telefoon = filter_input(INPUT_POST, "telefoon", FILTER_SANITIZE_STRING);
		$opmerking = filter_input(INPUT_POST, "opmerking", FILTER_SANITIZE_STRING);

		$interesses = [
			filter_input(INPUT_POST, "interesse1", FILTER_SANITIZE_STRING),
			filter_input(INPUT_POST, "interesse2", FILTER_SANITIZE_STRING),
			filter_input(INPUT_POST, "interesse3", FILTER_SANITIZE_STRING),
			filter_input(INPUT_POST, "interesse4", FILTER_SANITIZE_STRING),
		];

		$interessestring = '';
		foreach ($interesses as $interesse) {
			if ($interesse) {
				$interessestring .= " * " . $interesse . "\n";
			}
		}

		if ($achternaam || $this->bevatUrl($opmerking) || $this->isSpam($naam, $email, $adres, $postcode, $woonplaats, $telefoon, $opmerking, $interessestring)) {
			throw new CsrGebruikerException('Bericht bevat ongeldige tekst.');
		}

		$bericht = "
Beste OweeCie,

Het interesseformulier op de stek is ingevuld:

Naam: $naam
Email: $email
Adres: $adres
Postcode: $postcode
Woonplaats: $woonplaats
Telefoon: $telefoon

Interesses:
$interessestring
Opmerking:
$opmerking


Met vriendelijke groeten,
De PubCie.
";

		$mail = new Mail([$_ENV['EMAIL_OWEECIE'] => "OweeCie"], "Interesseformulier", $bericht);
		$mail->setFrom($email);
		$this->mailService->send($mail);

		return new PlainView('Bericht verzonden, je zult binnenkort meer horen.');
	}

	/**
	 * @return PlainView
	 * @Route("/contactformulier/owee", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function owee()
	{
		$resp = $this->checkCaptcha(filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING));

		if (!$resp['success']) {
			throw $this->createAccessDeniedException("Geen toegang");
		}

		$type = filter_input(INPUT_POST, "optie", FILTER_SANITIZE_STRING);

		$naam = filter_input(INPUT_POST, "naam", FILTER_SANITIZE_STRING);
		$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);
		$telefoon = filter_input(INPUT_POST, "telefoon", FILTER_SANITIZE_STRING);
		$eten = filter_input(INPUT_POST, "eten", FILTER_SANITIZE_STRING);
		$eetwens = filter_input(INPUT_POST, "eetwens", FILTER_SANITIZE_STRING);
		$slapen = filter_input(INPUT_POST, "slapen", FILTER_SANITIZE_STRING);
		$opmerking = filter_input(INPUT_POST, "opmerking", FILTER_SANITIZE_STRING);

		if ($this->isSpam($naam, $email, $telefoon, $eten, $eetwens, $opmerking)) {
			throw new CsrGebruikerException('Bericht bevat ongeldige tekst.');
		}

		if ($type === 'lid-worden') {
			$typeaanduiding = 'Ik wil lid worden';
			$commissie = "NovCie";
			$bestemming = [$_ENV['EMAIL_NOVCIE'] => $commissie];
		} else if ($type === 'lid-spreken') {
			$typeaanduiding = 'Eerst een lid spreken';
			$commissie = "OweeCie";
			$bestemming = [$_ENV['EMAIL_OWEECIE'] => $commissie];
		} else {
			$typeaanduiding = 'Aanmelden open avond';
			$commissie = "OweeCie";
			$bestemming = [$_ENV['EMAIL_OWEECIE'] => $commissie];
		}

		$bericht = $this->renderView('mail/bericht/contactformulier.mail.twig', [
			'telefoon' => $telefoon,
			'typeaanduiding' => $typeaanduiding,
			'naam' => $naam,
			'email' => $email,
			'commissie' => $commissie,
			'eten' => $eten,
			'eetwens' => $eetwens,
			'slapen' => $slapen,
			'opmerking' => $opmerking,
		]);

		$mail = new Mail($bestemming, "Lid worden formulier", $bericht);
		$mail->setFrom($_ENV['EMAIL_PUBCIE']);
		$this->mailService->send($mail);

		return new PlainView('Bericht verzonden, je zult binnenkort meer horen.');
	}

	/**
	 * @return PlainView
	 * @Route("/civitasproducties/bestel", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 * @CsrfUnsafe
	 */
	public function civitasproducties()
	{
		$resp = $this->checkCaptcha(filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING));

		if (!$resp['success']) {
			throw $this->createAccessDeniedException("Geen toegang");
		}

		$naam = filter_input(INPUT_POST, "naam", FILTER_SANITIZE_STRING);
		$voornaam = filter_input(INPUT_POST, "voornaam", FILTER_SANITIZE_STRING);
		$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);
		$aantal = filter_input(INPUT_POST, "aantal", FILTER_SANITIZE_STRING);

		if ($this->isSpam($naam, $email, $aantal)) {
			throw new CsrGebruikerException('Bericht bevat ongeldige tekst.');
		}

		$bericht = $this->renderView('mail/bericht/civitasproductiesbestelling.mail.twig', [
			'naam' => $naam,
			'voornaam' => $voornaam,
			'email' => $email,
			'aantal' => $aantal,
		]);

		$mail = new Mail([$_ENV['EMAIL_CIVITASPRODUCTIES'] => 'Civitas producties', $email => $voornaam . ' ' . $naam], "Bevestiging film ticket bestelling.", $bericht);
		$mail->setFrom($_ENV['EMAIL_CIVITASPRODUCTIES'], 'Civitas producties');
		$this->mailService->send($mail);

		return new PlainView('Bestelling verzonden. Er is een bevestiging gestuurd naar uw emailadres, binnenkort zult ontvangt u meer informatie.');
	}

	private function isSpam(...$input)
	{
		$filter = new SimpleSpamFilter();
		foreach ($input as $item) {
			if ($item && $filter->isSpam($item)) {
				return true;
			}
		}
		return false;
	}

	private function bevatUrl($opmerking)
	{
		return preg_match('/https?:|\.(com|ru|pw|pro|nl)\/?($|\W)/', $opmerking) == true;
	}

	/**
	 * @param $response
	 * @return mixed
	 */
	public function checkCaptcha($response)
	{
		$secret = $_ENV['GOOGLE_CAPTCHA_SECRET'];

		$ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=$secret&response=$response");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		return json_decode(curl_exec($ch), true);
	}
}
