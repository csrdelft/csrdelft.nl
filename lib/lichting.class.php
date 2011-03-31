<?php

class Lichting{

	public static function getJongsteLichting(){
		$lichting=date('Y');
		if(date('m')<=8){ $lichting--; }
		return $lichting;
	}
}
?>
