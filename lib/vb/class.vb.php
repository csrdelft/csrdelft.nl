<?php
require_once("class.vbitem.php");
require_once("class.vbsubject.php");
require_once("class.vbsource.php");
require_once("class.vblinksource.php");
require_once("class.vbbooksource.php");
require_once("class.vbdiscussionsource.php");
require_once("class.vbfilesource.php");
require_once("class.vbsourceopinion.php");
require_once("class.vbsourcesource.php");
require_once("class.vbsubjectsource.php");
//we laden hier forumonderwerp omdat we in onderwerpen werken.
require_once('class.forumonderwerp.php');

class VB {
	var $_db;		//De database connectie
	var $_lid;		//het huidige lid
	
	
	### public ###
	public function VB(){
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
	}

	public static function getParam($str)
	{
		if (isset($_POST[$str]))
			return $_POST[$str];
		else if (isset($_GET[$str]))
			return $_GET[$str];
		return NULL;
	}

	/** Kijk of iemand uberhaupt recht heeft iets te doen
	//TODO: wordt deze ergens gebruikt? */
	function isLid(){
		//TODO: rechten model aanpassen P_VB_READ, ipv forum rechten gebruiken
		//laat iemand dingen doen als hij P_Forum_read heeft
		return $this->_lid->hasPermission('P_FORUM_READ'); 
	}
	
	/** hulpfunctie voor de rechten, is deze persoon een vormingsbank moderator? */
	function isModerator() {
			//Michel, Gertjan, Rini, Gerrit, Sief, Marijn, Bert v. D
			//TODO: regel dit handiger, met rechtenmodelletje
			$mods = array("0438", "0429","0615","0431","0221","0203","0308");
			if (in_array($this->_lid->getUID(),$mods))
				return true;
			if ($this->_lid->hasPermission('P_ADMIN'))
				return true;
			return false;
	}
	
	/** mag dit object bewerkt worden? */
	function magBewerken($obj){
		//rechten model specificeerbaar per object en klasse
		//bewerk zelfgemaakte objecten, als ze een kloppend lid veld bevatten
		if (isset($obj->lid) && ($obj->lid == $this->_lid->getUID()))
			return true;
		if ($this->isModerator())
			return true;
		return false;
	}
	
	/** mag dit type object toevoegd worden */
	function magToevoegen($class)
	{
		//moderators en forumposters mogen dingen toevoegen
		if ($this->isModerator())
			return true;
		if ($this->_lid->hasPermission("P_FORUM_POST"))
			return true;
		return false;
	}
	
	/** laad een onderwerp. Laadt tevens die kind onderwerpen of bronnen */
	function getSubjectById($id)
	{
		$id = (int) $id;
		$query = "SELECT * FROM vb_subject WHERE id = '".$id."' LIMIT 1";
		$sub = VBSubject::FromSQLResult($this->singleSelect($query));
		if ($sub->isLeaf)
		{
			//NOTE, we skip the step of loading the subject source links, and load the sources
			//immediately, using a join
			//sources (alles behalve discussies)
			$query = 
				"SELECT vb_source.* 
				FROM vb_source JOIN vb_subjectsource ON vb_source.id = vb_subjectsource.sourceid
				WHERE vb_subjectsource.subjid = ".$id." AND vb_source.sourceType != 'discussion' ORDER BY vb_source.name ASC";
			$sources = VBSource::FromSQLResults($this->multipleSelect($query));
			$sub->sources = $sources;
			//discussion
			$query = 
				"SELECT vb_source.* 
				FROM vb_source JOIN vb_subjectsource ON vb_source.id = vb_subjectsource.sourceid
				WHERE vb_subjectsource.subjid = ".$id." AND vb_source.sourceType = 'discussion' ORDER BY vb_source.createdate ASC";
			$discussions = VBSource::FromSQLResults($this->multipleSelect($query));
			$sub->discussions = $discussions;
		}
		else 
		{
			$query = "SELECT * FROM vb_subject WHERE parent ='".$id."' ORDER BY name ASC";
			$subs =  VBSubject::fromSQLResults($this->multipleSelect($query));
			$sub->children = $subs;
		}
		//select parent
			//TODO:
	//$sub->parentobj = $this->getSubjectById($this->parent);
		return $sub;
	}
		
