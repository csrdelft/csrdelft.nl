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

	if ($lid->hasPermission('P_FORUM_MOD')) {
		require_once('class.forum.php');
		$forum = new Forum($lid, $db);
		if(isset($_GET['topic'])){
			$iTopicID=(int)$_GET['topic'];
			if(isset($_GET['sluiten'])){
				if($forum->sluitTopic($iTopicID)){
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					exit;
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Oeps, feutje, niets gesloten dus.';
					exit;
				}
			}elseif(isset($_GET['openen'])){
				if($forum->openTopic($iTopicID)){
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Oeps, feutje, niets geopend dus.';
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				$_SESSION['forum_foutmelding']='Hier snap ik geen snars van.';
			}
		}else{
			header('location: http://csrdelft.nl/forum/');
			$_SESSION['forum_foutmelding']='Niets om te sluiten of te openen.';
		}
	} else {
		header('location: http://csrdelft.nl/forum/');
		$_SESSION['forum_foutmelding']='Geen rechten hier';
	}	
}
?>
