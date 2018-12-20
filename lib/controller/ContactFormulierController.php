<?php

namespace CsrDelft\controller;
use CsrDelft\common\GoogleCaptcha;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\Mail;
use CsrDelft\view\JsonResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/12/2018
 */
class ContactFormulierController extends AclController {
	const CAPTCHA_URL = "https://www.google.com/recaptcha/api/siteverify";
	const EMAIL_DIESCIE = 'diescie@csrdelft.nl';

	public function __construct($query, $model, array $methods = array('GET', 'POST')) {
		parent::__construct($query, null);

		if ($this->getMethod() == 'POST') {
			$this->acl = [
				'dies' => 'P_PUBLIC',
			];
		} else {
			$this->acl = [];
		}
	}

	public function POST_dies() {
		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$naam = filter_input(INPUT_POST, 'naam', FILTER_SANITIZE_STRING);
		$verhaal = filter_input(INPUT_POST, 'verhaal', FILTER_SANITIZE_STRING);

		if (GoogleCaptcha::verify() && $email && $naam && $verhaal) {
			$mail = new Mail([self::EMAIL_DIESCIE], 'Bericht van de stek', <<<TEXT
email: $email
naam: $naam
verhaal: $verhaal
TEXT
			);
			$mail->setFrom($email);
			$mail->send();

			$this->view = new JsonResponse([true]);
		} else {
			$this->view = new JsonResponse([false]);
		}
	}

	public function POST_extern() {
		$naam = filter_input(INPUT_POST, "naam", FILTER_SANITIZE_STRING);
		$email = filter_input(INPUT_POST, "submit_by", FILTER_SANITIZE_STRING);
		$adres = filter_input(INPUT_POST, "straat", FILTER_SANITIZE_STRING);
		$postcode = filter_input(INPUT_POST, "postcode", FILTER_SANITIZE_STRING);
		$woonplaats = filter_input(INPUT_POST, "plaats", FILTER_SANITIZE_STRING);
		$telefoon = filter_input(INPUT_POST, "telefoon", FILTER_SANITIZE_STRING);
		$opmerking = filter_input(INPUT_POST, "opmerking", FILTER_SANITIZE_STRING);

		$interesses = [];

		if (isset($_POST["interesse1"]))
			array_push($interesses, filter_input(INPUT_POST, "interesse1", FILTER_SANITIZE_STRING));
		if (isset($_POST["interesse2"])) array_push($interesses, $_POST["interesse2"]);
		if (isset($_POST["interesse3"])) array_push($interesses, $_POST["interesse3"]);
		if (isset($_POST["interesse4"])) array_push($interesses, $_POST["interesse4"]);
		if (!GoogleCaptcha::verify()) {
			echo "Verzenden mislukt";
			exit;
		}

		$interessestring = '';
		foreach ($interesses as $interesse) $interessestring .= " * " . $interesse . "\n";

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

		$mail = new Mail(array("oweecie@csrdelft.nl" => "OweeCie", $email => $naam), "Interesseformulier", $bericht);
		$mail->setFrom($email);
		$mail->send();
	}
}
