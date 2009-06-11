<?php

require_once 'class.peiling.php';
//Testklasse. Het echte werk moet gebeuren in PeilingUbbContent
class PeilingContent{
	var $_peiling;
	
	function PeilingContent($peiling){
		$this->_peiling=$peiling;		
	}
	
	public function viewPeiling(){
		$this->processPOST();
		
		$pid = $this->_peiling->getID();
		$dbpeiling = $this->_peiling->getPeiling();
		if(!$dbpeiling){
			return 'Peiling '.$pid.' bestaat niet.';	
		}		
		
		$bewerken='';
		if(Peiling::magBewerken()){
			$bewerken = '<div style="float: right;">
					<form id="verwijderpeiling'.$pid.'" action="'.$thisurl.'" method="post">
						<input type="hidden" name="actie" value="verwijder"/>
						<input type="hidden" name="id" value="'.$pid.'"/>
						<input type="submit" value="Verwijder"/>
					</form>
				</div>';
		}
		
		$content='';
		if($this->_peiling->magStemmen()){
			$content = $this->stemFormulier($dbpeiling);
		}else{
			$content = $this->uitslag($dbpeiling);
		}				
		
		//Constructie vd tag //width:400px;		
		$thisurl = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$html = '
			<div style="border: 1px solid black; margin: 10px; padding: 5px 10px;">			
				'.$bewerken.'
				'.$content.'
			</div>';
		return $html;
	}
	
	public function processPOST(){
		if( isset($_POST['actie']) && isset($_POST['id']) && is_numeric($_POST['id'])){
			$id = (int)$_POST['id'];
			$actie = $_POST['actie'];
			switch ($actie) {
				case "stem":
					if(isset($_POST['optie']) && is_numeric($_POST['optie'])){						
						$optie = (int)$_POST['optie'];
						$r = $this->_peiling->stem($optie);
					}
					break;
				case "verwijder":
					$r = $this->_peiling->deletePeiling($id);
					break;
			}
		}
			
	}
	
	//Krijgt als argument een array met id, titel en tekst. 
	public function stemFormulier($rpeiling){
		//Ophalen arrays uit het databeest
		$pid = $rpeiling['id'];		
		$titel = $rpeiling['titel'];
		$verhaal = nl2br($rpeiling['tekst']); //XHTML=true				

		$ropties = $this->_peiling->getPeilingOpties();		
		$opties = '';
		foreach($ropties as $roptie){
			$optieid = $roptie['id'];
			$tekst = $roptie['optie'];
			//$aantal = $ropties[$i]['stemmen'];
			$opties .= '<input type="radio" name="optie" value="'.($optieid).'"/> '.$tekst.'<br/>';
		}

		$thisurl = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		//Constructie vd tag //width:400px;
		$html = '<h3>'.$titel.'</h3>
				'.$verhaal.'
				<form id="peiling'.$pid.'" action="'.$thisurl.'" method="post">
					<div style="padding:5px 5px;">
						<input type="hidden" name="actie" value="stem"/>
						<input type="hidden" name="id" value="'.$pid.'"/>					
						'.$opties.'
					</div>
					<input type="submit" value="Verzend" />
				</form>
			';
		return $html;
	}
	
	public function uitslag($rpeiling){
		//Ophalen arrays uit het databeest		
		$pid = $rpeiling['id'];		
		$titel = $rpeiling['titel'];
		$verhaal = nl2br($rpeiling['tekst']); //XHTML=true		
		
		$ropties = $this->_peiling->getPeilingOpties();		
		$opties = '';
		$max = 0;
		foreach($ropties as $roptie){
			$max = max($max, $roptie['stemmen']);
		}		
		foreach($ropties as $roptie){
			$tekst = $roptie['optie'];
			$aantal = $roptie['stemmen'];
			$perc = 0;
			if($max != 0){
				$perc = $aantal / $max;
			}
			//$opties .= '> '.$tekst.' ('.$aantal.')<br/>';
			//#6060FF #0000FF
			$opties .= '
			<div style="position:relative;">
				<div style="width:200px">
					'.$tekst.' 
				</div>
				<div style="position:absolute;top:0px;left:200px; height: 10px; width: '.($perc*300).'px; background-color: #6060FF; display: block; border: 1px outset #0A338D;">
					&nbsp;
				</div><div style="position:absolute;top:0px;left:515px;">('.$aantal.')</div>
			</div>';
		}
		
		$html = '<h3>'.$titel.'</h3>
				'.$verhaal.'
				<div style="padding:5px 5px;">			
				'.$opties.'
				</div>';
		return $html;
	}
	
	public function getHTML(){
		//Geen id, dus mogelijk alle peilingen printen? idee voor Frontend.
		return "";
	} 
	
	public function view(){
		echo $this->getHTML();
	}
}

//Stemmen met javascript
class PeilingUbbContent {
	private $pcontent;	
	
	public function __construct($peilingid){
		$this->pcontent = new PeilingContent(new Peiling($peilingid));		
	}
	
	public function getHTML(){
		return $this->pcontent->viewPeiling();
	}
	public function view(){
		echo $this->getHTML();
	}
}
?>
