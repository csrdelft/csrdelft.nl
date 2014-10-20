<?php

require_once 'peiling.class.php';

class PeilingBeheerContent implements View {

	private $pijlingen;

	public function __construct(array $pijlingen) {
		$this->pijlingen = $pijlingen;
	}

	public function getModel() {
		return $this->pijlingen;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return 'Peilingbeheer';
	}

	public function getHtml() {
		$lijst = '<h3>Peilingen:</h3>';
		foreach ($this->pijlingen as $peiling) {
			$pcontent = new PeilingContent(new Peiling($peiling['id']));
			$lijst.=$pcontent->getHtml($beheer = true);
		}
		$html = '
		<h1>Peilingbeheertool</h1>
		<div>
			' . getMelding() . '
			<span class="dikgedrukt">Nieuwe peiling:</span><br/>
			<form id="nieuwePeiling" action="/tools/peilingbeheer.php?action=toevoegen" method="post">
				<label for="titel">Titel:</label><input name="titel" type="text"/><br />
				<label for="verhaal">Verhaal:</label><textarea name="verhaal" rows="2"></textarea><br />
				<div id="peilingOpties">
					<label for="optie1">Optie 1</label><input name="opties[]" type="text" maxlength="255" /><br/>
					<label for="optie2">Optie 2</label><input name="opties[]" type="text" maxlength="255" /><br />
				</div>
				<label for="foo">&nbsp;</label> <input type="button" onclick="addOptie()" value="extra optie" /><br />
				<label for="submit">&nbsp;</label><input type="submit" value="Maak nieuwe peiling" />
			</form>
			<br />
			<div class="peilingen">
			' . $lijst . '
			</div>
		</div>
		<br/>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

}
