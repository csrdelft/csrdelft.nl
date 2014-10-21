<?php
require_once 'configuratie.include.php';

if (!LoginModel::mag('P_LEDEN_READ')) {
	redirect(CSR_ROOT);
}

/**
 * ledenmemory.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het spelletje memory met pasfotos en namen van leden
 */
class MemoryView extends HtmlPage {

	private $lichting;
	private $verticale;
	private $leden;
	private $learnmode;
	private $cheat;

	public function __construct() {
		$this->cheat = isset($_GET['rosebud']);
		$this->learnmode = isset($_GET['oefenen']);
		$this->verticale = null;
		if (isset($_GET['verticale'])) {
			$this->verticale = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
			$lettersById = OldVerticale::getLetters();
			$namenById = OldVerticale::getNamen();
			if (in_array($this->verticale, $namenById)) {
				$this->verticale = array_search($this->verticale, $namenById);
			} elseif (in_array($this->verticale, $lettersById)) {
				$this->verticale = array_search($this->verticale, $lettersById);
			}
			if (array_key_exists($this->verticale, $lettersById)) {
				$this->leden = Database::instance()->sqlSelect(array('uid', 'geslacht', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix'), 'lid', 'verticale = ? AND status IN ("S_GASTLID","S_LID","S_NOVIET","S_KRINGEL")', array($this->verticale), 'achternaam, tussenvoegsel, voornaam, voorletters')->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$this->verticale = null;
			}
		}
		if ($this->verticale === null) {
			$this->lichting = (int) filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
			if ($this->lichting < 1950) {
				$this->lichting = Lichting::getJongsteLichting();
			}
			$this->leden = Database::instance()->sqlSelect(array('uid', 'geslacht', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix'), 'lid', 'lidjaar = ? AND status IN ("S_GASTLID","S_LID","S_NOVIET","S_OUDLID","S_ERELID","S_OVERLEDEN")', array($this->lichting), 'achternaam, tussenvoegsel, voornaam, voorletters')->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function getTitel() {
		if ($this->verticale === null) {
			return 'Ledenmemory lichting ' . $this->lichting . ($this->learnmode ? 'oefenen' : '');
		}
		return 'Ledenmemory verticale ' . OldVerticale::getNaamById($this->verticale) . ($this->learnmode ? 'oefenen' : '');
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
		$title = ($this->cheat ? $lid['voornaam'] . ' ' . $lid['tussenvoegsel'] . ' ' . $lid['achternaam'] : '');
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
		$title = $lid['voornaam'] . ' ' . $lid['tussenvoegsel'] . ' ' . $lid['achternaam'];
		$flipped = ($this->learnmode ? 'flipped' : '');
		$naam = Lid::naamLink($lid['uid'], 'civitas', 'plain');
		return <<<HTML
<div uid="{$lid['uid']}" class="flip memorycard naam {$flipped}">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<h2 title="{$title}">{$naam}</h2>
	</div>
</div>
HTML;
	}

	public function view() {
		?>
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
		<?php
	}

}

$memory = new MemoryView();
$memory->addStylesheet($memory->getCompressedStyleUrl('layout', 'ledenmemory'), true);
$memory->addScript($memory->getCompressedScriptUrl('layout', 'ledenmemory'), true);

$smarty = CsrSmarty::instance();
$smarty->assign('titel', $memory->getTitel());
$smarty->assign('stylesheets', $memory->getStylesheets());
$smarty->assign('scripts', $memory->getScripts());
?><!DOCTYPE html>
<html>
	<head>
		<?= $smarty->fetch('html_head.tpl') ?>
	</head>
	<body>
		<?= $memory->view() ?>
	</body>
</html>