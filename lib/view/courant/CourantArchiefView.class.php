<?php

require_once 'view/courant/CourantView.class.php';

/**
 * CourantArchiefView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 */
class CourantArchiefView implements View {

	private $model;

	public function __construct(CourantModel $model) {
		$this->model = $model;
	}

	function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return '<a href="/courant" title="Courant"><img src="' . CSR_PICS . '/knopjes/email-16.png" class="module-icon"></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	function getTitel() {
		return 'Archief';
	}

	function view() {
		echo '<ul class="horizontal nobullets">
			<li>
				<a href="/courant/" title="Courantinzendingen">Courantinzendingen</a>
			</li>
			<li class="active">
				<a href="/courant/archief/" title="Archief">Archief</a>
			</li>
		</ul>
		<hr />';
		$aMails = $this->model->getArchiefmails();
		$sReturn = '<h1>Archief C.S.R.-courant</h1>';
		if (is_array($aMails)) {
			$sLijst = '';
			foreach ($aMails as $aMail) {
				if (isset($iLaatsteJaar)) {
					if ($iLaatsteJaar != $aMail['jaar']) {
						$sReturn .= '<div class="courantArchiefJaar"><h3>' . $iLaatsteJaar . '</h3>' . $sLijst . '</div>';
						$sLijst = '';
					}
				}
				$iLaatsteJaar = $aMail['jaar'];
				$sLijst .= '<a href="/courant/archief/' . $aMail['ID'] . '">' . strftime('%d %B', strtotime($aMail['verzendMoment'])) . '</a><br />';
			}
			$sReturn .= '<div class="courantArchiefJaar"><h3>' . $iLaatsteJaar . '</h3>' . $sLijst . '</div>';
		} else {
			$sReturn .= 'Geen couranten in het archief aanwezig';
		}
		return $sReturn;
	}

}
