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

	if(isset($_GET['topic'])){
		$iTopicID=(int)$_GET['topic'];
		require_once('class.forum.php');
		$forum = new Forum($lid, $db);
		require_once('class.forumpoll.php');
		$poll = new ForumPoll($forum);
		$iCat=$forum->getCategorieVoorTopic($iTopicID);
		if($forum->catExistsVoorUser($iCat) AND $lid->hasPermission($forum->getRechten_post($iCat))) {
			if(isset($_POST['pollOptie']) AND $poll->topicHeeftPoll($iTopicID)){
				$iPollOptie=(int)$_POST['pollOptie'];
				//controleren of er al gestemd is
				if($poll->uidMagStemmen($iTopicID)){
					//stemmen dan maar...
					if($poll->addStem($iPollOptie)){
						if($iTopicID==7){
							header('location: http://csrdelft.nl/leden/');
						}else{
							header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
						}	
					}else{
						header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Optie bestaat niet.'));
					}
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('U mag maar een keer stemmen.'));
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Onjuiste gegevens.'));
			}
		}else{
			header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('U mag hier niet stemmen.'));
		}
	}else{
		header('location: http://csrdelft.nl/forum/?fout='.
			base64_encode('Hier snap ik geen snars van (waar is het topicID?).'));
	}	
}
?>
