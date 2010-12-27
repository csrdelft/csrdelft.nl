<?php

setlocale(LC_MONETARY, 'nl_NL');
function euro($amount){
	return '&euro; '.money_format('%i', $amount);
}

function parse_bestelstring($string){
	$ptr=0;
	$return=array();

	$multiplier=1;

	while($ptr<strlen($string)){
		$current=substr($string, $ptr, 1);
		//echo $ptr.' - '.$current.'<br />';

		//if($current=='h'){
		//	$multiplier=0.5;
		//}else
		if(is_numeric($current)){
			$numptr=$ptr+1;
			$num=$current;
			while(is_numeric(substr($string, $numptr, 1))){
				$num.=substr($string, $numptr, 1);
				$numptr++;
			}
			$multiplier=$num;
			//sla het gelezen getal over.
			$ptr=$numptr-1;
		}else{
			$add=($multiplier*1);
			if(isset($return[$current])){
				$return[$current]+=$add;
			}else{
				$return[$current]=$add;
			}
			$multiplier=1;
		}
		$ptr++;
	}
	return $return;
}
?>