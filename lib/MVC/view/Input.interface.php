<?php

/**
 * Input.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een interface met validate() methode om de invoer te checken.
 * 
 */
interface Input {
	
	public function validate();
	
	public function getError();
	
}

?>