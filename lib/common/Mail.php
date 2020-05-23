<?php

namespace CsrDelft\common;

use CsrDelft\view\bbcode\CsrBB;

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

	private $onderwerp;
	private $bericht;
	private $from = array('pubcie@csrdelft.nl' => 'PubCie C.S.R. Delft');
	private $replyTo = array();
	private $to = array();
	private $bcc = array();
	private $type = 'html'; // plain or html
	private $charset = 'UTF-8';
	private $placeholders = array();
	private $lightBB = false;

	public function __construct(array $to, $onderwerp, $bericht) {
		$this->onderwerp = $onderwerp;
		$this->bericht = $bericht;
		$this->addTo($to);
	}

	public function setLightBB($lightBB = true) {
		$this->lightBB = $lightBB;
	}

	public function getFrom($email_only = false) {
		$name = reset($this->from);
		$email = key($this->from);
		if ($email_only) {
			return $email;
		}
		return $name . ' <' . $email . '>';
	}

	public function setFrom($email, $name = null) {
		if (!email_like($email)) {
			throw new CsrGebruikerException('Emailadres in $from geen valide e-mailadres');
		}
		// Geen speciale tekens in naam vanwege spamfilters
		$this->from = array($email => filter_var($name, FILTER_SANITIZE_EMAIL));
	}

	public function getReplyTo($email_only = false) {
		$name = reset($this->replyTo);
		$email = key($this->replyTo);
		if ($email_only) {
			return $email;
		}
		return $name . ' <' . $email . '>';
	}

	public function setReplyTo($email, $name = null) {
		if (!email_like($email)) {
			throw new CsrGebruikerException('Emailadres in $reply_to geen valide e-mailadres');
		}
		// Geen speciale tekens in naam vanwege spamfilters
		$this->replyTo = array($email => filter_var($name, FILTER_SANITIZE_EMAIL));
	}

	public function getTo() {
		$to = array();
		foreach ($this->to as $email => $name) {
			if (empty($name)) {
				$to[] = $email;
			} else {
				$to[] = $name . ' <' . $email . '>';
			}
		}
		return implode(', ', $to);
	}

	public function addTo(array $to) {
		foreach ($to as $email => $name) {
			if (!email_like($email)) {
				throw new CsrGebruikerException('Invalid e-mailadres in TO "' . $email . '"');
			}
			// Geen speciale tekens in naam vanwege spamfilters
			$this->to[$this->production_safe($email)] = filter_var($name, FILTER_SANITIZE_EMAIL);
		}
	}

	public function getBcc() {
		$bcc = array();
		foreach ($this->bcc as $email => $name) {
			if (empty($name)) {
				$bcc[] = $email;
			} else {
				$bcc[] = $name . ' <' . $email . '>';
			}
		}
		return implode(', ', $bcc);
	}

	public function addBcc(array $bcc) {
		foreach ($bcc as $email => $name) {
			if (!email_like($email)) {
				throw new CsrGebruikerException('Invalid e-mailadres in BCC "' . $email . '"');
			}
			// Geen speciale tekens in naam vanwege spamfilters
			$this->bcc[$this->production_safe($email)] = filter_var($name, FILTER_SANITIZE_EMAIL);
		}
	}

	public function getSubject() {
		$onderwerp = $this->onderwerp;
		if ($this->inDebugMode()) {
			$onderwerp .= ' [Mail: Debug-modus actief]';
		}
		if ($this->charset === 'UTF-8') {
			// Zorg dat het onderwerp netjes utf8 in base64 is. Als je dit niet doet krijgt het
			// spampunten van spamassasin (SUBJECT_NEEDS_ENCODING,SUBJ_ILLEGAL_CHARS)
			$onderwerp = ' =?UTF-8?B?' . base64_encode($onderwerp) . "?=\n";
		}
		return $onderwerp;
	}

	/**
	 * Mails uit testomgevingen moet en niet naar andere dingen dan naar
	 * het pubcie-mailadres.
	 */
	private function production_safe($email) {
		if ($this->inDebugMode()) {
			return 'pubcie@csrdelft.nl';
		} else {
			return $email;
		}
	}

	public function inDebugMode() {
		return !isSyrinx();
	}

	public function setPlaceholders(array $placeholders) {
		$this->placeholders = $placeholders;
	}

	public function getHeaders() {
		$headers[] = 'From: ' . $this->getFrom();
		if (!empty($this->replyTo)) {
			$headers[] = 'Reply-To: ' . $this->getReplyTo();
		}
		if (!empty($this->bcc)) {
			$headers[] = 'Bcc: ' . $this->getBcc();
		}
		$headers[] = 'Content-Type: text/' . $this->type . '; charset=' . $this->charset;
		$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
		return implode("\r\n", $headers);
	}

	public function getExtraparameters() {
		return '-f ' . $this->getFrom(true);
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
		if ($this->type === 'html') {
			$body = CsrBB::parseMail($body, $this->lightBB);
		}
		return $body;
	}

	////////// active-record //////////

	public function send($debug = false) {
		switch ($this->type) {
			case 'html':
				$body = view('mail.letter', ['body' => $this->getBody()]);
				break;

			case 'plain':
				$body = $this->getBody();
				break;

			default:
				throw new CsrException('unknown mail type: "' . $this->type . '"');
		}
		if ($this->inDebugMode() AND !$debug) {
			setMelding($body, 0);
			return false;
		}
		return mail($this->getTo(), $this->getSubject(), $body, $this->getHeaders(), $this->getExtraparameters());
	}

	public function __toString() {
		return $this->getHeaders() . "\nSubject:" . $this->getSubject() . "\n" . $this->bericht;
	}

}
