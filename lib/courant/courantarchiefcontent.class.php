<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant/class.courantarchiefcontent.php
# -------------------------------------------------------------------
# Verzorgt het weergeven van het archief van de c.s.r.-courant
# -------------------------------------------------------------------

require_once 'courant/courantcontent.class.php';

class CourantarchiefContent implements View {

	private $courant;

	public function __construct(&$courant) {
		$this->courant = $courant;
		//opgevraagde mail inladen
		if (isset($_GET['ID'])) {
			$this->courant->load((int) $_GET['ID']);
		}
	}

	function getModel() {
		return $this->courant;
	}

	public function getBreadcrumbs() {
		return '<a href="/courant" title="Courant"><img src="' . CSR_PICS . '/knopjes/email-16.png" class="module-icon"></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	function getTitel() {
		if ($this->courant->getID() == 0) {
			return 'Archief';
		}
		return 'C.S.R.-courant van ' . $this->getVerzendMoment();
	}

	private function getArchiefmails() {
		$aMails = $this->courant->getArchiefmails();
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

	function getVerzendMoment() {
		return strftime('%d %B %Y', strtotime($this->courant->getVerzendmoment()));
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
		if ($this->courant->getID() == 0) {
			//overzicht
			echo $this->getArchiefmails();
		} else {
			echo '<h1>C.S.R.-courant ' . $this->getVerzendMoment() . '</h1>';
			echo '<iframe src="/courant/archief/iframe/' . $this->courant->getID() . '" id="courantIframe"></iframe>';
		}
	}

}
