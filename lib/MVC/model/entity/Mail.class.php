<?php

/**
 * Mail.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Alle mailadressen in to of bcc zullen als de host niet syrinx is
 * worden aangepast naar pubcie@csrdelft.nl
 */
class Mail {

	protected $onderwerp;
	protected $bericht;
	protected $from = 'pubcie@csrdelft.nl';
	protected $to = array();
	protected $bcc = array();
	protected $type = 'html'; // plain or html
	protected $charset = 'utf8';
	protected $layout = 'letter';
	protected $ubb = true;
	protected $placeholders = array();

	public function __construct($to, $onderwerp, $bericht) {
		$this->onderwerp = $onderwerp;
		$this->bericht = $bericht;
		$this->addTo($to);
	}

	public function getLayout() {
		return $this->layout;
	}

	public function setLayout($template) {
		$this->layout = $template;
	}

	public function getTo() {
		return implode(',', $this->to);
	}

	public function addTo($to) {
		if (strpos($to, ',') !== false) {
			foreach (explode(',', $to) as $email) {
				$this->addTo($email);
			}
		} else {
			if (!email_like($to)) {
				throw new Exception('Emailadres in $to geen valide email-adres');
			}
			$this->to[] = $this->production_safe($to);
		}
	}

	public function setFrom($from) {
		if (!email_like($from)) {
			throw new Exception('Emailadres in $from geen valide email-adres');
		}
		$this->from = $from;
	}

	public function getBcc() {
		return implode(',', $this->bcc);
	}

	public function addBcc($bcc) {
		if (strpos($bcc, ',') !== false) {
			foreach (explode(',', $bcc) as $email) {
				$this->addBcc($email);
			}
		} else {
			if (!email_like($bcc)) {
				throw new Exception('Emailadres in $bcc geen valide email-adres');
			}
			$this->bcc[] = $this->production_safe($bcc);
		}
	}

	/**
	 * Mails uit testomgevingen moet en niet naar andere dingen dan naar
	 * het pubcie-mailadres.
	 */
	protected function production_safe($email) {
		if ($this->inDebugMode()) {
			return 'pubcie@csrdelft.nl';
		} else {
			return $email;
		}
	}

	public function inDebugMode() {
		return !isSyrinx();
	}

	public function getHeaders() {
		$headers = "From: " . $this->from . "\n";

		if ($this->bcc != '') {
			$headers.="BCC: " . $this->getBcc() . "\n";
		}

		if ($this->charset === 'utf8') {
			$headers .= "Content-Type: text/" . $this->type . "; charset=UTF-8\r\n";
		}
		$headers .= 'X-Mailer: nl.csrdelft.lib.Mail' . "\n\r";

		return $headers;
	}

	public function getExtraparameters() {
		return "-f " . $this->from;
	}

	public function getSubject() {
		$onderwerp = $this->onderwerp;
		if ($this->inDebugMode()) {
			$onderwerp .= ' [Mail: Debug modus actief]';
		}
		if ($this->charset === 'utf8') {
			//zorg dat het onderwerp netjes utf8 in base64 is. Als je dit niet doet krijgt het
			//spampunten van spamassasin (SUBJECT_NEEDS_ENCODING,SUBJ_ILLEGAL_CHARS)
			$onderwerp = ' =?UTF-8?B?' . base64_encode($onderwerp) . "?=\n";
		}
		return $onderwerp;
	}

	public function setPlaceholders(array $placeholders) {
		$this->placeholders = $placeholders;
	}

	/**
	 * Eenvoudige search-and-replace jetzer.
	 *
	 * voorbeeld: "hallo NAAM, groet, AFZENDER"
	 * replace_values: array('NAAM' => 'Jieter', 'AFZENDER' => 'PubCie');
	 * resultaat in body: "hallo Jieter, groet, PubCie"
	 *
	 * Controleert niet of alle placeholders ook gegeven worden in de
	 * values-array!
	 */
	public function getBody() {
		$body = $this->bericht;
		foreach ($this->placeholders as $key => $value) {
			$body = str_replace($key, $value, $body);
		}
		if ($this->ubb) {
			$body = CsrUbb::parse($body);
		}
		return $body;
	}

	public function __toString() {
		return $this->getHeaders() . "\nSubject:" . $this->getSubject() . "\n" . $this->bericht;
	}

	///////// active-record /////////

	public function send($debug = false) {
		if (empty($this->getSubject())) {
			throw new Exception('Geen onderwerp ingevuld');
		}
		if ($this->getLayout() === 'letter') {
			require_once 'MVC/view/MailTemplateView.class.php';
			$view = new MailTemplateView($this);
			$body = $view->getBody();
		} else {
			$body = $this->getBody();
		}
		if ($this->inDebugMode() AND ! $debug) {
			return false;
		}
		return mail($this->getTo(), $this->getSubject(), $body, $this->getHeaders(), $this->getExtraparameters());
	}

}