	/** laad een bron, met alle context objecten (onderwerpen, opinies...) */
	function getSourceById($id)
	{
		$id = (int) $id;
		//load the source object
		$source = $this->getUncachedSourceById($id);
		//the subjects, join direct the subject table, VBSubjectSource can handle this
		$query = "SELECT vb_subjectsource.*, vb_subject.name AS subjname FROM vb_subjectsource JOIN vb_subject ON vb_subjectsource.subjid = vb_subject.id WHERE sourceid = '".$id."'";
		$subjects = VBSubjectSource::fromSQLResults($this->multipleSelect($query));
		//the related sources, 
		$query = "SELECT * FROM vb_sourcesource WHERE (source1 = '".$id."') OR (source2 = '".$id."') ORDER BY date ASC";
		$links = VBSourceSource::fromSQLResults($this->multipleSelect($query));
		foreach($links as $link)
			$link->cacheTo($id,$this); //load the other side of the relation
		//the opinions
		$query = "SELECT * FROM vb_sourceopinion WHERE sid = '".$id."' ORDER BY createdate ASC";
		$opinions = VBSourceOpinion::fromSQLResults($this->multipleSelect($query));
		//
		$source->setRelations($subjects, $links,$opinions);
		return $source;
	}
	
	/** laad een object, op bassis van class en id */
	function getObjectById($class, $id)
	{var_dump($id);
		switch($class)
		{
			case "vbsubject":
				return $this->getSubjectById($id);
			case "vblinksource":
			case "vbfilesource":
			case "vbbooksource":
			case "vbdiscussionsource":
				return $this->getUncachedSourceById($id);
			case "vbsubjectsource":
				return $this->getUncachedSubjectSourceById(VB::getParam('subjid'), VB::getParam('sourceid'));
			case "vbsourcesource":
				return $this->getUncachedSourceSourceById(VB::getParam('source1'), VB::getParam('source2'));
			default:
				die("could not fetch: ".$class);				
		}
	}
	
	/** laad een bron, zonder daarbij de context te laden (die is meestal toch niet nodig) */
	function getUncachedSourceById($id)
	{
		$id = (int) $id;
		//the source
		$query = "SELECT * FROM vb_source WHERE id = '".$id."' LIMIT 1";
		$source = VBSource::FromSQLResult($this->singleSelect($query));
		return $source;		
	}
	
	function getUncachedSubjectSourceById($subjid, $sourceid)
	{
		$subjid = (int) $subjid;
		$sourceid = (int) $sourceid;
		//the source
		$query = "SELECT * FROM vb_subjectsource WHERE  subjid = '".$subjid."' AND sourceid = '".$sourceid."' LIMIT 1";
		$source = VBSubjectSource::FromSQLResult($this->singleSelect($query));
		return $source;		
	}
	
	function getUncachedSourceSourceById($source1, $source2)
	{
		$source1 = (int) $source1;
		$source2= (int) $source2;
		//the source
		$query = "SELECT * FROM vb_sourcesource WHERE  (source1 = '".$source1."' AND source2 = '".$source2."') OR (source2 = '".$source1."' AND source1 = '".$source2."') LIMIT 1";
		$source = VBSourceSource::FromSQLResult($this->singleSelect($query));
		return $source;		
	}
	
	/** laad de laatste bronnen die toegevoegd zijn, 
	//obselete?:  binnen het onderwerp inID of zijn kinderen */
	function getLastPosts($inId)
	{
		//TODO: maak dit contextgevoelig, alleen bronnen binnen onderwerp inID
		$query = "SELECT * FROM vb_source WHERE sourceType != 'discussion' ORDER BY createdate DESC LIMIT 10";
		$sources = VBSource::fromSQLResults($this->_db->result2array($this->_db->select($query)));
		return $sources;
	}

