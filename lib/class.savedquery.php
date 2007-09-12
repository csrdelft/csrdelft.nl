<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.savedqery.php
# -------------------------------------------------------------------

class savedQuery{
	
	private $queryID;
	private $beschrijving;
	private $result=null;
	
	public function savedQuery($id){
		$this->queryID=(int)$id;
		$this->load();
	}
	private function load(){
		$db=MySql::get_MySql();
		//query ophalen
		$selectQuery="
			SELECT
				savedquery, beschrijving
			FROM
				savedquery
			WHERE 
				ID=".$this->queryID."
			LIMIT 1;";
		$result=$db->query($selectQuery);
		$querydata=$db->result2array($result);
		
		//beschrijving opslaan
		$this->beschrijving=$querydata[0]['beschrijving'];
		
		//query nog uitvoeren...
		$queryResult=$db->query($querydata[0]['savedquery']);
		$this->result=$db->result2array($queryResult);
		
	}
	public function getHtml(){
		if(is_array($this->result)){
			$return=$this->beschrijving.'<br />' .
				'<table class="query_table">';
			$keysPrinted=false;
			$return.='<tr>';
			foreach(array_keys($this->result[0]) as $kopje){
				$return.='<th>'.$kopje.'</th>';
			}
			$return.='</tr>';
			foreach($this->result as $rij){
				$return.='<tr>';
				foreach($rij as $veld){
					$return.='<td>'.$veld.'</td>';
				}
				$return.='</tr>';
			}
			$return.='</table>';
		}else{
			$return='Query bestaat niet.';
		}
		return $return;
	}
}
?>
