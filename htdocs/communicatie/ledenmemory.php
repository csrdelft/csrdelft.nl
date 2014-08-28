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
class MemoryView implements View {

	private $lidjaar;
	private $leden;
	private $learnmode;
	private $cheat;

	public function __construct() {
		$this->cheat = isset($_GET['rosebud']);
		$this->learnmode = isset($_GET['oefenen']);
		$this->lidjaar = filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
		if ($this->lidjaar < 1950) {
			$this->lidjaar = Lichting::getJongsteLichting();
		}
		$this->leden = Database::instance()->sqlSelect(array('uid', 'geslacht', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix'), 'lid', 'lidjaar = ? AND status IN ("S_GASTLID","S_LID","S_NOVIET","S_OUDLID","S_KRINGEL","S_ERELID","S_OVERLEDEN")', array($this->lidjaar), 'achternaam, tussenvoegsel, voornaam, voorletters')->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getTitel() {
		return 'Ledenmemory lichting ' . $this->lidjaar . ($this->learnmode ? 'oefenen' : '');
	}

	public function getModel() {
		return $this->leden;
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
						if (!$this->cheat) {
							shuffle($this->leden);
						}
						foreach ($this->leden as $lid) {
							echo $this->getPasfotoMemorycard($lid);
						}
						?>
					</td>
					<td class="namen">
						<?php
						if (!$this->cheat) {
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
?><!DOCTYPE html>
<html>
	<head>
		<title><?= $memory->getTitel() ?></title>
		<link rel="stylesheet" href="/layout/css/flip.min.css" type="text/css" />
		<link rel="stylesheet" href="/layout/css/ledenmemory.min.css" type="text/css" />
		<script type="text/javascript" src="/layout/js/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="/layout/js/jquery/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/layout2/js/jquery.backstretch.min.js"></script>
		<script type="text/javascript" src="/layout/js/ledenmemory.min.js"></script>
	</head>
	<body>
		<?= $memory->view() ?>
	</body>
</html>