	/** cache beide bronnen in een bron-bron relatie */
	function preloadSourceSourceObjects($linksource)
	{
		$query = "SELECT * FROM vb_sourcesource WHERE id = '".(int)$linksource->source1."'";
		$obj1 = VBSource::fromSQLResult($this->singleSelect($query)) ;
		$query = "SELECT * FROM vb_sourcesource WHERE id = '".(int)$linksource->source2."'";
		$obj2 = VBSource::fromSQLResult($this->singleSelect($query)) ;
		$linksource->setSourceObjects($obj1, $obj2);		
	}
	
	/** selecteer één object a.d.h. van een select query */
	public function singleSelect($query)
	{
		$res = $this->_db->select($query);
		if (!$res)
			return false;
		return $this->_db->next($res);
	}
	
	/** selecteer meerdere objecten a.d.h.v. een select query */
	public function multipleSelect($query)
	{
		$res = $this->_db->select($query);
		if (!$res)
			return false;
		return $this->_db->result2array($res);
	}

	/** zet een onderwerp om van knoop (heeft subonderwerpen) naar blad (bevat alleen bronnen) en vice versa */
	function convertSubject($id, $vb)
	{
		$id = (int) $id;
		$target = $_GET['target']; //use param to dont keep converting subjects accidentally when for example refreshing
		$r = $this->getSubjectById($id);
		if (!$this->magBewerken($r))
			return $vb->notify("U heeft geen rechten voor conversie");
		if ($r->isLeaf && $target == 'knoop')
		{
			//nieuwe bron aanmaken
			$new = new VBSubject();
			$new->name = "diversen";
			$new->description = "nog niet uitgesorteerde bronnen";
			$new->parent = $r->id;
			$new->isLeaf = 1;
			$res = $this->_db->query($new->getInsertQuery());
			if (!$res)
			{
				$vb->notifiy("Aanmaken van tijdelijk onderwerp gefaald");
				return;
			}
			else
				$vb->notify("Nieuw subonderwerp aangemaakt");
			$newid = $this->_db->insert_id();
			//update bronnen
			$query = "UPDATE vb_subjectsource SET subjid = '".$newid."' WHERE subjid = '".(int)$r->id."'";
			$res = $this->_db->query($query);
			if (!$res)
			{
				$vb->notifiy("Verplaaten van bronnen gefaald");
				return;
			}
			else
				$vb->notify("Bronnen verplaatst");
			//update onderwerp self
			$query = "UPDATE vb_subject SET isLeaf = '0' WHERE id = '".(int)$r->id."'";
			$res = $this->_db->query($query);
			if (!$res)
			{
				$vb->notify("Type wijzigen van onderwerp gefaald");
				return;
			}
			else
				$vb->notify("Onderwerp geconverteerd naar 'knoop'-onderwerp");
		}
		else if (!$r->isLeaf && $target == 'blad')//naar isLeaf = 1
		{
			//controleren of alle bronnen leaf zijn
			if (sizeof($r->children) > 0)
			{
				foreach($r->children as $child)
					if (!$child->isLeaf)
					{
						$vb->notify("Niet alle kinderen van dit onderwerp zijn 'blad'-onderwerpen, wijzig dit eerst");
						return;
					}
				//alle bronnen van subonderwerpen hierin stoppen, subonderwerpen verwijderen
				foreach($r->children as $child)
				{
					$query = "UPDATE vb_subjectsource SET subjid = '".(int)$r->id."' WHERE subjid = '".(int)$child->id."'";
					$res = $this->_db->query($query);
					if (!$res)
					{
						$vb->notifiy("Verplaaten van bronnen uit ".$child->name." gefaald");
						return;
					}
					else
						$vb->notify("Bronnen uit ".$child->name." verplaatst");
					$query = "DELETE FROM vb_subject WHERE id = '".$child->id."'";
					$res = $this->_db->query($query);
					if (!$res)
					{
						$vb->notifiy("Verwijderen van onderwerp ".$child->name." gefaald");
						return;
					}
					else
						$vb->notify("Bron verwijderd: ".$child->name);
				}
			}
			//update onderwerp self
			$query = "UPDATE vb_subject SET isLeaf = '1' WHERE id = '".(int)$r->id."'";
			$res = $this->_db->query($query);
			if (!$res)
			{
				$vb->notifiy("Type wijzigen van onderwerp gefaald");
				return;
			}
			else
				$vb->notify("Onderwerp geconverteerd naar 'blad'-onderwerp");
		}
		else
			$vb->notify("Ongeldige combinatie van huidig- en doeltype. Mogelijk is het onderwerp reeds geconverteerd.");
	}
	
