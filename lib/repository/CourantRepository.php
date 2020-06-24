<?php

namespace CsrDelft\repository;

use CsrDelft\entity\courant\Courant;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\courant\CourantView;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;

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
class CourantRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Courant::class);
	}

	public function magBeheren() {
		return LoginService::mag(P_MAIL_COMPOSE);
	}

	public function magVerzenden() {
		return LoginService::mag(P_MAIL_SEND);
	}

	public function nieuwCourant() {
		$courant = new Courant();
		$courant->verzendMoment = new DateTime();
		$courant->verzender_profiel = LoginService::getProfiel();
		$courant->verzender = LoginService::getUid();

		return $courant;
	}

	public function verzenden($email, CourantView $view) {
		$sMail = $view->getHtml(true);

		$smtp = fsockopen('localhost', 25, $feut, $fout);
		echo 'Zo, mail verzenden naar ' . $email . '.<pre>';
		echo fread($smtp, 1024);
		fwrite($smtp, "HELO localhost\r\n");
		echo "HELO localhost\r\n";
		echo fread($smtp, 1024);
		fwrite($smtp, "MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		echo htmlspecialchars("MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		echo fread($smtp, 1024);
		fwrite($smtp, "RCPT TO:<" . $email . ">\r\n");
		echo htmlspecialchars("RCPT TO:<" . $email . ">\r\n");
		echo fread($smtp, 1024);
		fwrite($smtp, "DATA\r\n");
		echo htmlspecialchars("DATA\r\n");
		echo fread($smtp, 1024);

		fwrite($smtp, $sMail . "\r\n");
		echo htmlspecialchars("[mail hier]\r\n");
		fwrite($smtp, "\r\n.\r\n");
		echo htmlspecialchars("\r\n.\r\n");
		echo fread($smtp, 1024);
		echo '</pre>';
	}
}
