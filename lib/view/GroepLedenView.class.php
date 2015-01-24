<?php

/**
 * GroepLedenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class GroepTabView implements View {

	protected $groep;

	public function __construct(Groep $groep) {
		$this->groep = $groep;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function view() {
		echo '<div id="groep-leden-' . $this->groep->id . '" class="groep-leden"><ul class="groep-tabs nobullets">';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepPasfotosView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Pasfotos . '" title="Pasfoto\'s tonen"><span class="fa fa-user"></span></a></li>';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepLijstView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Lijst . '" title="Pasfoto\'s tonen"><span class="fa fa-align-justify"></span></a></li>';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepStatistiekView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Statistiek . '" title="Pasfoto\'s tonen"><span class="fa fa-pie-chart"></span></a></li>';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepEmailsView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Emails . '" title="Pasfoto\'s tonen"><span class="fa fa-envelope"></span></a></li>';

		echo '</ul><div class="groep-tab-content">';
	}

}

class GroepPasfotosView extends GroepTabView {

	public function view() {
		parent::view();
		foreach ($this->groep->getLeden() as $lid) {
			echo '<div class="pasfoto">' . ProfielModel::getLink($lid->uid, 'pasfoto') . '</div>';
		}
		echo '</div></div>';
	}

}

class GroepLijstView extends GroepTabView {

	public function view() {
		parent::view();
		echo '<table class="groep-lijst"><tbody>';
		$suggestions = $this->groep->getSuggesties();
		foreach ($this->groep->getLeden() as $lid) {
			echo '<tr><td>' . ProfielModel::getLink($lid->uid, 'civitas') . '</td>';
			echo '<td>';
			if ($this->groep->mag(A::Bewerken)) {
				$form = new GroepLidForm($lid, $suggestions);
				$form->view();
			} else {
				echo $lid->opmerking;
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div></div>';
	}

}

class GroepStatistiekView extends GroepTabView {

	public function view() {
		parent::view();
		echo '<table class="groep-stats">';
		foreach ($this->groep->getStatistieken() as $title => $stat) {
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead>';
			echo '<tbody>';
			if (!is_array($stat)) {
				echo '<tr><td colspan="2">' . $stat . '</td></tr>';
				continue;
			}
			foreach ($stat as $row) {
				echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
			}
			echo '</tbody>';
		}
		echo '</table></div></div>';
	}

}

class GroepEmailsView extends GroepTabView {

	public function view() {
		parent::view();
		echo '<div class="groep-emails">';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				echo $profiel->getPrimaryEmail() . ';';
			}
		}
		echo '</div></div></div>';
	}

}
