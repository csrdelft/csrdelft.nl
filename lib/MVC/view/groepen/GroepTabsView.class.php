<?php

/**
 * GroepTabsView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepLijstView extends TemplateView {

	private $forms = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->model->getGroepLeden() as $groeplid) {
			$this->forms[] = new GroepLidForm($groeplid);
		}
	}

	public function view() {
		echo '<table class="groepLeden"><tbody>';
		foreach ($this->forms as $form) {
			echo '<tr><td>' . Lid::naamLink($form->getModel()->lid_id, 'civitas', 'visitekaartje') . '</td>';
			echo '<td>';
			$form->view();
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

}

class GroepPasfotosView extends TemplateView {

	public function __construct(Groep $groep) {
		parent::__construct($groep);
	}

	public function view() {
		foreach ($this->model->getGroepLeden() as $groeplid) {
			echo '<div class="pasfoto">' . Lid::naamLink($groeplid->lid_id, 'pasfoto', 'link') . '</div>';
		}
	}

}

class GroepStatistiekView extends TemplateView {

	public function __construct(Groep $groep) {
		parent::__construct($groep->getStatistieken());
	}

	public function view() {
		echo '<table class="groepStats">';
		foreach ($this->model as $title => $stat) {
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead><tbody>';
			if (!is_array($stat)) {
				echo '<tr><td colspan="2">' . $stat . '</td></tr>';
				continue;
			}
			foreach ($stat as $row) {
				echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
			}
		}
		echo '</tbody></table>';
	}

}

class GroepEmailsView extends TemplateView {

	private $emails = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->model->getGroepLeden() as $groeplid) {
			$lid = LidCache::getLid($groeplid->lid_id);
			if ($lid instanceof Lid AND $lid->getEmail() != '') {
				$this->emails[] = $lid->getEmail();
			}
		}
	}

	public function view() {
		echo '<div class="emails">' . implode(', ', $this->emails) . '</div>';
	}

}
