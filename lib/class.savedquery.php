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
		$db=MySql::instance();
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

			if($this->magWeergeven($querydata['permissie'])){
				//beschrijving opslaan
				$this->beschrijving=$querydata['beschrijving'];
				$this->permissie=$querydata['permissie'];

				//query nog uitvoeren...
				$queryResult=$db->query($querydata['savedquery']);
				if($queryResult!==false){
					$this->result=$db->result2array($queryResult);
				}elseif(Lid::instance()->hasPermission('P_ADMIN')){
					$this->result=mysql_error();
				}
			}
		}
	}

	public function magBekijken(){
		return $this->magWeergeven($this->permissie);

	}
	//Query's mogen worden weergegeven als de permissiestring toegelaten wordt door Lid::hasPermission()'
	//of als gebruiker P_ADMIN heeft.
	public static function magWeergeven($permissie){
		$lid=Lid::instance();
		return $lid->hasPermission($permissie) OR $lid->hasPermission('P_ADMIN');
	}
	public function getHtml(){
		$lid=Lid::instance();

		if(is_array($this->result)){
			$return=$this->beschrijving.'<br /><table class="query_table">';
			$keysPrinted=false;
			$return.='<tr>';
			foreach(array_keys($this->result[0]) as $kopje){
				$return.='<th>';
				if($kopje=='uid_naam'){
					$return.='Naam';
				}elseif($kopje=='groep_naam'){
					$return.='Groep';
				}elseif(substr($kopje, 0, 10)=='groep_naam'){
					$return.=substr($kopje, 11);
				}elseif($kopje='onderwerp_link'){
					$return.='Onderwerp';
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
					}elseif($key=='onderwerp_link'){ //link naar het forum.
						$return.='<a href="/communicatie/forum/onderwerp/'.$veld.'">'.$veld.'</a>';
					}elseif(substr($key, 0, 10)=='groep_naam' AND $veld!=''){
						require_once('groepen/class.groep.php');
						//$veld mag een enkel id zijn of een serie door komma's gescheiden id's
						$groepen=explode(',', $veld);
						$groeplinks=array();
						foreach($groepen as $groepid){
							$groepid=(int)$groepid;
							if($groepid!=0){
								$groep=new Groep($groepid);
								$groeplinks[].=$groep->getLink();
							}
						}
						$return.=implode('<br />', $groeplinks);
					}else{
						$return.=mb_htmlentities($veld);
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
		$db=MySql::instance();
		$selectQuery="
			SELECT
				ID, beschrijving, permissie, categorie
			FROM
				savedquery
			ORDER BY categorie, beschrijving;";
		$result=$db->query($selectQuery);
		$return=array();
		while($data=$db->next($result)){
			if(savedQuery::magWeergeven($data['permissie'])){
				$return[]=$data;
			}
		}
		return $return;
	}
	static public function getQueryselector($id=0){

		$return='<a class="knop" href="#" onclick="toggleDiv(\'sqSelector\')">Laat queryselector zien.</a>';
		$return.='<div id="sqSelector" class="verborgen">';
		$current='';
		foreach(self::getQuerys() as $query){
			if($current!=$query['categorie']){
				if($current!=''){ $return.='</ul></div>'; }
				$return.='<div class="sqCategorie" style="float: left; width: 450px; margin-right: 20px;"><strong>'.$query['categorie'].'</strong><ul>';
				$current=$query['categorie'];
			}
			$return.='<li><a href="query.php?id='.$query['ID'].'">';
			if($id==$query['ID']){ $return.='<em>'; }
			$return.=mb_htmlentities($query['beschrijving']);
			if($id==$query['ID']){ $return.='</em>'; }
			$return.='</a></li>';
		}
		$return.='</ul></div></div><div class="clear"></div>';
		return $return;
	}
}
?>