	/** maakt een link van een onderwerp naar een bron aan, met een bepaalde reden
	pre: rechten gecheckt */
	function createSourceSubjectLink($subjid, $sourceid, $reason)
	{
		$ss = new vbsubjectsource();
		$ss->subjid = $subjid;
		$ss->sourceid = $sourceid;
		$ss->reason = $reason;
		$ss->lid = $this->_lid->getUID();
		$ss->createdate = getDateTime();
		$query = $ss->getInsertQuery();
		return $this->_db->query($query);
	}
	
	function createSourceSourceLink($source1, $source2, $reason)
	{
		$ss = new vbsourcesource();
		$ss->source1 = $source1;
		$ss->source2 = $source2;
		$ss->reason = $reason;
		$ss->lid = $this->_lid->getUID();
		$ss->date = getDateTime();
		$ss->status = 'approved';
		$query = $ss->getInsertQuery();
		var_dump($query);
		return $this->_db->query($query);
	}
	
	/** removes a subject object, provide contentmanager for notify callbacks
	pre: rechten gecheckt */
	function removeSubject($r,$cm)
	{
		if (sizeof($r->children) > 0)
		{
			$cm->notify("Verwijder eerst de kindobjecten!");
			return false;
		}
		//verwijder eerst alle links
		$query = "DELETE FROM vb_subjectsource WHERE subjid = '".(int)$r->id."'";
		$res = $this->_db->query($query);
		if (!$res)
		{
			$cm->notify("Kon gelinkte bronnen niet verwijderen: "+query);
			return false;
		}
		return $this->_db->query($r->getDeleteQuery());
	}
	
	/** removes a source object, provide contentmanager for notify callbacks
	pre: rechten gecheckt 
	TODO: sources should only be removed when the nummer of relatoins drop to zero, or this is explicit decided. 
	in a subject, only the link should be removed
	*/
	function removeSource($r,$cm)
	{
		//verwijder eerst alle relaties
		$query = "DELETE FROM vb_subjectsource WHERE sourceid = '".(int)$r->id."'";
		$res = $this->_db->query($query);
		$cm->notify("Verwijderen uit thema's... ".($res?"voltooid":"mislukt"));
		
		$query = "DELETE FROM vb_sourceopinion WHERE sid = '".(int)$r->id."'";
		$res = $this->_db->query($query);
		$cm->notify("Verwijderen beoordelingen... ".($res?"voltooid":"mislukt"));
		
		$query = "DELETE FROM vb_sourcesource WHERE (source1 = '".(int)$r->id."') OR (source2 = '".(int)$r->id."')";
		$res = $this->_db->query($query);
		$cm->notify("Verwijderen links naar andere bronnen... ".($res?"voltooid":"mislukt"));
		
		if (!$this->postRemoveSource($r,$cm))
			$cm->notify("fout tijdens verwijderen van resources van de bron");
		else
			return $this->_db->query($r->getDeleteQuery());
	}
	
	/**
	 * After creating a source, a lot of postprocesing has to be done,
	 * creating subject links, uploading files, creating forum topics etc...
		* pre: rechten gecheckt *
	 * @param unknown_type $r
	 * @param unknown_type $cm
	 */
	function postInsertSource($r,$cm)
	{
		//onderwerp relatie leggen
		$cm->notify("Nieuwe bron registreren onder huidig onderwerp");
		if (!$this->createSourceSubjectLink($_POST['autoLinkToSubject'],(int)$r->id, "Originele locatie"))
			$cm->notify(" ..MISLUKT!");	
		//bronnen verwerken
		switch($r->sourceType)
		{
			case 'discussion':
				$this->creatediscussion($r,$cm);
				return;
			case 'file':
				$this->uploadfile($r,$cm);
				return;
			case 'book':
				$this->linkbook($r,$cm);
				return;	
		}		
	}
	
