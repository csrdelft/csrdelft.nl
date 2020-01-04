<?php

namespace CsrDelft\model;

use CsrDelft\common\CsrNotFoundException;
use CsrDelft\model\entity\courant\Courant;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\view\courant\CourantView;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * CourantModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 *
 * Verzorgt het opvragen van courantgegevens.
 *
 */
class CourantModel extends PersistenceModel {
	const ORM = Courant::class;

	public function magToevoegen() {
		return LoginModel::mag(P_MAIL_POST);
	}

	public function magBeheren($uid = null) {
		return LoginModel::mag(P_MAIL_COMPOSE) OR LoginModel::mag($uid);
	}

	public function magVerzenden() {
		return LoginModel::mag(P_MAIL_SEND);
	}

	/**
	 * @param $id
	 * @return Courant|PersistentEntity
	 */
	public function get($id) {
		$courant = $this->retrieveByPrimaryKey([$id]);
		if (!$courant) {
			throw new CsrNotFoundException();
		}

		return $courant;
	}

	public function nieuwCourant() {
		$courant = new Courant();
		$courant->verzendMoment = getDateTime();
		$courant->verzender = LoginModel::getUid();

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
