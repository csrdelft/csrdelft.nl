<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.commissiecontent.php
# -------------------------------------------------------------------
# Beeldt informatie af over Commissies
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.bestuur.php');

class BestuurContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_bestuur;
	var $_lid;

	### public ###

	function BestuurContent (&$bestuur){
		$this->_bestuur =& $bestuur;
		$this->_lid=Lid::get_lid();
	}
	function getTitel(){
		return 'Besturen der Civitas Studiosorum Reformatorum';
	}
	function viewWaarbenik(){
		echo '<a href="/vereniging/">Vereniging</a> &raquo; '.$this->getTitel();
	}
	
	function view(){
		$jaar=(int)getOrPost('jaar');
		$aBestuur=$this->_bestuur->getBestuur($jaar);
		$bestuur=new Smarty_csr();
		$bestuur->caching=false;
	
		$bestuur->assign('bestuur', $aBestuur);
		$bestuur->display('bestuur.tpl'); 
	}
}
class BestuurZijkolomContent extends BestuurContent{
	
	function view(){
		$aBesturen=$this->_bestuur->getBesturen();
		if(is_array($aBesturen)){
			echo '<ul style="list-style:none">';
			foreach($aBesturen as $bestuur){
				echo '<li style="margin-left: 0px; padding-left: 0px;">
					'.$bestuur['jaar'].'-'.($bestuur['jaar']+1).'&nbsp;';
				if($bestuur['praeses']!=''){
					echo '<a href="/vereniging/bestuur/'.$bestuur['jaar'].'">';
				}
				echo str_replace(' ', '&nbsp;', $bestuur['naam']);
				if($bestuur['praeses']!=''){ echo '</a>';}
				echo '</li>';
			}
			echo '</ul>';
		}
	}
			
		
}

?>
