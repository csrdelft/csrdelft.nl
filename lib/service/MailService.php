<?php

namespace CsrDelft\service;

use CsrDelft\common\FlashType;
use CsrDelft\common\Mail;
use CsrDelft\common\Util\FlashUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class MailService
{
	/**
	 * @var Environment
	 */
	private $environment;
	/**
	 * @var RequestStack
	 */
	private $requestStack;

	public function __construct(
		Environment $environment,
		RequestStack $requestStack
	) {
		$this->environment = $environment;
		$this->requestStack = $requestStack;
	}

	public function send(Mail $mail): bool
	{
		$boundary = uniqid('csr_');

		$htmlBody = $this->environment->render('mail/letter.mail.twig', [
			'bericht' => $mail->getBericht(),
		]);
		$plainBody = $this->environment->render('mail/plain.mail.twig', [
			'bericht' => $mail->getBericht(),
		]);

		$headers = $this->getHeaders($mail);
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

		if ($mail->inDebugMode()) {
			$this->requestStack
				->getSession()
				->getFlashBag()
				->add(FlashType::HTML, $htmlBody);
			return false;
		}
		return mail(
			$mail->getTo(),
			$mail->getSubject(),
			$body,
			$headers,
			$this->getExtraParameters($mail)
		);
	}

	private function getHeaders(Mail $mail): string
	{
		$headers = [];
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'From: ' . $mail->getFrom();
		if (!empty($this->replyTo)) {
			$headers[] = 'Reply-To: ' . $mail->getReplyTo();
		}
		if (!empty($this->bcc)) {
			$headers[] = 'Bcc: ' . $mail->getBcc();
		}
		$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
		return implode("\r\n", $headers);
	}

	private function getExtraParameters(Mail $mail): string
	{
		return '-f ' . $mail->getFrom(true);
	}
}
