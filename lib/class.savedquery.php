<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.savedqery.php
# -------------------------------------------------------------------

class savedQuery{
	
	private $queryID;
	private $beschrijving;
	private $permissie='P_ADMIN';
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

		if($result!==false AND $db->numRows($result)==1){
			$querydata=$db->next($result);
			
			$lid=Lid::get_Lid();
			if($this->magWeergeven($querydata['permissie'])){
				//beschrijving opslaan
				$this->beschrijving=$querydata['beschrijving'];
				$this->permissie=$querydata['permissie'];
				
				//query nog uitvoeren...
				$queryResult=$db->query($querydata['savedquery']);
				if($queryResult!==false){
					$this->result=$db->result2array($queryResult);
				}elseif($lid->hasPermission('P_ADMIN')){
					$this->result=mysql_error();
				}
			}
		}
	}
	
	public function magBekijken(){
		return $this->magWeergeven($this->permissie);
		
	}
	//query's zijn zichtbaar als:
	// - De gebruiker de in de database opgeslagen permissie heeft.
	// - De gebruiker een van de in de database opgeslagen uids heeft.
	// - De gebruiker P_ADMIN heeft
	public static function magWeergeven($permissie){
		$lid=Lid::get_Lid();
		$uids=explode(',', $permissie);
		return $lid->hasPermission($permissie) OR 
				$lid->hasPermission('P_ADMIN') OR 
				in_array($lid->getUid(), $uids);
	}
	public function getHtml(){
		$lid=Lid::get_Lid();
	
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
						
						$return.=$lid->getNaamLink($veld, 'full', true);
					}else{
						$return.=$veld;
					}
					$return.='</td>';
				}
				$return.='</tr>';
			}
			$return.='</table>';
		}elseif(is_string($this->result) AND $lid->hasPermission('P_ADMIN')){
			//als this->result een string is, en we hebben te maken met een P_ADMIN, dan die string weergeven, want daar zit
			//de php_error(); in.
			$return='Query ('.$this->queryID.') gaf een foutmelding:  <br /><pre style="margin: 10px;">'.$this->result.' </pre>';
		}else{
			//foutmelding in geval van geen resultaat, dus of geen query die bestaat, of niet
			//voldoende rechten.
			$return='Query ('.$this->queryID.') bestaat niet, of u heeft niet voldoende rechten.';
		}
		return $return;
	}
	//geef een array terug met de query's die de huidige gebruiker mag bekijken.
	static public function getQuerys(){
		$db=MySql::get_MySql();
		$selectQuery="
			SELECT
				ID, beschrijving, permissie
			FROM
				savedquery
			ORDER BY beschrijving;";
		$result=$db->query($selectQuery);
		$return=array();
		$lid=Lid::get_Lid();
		while($data=$db->next($result)){
			if(savedQuery::magWeergeven($data['permissie'])){
				$return[]=$data;
			}
		}
		return $return;
	}
}
?>
