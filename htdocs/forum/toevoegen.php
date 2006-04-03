<?php

# prevent global namespace poisoning
main();
exit;
function main() {

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	session_start();
	$db = new MySQL();
	$lid = new Lid($db);
		
	//forum klasse laden
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
	require_once('bbcode/include.bbcode.php');
	$bbcode_uid=bbnewuid();
	
	# uitzoeken wat er moet gebeuren.
	
	//kijk of er een categorie-id gezet is.
	if(isset($_GET['forum'])){
		$iCatID=(int)$_GET['forum'];
		//kijken of er deze categorie gepost mag worden 
		if($lid->hasPermission($forum->getRechten_post($iCatID))){
			if(isset($_POST['bericht']) AND isset($_POST['titel'])){
				if(strlen(trim($_POST['bericht']))>0 AND strlen(trim($_POST['titel']))>0){
					$sBericht=bbsave($_POST['bericht'], $bbcode_uid, $db->dbResource());
					$sTitel=addslashes($_POST['titel']);
					//moderatiestap of niet?
					if($lid->hasPermission('P_LOGGED_IN')){ $bModerate_step=false; }else{ $bModerate_step=true; }
					$iTopicID=$forum->addPost($sBericht, $bbcode_uid, 0, $iCatID, $sTitel, $bModerate_step);
					if(is_int($iTopicID)){
						if($bModerate_step){
							//niet naar het topic refreshen, die is nog niet leesbaar...
							header('location: http://csrdelft.nl/forum/categorie/'.$iCatID.'&fout='.
								base64_encode('Uw bericht zal bekeken worden door de PubCie, bedankt voor het posten'));
						}else{
							header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'#laatste');
						}
					}else{
						header('location: http://csrdelft.nl/forum/categorie/'.$iCatID.'&fout='.
							base64_encode('Er ging iets mis met het databeest.'));
					}
				}else{
					//geen bericht ingevoerd
					header('location: http://csrdelft.nl/forum/categorie/'.$iCatID.'&fout='.
						base64_encode('U heeft een leeg bericht ingevoerd.'));
				}
			}else{
				//formulier is niet compleet.
				header('location: http://csrdelft.nl/forum/categorie/'.$iCatID.'&fout='.
					base64_encode('<h3>Helaas</h3>Het formulier is niet compleet!'));
			}
		}else{
			if($forum->catExistsVoorUser($iCatID)){
				//mag niet posten, maar wel de categorie zien, daarheen dan maar...
				header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
			}else{
				//mag niet kijken in de categorie, ook niet posten. Terugt naar het forumoverzicht
				header('location: http://csrdelft.nl/forum/?fout='.
					base64_encode('U heeft niet voldoende rechten om in deze categorie te posten of deze categorie te bekijken.'));
			}
		}
	}elseif(isset($_GET['topic'])){
		$iTopicID=(int)$_GET['topic'];
		if(isset($_POST['bericht'])){
			//reageren mag nog niet als men niet is ingelogged...
			if($forum->magBerichtToevoegen($iTopicID) AND $lid->isLoggedIn()){
				//post toevoegen aan een bestaand onderwerp
				if(strlen(trim($_POST['bericht']))>0){
					$sBericht=bbsave($_POST['bericht'], $bbcode_uid, $db->dbResource());
					if($forum->addPost($sBericht, $bbcode_uid, $iTopicID)){
						header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'#laatste');
					}else{
						header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'&fout='.
							base64_encode('Er ging iets mis met het databeest.'));
					}
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'&fout='.
						base64_encode('Bericht bevat geen tekens.'));
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'&fout='.
					base64_encode('U mag hier niet posten.'));
			}
		}else{
			header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'&fout='.
				base64_encode('Formulier incompleet.'));
		}
	}else{
		//geen catID en geen topicID gezet, niet compleet dus.
		header('location: http://csrdelft.nl/forum/?fout='.
			base64_encode('<h3>Helaas</h3>Het formulier is niet compleet (geen IDs)!'));
	}
}

?>
