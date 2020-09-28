<?php

namespace CsrDelft\common;

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
	private $from = ['pubcie@csrdelft.nl' => 'PubCie C.S.R. Delft'];
	private $replyTo = [];
	private $to = [];
	private $bcc = [];
	private $charset = 'UTF-8';

	public function __construct(array $to, $onderwerp, $bericht) {
		$this->onderwerp = $onderwerp;
		$this->bericht = $bericht;
		$this->addTo($to);
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
		$this->from = array($email => filter_var($name, FILTER_SANITIZE_STRING));
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
		$this->replyTo = array($email => filter_var($name, FILTER_SANITIZE_STRING));
	}

	public function getTo() {
		$toLijst = [];
		foreach ($this->to as $email => $name) {
			if (empty($name)) {
				$toLijst[] = $email;
			} else {
				$toLijst[] = $name . ' <' . $email . '>';
			}
		}
		return implode(', ', $toLijst);
	}

	public function addTo(array $to) {
		foreach ($to as $email => $name) {
			if (!email_like($email)) {
				throw new CsrGebruikerException('Invalid e-mailadres in TO "' . $email . '"');
			}
			// Geen speciale tekens in naam vanwege spamfilters
			$this->to[$this->production_safe($email)] = filter_var($name, FILTER_SANITIZE_STRING);
		}
	}

	public function getBcc() {
		$bccLijst = [];
		foreach ($this->bcc as $email => $name) {
			if (empty($name)) {
				$bccLijst[] = $email;
			} else {
				$bccLijst[] = $name . ' <' . $email . '>';
			}
		}
		return implode(', ', $bccLijst);
	}

	public function addBcc(array $bcc) {
		foreach ($bcc as $email => $name) {
			if (!email_like($email)) {
				throw new CsrGebruikerException('Invalid e-mailadres in BCC "' . $email . '"');
			}
			// Geen speciale tekens in naam vanwege spamfilters
			$this->bcc[$this->production_safe($email)] = filter_var($name, FILTER_SANITIZE_STRING);
		}
	}

	public function getSubject() {
		$subject = $this->onderwerp;
		if ($this->inDebugMode()) {
			$subject .= ' [Mail: Debug-modus actief]';
		}
		if ($this->charset === 'UTF-8') {
			// Zorg dat het onderwerp netjes utf8 in base64 is. Als je dit niet doet krijgt het
			// spampunten van spamassasin (SUBJECT_NEEDS_ENCODING,SUBJ_ILLEGAL_CHARS)
			$subject = ' =?UTF-8?B?' . base64_encode($subject) . "?=\n";
		}
		return $subject;
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

	public function getHeaders() {
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'From: ' . $this->getFrom();
		if (!empty($this->replyTo)) {
			$headers[] = 'Reply-To: ' . $this->getReplyTo();
		}
		if (!empty($this->bcc)) {
			$headers[] = 'Bcc: ' . $this->getBcc();
		}
		$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
		return implode("\r\n", $headers);
	}

	public function getExtraparameters() {
		return '-f ' . $this->getFrom(true);
	}

	////////// active-record //////////

	public function send($debug = false) {
		$twig = ContainerFacade::getContainer()->get('twig');
		$boundary = uniqid('csr_');

		$htmlBody = $twig->render('mail/letter.mail.twig', [
			'bericht' => $this->bericht,
		]);
		$plainBody = $twig->render('mail/plain.mail.twig', ['bericht' => $this->bericht]);

		$headers = $this->getHeaders();
		$headers .= "\r\nContent-Type: multipart/alternative;boundary=\"$boundary\"\r\n";

		$body = <<<MAIL
This is a mime encode message

--$boundary
Content-Type: text/plain;charset="utf-8"

$plainBody

--$boundary
Content-Type: text/html;charset="utf-8"

$htmlBody

--$boundary--
MAIL;
		$body = str_replace("\n", "\r\n", $body);

		if ($this->inDebugMode() && !$debug) {
			setMelding($htmlBody, 0);
			return false;
		}
		return mail($this->getTo(), $this->getSubject(), $body, $headers, $this->getExtraparameters());
	}
}
