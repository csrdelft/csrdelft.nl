<?php

namespace CsrDelft\view\courant;

use CsrDelft\common\Ini;
use CsrDelft\model\CourantModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * CourantView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
class CourantView extends SmartyTemplateView {

	private $instellingen;

	/**
	 * CourantView constructor.
	 * @param CourantModel $courant
	 * @throws \CsrDelft\common\CsrException
	 */
	public function __construct(CourantModel $courant) {
		parent::__construct($courant);
		setlocale(LC_ALL, 'nl_NL@euro');
		$this->instellingen = Ini::lees(Ini::CSRMAIL);
	}

	public function getTitel() {
		return 'C.S.R.-courant van ' . $this->getVerzendMoment();
	}

	public function getVerzendMoment() {
		return strftime('%d %B %Y', strtotime($this->model->getVerzendmoment()));
	}

	public function getHtml($headers = false) {
		$this->smarty->assign('instellingen', $this->instellingen);
		$this->smarty->assign('courant', $this->model);

		$this->smarty->assign('indexCats', $this->model->getCats());
		$this->smarty->assign('catNames', $this->model->getCats(true));

		$this->smarty->assign('headers', $headers);

		return $this->smarty->fetch($this->model->getTemplatePath());
	}

	public function view() {
		echo $this->getHtml();
	}

	public function verzenden($sEmailAan) {
		$sMail = $this->getHtml(true);

		$smtp = fsockopen('localhost', 25, $feut, $fout);
		echo 'Zo, mail verzenden naar ' . $sEmailAan . '.<pre>';
		echo fread($smtp, 1024);
		fwrite($smtp, "HELO localhost\r\n");
		echo "HELO localhost\r\n";
		echo fread($smtp, 1024);
		fwrite($smtp, "MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		echo htmlspecialchars("MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		echo fread($smtp, 1024);
		fwrite($smtp, "RCPT TO:<" . $sEmailAan . ">\r\n");
		echo htmlspecialchars("RCPT TO:<" . $sEmailAan . ">\r\n");
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
