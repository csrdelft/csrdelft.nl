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
function wait_for_login(){
	if(!(isset($_SESSION['authenticated']) AND $_SESSION['authenticated'])){
		if(isset($_POST['password']) AND md5($_POST['password'])=='3f83075f710a248c7786cf72d0e501ce'){
			$_SESSION['authenticated']=true;
		}else{
			?>
			<h3>SoccieStreeplijstrapportagegeneratortool login</h3>

			<form method="post">
				<input type="password" name="password" /><input type="submit" value="inloggen" />
			</form>
			<?php

			exit;
		}
	}
}
?>