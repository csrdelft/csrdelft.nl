<?php

namespace CsrDelft\service\exact;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fiscaat\exact\ExactToken;
use CsrDelft\repository\fiscaat\exact\ExactTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Picqer\Financials\Exact\Connection;

class Exact
{
	/** @var ExactTokenRepository */
	private $tokenRepo;
	/** @var EntityManagerInterface */
	private $em;

	public function __construct(ExactTokenRepository $tokenRepo, EntityManagerInterface $em) {
		$this->tokenRepo = $tokenRepo;
		$this->em = $em;
	}

	public function setupConnection(): Connection {
		$connection = new Connection();

		$connection->setRedirectUrl($_ENV['EXACT_CALLBACK_URL']);
		$connection->setExactClientId($_ENV['EXACT_CLIENT_ID']);
		$connection->setExactClientSecret($_ENV['EXACT_CLIENT_SECRET']);

		return $connection;
	}

	private function getToken(): ?ExactToken {
		return $this->tokenRepo->findOneBy([], ['id' => 'desc']);
	}

	public function loadConnection(): ?Connection {
		$connection = $this->setupConnection();
		$token = $this->getToken();

		if (!$token) {
			return null;
		}

		$connection->setAccessToken($token->getAccessToken());
		$connection->setRefreshToken($token->getRefreshToken());
		$connection->setTokenExpires($token->getExpires());
		return $this->connect($connection);
	}

	public function createConnection($authorizationCode): ?Connection {
		$connection = $this->setupConnection();
		$connection->setAuthorizationCode($authorizationCode);

		return $this->connect($connection);
	}

	private function connect(Connection $connection): ?Connection {
		try {
			$connection->connect();
		} catch (Exception $e) {
			return null;
		}

		$this->saveConnection($connection);
		return $connection;
	}

	private function saveConnection(Connection $connection) {
		$token = $this->getToken();

		if (!$token) {
			$token = new ExactToken();
			$new = true;
		} else {
			$new = false;
		}

		$token->setAccessToken($connection->getAccessToken());
		$token->setRefreshToken($connection->getRefreshToken());
		$token->setExpires($connection->getTokenExpires());

		if ($new) {
			$this->em->persist($token);
		}
		$this->em->flush();
	}
}
