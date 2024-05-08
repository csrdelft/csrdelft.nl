<?php

namespace CsrDelft\service\pin;

use CsrDelft\entity\pin\PinTransactie;
use CsrDelft\repository\pin\PinTransactieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 22/02/2018
 *
 * In november 2022 aangepast om de Rabo Smart Pay Merchant Services API v3.2.12 te gebruiken i.p.v. Payplaza.
 * Zie documentatie in /docs/onderdelen/pinmatchtool.md.
 */
class PinTransactieDownloader
{
	/**
	 * Url constants.
	 */
	const CLIENT_ID_HEADER = 'X-IBM-Client-Id';

	/**
	 * @var PinTransactieRepository
	 */
	private $pinTransactieRepository;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var HttpClientInterface
	 */
	private $httpClient;

	public function __construct(
		PinTransactieRepository $pinTransactieRepository,
		EntityManagerInterface $entityManager,
		HttpClientInterface $httpClient
	) {
		$this->pinTransactieRepository = $pinTransactieRepository;
		$this->entityManager = $entityManager;
		$this->httpClient = $httpClient;
	}

	/**
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws PinDownloadException
	 */
	public function download(
		$moment,
		$pinURL,
		$clientID,
		$certificatePath,
		$privateKeyPath
	) {
		$momentStart = date_create_immutable($moment);
		$momentEnd = $momentStart->modify('+1 day');

		$request = $this->httpClient->request('POST', $pinURL, [
			'headers' => [
				self::CLIENT_ID_HEADER => $clientID,
			],
			'local_cert' => $certificatePath,
			'local_pk' => $privateKeyPath,
			'body' => json_encode([
				'limit' => 5000,
				'start_datetime' => $momentStart->format('c'),
				'end_datetime' => $momentEnd->format('c'),
				'payment_channels' => ['PIN'],
				'transaction_statuses' => ['ACCEPTED', 'NEW', 'SETTLED', 'SUCCESS'],
				'transaction_types' => ['PAYMENT'],
			]),
		]);

		if ($request->getStatusCode() !== 200) {
			$content = $request->toArray(false);
			throw new PinDownloadException(
				'Pin transacties ophalen mislukt (' .
					$request->getStatusCode() .
					'): ' .
					$content['errors'][0]['error_message'] .
					'(' .
					$content['errors'][0]['error_code'] .
					')'
			);
		}

		$content = json_decode($request->getContent());
		$transacties = $content->transactions;
		$pinTransacties = [];
		foreach ($transacties as $transactie) {
			$pinTransactie = $this->createTransactie($transactie);
			$this->entityManager->persist($pinTransactie);
			$pinTransacties[] = $pinTransactie;
		}

		$this->entityManager->flush();

		return $pinTransacties;
	}

	private function createTransactie($transaction): PinTransactie
	{
		$pinTransactie = new PinTransactie();

		$pinTransactie->datetime = date_create_immutable($transaction->date_time);
		$pinTransactie->brand = $transaction->payment_brand;
		$pinTransactie->merchant = $transaction->merchant_name;
		$pinTransactie->store = $transaction->shop_name;
		$pinTransactie->terminal = $transaction->terminal_id;
		$pinTransactie->TID = $transaction->terminal_id;
		$pinTransactie->MID = $transaction->shop_id;
		$pinTransactie->ref = $transaction->omnikassa_transaction_id;
		$pinTransactie->type = $transaction->transaction_type;
		$amount = floatval($transaction->transaction_amount->amount);
		$formattedAmount = number_format($amount, 2, ',', '');
		$pinTransactie->amount =
			$transaction->transaction_amount->currency . ' ' . $formattedAmount;
		$pinTransactie->AUTRSP = $transaction->reference;
		$pinTransactie->STAN = '';

		return $pinTransactie;
	}
}