	/**
	 * Give the resoures used by the source free
	 *
	 * @param unknown_type $r
	 * @param unknown_type $cm
	 */
	function postRemoveSource($r,$cm)
	{
		if ($r->sourceType == "file") {
			$catid = $this->getVBFileCatID();
			if (is_int((int)$r->id) && is_int((int)$catid)){
				$docnaam = "vb_".$r->id."_1";
				$res = $this->singleSelect("SELECT id FROM document WHERE name ='".$docnaam." AND categorie = '".$catid."'");
				if ($res && isset($res['id'])) {
					require_once("class.document.php");
					$docs = new Documenten($this->_lid,$this->_db);
					if($docs->deleteDocument($res['id']))	{
						$cm->notify("Bron bestanden zijn verwijderd");
						return true;
					}
					else
						$cm->notify("fout in Documenten::deleteDocument, kon document ".$res['id']." niet verwijderen");
				}
				else {
					$cm->notify("kon de bron ".$docnaam." niet vinden in de database, er is geen bestand verwijderd");
					return true; //this is allowed
				}
			}
			else
				$cm->notify("kan bron niet verwijderen, bron id '".$r->id."' is ongeldig of categorie '".$catid."' niet gevonden");
			return false;
		}
		else //geen bestand
			return true;
	}
	
	/** waar moeten vormingsbank topics gecreëerd worden? */
	function getvbforumcategorie()
	{
		$res = $this->singleSelect("SELECT id FROM forum_cat WHERE titel='vormingsbank'");
		$id = (int)$res['id'];
		if (!$res || $id <= 0 || $id == null)
			die("Kon VormingsBank forum niet vinden");
		return $id;
	}
	
