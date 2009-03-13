<?php
/*
 * class.mededeling.php	|  Maarten Somhorst
 *
 *
 */


class Mededeling{


}
class Mededelingen{

	private $mededelingen=null;

	public function __construct($aantal, $categorie=0){

	}

	public function getMededelingen($force=false){
		if($force OR $this->mededelingen===null){
			//load
		}
		return $this->mededelingen;
	}
}
?>
