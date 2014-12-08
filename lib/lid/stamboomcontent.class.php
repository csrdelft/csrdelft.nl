<?php

/**
 * Stamboompjes weergeven...
 *
 * Geeft een stamboom weer vanaf een startuid. Met de patroonlinkjes kan
 * je doorklikken naar boven. Verder niet zo'n spannend ding, zou een
 * stuk mooier gelayout kunnen worden...
 */
class StamboomContent implements View {

	/**
	 * Root-lid
	 * @var Lid
	 */
	private $root;
	private $kinderen = 0;

	public function __construct($startuid, $levels = 3) {
		if (!Lid::isValidUid($startuid)) {
			throw new Exception('Opgegeven uid is niet geldig');
		}
		$this->root = LidCache::getLid($startuid);
	}

	public function getModel() {
		return $this->root;
	}

	public function getBreadcrumbs() {
		return '<a href="/communicatie/ledenlijst" title="Ledenlijst"><img src="' . CSR_PICS . '/knopjes/people-16.png" class="module-icon"></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return 'Stamboom voor het geslacht van ' . $this->root->getNaam();
	}

	private function viewNode(Lid $lid, $viewPatroon = false) {
		echo '<div class="node">';
		echo '<div class="lid">';
		echo $lid->getNaamLink('pasfoto', 'link');
		echo $lid->getNaamLink('civitas', 'link');
		if ($lid->getAantalKinderen() == 1) {
			echo '<span class="small"> (1 kind)</span>';
		} elseif ($lid->getAantalKinderen() > 1) {
			echo '<span class="small"> (' . $lid->getAantalKinderen() . ' kinderen)</span>';
		}

		if ($viewPatroon) {
			$patroon = $lid->getPatroon();
			if ($patroon instanceof Lid) {
				echo '<br /><a href="/communicatie/stamboom.php?uid=' . $patroon->getUid() . '" title="Stamboom van ' . htmlspecialchars($patroon->getNaam()) . '"> &uArr; ' . $patroon->getNaam('civitas') . '</a>';
			}
		}
		echo '</div>';

		if (count($lid->getKinderen()) > 0) {
			echo '<div class="kinderen">';
			foreach ($lid->getKinderen() as $kind) {
				$this->kinderen++;
				$this->viewNode($kind);
			}
			echo '<div class="clear"></div>';
			echo '</div>';
		}

		echo '</div>';
	}

	public function view() {
		$this->viewNode($this->root, true);

		echo '<h3>Omvang van het nageslacht van ' . $this->root->getNaam() . ': ' . $this->kinderen . '</h3>';
	}

}
