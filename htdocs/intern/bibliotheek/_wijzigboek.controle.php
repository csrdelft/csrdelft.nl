<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	/*
	 * begin controleren invoer
	 */
	 	include("_invoercontrole.php");
		
		// resultaat geven, 
		if (isset($invoerOK) AND $invoerOK) {
			// teken van goedkeuring voor de pagina
			echo "ok";
		} else {
			$formulierModus = 'wijzig';
			include("_boek.form.php");
		}
	/*
	 * eind controleren invoer
	 */
?>