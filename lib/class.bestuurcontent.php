<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.commissiecontent.php
# -------------------------------------------------------------------
#
# Beeldt informatie af over Commissies
#
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.bestuur.php');

class BestuurContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_bestuur;
	var $_lid;

	### public ###

	function BestuurContent (&$bestuur, &$lid) {
		$this->_bestuur =& $bestuur;
		$this->_lid =& $lid;
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

?>
