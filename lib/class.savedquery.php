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
				savedquery, beschrijving, permissie
			FROM
				savedquery
			WHERE 
				ID=".$this->queryID."
			LIMIT 1;";
		$result=$db->query($selectQuery);
		$querydata=$db->result2array($result);
		$querydata=$querydata[0];
		
		$lid=Lid::get_Lid();
		
		if($lid->hasPermission($querydata['permissie'])){
			//beschrijving opslaan
			$this->beschrijving=$querydata['beschrijving'];
			
			//query nog uitvoeren...
			$queryResult=$db->query($querydata['savedquery']);
			$this->result=$db->result2array($queryResult);
		}
		
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
