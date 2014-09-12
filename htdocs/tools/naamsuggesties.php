<?php
/*
 * naamlink.php	| 	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in naamsuggesties voor de jquery.autocomplete plugin
 * 
 * request url: /tools/naamsuggesties/{$zoekin}?q=zoeknaam&limit=20&timestamp=1336432238620
 * response: [{"data":["Jan Lid","x101"],"value":"Jan Lid","result":"Jan Lid"},{...}]
 */

require_once 'configuratie.include.php';

if(!LoginModel::mag('P_LEDEN_READ')){
	echo json_encode(array(array('data'=>array('Niet voldoende rechten'), 'value'=>'Niet voldoende rechten', 'result'=>'')));
	exit;
}

//welke subset van leden?
$zoekin=array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID','S_ERELID');
$toegestanezoekfilters=array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies');
if(isset($_GET['zoekin']) AND in_array($_GET['zoekin'], $toegestanezoekfilters)){
	$zoekin=$_GET['zoekin'];
}
$zoekterm = '';
if(isset($_GET['q'])) {
	$zoekterm = $_GET['q'];

}
$velden=array('uid', 'voornaam', 'tussenvoegsel', 'achternaam');
$limiet = 0;
if(isset($_GET['limit'])) {
	$limiet = (int)$_GET['limit'];
}

$namen=Zoeker::zoekLeden($zoekterm, 'naam', 'alle', 'achternaam', $zoekin, $velden, $limiet);

$result=array();
foreach($namen as $naam){
	$tussenvoegsel=($naam['tussenvoegsel']!='') ? $naam['tussenvoegsel'].' ' : '';
	$fullname=$naam['voornaam'].' '.$tussenvoegsel.$naam['achternaam'];
	if(isset($_GET['result']) AND $_GET['result']=='uid'){
		$resultstr = $naam['uid'];
	}else{
		$resultstr = $fullname;
	}
	$result[]=array('data'=>array($fullname, $naam['uid']), 'value'=>$fullname, 'result'=>$resultstr);
}
echo json_encode($result);
exit;
