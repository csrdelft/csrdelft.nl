<?php
	require_once('inc.common.php');
	
	$boekID = (int) $_POST['boekID'];
	$beschrijving = db_escape(nl2br(htmlentities($_POST['beschrijving'], ENT_QUOTES, 'UTF-8')));
	
	$result = db_query("
		SELECT id FROM biebbeschrijving 
		WHERE schrijver_uid = '" . INGELOGD_UID . "' AND boek_id = $boekID"
	);
	
	# check op eerdere beschrijving
	if (mysql_num_rows($result) > 0) {
		echo "U heeft al eerder een beschrijving voor dit boek toegevoegd";
		exit;
	}
	
	if ($beschrijving != '') {
		$result = db_query("INSERT INTO biebbeschrijving SET 
			boek_id = $boekID, 
			beschrijving='$beschrijving', 
			schrijver_uid='" . INGELOGD_UID . "',
			toegevoegd = " . time()
		);
		
		if ($result) echo "ok";
		else echo "Fout opgetreden bij uitvoeren database-query";
	} else {
		echo "Beschrijving is leeg";
	}
?>