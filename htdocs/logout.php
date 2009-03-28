<?php

# logout.php

require_once('include.config.php');

$loginlid->logout();

# url checken
if (isset($_POST['url']) and preg_match("/^[-\w?&=.\/]+$/", $_POST['url'])){
	header("Location: '.CSR_ROOT.'{$_POST['url']}");
}else{
	if(isset($_SERVER['HTTP_REFERER'])){
		header('location: '.$_SERVER['HTTP_REFERER']);
	}else{
		header('location: '.CSR_ROOT);
	}
}

exit;

?>
