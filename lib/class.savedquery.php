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
			$return=$this->beschrijving.'<br /><table class="query_table">';
			$keysPrinted=false;
			$return.='<tr>';
			foreach(array_keys($this->result[0]) as $kopje){
				$return.='<th>';
				if($kopje=='uid_naam'){
					$return.='Naam';
				}else{
					$return.=$kopje;
				}
				$return.='</th>';
			}
			$return.='</tr>';
			$rowColor=false;
			foreach($this->result as $rij){
				//kleurtjes omwisselen
				if($rowColor){
					$style='style="background-color: #ccc;"';
				}else{
					$style='';
				}
				$rowColor=(!$rowColor);
				
				//uit te poepen html maken
				$return.='<tr>';
				foreach($rij as $key=>$veld){
					$return.='<td '.$style.'>';
					//als het veld uid als uid_naam geselecteerd wordt, een linkje 
					//weergeven
					if($key=='uid_naam'){
						$lid=Lid::get_Lid();
						$return.=$lid->getNaamLink($veld, 'full', true);
					}else{
						$return.=$veld;
					}
					$return.='</td>';
				}
				$return.='</tr>';
			}
			$return.='</table>';
		}else{
			//foutmelding in geval van geen resultaat, dus of geen query die bestaat, of niet
			//voldoende rechten.
			$return='Query ('.$this->queryID.') bestaat niet, of u heeft niet voldoende rechten.';
		}
		return $return;
	}
}
?>
