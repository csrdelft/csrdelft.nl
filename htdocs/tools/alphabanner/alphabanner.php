<?php
/*
 * Alphacursusbanner.
 */
//header('content-type: image/jpeg');

$cachename='alphabanner.cache.jpg';
$sourcepath='';

if(file_exists($cachename)){
	$valid=filemtime($cachename)-strtotime(date('Y-m-d')) < 60*60*24;
}else{
	$valid=false;
}

if(!$valid){
	$img=imagecreatetruecolor(370, 62);
	
	$diff=strtotime('2010-02-18')-time();
	$diffdays=floor($diff/(60*60*24));
	
	if($diffdays<1){
		$parts=array('alpha', 'leeg', 'alpha', 'leeg', 'alpha', 'leeg');
	}else{
		$parts=array('alpha', 'start', 'in');
		if($diffdays>9){
			$parts[]=floor($diffdays/10);
			$parts[]=($diffdays-floor($diffdays/10))/10;
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
		imagecopyresampled($img, imagecreatefromjpeg($sourcefile), $i, 0, 0, 0, 62, 62, 50, 50);
		$i=$i+62;
	}
	imagejpeg($img, $cachename);
}

echo file_get_contents($cachename);

?>
