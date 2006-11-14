<?php

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
		
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
					//Nieuw onderwerp toevoegen toevoegen
					$sBericht=bbsave($_POST['bericht'], $bbcode_uid, $db->dbResource());
					$sTitel=addslashes($_POST['titel']);
					//modereren of niet.
					$bModerate_step=(!$lid->hasPermission('P_LOGGED_IN'));
					//topic daadwerkelijk toevoegen.
					$iTopicID=$forum->addPost($sBericht, $bbcode_uid, 
						0, $iCatID, //0 voor een nieuw topic, in een bepaalde categorie
						$sTitel, 
						//direct zichtbaar, of eerst door mods bevestigen 
						$bModerate_step);
					//als topicID een integer is is het onderwerp succesvol toegevoegd. 
					if(is_int($iTopicID)){
						if($bModerate_step){
							//niet naar het topic refreshen, die is nog niet leesbaar...
							header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
							$_SESSION['forum_foutmelding']='Uw bericht zal bekeken worden door de PubCie, bedankt voor het posten';
						}else{
							header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'#laatste');
						}
					}else{
						header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
						$_SESSION['forum_foutmelding']='Er ging iets mis met het databeest.';
					}
				}else{
					//geen bericht ingevoerd
					header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
					$_SESSION['forum_foutmelding']='U heeft een leeg bericht ingevoerd.';
				}
			}else{
				//formulier is niet compleet.
				header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
				$_SESSION['forum_foutmelding']='<h3>Helaas</h3>Het formulier is niet compleet!';
			}
		}else{
			if($forum->catExistsVoorUser($iCatID)){
				//mag niet posten, maar wel de categorie zien, daarheen dan maar...
				header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
			}else{
				//mag niet kijken in de categorie, ook niet posten. Terugt naar het forumoverzicht
				header('location: http://csrdelft.nl/forum/');
				$_SESSION['forum_foutmelding']='U heeft niet voldoende rechten om in deze categorie te posten of deze categorie te bekijken.';
			}
		}
	//Nieuwe berichten toevoegen.
	}elseif(isset($_GET['topic'])){
		$iTopicID=(int)$_GET['topic'];
		if(isset($_POST['bericht'])){
			//reageren mag nog niet als men niet is ingelogged...
			if($forum->magBerichtToevoegen($iTopicID)){
				//post toevoegen aan een bestaand onderwerp
				if(strlen(trim($_POST['bericht']))>0){
					$bModerate_step=(!$lid->hasPermission('P_LOGGED_IN')); //modereren of niet.
					$sBericht=bbsave($_POST['bericht'], $bbcode_uid, $db->dbResource());
					if($forum->addPost($sBericht, $bbcode_uid, $iTopicID, 0, '', $bModerate_step)){
						if($bModerate_step){
							header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'#laatste');
							$_SESSION['forum_foutmelding']='Uw bericht is verwerkt, het zal binnenkort goedgekeurd worden.';
						}else{
							header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'#laatste');
						}
					}else{
						header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
						$_SESSION['forum_foutmelding']='Er ging iets mis met het databeest.';
					}
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Bericht bevat geen tekens.';
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				$_SESSION['forum_foutmelding']='U mag hier niet posten.';
			}
		}else{
			header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
			$_SESSION['forum_foutmelding']='Formulier incompleet.';
		}
	}else{
		//geen catID en geen topicID gezet, niet compleet dus.
		header('location: http://csrdelft.nl/forum/');
		$_SESSION['forum_foutmelding']='<h3>Helaas</h3>Het formulier is niet compleet (geen ID\'s)!';
	}


?>
