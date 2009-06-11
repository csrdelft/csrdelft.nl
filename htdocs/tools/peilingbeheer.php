<?php
/*
 * Peiling beheerpagina
 * 
 */
# instellingen & rommeltjes
require_once 'include.config.php';
require_once 'class.peilingcontent.php' ;

// if user has no permission
if(!$loginlid->hasPermission('P_LOGGED_IN') OR !Peiling::magBewerken()){
	$melding="Je hebt geen rechten om deze pagina te bekijken.";
	$pagina=new csrdelft(new Stringincluder($melding, 'Peilingbeheer'));
	$pagina->view();
}

$html = '';
$resultaat ='';
if(isset($_POST) && isset($_POST['titel'])){
	//Process post
	require_once('class.peiling.php');
	$titel = $_POST['titel'];
	$properties['titel'] = $titel;
	$verhaal = $_POST['verhaal'];
	$properties['verhaal'] = $verhaal;
	$optieid = 1;
	$opties=array();
	while(isset($_POST['optie'.$optieid]) ){
		$opties[$optieid] = $_POST['optie'.$optieid];
		$optieid++;
	}
	$properties['opties']=$opties;
	print_r($properties);

	$peiling = new Peiling(0);
	$pid = $peiling->maakPeiling($properties);	
	$resultaat = 'De nieuwe peiling heeft id '.$pid.'.';
}


//require_once('class.peilingbeheercontent.php');

$lijst='Peilingen: ';
$peilingen = Peiling::getLijst();
if($peilingen){
	foreach($peilingen as $peiling){
		$lijst .= $peiling['id'].' ';
	}
}
$lijst = $lijst.'<br/>';


$html .= '
<script type="text/javascript">
var i=2;
function addOptie()
{
i++;
var xl=document.getElementById("opties_l");
var xr=document.getElementById("opties_r");
xl.innerHTML += \'<div class="optie">Optie \'+i+\':</div>\';
xr.innerHTML += \'<div class="optie"><input name="optie\'+i+\'" type="text"/></div>\';
}
</script>
<style type="text/css">
.pb_n_rij {
	position:relative;
	display:table;	
}
.pb_n_col1 {	
	position:relative;
	float:left;
	width:90px;	
}
.pb_n_col2 {
	position:relative;
	float:right;	
}
.optie {
	height: 15px;
}
#submitd{
	position:relative;
	//top:60px;
}

</style>
<h1>Peilingbeheertool</h1>
<div style="position:relative">
	<b>Nieuwe peiling:</b><br/>
	<form id="nieuw" action="/tools/peilingbeheer.php" method="post">
		<div class="pb_n_rij">
			<div class="pb_n_col1">
				Titel:<br/>
				Verhaal:<br/>
				<div style="height:50px;"></div>
				<div id="opties_l">
					<input type="button" onclick="addOptie()" value="extra optie"/><br/>
					<br/>
					<div class="optie">Optie 1:</div>
					<div class="optie">Optie 2:</div>
				</div>
			</div>
			<div class="pb_n_col2">
				<input name="titel" type="text"/><br/>
				<textarea name="verhaal" rows="2"></textarea>
				<div style="height:39px;"></div>				
				<div id="opties_r">
					<div class="optie"><input name="optie1" type="text"/></div>
					<div class="optie"><input name="optie2" type="text"/></div>
				</div>
			</div>
		</div>
		<div id="submitd">			
			<input type="submit" value="Maak nieuwe peiling"/>
		</div>	
	</form>
	<div style="position:relative;">
	'.$lijst.' <br/><br/>
	'.$resultaat.'	
	</div>
</div>
<br/>';

$pagina=new csrdelft(new stringincluder($html, 'Peilingbeheer'));
$pagina->view();
?>
