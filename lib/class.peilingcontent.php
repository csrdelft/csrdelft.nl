<?php

require_once 'class.peiling.php';

class PeilingContent{
	var $_peiling;
	
	function PeilingContent($peiling){
		$this->_peiling=$peiling;		
	}

	//Krijgt als argument een array met id, titel en tekst. 
	public function stemFormulier($rpeiling){
		//Ophalen arrays uit het databeest
		$pid = $rpeiling['id'];		
		$titel = $rpeiling['titel'];			
		$verhaal = CsrUbb::instance()->getHTML($rpeiling['tekst']);
		
		$opties = '';
		$ropties = $this->_peiling->getPeilingOpties();		
		if(!empty($ropties)){
			foreach($ropties as $roptie){
				$optieid = $roptie['id'];
				$tekst = CsrUbb::instance()->getHTML($roptie['optie']);
				//$aantal = $ropties[$i]['stemmen'];
				$opties .= '<input type="radio" name="optie" value="'.($optieid).'"/> '.$tekst.'<br/>';
			}
		}
		
		//Constructie vd tag //width:400px;
		$html = '<h3>'.$titel.'</h3>
				'.$verhaal.'
				<form id="peiling'.$pid.'" action="/tools/peilingbeheer.php" method="post">
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
		$verhaal = CsrUbb::instance()->getHTML($rpeiling['tekst']);	
		
		$opties = '';
		$ropties = $this->_peiling->getPeilingOpties();
		if(!empty($ropties)){
			$max = 0;
			foreach($ropties as $roptie){
				$max = max($max, $roptie['stemmen']);
			}		
			foreach($ropties as $roptie){
				$tekst = CsrUbb::instance()->getHTML($roptie['optie']);
				$aantal = $roptie['stemmen'];
				$perc = 0;
				if($max != 0){
					$perc = $aantal / $max;
				}
	
				$opties .= '
				<div class="peilingoptie">
					<div class="peilingoptietekst">
						'.$tekst.' 
					</div>
					<div class="peilingoptiebalk">
					 <div class="peilingbalk" style="width: '.round($perc*150).'px;">&nbsp;</div>
					</div>
					<div class="peilingoptieaantal">('.$aantal.')</div>
				</div>';
			}
		}
		
		$html = '<h3>'.$titel.'</h3>
				'.$verhaal.'
				<div class="peilingopties">			
				'.$opties.'
				<div class="clear">&nbsp;</div>
				</div>';
		return $html;
	}
	
	private function bewerkVeld(){
		if(Peiling::magBewerken()){
			$pid = $this->_peiling->getID();	
			return '<div style="float: right;">
					<form id="verwijderpeiling'.$pid.'" action="/tools/peilingbeheer.php" method="post">
						<input type="hidden" name="actie" value="verwijder"/>
						<input type="hidden" name="id" value="'.$pid.'"/>
						<input type="submit" value="Verwijder"/>
					</form>
				</div>';
		}
	}	
	
	public function getBeheerHTML(){
		$dbpeiling = $this->_peiling->getPeiling();
		if(!$dbpeiling){
			return 'Peiling '.$pid.' bestaat niet.';	
		}				

		$html = '
			<div class="peiling">			
				'.$this->bewerkVeld().'
				Peiling #'.$dbpeiling['id'].'
				'.$this->uitslag($dbpeiling).'
			</div>';
		return $html;
	}
	
	public function getHTML(){				
		$pid = $this->_peiling->getID();
		$dbpeiling = $this->_peiling->getPeiling();
		if(!$dbpeiling){
			return 'Peiling '.$pid.' bestaat niet.';	
		}		
				
		$content='';
		if($this->_peiling->magStemmen()){
			$content = $this->stemFormulier($dbpeiling);
		}else{
			$content = $this->uitslag($dbpeiling);
		}				
		
		//Constructie vd tag
		$html = '
			<div class="peiling" id="peiling'.$pid.'">
				'.$content.'
			</div>';
		return $html;
	} 
	
	public function view(){
		echo $this->getHTML();
	}
}

class PeilingUbbContent {
	private $pcontent;	
	
	public function __construct($peilingid){
		$this->pcontent = new PeilingContent(new Peiling($peilingid));		
	}
	
	public function getHTML(){
		return $this->pcontent->getHTML();
	}
	public function view(){
		echo $this->getHTML();
	}
}
?>
