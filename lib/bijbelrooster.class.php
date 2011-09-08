<?php
require_once 'lid/instellingen.class.php';
require_once 'memcached.class.php';

class Bijbelrooster{
	
	
	
	
	function getLink($stukje){
		$bijbelvertalingen= array("NBV"=>"id18=1", "NBG" => "id16=1", "Herziene Statenvertaling" => "id47=1", "Statenvertaling (Jongbloed)" => "id37=1", "Groot Nieuws Bijbel" => "id17=1", "Willibrordvertaling"=>"id35=1");
		$link = str_replace(' ', '+', $stukje);
		$link = 'http://www.biblija.net/biblija.cgi?m='.$link.'&'.$bijbelvertalingen[Instelling::get('algemeen_bijbel')].'&l=nl&set=10';
		
		return '<a href='.$link.'>'.$stukje.'</a>';
	}
	
	function view(){
		$db=MySql::instance();
		$query="SELECT * FROM bijbelrooster ORDER BY dag ASC";
		$res=$db->select($query);
		$return='
			<h1>Bijbelrooster</h1>
			<p>Hier vindt u het bijbelrooster der C.S.R.. Uw favoriete bijbelvertaling kunt u instellen bij uw instellingen.</p><p>';
		while($row = mysql_fetch_array($res, MYSQL_ASSOC)){
			$class = '';
			if($row['dag']<date('Y-m-d')){
				$class = 'lichtgrijs';
			}
			$return.= '<span class="' .$class. '">' . date('d-m-Y', strtotime($row['dag'])) . ':</span> ' .$this->getLink($row['stukje']). "<br />";
		}
		echo '</p>'.$return;
	}
	
}
?>