	/**
	 * this object creates an discuion for an existing subject. 
	 * Note that the name will bes tored in both forum and source, for easier showing subjects
	 * @param unknown_type $r
	 * @param unknown_type $cm
	 * @return unknown
	 */
	function creatediscussion($r,$cm)
	{
		$cm->notify("Bezig met creeren van nieuwe forum draad ..");
		$forum = new ForumOnderwerp();
		$res = false;
		//als er geen bericht is gaan we sowieso niets doen.
		if((!isset($r->description)) || ($r->description == ""))
		{
			$cm->notify("Uw bericht is leeg");
			return false;
		}
		//een nieuw topic toevoegen?
		//if(!isset($_GET['topic']) AND isset($_GET['forum'])){
		$forum->setCat((int)$this->getvbforumcategorie());
		if(strlen(trim($r->name))<1)
			$cm->notify('De titel mag niet leeg zijn.');
		//addTopic laadt zelf de boel in die hij net heeft toegevoegd...
		else if($forum->addTopic($r->name)===false)
			$cm->notify('Helaas, er gaat iets goed mis bij het toevoegen van het onderwerp.....');
		# er is een onderwerp geselecteerd, nu nog even het bericht er aan toevoegen...
		else if(!($forum->magToevoegen()))
			$cm->notify("U ontberen de benodigde rechten...");
		else if ($forum->addPost($r->description) === false)
			$cm->notify('Het ging weer eens hartstikke mis met toeveogen..');
		else
			$res = true;
		if(!$res)
			return false;
		
		/** het ging allemaal goed, wijzig the source en set het topic type goed (bij addtopic doen is mooier) */
		$r->link = $forum->getID();
		$r->description=""; //staat anders 2 keer in DB
		$res = false;
		if(!$this->_db->query("UPDATE forum_topic SET soort = 'T_VBANK' WHERE id='".(int)$forum->getID()."'"))
			$cm->notify("Kon type van topic niet wijzigen");
		else if(!$this->_db->query($r->getUpdateQuery()))
			$cm->notify("Kon het gewijzigde Discussieobject niet opslaan");
		else
		{
			$cm->notify("geslaagd");
			$res = true;	
		}
		return $res;
	}

	
	/**
	 * this performs a file upload
	* TODO: wat gebeurt er als een bron gewijzigd wordt?
	 */
	function uploadfile($r,$cm)
	{
		$cm->notify("Uploading file");
		$catid = $this->getVBFileCatID();
		$title = "vb_".$r->id."_1";
		$cleanup = true;
		if ($catid == -1)
			die("Kon de vormingsbank uploadcategorie niet vinden: ".$catid);
		$result;
		//those two variables are expected by the uploadscript, so lets create them
		$_POST['cat1'] = $catid;
		$_POST['title1'] = $title;
		$errorcodes;
		//copied from class.toevoegencontent.php / class.document.php
		$postIsArray = isset($_POST) && is_array($_POST);
		if( !(($postIsArray
				&& empty($_POST))
				&& (isset($_FILES) 
				&& is_array($_FILES) 
				&& empty($_FILES)) ) ) { // TODO: overbodige checks weglaten
		// als de arrays $_POST en $_FILES *niet* leeg zijn
			require_once ('class.toevoegen.php');
			$toevoegen = new Toevoegen($this->_db, $this->_lid);
			$toevoegen->uploadFiles(false);
			$errorcodes=$toevoegen->getErrorcodes();
		} 
		else { // $_POST of $_FILES wel leeg
		    $cm->notify("geen bestanden in request gevonden/ ongeldige request");
		}
		if(isset($errorcodes) && is_array($errorcodes) && !empty($errorcodes)) {
			// er zijn bestanden geupload (want er zijn errorcodes)
			$numberOfErrors=$toevoegen->getNumberOfErrors($errorcodes);
			if($numberOfErrors==-1)
				$cm->notify('Er zijn geen bestanden opgegeven. Probeer het opnieuw.');
			else if($numberOfErrors>0) {
				//statische functies zouden zooo handig zijn...., num aar even voor de foutmelding een toevoegen content aanma
				require_once('class.toevoegencontent.php');
				$tmp = new toevoegencontent($toevoegen);
				$tmp->errorcodes = $errorcodes;
				$cm->notify($tmp->getErrorLine(1, $title));
				//TODO: als bestand al bestand, bijv door andere bron, dan niet verwijderen
			}
			else {
				$cm->notify("uploaden voltooid");
				//hmm... omslachtig? vind het ingevoegde bestand, en link ernaartoe
				$rName = $this->_db->select("
					SELECT documentbestand.id 
					FROM documentbestand JOIN document ON documentbestand.documentID = document.id 
					WHERE document.naam = '".$title."' AND document.categorie = '".$catid."' LIMIT 1");
				if( mysql_num_rows($rName) == 1 ){
					$arr = mysql_fetch_array($rName);
					$r->link = (int)$arr['id'];
					if (!$this->_db->query($r->getUpdateQuery()))
						$cm->notify("kon de link naar het document niet opslaan!");
					else //alles gelukt
						$cleanup = false;
				}
				else
					$cm->notify("kon geuploade bestand niet terugvinden in de database");
			}
		}	
		if ($cleanup) {
		//als iets gefaald heeft verwijder bron besetanden etc...
			$cm->notify("er ging ergens iets mis, alles wordt nu netjes opgeruimd, probeer het daarna eens overnieuw op te doen");
			$this->removeSource($r, $cm);
		}
	}
	
	/**
	 * this function finds the category where the vormingsbank files have to be stored
	 */
	private function getVBFileCatID() {
		$rName = $this->_db->select("
			SELECT	ID
			FROM	documentencategorie
			WHERE	naam = 'vormingsbank';"
		);
		if( mysql_num_rows($rName) == 0 ) {
			return -1;
		} else {
			$arr = mysql_fetch_array($rName);
			return $arr['ID'];
		}
	}
}
?>