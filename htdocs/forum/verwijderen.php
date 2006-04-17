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
		if(isset($_GET['post'])){
			$iPostID=(int)$_GET['post'];
			$iTopicID=$forum->getTopicVoorPostID($iPostID);
			if($forum->deletePost($iPostID)){
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				exit;
			}else{
				header('location: http://csrdelft.nl/forum/?fout='.
					base64_encode('Verwijderen van bericht mislukt, iets mis met de db ofzo.'));
				exit;
			}
		}elseif(isset($_GET['topic'])){
			$iTopicID=(int)$_GET['topic'];
			$iCatID=$forum->getCategorieVoorTopic($iTopicID);
			if($forum->deleteTopic($iTopicID)){
				header('location: http://csrdelft.nl/forum/categorie/'.$iCatID);
				exit;
			}else{
				header('location: http://csrdelft.nl/forum/?fout='.
					base64_encode('Verwijderen van topic mislukt, iets mis met de db ofzo.'));
				exit;
			}
		}else{
			header('location: http://csrdelft.nl/forum/?fout='.base64_encode('Ik heb niets om te verwijderen'));
			exit;
		}
	} else {
		header('location: http://csrdelft.nl/forum/?fout='.base64_encode('Niets te zoeken hier!'));
	}	

}
?>
