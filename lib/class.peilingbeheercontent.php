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
			#pb_col_links {	
				position:relative;
				float:left;
				width:90px;	
			}
			#pb_col_rechts {
				position:relative;
				float:right;	
			}
			.optie {
				height: 15px;
			}
			#submitd{
				position:relative;
				//top:60px;
			}		
		</style>
		<h1>Peilingbeheertool</h1>
		<div style="position:relative">			 
			'.$resultaat.'	
			<b>Nieuwe peiling:</b><br/>
			<form id="nieuw" action="/tools/peilingbeheer.php" method="post">
				<div class="pb_rij">
					<div id="pb_col_links">
						Titel:<br/>
						Verhaal:<br/>
						<div style="height:50px;"></div>
						<div id="opties_l">
							<input type="button" onclick="addOptie()" value="extra optie"/><br/>
							<br/>
							<div class="optie">Optie 1:</div>
							<div class="optie">Optie 2:</div>
						</div>
					</div>
					<div id="pb_col_rechts">
						<input name="titel" type="text"/><br/>
						<textarea name="verhaal" rows="2"></textarea>
						<div style="height:39px;"></div>				
						<div id="opties_r">
							<div class="optie"><input name="optie1" type="text" maxlength=255/></div>
							<div class="optie"><input name="optie2" type="text" maxlength=255/></div>
						</div>
					</div>
				</div>
				<div id="submitd">			
					<input type="submit" value="Maak nieuwe peiling"/>
				</div>	
			</form>
			<br />
			<div style="position:relative;">
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
