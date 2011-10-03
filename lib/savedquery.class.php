<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# (Jieter) dit is een slecht voorbeeld van de toepassing van het MVC-paradigma. Dit is een model+view in elkaar...
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
	
	public static function render_header($name){
		switch($name){
			case 'uid_naam': return 'Naam'; break;
			case 'groep_naam': return 'Groep'; break;
			case 'onderwerp_link': return 'Onderwerp'; break;
			case 'med_link': return 'Mededeling'; break;
			default:
				if(substr($name, 0, 10)=='groep_naam'){
					return substr($name, 11);
				}
		}
		return $name;
	}
	public static function render_field($name, $contents){
		if($name=='uid_naam'){
			return LidCache::getLid($contents)->getNaamLink('full', 'link');
		}elseif($name=='onderwerp_link'){ //link naar het forum.
			$return='<a href="/communicatie/forum/onderwerp/'.$contents.'">'.$contents.'</a>';
		}elseif(substr($name, 0, 10)=='groep_naam' AND $contents!=''){
			require_once('groepen/groep.class.php');
			return Groep::ids2links($contents, '<br />');
		}elseif($name=='med_link'){ //link naar een mededeling.
			return '<a href="/actueel/mededelingen/'.$contents.'">'.$contents.'</a>';
		}
		
		return mb_htmlentities($contents);
	}
	public function getHtml(){

		if(is_array($this->result)){
			$return=$this->beschrijving.' ('.count($this->result).' regels)<br /><table class="query_table">';
			
			//header
			$return.='<tr>';
			foreach(array_keys($this->result[0]) as $kopje){
				$return.='<th>'.self::render_header($kopje).'</th>';
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

				$return.='<tr>';
				foreach($rij as $key => $veld){
					$return.='<td '.$style.'>'.self::render_field($key, $veld).'</td>';
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
