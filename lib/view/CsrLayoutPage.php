<?php

namespace CsrDelft\view;

use CsrDelft\model\LidToestemmingModel;
use CsrDelft\view\formulier\ModalForm;
use CsrDelft\view\toestemming\ToestemmingModalForm;


/**
 * CsrLayoutPage
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De stek layout van 2006
 */
class CsrLayoutPage extends CompressedLayout {

	/**
	 * Zijbalk
	 * @var View[]
	 */
	public $zijbalk;
	/**
	 * Modal popup inhoud
	 * @var ModalForm
	 */
	public $modal;

	public function __construct(View $body, array $zijbalk = array(), ModalForm $modal = null) {
		parent::__construct($body, $body->getTitel());
		$this->zijbalk = $zijbalk;
		$this->modal = $modal;
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	/**
	 * @throws \Exception
	 */
	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		if (!$this->modal && !LidToestemmingModel::toestemmingGegeven()) {
			$this->modal = new ToestemmingModalForm();
		}

		$breadcrumbs = $this->getBody()->getBreadcrumbs();
		if ($breadcrumbs) {
			$breadcrumbs = '<ol class="breadcrumb">' .$breadcrumbs . '</ol>';
		}

		view('pagina', [
			'titel' => $this->getTitel(),
			'breadcrumbs' => $breadcrumbs,
			'zijbalk' => $this->zijbalk,
			'modal' => $this->modal,
			'body' => $this->getBody(),
		])->view();
	}
}
