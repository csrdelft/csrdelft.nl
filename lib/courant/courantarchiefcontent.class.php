<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant/class.courantarchiefcontent.php
# -------------------------------------------------------------------
# Verzorgt het weergeven van het archief van de c.s.r.-courant
# -------------------------------------------------------------------

require_once 'courant/courantcontent.class.php';

class CourantarchiefContent extends TemplateView {

	public function __construct(&$courant) {
		parent::__construct($courant);
		//opgevraagde mail inladen
		if (isset($_GET['ID'])) {
			$this->model->load((int) $_GET['ID']);
		}
	}

	function getTitel() {
		return 'C.S.R.-courant van ' . $this->getVerzendMoment();
	}

	private function getArchiefmails() {
		$aMails = $this->model->getArchiefmails();
		$sReturn = '<h1>Archief C.S.R.-courant</h1>';
		if (is_array($aMails)) {
			$sLijst = '';
			foreach ($aMails as $aMail) {
				if (isset($iLaatsteJaar)) {
					if ($iLaatsteJaar != $aMail['jaar']) {
						$sReturn .= '<div class="courantArchiefJaar"><h2>' . $iLaatsteJaar . '</h2>' . $sLijst . '</div>';
						$sLijst = '';
					}
				}
				$iLaatsteJaar = $aMail['jaar'];
				$sLijst .= '<a href="/actueel/courant/archief/' . $aMail['ID'] . '">' . strftime('%d %B', strtotime($aMail['verzendMoment'])) . '</a><br />';
			}
			$sReturn .= '<div class="courantArchiefJaar"><h2>' . $iLaatsteJaar . '</h2>' . $sLijst . '</div>';
		} else {
			$sReturn .= 'Geen couranten in het archief aanwezig';
		}
		return $sReturn;
	}

	function getVerzendMoment() {
		return strftime('%d %B %Y', strtotime($this->model->getVerzendmoment()));
	}

	function view() {
		echo '<ul class="horizontal nobullets">
			<li>
				<a href="/actueel/courant/" title="Courantinzendingen">Courantinzendingen</a>
			</li>
			<li class="active">
				<a href="/actueel/courant/archief/" title="Archief">Archief</a>
			</li>
		</ul>
		<hr />';
		if ($this->model->getID() == 0) {
			//overzicht
			echo $this->getArchiefmails();
		} else {
			echo '<h1>C.S.R.-courant ' . $this->getVerzendMoment() . '</h1>';
			echo '<iframe src="/actueel/courant/archief/iframe/' . $this->model->getID() . '"
					style="width: 700px; height: 700px; border: 0;"></iframe>';
		}
	}

}

//einde classe