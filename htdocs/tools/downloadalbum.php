<?php
require_once 'include.config.php';
require_once 'class.fotoalbum.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){
        header('location: '.CSR_ROOT);
        exit;
}

set_time_limit(0);

//Album maken
$pad=$_GET['album'];

$mapnaam=explode('/',$pad);
array_pop($mapnaam);
$mapnaam=array_pop($mapnaam);

$fotoalbum=new Fotoalbum($pad, $mapnaam);

$fotos=$fotoalbum->getFotos();

//Headers
header('Content-type: application/x-tar');
header('Content-Disposition: attachment; filename="'.$mapnaam.'.tar"');

//tar-command maken
$cmd = "tar cC ".escapeshellarg(PICS_PATH.'/fotoalbum/'.$fotoalbum->getPad());
foreach($fotos as $foto){
	$cmd.=' '.escapeshellarg($foto->getBestandsnaam());
}

//teh magic
$fh=popen($cmd, 'r');
while(!feof($fh)){
	print fread($fh, 8192);
}
pclose($fh);

?>
