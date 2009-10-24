<?php

require_once 'class.peiling.php';

class PeilingBeheerContent{
	private $resultaat;	
	
	function PeilingBeheerContent(){}

	public function setResultaat($resultaat){
		$this->resultaat = $resultaat;
	}
	
	public function getHTML(){				
		$lijst='<h3>Peilingen:</h3>';
		$peilingen = Peiling::getLijst();
		if($peilingen){
			foreach($peilingen as $peiling){
				$pcontent = new PeilingContent(new Peiling($peiling['id']));
				$lijst .= $pcontent->getBeheerHTML();
			}
		}
		$lijst = $lijst.'<br />';
		
		$resultaat = $this->resultaat;
		if($resultaat != ''){
			$resultaat .= '<br /><br />';
		}
		
		//TODO: aparte css file
		$html = '
		<script type="text/javascript" src="/layout/js/peilingbeheer.js"></script>
		<style type="text/css">
			.pb_rij {
				position:relative;
				display:table;	
			}
			.optie {
				height: 15px;
			}
			#nieuw label{ clear: left; float: left; width: 150px;}
			#nieuw input, textarea{ margin-bottom: 4px; }
			#opties input{ width: 400px;}
		</style>
		<h1>Peilingbeheertool</h1>
		<div style="position:relative">			 
			'.$resultaat.'	
			<b>Nieuwe peiling:</b><br/>
			<form id="nieuw" action="/tools/peilingbeheer.php" method="post">
				<label for="titel">Titel:</label><input name="titel" type="text"/><br />
				<label for="verhaal">Verhaal:</label><textarea name="verhaal" rows="2"></textarea><br />
				<div id="opties">
					<label for="optie1">Optie 1</label><input name="opties[]" type="text" maxlength="255" /><br/>
					<label for="optie2">Optie 2</label><input name="opties[]" type="text" maxlength="255" /><br />
				</div>
				<label for="foo">&nbsp;</label> <input type="button" onclick="addOptie()" value="extra optie" /><br />
				<label for="submit">&nbsp;</label><input type="submit" value="Maak nieuwe peiling" />
			</form>
			<br />
			<div class="peilingen">
			'.$lijst.'
			</div>
		</div>
		<br/>';
		return $html;
	} 
	
	public function getTitel(){ return 'Peilingbeheer'; }
	
	public function view(){
		echo $this->getHTML();
	}
}
?>
