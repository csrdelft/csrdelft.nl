<?php

namespace CsrDelft\repository;

use CsrDelft\entity\courant\Courant;
use CsrDelft\service\security\LoginService;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * CourantModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 *
 * Verzorgt het opvragen van courantgegevens.
 *
 * @method Courant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Courant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Courant[]    findAll()
 * @method Courant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourantRepository extends AbstractRepository
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(ManagerRegistry $registry, Security $security)
	{
		parent::__construct($registry, Courant::class);
		$this->security = $security;
	}

	public function magBeheren()
	{
		return LoginService::mag(P_MAIL_COMPOSE);
	}

	public function magVerzenden()
	{
		return LoginService::mag(P_MAIL_SEND);
	}

	public function nieuwCourant()
	{
		$courant = new Courant();
		$courant->verzendMoment = new DateTime();
		$courant->verzender_profiel = $this->security->getUser()->profiel;
		$courant->verzender = $this->security->getUser()->getUsername();

		return $courant;
	}

	public function verzenden($email, $inhoud)
	{
		$csrMailPassword = $_ENV['CSRMAIL_PASSWORD'];
		$datum = date_format_intl(date_create_immutable(), 'd MMMM y');
		$headers = <<<HEAD
From: PubCie <pubcie@csrdelft.nl>
To: leden@csrdelft.nl
Organization: C.S.R. Delft
MIME-Version: 1.0
Content-Type: text/html; charset=utf-8
User-Agent: telnet localhost 25
X-Complaints-To: pubcie@csrdelft.nl
Approved: $csrMailPassword
Subject: C.S.R.-courant $datum

HEAD;

		$response = '';

		$smtp = fsockopen('localhost', 25, $feut, $fout);
		$response .= 'Zo, mail verzenden naar ' . $email . '.<pre>';
		$response .= fread($smtp, 1024);
		fwrite($smtp, "HELO localhost\r\n");
		$response .= "HELO localhost\r\n";
		$response .= fread($smtp, 1024);
		fwrite($smtp, "MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		$response .= htmlspecialchars("MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		$response .= fread($smtp, 1024);
		fwrite($smtp, 'RCPT TO:<' . $email . ">\r\n");
		$response .= htmlspecialchars('RCPT TO:<' . $email . ">\r\n");
		$response .= fread($smtp, 1024);
		fwrite($smtp, "DATA\r\n");
		$response .= htmlspecialchars("DATA\r\n");
		$response .= fread($smtp, 1024);

		fwrite($smtp, $headers . $inhoud . "\r\n");
		$response .= htmlspecialchars("[mail hier]\r\n");
		fwrite($smtp, "\r\n.\r\n");
		$response .= htmlspecialchars("\r\n.\r\n");
		$response .= fread($smtp, 1024);
		$response .= '</pre>';

		return $response;
	}
}
