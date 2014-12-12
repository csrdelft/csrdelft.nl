<?php

/**
 * LedenMemoryView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het spelletje memory met pasfotos en namen van leden
 */
class LedenMemoryView extends CompressedLayout {

	private $lichting;
	private $verticale;
	private $leden;
	private $learnmode;
	private $cheat;

	public function __construct() {
		$this->cheat = isset($_GET['rosebud']);
		$this->learnmode = isset($_GET['oefenen']);
		if (isset($_GET['verticale'])) {
			$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
			if (strlen($v) > 1) {
				$result = VerticalenModel::instance()->findVerticaleByName($v);
				if ($result->rowCount() === 1) {
					$this->verticale = $result->fetch();
				}
			} else {
				$verticale = VerticalenModel::instance()->getVerticaleByLetter($v);
				if ($verticale instanceof Verticale) {
					$this->verticale = $verticale;
				}
			}
			$this->leden = Database::instance()->sqlSelect(array('uid', 'geslacht', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix'), 'lid', 'verticale = ? AND status IN ("S_GASTLID","S_LID","S_NOVIET","S_KRINGEL")', array($this->verticale->id), 'achternaam, tussenvoegsel, voornaam, voorletters')->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$this->lichting = (int) filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
			if ($this->lichting < 1950) {
				$this->lichting = Lichting::getJongsteLichting();
			}
			$this->leden = Database::instance()->sqlSelect(array('uid', 'geslacht', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix'), 'lid', 'lidjaar = ? AND status IN ("S_GASTLID","S_LID","S_NOVIET","S_OUDLID","S_ERELID","S_OVERLEDEN")', array($this->lichting), 'achternaam, tussenvoegsel, voornaam, voorletters')->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function getTitel() {
		if (isset($this->lichting)) {
			return 'Ledenmemory lichting ' . $this->lichting . ($this->learnmode ? 'oefenen' : '');
		}
		return 'Ledenmemory verticale ' . $this->verticale->naam . ($this->learnmode ? 'oefenen' : '');
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
		return CSR_PICS . '/pasfoto/' . $uid . '.vierkant.png';
	}

	private function getPasfotoMemorycard($lid) {
		$cheat = ($this->cheat ? $lid['uid'] : '');
		$title = htmlspecialchars($this->cheat ? $lid['voornaam'] . ' ' . $lid['tussenvoegsel'] . ' ' . $lid['achternaam'] : '');
		$flipped = ($this->learnmode ? 'flipped' : '');
		$src = $this->getPasfotoPath($lid['uid']);
		return <<<HTML
<div uid="{$lid['uid']}" class="flip memorycard pasfoto {$flipped}">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<img src="{$src}" title="{$title}" />
	</div>
</div>
HTML;
	}

	private function getNaamMemorycard($lid) {
		$cheat = ($this->cheat ? $lid['uid'] : '');
		$title = htmlspecialchars($lid['voornaam'] . ' ' . $lid['tussenvoegsel'] . ' ' . $lid['achternaam']);
		$flipped = ($this->learnmode ? 'flipped' : '');
		$naam = Lid::naamLink($lid['uid'], 'civitas', 'plain');
		return <<<HTML
<div uid="{$lid['uid']}" class="flip memorycard naam {$flipped}">
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
			<body>
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
