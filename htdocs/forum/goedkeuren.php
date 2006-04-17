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
			if($forum->keurTopicGoed($iTopicID)){
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Onderwerp nu voor iedereen zichtbaar.'));
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'/'.base64_encode('Goedkeuren ging mis.'));
			}
		}else{
			header('location: http://csrdelft.nl/forum/?fout='.base64_encode('Geen topicID gezet.'));
		}
	} else {
		header('location: http://csrdelft.nl/forum/?fout='.base64_encode('U heeft daar niets te zoeken.'));
	}		
}

?>
