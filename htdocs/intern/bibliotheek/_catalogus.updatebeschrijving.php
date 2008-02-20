<?php
	require_once('inc.common.php');
	
	$boekID = (int) $_POST['boekID'];
	$beschrijving = db_escape(nl2br(htmlentities($_POST['beschrijving'], ENT_QUOTES, 'UTF-8')));
	
	if ($beschrijving != '') {
		$result = db_query("UPDATE biebbeschrijving SET 
				beschrijving='$beschrijving', 
				toegevoegd = " . time() . "
			WHERE
				boek_id = $boekID AND
				schrijver_uid='" . INGELOGD_UID . "'"
		);
		
		if ($result) echo "ok";
		else echo "Fout opgetreden bij uitvoeren database-query";
	} else {
		echo "Beschrijving is leeg";
	}
?>