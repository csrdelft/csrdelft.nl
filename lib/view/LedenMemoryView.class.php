<?php

/**
 * LedenMemoryView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het spelletje memory met pasfotos en namen van leden
 */
class LedenMemoryView extends CompressedLayout {

	private $leden = array();
	private $learnmode;
	private $cheat;
	private $groep;

	public function __construct() {
		$lidstatus = array_merge(LidStatus::$lidlike, LidStatus::$oudlidlike);
		$lidstatus[] = LidStatus::Overleden;
		$this->groep = array();
		$this->cheat = isset($_GET['rosebud']);
		$this->learnmode = isset($_GET['oefenen']);
		if (isset($_GET['verticale'])) {
			$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
			if (strlen($v) > 1) {
				$verticale = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'), null, null, 1)->fetch();
			} else {
				$verticale = VerticalenModel::get($v);
			}
			if ($verticale) {
				$this->titel = 'Ledenmemory verticale ' . $verticale->naam . ($this->learnmode ? 'oefenen' : '');
				$this->groep = $verticale;
			}
		} else {
			$l = (int) filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
			if ($l < 1950) {
				$l = LichtingenModel::getJongsteLidjaar();
			}
			$lichting = LichtingenModel::get($l);
			if ($lichting) {
				$this->titel = 'Ledenmemory lichting ' . $lichting->lidjaar . ($this->learnmode ? 'oefenen' : '');
				$this->groep = $lichting;
			}
		}
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if (in_array($profiel->status, $lidstatus)) {
				$this->leden[] = $profiel;
			}
		}
	}

	public function getModel() {
		return $this->leden;
	}

	public function getBreadcrumbs() {
		return null;
	}

	private function getPasfotoPath($uid) {
		$pasfoto = 'pasfoto/geen-foto.jpg';
		foreach (array('png', 'jpeg', 'jpg', 'gif') as $validExtension) {
			if (file_exists(PICS_PATH . 'pasfoto/' . $uid . '.' . $validExtension)) {
				$pasfoto = 'pasfoto/' . $uid . '.' . $validExtension;
				break;
			}
		}
		// kijken of de vierkante bestaat, en anders maken.
		$vierkant = PICS_PATH . 'pasfoto/' . $uid . '.vierkant.png';
		if (!file_exists($vierkant)) {
			square_crop(PICS_PATH . $pasfoto, $vierkant, 150);
		}
		return CSR_ROOT . '/plaetjes/pasfoto/' . $uid . '.vierkant.png';
	}

	private function getPasfotoMemorycard(Profiel $profiel) {
		$cheat = ($this->cheat ? $profiel->uid : '');
		$title = htmlspecialchars($this->cheat ? $profiel->voornaam . ' ' . $profiel->tussenvoegsel . ' ' . $profiel->achternaam : '');
		$flipped = ($this->learnmode ? 'flipped' : '');
		$src = $this->getPasfotoPath($profiel->uid);
		return <<<HTML
<div uid="{$profiel->uid}" class="flip memorycard pasfoto {$flipped}">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<img src="{$src}" title="{$title}" />
	</div>
</div>
HTML;
	}

	private function getNaamMemorycard(Profiel $profiel) {
		$cheat = ($this->cheat ? $profiel->uid : '');
		$title = htmlspecialchars($profiel->voornaam . ' ' . $profiel->tussenvoegsel . ' ' . $profiel->achternaam);
		$flipped = ($this->learnmode ? 'flipped' : '');
		$naam = ProfielModel::getNaam($profiel->uid, 'civitas');
		return <<<HTML
<div uid="{$profiel->uid}" class="flip memorycard naam {$flipped}">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<h3 title="{$title}">{$naam}</h3>
	</div>
</div>
HTML;
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		?><!DOCTYPE html>
		<html>
			<head>
				<?= $smarty->fetch('html_head.tpl') ?>
			</head>
			<body data-groep="<?= $this->groep->getUUID() ?>">
				<table>
					<tbody>
						<tr>
							<td class="pasfotos">
								<?php
								$leden = $this->leden;
								if (!$this->cheat) {
									shuffle($leden);
								}
								foreach ($leden as $lid) {
									echo $this->getPasfotoMemorycard($lid);
								}
								?>
							</td>
							<td class="namen">
								<?php
								if (!$this->cheat AND ! $this->learnmode) {
									shuffle($this->leden);
								}
								foreach ($this->leden as $lid) {
									echo $this->getNaamMemorycard($lid);
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</body>
		</html>
		<?php
	}

}

class LedenMemoryScoreForm extends Formulier {

	public function __construct(LedenMemoryScore $score) {
		parent::__construct($score, '/leden/memoryscore');

		$fields[] = new RequiredIntField('tijd', $score->tijd, null, 1);
		$fields[] = new RequiredIntField('beurten', $score->beurten, null, 1);
		$fields[] = new RequiredIntField('goed', $score->goed, null, 1);
		$fields[] = new TextField('groep', $score->groep, null);
		$fields[] = new RequiredIntField('eerlijk', $score->eerlijk, null, 0, 1);

		$this->addFields($fields);
	}

}

class LedenMemoryScoreTable extends DataTable {

	public function __construct(AbstractGroep $groep = null) {
		parent::__construct(LedenMemoryScoresModel::orm, '/leden/memoryscores/' . ($groep ? $groep->getUUID() : null), 'Topscores Ledenmemory', 'groep');
		$this->settings['tableTools']['aButtons'] = array();
		$this->settings['dom'] = 'rtpli';

		$this->hideColumn('goed');
		$this->hideColumn('eerlijk');
		$this->hideColumn('wanneer');

		$this->setColumnTitle('door_uid', 'Naam');
	}

}

class LedenMemoryScoreResponse extends DataTableResponse {

	private $titles = array();

	public function getJson($score) {
		$array = $score->jsonSerialize();

		$minutes = floor($score->tijd / 60);
		$seconds = $score->tijd % 60;
		$array['tijd'] = ($minutes < 10 ? '0' : '') . $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;

		$array['door_uid'] = ProfielModel::getLink($score->door_uid, 'civitas');

		if (!isset($this->titles[$score->groep])) {
			$this->titles[$score->groep] = '';

			// Cache the title of the group
			$parts = explode('@', $score->groep);
			if (isset($parts[0], $parts[1])) {
				switch ($parts[1]) {

					case 'verticale.csrdelft.nl':
						$groep = VerticalenModel::getUUID($score->groep);
						$this->titles[$score->groep] = 'Verticale ' . $groep->naam;
						break;

					case 'lichting.csrdelft.nl':
						$this->titles[$score->groep] = 'Lichting ' . $parts[0];
						break;
				}
			}
		}
		$array['groep'] = $this->titles[$score->groep];

		return parent::getJson($array);
	}

}
