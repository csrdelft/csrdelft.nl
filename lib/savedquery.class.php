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
	private $resultCount=0;

	public function __construct($id){
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
					if($db->numRows($queryResult)==0){
						$this->result[]=array('Leeg resultaatset' => 'Query leverde geen resultaten terug.');
					}else{
						$this->result=$db->result2array($queryResult);
						$this->resultCount=count($this->result);
					}
				}elseif(LoginLid::instance()->hasPermission('P_ADMIN')){
					$this->result[]=array('Mysql_error' => mysql_error());
				}
			}
		}
	}
	

	public function magBekijken(){
		return $this->magWeergeven($this->permissie);
	}
	
	public function getID(){ return $this->queryID; }
	public function count(){ return $this->resultCount; }
	//Query's mogen worden weergegeven als de permissiestring toegelaten wordt door Lid::hasPermission()'
	//of als gebruiker P_ADMIN heeft.
	public static function magWeergeven($permissie){
		$loginlid=LoginLid::instance();
		return $loginlid->hasPermission($permissie) OR $loginlid->hasPermission('P_ADMIN');
	}
	public function getHtml(){

		if(is_array($this->result)){
			$return=$this->beschrijving.' ('.count($this->result).' regels)<br /><table class="query_table">';
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
				}elseif($kopje=='onderwerp_link'){
					$return.='Onderwerp';
				}elseif($kopje=='med_link'){
					$return.='Mededeling';
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
				foreach($rij as $key => $veld){
					$return.='<td '.$style.'>';
					//als het veld uid als uid_naam geselecteerd wordt, een linkje
					//weergeven
					if($key=='uid_naam'){
						$return.=LidCache::getLid($veld)->getNaamLink('full', 'link');
					}elseif($key=='onderwerp_link'){ //link naar het forum.
						$return.='<a href="/communicatie/forum/onderwerp/'.$veld.'">'.$veld.'</a>';
						//neem een verwijderlinkje op als het om spam gaat, lekker ranzige hardcoded meuk.
						if(isset($rij['zichtbaar'], $rij['id']) AND $rij['zichtbaar']=='spam' AND LoginLid::instance()->hasPermission('P_FORUM_MOD')){
							$return.='<br /><a href="/communicatie/forum/verwijder-bericht/'.$rij['id'].'">verwijder&nbsp;bericht</a>';
						}
					}elseif(substr($key, 0, 10)=='groep_naam' AND $veld!=''){
						require_once('groepen/groep.class.php');
						$return.=Groep::ids2links($veld, '<br />');
					}elseif($key=='med_link'){ //link naar een mededeling.
						$return.='<a href="/actueel/mededelingen/'.$veld.'">'.$veld.'</a>';
					}else{
						$return.=mb_htmlentities($veld);
					}
					$return.='</td>';
				}
				$return.='</tr>';
			}
			$return.='</table>';
		}else{
			//foutmelding in geval van geen resultaat, dus of geen query die bestaat, of niet
			//voldoende rechten.
			$return='Query ('.$this->queryID.') bestaat niet, geeft een fout, of u heeft niet voldoende rechten.';
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
		$return.='<div id="sqSelector" ';
		if($id!=0){ $return.='class="verborgen"'; }
		$return.='>';
		$current='';
		foreach(self::getQuerys() as $query){
			if($current!=$query['categorie']){
				if($current!=''){ $return.='</ul></div>'; }
				$return.='<div class="sqCategorie" style="float: left; width: 450px; margin-right: 20px; margin-bottom: 10px;"><strong>'.$query['categorie'].'</strong><ul>';
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
