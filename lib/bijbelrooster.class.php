<?php
require_once 'lid/instellingen.class.php';
require_once 'memcached.class.php';

class Bijbelrooster{
	
	
	function ubbContent($aantal){
		$aantal= max($aantal,2);
		$begin = Date('y:m:d', strtotime("-".min(abs($aantal/2), 2)." days"));
		$return = '<div class="mededeling-grotebalk"><div class="titel"><a href="/actueel/bijbelrooster/">Bijbelleesrooster</a></div><p class="half">';
		$db=MySql::instance();
		$query='SELECT * FROM bijbelrooster WHERE dag >= "'.$begin.'" ORDER BY dag ASC LIMIT 0,'.$aantal;
		$res=$db->select($query);
		$itemsEachRow = ceil(mysql_num_rows($res)/2);
		$i = 0;
		while($row = mysql_fetch_array($res, MYSQL_ASSOC)){
			if($i++ % $itemsEachRow == 0 && $i != 1)
				$return .= '</p><p class="half">';
			$class = '';
			if($row['dag']<date('Y-m-d')){
				$class = 'lichtgrijs';
			}
			$return.= '<span class="' .$class. '">' . date('d-m-Y', strtotime($row['dag'])) . ':</span> ' .$this->getLink($row['stukje']). "<br />";
		}
		return $return. '</p><div class="clear"></div></div>';
	}
	
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
		$itemsEachRow = ceil(mysql_num_rows($res)/3);
		$return='
			<h1>Bijbelrooster</h1>
			<p>Hier vindt u het bijbelrooster der C.S.R.. Uw favoriete bijbelvertaling kunt u instellen bij uw instellingen.</p><p class="oneThirth">';
		$i = 0;
		while($row = mysql_fetch_array($res, MYSQL_ASSOC)){
			if($i++ % $itemsEachRow == 0 && $i != 1)
				$return .= '</p><p class="oneThirth">';
			$class = '';
			if($row['dag']<date('Y-m-d')){
				$class = 'lichtgrijs';
			}
			$return.= '<span class="' .$class. '">' . date('d-m-Y', strtotime($row['dag'])) . ':</span> ' .$this->getLink($row['stukje']). "<br />";
		}
		echo $return.'</p><div class="clear"></div>';
	}
	
}
?>