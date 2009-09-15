<?php


class StamboomContent{

	private $root;
	
	public function __construct($startuid, $levels=3){
		if(!Lid::isValidUid($startuid)){
			throw new Exception('Opgegeven uid is niet geldig');
		}
		$this->root=LidCache::getLid($startuid);
	}
	public function getTitel(){
		return 'Stamboom voor het geslacht van '.$this->root->getNaam();
	}
	private function viewNode($lid){
		echo '<div class="node">';
		echo $lid->getPasfoto();
		echo $lid->getNaamLink('civitas', 'link').'<br />';
		
		if(count($lid->getKinderen())>0){
			echo '<div class="kinderen">';
			foreach($lid->getKinderen() as $kind){
				$this->viewNode($kind);
			}
			echo '<div class="clear">&nbsp;</div>';
			echo '</div>';
		}

		echo '</div>';
	}
	public function view(){
		$this->viewNode($this->root);
	}

}
?>
