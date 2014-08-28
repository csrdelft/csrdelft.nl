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
	private $cheat;

	public function __construct() {
		$this->cheat = isset($_GET['rosebud']);
		$this->lidjaar = filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
		if ($this->lidjaar < 1950) {
			$this->lidjaar = Lichting::getJongsteLichting();
		}
		$this->leden = Database::instance()->sqlSelect(array('uid', 'geslacht', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix'), 'lid', 'lidjaar = ? AND status IN ("S_GASTLID","S_LID","S_NOVIET","S_OUDLID","S_KRINGEL","S_ERELID","S_OVERLEDEN")', array($this->lidjaar), 'achternaam, tussenvoegsel, voornaam, voorletters')->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getTitel() {
		return 'Ledenmemory lichting ' . $this->lidjaar;
	}

	public function getModel() {
		return $this->leden;
	}

	private function getPasfotoMemorycard($lid) {
		$cheat = ($this->cheat ? $lid['uid'] : '');
		return <<<HTML
<div uid="{$lid['uid']}" class="box flip memorycard pasfoto">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<img src="http://plaetjes.csrdelft.nl/pasfoto/{$lid['uid']}.vierkant.png" />
	</div>
</div>
HTML;
	}

	private function getNaamMemorycard($lid) {
		$cheat = ($this->cheat ? $lid['uid'] : '');
		return <<<HTML
<div uid="{$lid['uid']}" class="box flip memorycard naam">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<h2>{$lid['voornaam']} {$lid['tussenvoegsel']} {$lid['achternaam']}</h2>
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
		<link rel="stylesheet" href="/layout/css/ledenmemory.css" type="text/css" />
		<script type="text/javascript" src="/layout/js/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="/layout2/js/jquery.backstretch.js"></script>
		<script type="text/javascript" src="/layout/js/ledenmemory.js"></script>
	</head>
	<body>
		<?= $memory->view() ?>
	</body>
</html>