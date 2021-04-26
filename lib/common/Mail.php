<?php

namespace CsrDelft\common;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Alle mailadressen in to of bcc zullen als de host niet syrinx is
 * worden aangepast naar pubcie@csrdelft.nl
 */
class Mail
{

	/** @var string */
	private $onderwerp;
	/** @var string */
	private $bericht;
	/** @var array<string, string> */
	private $from = ['pubcie@csrdelft.nl' => 'PubCie C.S.R. Delft'];
	/** @var string[] */
	private $replyTo = [];
	/** @var array<string, string> */
	private $to = [];
	/** @var array<string, string> */
	private $bcc = [];
	/** @var string */
	private $charset = 'UTF-8';

	/**
	 * Mail constructor.
	 * @param array<string, string> $to
	 * @param string $onderwerp
	 * @param string $bericht
	 */
	public function __construct(array $to, string $onderwerp, string $bericht)
	{
		$this->onderwerp = $onderwerp;
		$this->bericht = $bericht;
		$this->addTo($to);
	}

	public function addTo(array $to)
	{
		foreach ($to as $email => $name) {
			if (!email_like($email)) {
				throw new CsrGebruikerException('Invalid e-mailadres in TO "' . $email . '"');
			}
			// Geen speciale tekens in naam vanwege spamfilters
			$this->to[$this->productionSafe($email)] = filter_var($name, FILTER_SANITIZE_STRING);
		}
	}

	/**
	 * Mails uit testomgevingen moet en niet naar andere dingen dan naar
	 * het pubcie-mailadres.
	 * @param string $email
	 * @return string
	 */
	private function productionSafe(string $email): string
	{
		if ($this->inDebugMode()) {
			return 'pubcie@csrdelft.nl';
		} else {
			return $email;
		}
	}

	public function inDebugMode(): bool
	{
		return !isSyrinx();
	}

	public function addBcc(array $bcc)
	{
		foreach ($bcc as $email => $name) {
			if (!email_like($email)) {
				throw new CsrGebruikerException('Invalid e-mailadres in BCC "' . $email . '"');
			}
			// Geen speciale tekens in naam vanwege spamfilters
			$this->bcc[$this->productionSafe($email)] = filter_var($name, FILTER_SANITIZE_STRING);
		}
	}

	/**
	 * @param bool $debug
	 * @return bool
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function send(): bool
	{
		$twig = ContainerFacade::getContainer()->get('twig');
		$boundary = uniqid('csr_');

		$htmlBody = $twig->render('mail/letter.mail.twig', ['bericht' => $this->bericht]);
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

		if ($this->inDebugMode()) {
			setMelding($htmlBody, 0);
			return false;
		}
		return mail($this->getTo(), $this->getSubject(), $body, $headers, $this->getExtraparameters());
	}

	public function getHeaders(): string
	{
		$headers = [];
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

	/**
	 * @param bool $emailOnly
	 * @return int|string|null
	 */
	public function getFrom($emailOnly = false)
	{
		$name = reset($this->from);
		$email = key($this->from);
		if ($emailOnly) {
			return $email;
		}
		return $name . ' <' . $email . '>';
	}

	/**
	 * @param string $email
	 * @param string|null $name
	 */
	public function setFrom(string $email, string $name = null)
	{
		if (!email_like($email)) {
			throw new CsrGebruikerException('Emailadres in $from geen valide e-mailadres');
		}
		// Geen speciale tekens in naam vanwege spamfilters
		$this->from = [$email => filter_var($name, FILTER_SANITIZE_STRING)];
	}

	/**
	 * @param bool $emailOnly
	 * @return string
	 */
	public function getReplyTo(bool $emailOnly = false): string
	{
		$name = reset($this->replyTo);
		$email = key($this->replyTo);
		if ($emailOnly) {
			return $email;
		}
		return $name . ' <' . $email . '>';
	}

	public function setReplyTo(string $email, string $name = null)
	{
		if (!email_like($email)) {
			throw new CsrGebruikerException('Emailadres in $reply_to geen valide e-mailadres');
		}
		// Geen speciale tekens in naam vanwege spamfilters
		$this->replyTo = [$email => filter_var($name, FILTER_SANITIZE_STRING)];
	}

	public function getBcc(): string
	{
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

	public function getTo(): string
	{
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

	public function getSubject(): string
	{
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

	public function getExtraparameters(): string
	{
		return '-f ' . $this->getFrom(true);
	}
}
