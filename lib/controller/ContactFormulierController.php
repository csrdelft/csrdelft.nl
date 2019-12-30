<?php

namespace CsrDelft\controller;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Ini;
use CsrDelft\common\SimpleSpamFilter;
use CsrDelft\model\entity\Mail;
use CsrDelft\view\PlainView;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/12/2018
 */
class ContactFormulierController {
	public function interesse() {
		$naam = filter_input(INPUT_POST, "naam", FILTER_SANITIZE_STRING);
		$email = filter_input(INPUT_POST, "submit_by", FILTER_SANITIZE_STRING);
		$adres = filter_input(INPUT_POST, "straat", FILTER_SANITIZE_STRING);
		$postcode = filter_input(INPUT_POST, "postcode", FILTER_SANITIZE_STRING);
		$woonplaats = filter_input(INPUT_POST, "plaats", FILTER_SANITIZE_STRING);
		$telefoon = filter_input(INPUT_POST, "telefoon", FILTER_SANITIZE_STRING);
		$opmerking = filter_input(INPUT_POST, "opmerking", FILTER_SANITIZE_STRING);

		$interesses = [];

		$interesse1 = filter_input(INPUT_POST, "interesse1", FILTER_SANITIZE_STRING);
		$interesse2 = filter_input(INPUT_POST, "interesse2", FILTER_SANITIZE_STRING);
		$interesse3 = filter_input(INPUT_POST, "interesse3", FILTER_SANITIZE_STRING);
		$interesse4 = filter_input(INPUT_POST, "interesse4", FILTER_SANITIZE_STRING);

		if ($interesse1) array_push($interesses, $interesse1);
		if ($interesse2) array_push($interesses, $interesse2);
		if ($interesse3) array_push($interesses, $interesse3);
		if ($interesse4) array_push($interesses, $interesse4);

		$interessestring = '';
		foreach ($interesses as $interesse) $interessestring .= " * " . $interesse . "\n";

		if ($this->bevatUrl($opmerking) || $this->isSpam($naam, $email, $adres, $postcode, $woonplaats, $telefoon, $opmerking, $interessestring)) {
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

		$mail = new Mail([Ini::lees(Ini::EMAILS, 'oweecie') => "OweeCie"], "Interesseformulier", $bericht);
		$mail->setFrom($email);
		$mail->send();

		return new PlainView('Bericht verzonden, je zult binnenkort meer horen.');
	}

	private function isSpam(...$input) {
		$filter = new SimpleSpamFilter();
		foreach ($input as $item) {
			if ($item && $filter->isSpam($item)) {
				return true;
			}
		}
		return false;
	}

	private function bevatUrl($opmerking) {
		return preg_match('/https?:|\.(com|ru|pw|pro|nl)\/?($|\W)/', $opmerking) == true;
	}
}
