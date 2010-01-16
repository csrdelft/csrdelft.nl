<?php
/*
 * Alphacursusbanner.
 */
header('content-type: image/jpeg');

$width=370;
$height=62;
if(isset($_GET['width'])){
	if($_GET['width']>300){
		$width=(int)$_GET['width'];
		$height=ceil($width/6);
	}
}


$cachename='alphabanner.cache.'.$width.'.jpg';
$sourcepath='';

if(file_exists($cachename)){
	$valid=date('Y-m-d', filemtime($cachename)) == date('Y-m-d');
}else{
	$valid=false;
}

if(!$valid){
	$img=imagecreatetruecolor($width, $height);
	
	$diff=strtotime('2010-02-18')-time();
	$diffdays=floor($diff/(60*60*24));
	
	if($diffdays<1){
		$parts=array('alpha', 'leeg', 'alpha', 'leeg', 'alpha', 'leeg');
	}else{
		$parts=array('alpha', 'start', 'in');
		if($diffdays>9){
			$parts[]=floor($diffdays/10);
			$parts[]=floor(($diffdays-floor($diffdays/10))/10);
		}else{
			$parts[]='leeg';
			$parts[]=$diffdays;
		}
		if($diffdays>1){
			$parts[]='dagen';
		}else{
			$diffdays='dag';
		}
	}
	
	$i=0;
	foreach($parts as $part){
		$sourcefile=$sourcepath.'tegel_'.$part.'.jpg';
		//imagecopy($img, imagecreatefromjpeg($sourcefile), $i, 0, 0, 0, 62, 62);
		imagecopyresampled($img, imagecreatefromjpeg($sourcefile), $i, 0, 0, 0, $height, $height, 50, 50);
		$i=$i+$height;
	}
	imagejpeg($img, $cachename);
	imagejpeg($img);
}else{
	echo file_get_contents($cachename);
}

?>
