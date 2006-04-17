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
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Oeps, feutje..'));
					exit;
				}
			}elseif(isset($_GET['openen'])){
				if($forum->openTopic($iTopicID)){
					header('location: /forum/onderwerp/'.$iTopicID);
					exit;
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Oeps, feutje..'));
					exit;
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Hier snap ik geen snars van.'));
				exit;
			}
		}else{
			header('location: http://csrdelft.nl/forum/?fout='.base64_encode('Niets om te sluiten of te openen.'));
				exit;
		}
	} else {
		header('location: http://csrdelft.nl/forum/?fout='.base64_encode('Geen rechten hier'));
	}	
}
?>
