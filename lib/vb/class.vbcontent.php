<?php
# C.S.R. Delft | vormingsbank@csrdelft.nl
# -------------------------------------------------------------------
# class.vbcontent.php
# -------------------------------------------------------------------
# author: Michel Weststrate
# in het MCV model is dit de viewer en de VB classe de controler, hoewel
# die scheidslijn niet heel hard is getrokken. Het afhandelen van http
# request en de programma flow wordt hier afgehandeld, de VB dient daar bij 
# als een zet van hulpfuncties waar de complexere logica wordt afgehandeld. 
	require_once("class.vbsearch.php");
	
class VBContent extends SimpleHTML {
	### private ###
	var $_vb;		//De VormingsBank databeheer klasse
	var $_action; 	//de huidige actie
	var $_objid = 0;	//Het objectid wat we momenteel aan het bekijken zijn
	var $_search;
	
	### public ###
	public function VBContent(&$vb, $actie,$objid){
		//deze drie zijn meestal aanwezig, laten we ze opslaan
		$this->_vb=$vb;
		$this->_action = $actie;
		$this->_objid = $objid;
		$this->_search = new vbsearch($vb);
	}

	function getTitel(){
		return "Vormingsbank C.S.R. Delft";
	}
	
	/**
	 * Ingang in de controller, door view aan te roepen wordt de huidige actie uitgevoerd.
	 * Deze wordt ook regelmatig intern aangeroepen om de flow te veranderen, bijv na het opslaan
	 * van een object.
	 */
	function view(){
		switch($this->_action)
		{
			case "home":
				$this->showHomePage(); 
				break;
			case "theme":
				$this->showThemePage();
				break;
			case "subject":
				$this->showSubject();
				break;
			case "search":
				$this->showSearch();
				break;
			case "lastposts":
				$this->showLeftColumn();
				break;
			case "source":
				$this->showSourcePage();
				break;
			case "commit":
				$this->commitOrAddObject();
				break;
			case "remove":
				$this->removeObject();
				break;
			case "convertsubject":
				$this->convertSubject();
				break;
			case "addsubjectsourcelink":
				$this->addsubjectSourceLink();
				break;
			case "addsourcesourcelink":
				$this->addSourceSourceLink();
				break;
			case "sourcebydiscussion":
				$this->showSourceByDiscussionId();
				break;
			case "pwd": //TODO: remove
				die($this->_makepasswd($_GET['pwd']));
			default:
				die("onbekende actie! : ".$this->_action);
		}			
	}
	
	/** mw added temporarily, to create local passwords */
	function _makepasswd($pass) {
		$salt = mhash_keygen_s2k(MHASH_SHA1, $pass, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass.$salt).$salt);
	}
	
	/** shows the homepage, containg information about the vormingsbank, and a list of main categories */
	function showHomePage()
	{
		$home = $this-> newTemplate();
		//search box
		$home->assign('search', $this->showSimpleSearch());
		$sub = $this->_vb->getSubjectById(0); //show default themes
		//hoofd onderwerpen
		$home->assign('themes',$sub->children);
		$home->display('vb/home.tpl'); 
	}
	
	/** shows the last posts, and last added sources */
	function showLeftColumn()
	{
		echo '<h1><a href="/vb/">Laatste bronnen</a></h1>';
		foreach($this->_vb->getLastPosts($this->_objid) as $source){
			$titel=mb_htmlentities($source->name);
			if(strlen($titel)>21){
				$titel=str_replace(' ', '&nbsp;', trim(substr($titel, 0, 18)).'…');
			}
			$bericht=preg_replace('/(\[(|\/)\w+\])/', '|', $source->description);
			$berichtfragment=substr(str_replace(array("\n", "\r", ' '), ' ', $bericht), 0, 40);
			echo '<div class="item"><span class="tijd">'.date('d-m', $source->createdate).'</span>&nbsp;';
			echo '<a href="index.php?actie=source&id='.$source->id.'" 
				title="['.mb_htmlentities($source->name).'] '.
					mb_htmlentities($berichtfragment).'">'.$titel.'</a><br />'."\n";
			echo '</div>';
			
		}
		//snel zoek ding
		?><br/><br/>
			<table id="vbzoektable">
				<tr><td>
					<form action="vb/index.php" method="get">
						<input type="hidden" name="actie" value="staticquicksearch"/>
						<input id="vbzoekveldlinks" type="text" value="zoeken..." onfocus="this.value=\'\'" onclick="form.submit();" name="searchvalue"/>
					</form>
				</td></tr>
			</table>
		<?php
	}
	
	/** eenvoudige search box */
	function showSimpleSearch()
	{
		return $this->_search->createSearchForm('quicksearch',
			'<input type="text" value="Zoeken in vormingsbank..." 
				onfocus="this.value=\'\'" name="searchvalue" 
				onkeyup="
					if (this.value.length > 2) 
					{
						this.form.button.click();
						document.getElementById(\'hoofdthemas\').style.display = \'none\';
						document.getElementById(\'searchdiv\').style.display = \'block\';
					}
					else
					{
						document.getElementById(\'hoofdthemas\').style.display = \'block\';
						document.getElementById(\'searchdiv\').style.display = \'none\';
					}
				"			
			/>');
	}
	
	/** zoek actie uitvoeren en resultaten weergeven */
	function showSearch()
	{
		$tpl = $this->newTemplate();
		$tpl->assign(searchform, $this->_search->createSearchForm("complexsearch",
		'
				<input type="text" id="zoekveld2" name="searchvalue" value="zoekterm" onfocus="this.value=\'\'; this.style.textAlign=\'left\';"
					onkeyup="if (this.value.length > 2) this.form.button.click();"/>
				<h2>Criteria</h2><br>
				<table><tr><td>
				<input type="checkbox" name="subjects" value="1"   class="checkbox" id="veld0"  checked  onclick="this.value = (this.checked?\'1\':\'0\');"/><label for="veld0">onderwerp</label><br/>
				<input type="checkbox" name="links" value="1"  class="checkbox" id="veld1"  checked onclick="this.value = (this.checked?\'1\':\'0\');"/><label for="veld1">internet link</label><br/>
				<input type="checkbox" name="files" value="1"  class="checkbox" id="veld2"  checked  onclick="this.value = (this.checked?\'1\':\'0\');"/><label for="veld2">bestand</label><br/>
				</td><td>
				<input type="checkbox" name="books" value="1"  class="checkbox" id="veld5"  checked  onclick="this.value = (this.checked?\'1\':\'0\');"/><label for="veld5">boek</label><br/>
				<input type="checkbox" name="discus1" value="1"  class="checkbox" id="veld3"  checked  onclick="this.value = (this.checked?\'1\':\'0\');"/><label for="veld3">discussie omschrijving</label><br/>
				<input type="checkbox" name="discus2" value="1"  class="checkbox" id="veld4" checked  onclick="this.value = (this.checked?\'1\':\'0\');"/><label for="veld4">discussie inhoud</label><br/>		
				</td></tr></table>'));
		$tpl->display('vb/search.tpl');
	}
	
	/** shows a page containing a theme tree, and an advanced search field box  */
	function showThemePage()
	{
		//TODO:
		$this->showThemesTree();
	}
	
	/** shows a subject, its either a list of themes, or a list of sources and discussions,
	//depending on isLeaf subject */
	function showSubject()
	{
		//onderwerp weergeven
		$sub = $this->_vb->getSubjectById($this->_objid);
		$this->generateLocationBar($sub);
		$tpl = $this->newTemplate();
		$tpl->assign(sub,$sub);
//		if ($sub->isLeaf != "1")
//		{
			//edit subject formulier weergeven
			$tpl->assign(editdiv,VBSubject::getEditDiv());
			//create a temporary object, that we want to edit if 'toevoegen' is pressed
			$tmp = new VBSubject();
			$tmp->parent = $sub->id;
			$tpl->assign(addsubjectclick, $tmp->getJSAddHandler());
//		}
//		else
//		{
			//edit sources
			$tmp = new VBSource();
			$tpl->assign(addsourceclick,$tmp->getJSAddHandler()); //de JSAddHandler is generiek en bevat code voor alle types bronnen, niet elegant, wel makkelijk
			$tpl->assign(editlinkdiv, 		VBLinkSource::getEditDiv()); 
			//TODO: andere editdivds
			$tpl->assign(editfilediv, 		VBFileSource::getEditDiv());
			$tpl->assign(editdiscussiondiv, VBDiscussionSource::getEditDiv());
	//		$tpl->assign(editbookdiv, 		VBBookSource::getEditDiv());
//		}
		//display
		$tpl->display('vb/subject.tpl');
		
	}
	
	/** dat ding dat bovenin moet */
	function generateLocationBar($obj)
	{
		//TODO:
		//echo "Hier > ziet > u > straks > waar > u > bent<br/>";	
	}
	
	/** linkje naar een bepaalde gebruiker */
	function userLink($lidnr)
	{
		//TODO: (zie lid klasse)
	}
	
	/** shows the tree of themes */
	function showThemesTree()
	{
		
	}
	
	/** for redirecting from forum edit stuff: find the proper source based on the current forumtopic id*/
	function showSourceByDiscussionId()
	{
		$query = "SELECT id FROM vb_source WHERE sourceType='discussion' AND link='".$this->_objid."' LIMIT 1";
		$res = $this->_vb->singleSelect($query);
		if($res == false)
			die("cannot find required source for discussion".$this->objid.":".$query);
		$this->_objid = $res['id'];
		$this->_action = "source";
		$this->view();
	}
	
	/** shows a source ("Bron"), either book, link, discussion or uploaded file */
	function showSourcePage()
	{
		$source = $this->_vb->getSourceById($this->_objid); //deze functie cached ook alle gerelateerde objecten :)
		$this->generateLocationBar($source);
		$tpl = $this->newTemplate();
		$tpl->assign(source,$source);
		//TODO: comefrom is een tijdelijke variable om navigatie te vereeenvoudigen, verwijderen straks
		$comefrom = (isset($_GET['comefrom'])?$_GET['comefrom']:'-1');
		$tpl->assign(comefrom,$comefrom);
		//edit gerelateerde onderwerpen
		$tpl->assign(editsubjectsourcediv, VBSubjectSource::getEditDiv());
		$tpl->assign(addlabelclick, $this->_search->createEditFormLink("addlabel"));
		$tpl->assign(addlabeldiv, $this->_search->createSearchBasedEditForm(
			"<img src='images/leaf.png'/>Nieuw label toekennen aan ".$source->name,
			"addlabel",
			"Criterium: <input type='text' width='200' name='searchvalue'/><input type='hidden' name='class' value='vbsubject'/>",
			"subjid",
			VBItem::generateHiddenFields(array("actie"=>"addsubjectsourcelink", "sourceid"=>$this->_objid)).
			"<textarea name='reason'>&lt;reden voor deze relatie&gt;</textarea>"));			
		//edit gerelateerde bronnen
		$tpl->assign(editsourcesourcediv, VBSourceSource::getEditDiv());
		$tpl->assign(addsourceclick, $this->_search->createEditFormLink("addsource"));
		$tpl->assign(addsourcediv, $this->_search->createSearchBasedEditForm(
			"<img src='images/book.png'/>Nieuwe bron-bron relatie toevoegen aan ".$source->name,
			"addsource",
			"Criterium: <input type='text' width='200' name='searchvalue'/><input type='hidden' name='class' value='vbsource'/>",
			"source2",
			VBItem::generateHiddenFields(array("actie"=>"addsourcesourcelink", "source1"=>$this->_objid)).
			"<textarea name='reason'>&lt;reden voor deze relatie&gt;</textarea>"));			
		$tpl->display('vb/source.tpl');
		//render forum discussie
		//TODO: fourm hangt er nog niet echt lekker in..... redirecten is een ramp nu
		if ($source->sourceType=="discussion") {
			//copied from forumonderwerp.php
			require_once('include.config.php');
			require_once('class.forumonderwerp.php');
			require_once('class.forumcontent.php');
			require_once('class.forumonderwerpcontent.php');
			# Het middenstuk
			if($this->_vb->_lid->hasPermission('P_FORUM_READ')) {
				$forum = new ForumOnderwerp();
				//onderwerp laden
				$forum->load((int)$source->link);
				$midden = new ForumOnderwerpContent($forum);
			} else {
			# geen rechten
			#	echo "denied";
				require_once 'class.paginacontent.php';
				$pagina=new Pagina('geentoegang');
				$midden = new PaginaContent($pagina);
			}	
			$midden->view();
		}
	}
	
	/** Deze methode bevat de logica voor het opslaan van een object: toevoegen als id == -1, anders opslaan */
	function commitOrAddObject()
	{
		$class = $_POST['class'];
		$res = false; //result of our action
		$this->notify("Vastleggen van object met type ".$class);
		$r;
		//NIEUW OBJECT INVOEGEN
		if ($this->_objid ==-1 && $this->_vb->magToevoegen($class))
		{
			//aanmaken
			$r = new $class();
			if ($r == null)
			{
				$this->notify("Onbekende classe: ".$class);
				return;
			}
			//parse from request
			$r->updateFromRequest($_POST,array()); //2nd paramter: update all fields, ignore nothing
			$res = $this->_vb->_db->query($r->getInsertQuery());
			$r->id = $this->_vb->_db->insert_id();
			//CLASS SPECIFIC insertion behaviour:
			//auto create subject source link
			if (is_a($r,"VBSource") && $res)
				$this->_vb->postInsertSource($r,$this);
		}						
		//OBJECT WIJZIGEN				
		else 
		{
			//ophalen en update  van Post, (geen velden negeren)  opslaan
			$r = $this->_vb->getObjectById($class, $this->_objid);
			if (!$r) 
				die("could not find object".$class.$this->_objid);
			if (!($this->_vb->magBewerken($r)))
			{
				$this->notify("U mag dit object niet bewerken");
				return;
			}
			$r->updateFromRequest($_POST, array());
			$res = $this->_vb->_db->query($r->getUpdateQuery());
		}
		//RESULTAAT EVALUEREN
		if ($res !=false)
		{
			$this->notify("Object opgeslagen (#".$r->id.")");
			//succesfully created, rederict action, class specific
			$this->_objid = $r->id;
			switch($class)
			{
				case "vbsubject":
					$this->_action="subject";
					break;
				case "vblinksource":
				case 'vbdiscussionsource':
				case 'vbfilesource':
				case 'vbbooksource':				
					$this->_action="source";
					break;
				case 'vbsubjectsource':
					$this->_action="source";
					$this->_objid = $_POST['sourceid'];
					break;
				case 'vbsourcesource':
					$this->_action="source";
					$this->_objid = $_POST['source1'];
					break;
			}
			$this->view();
		}
		else
		{ 
			$this->notify("U heeft geen rechten voor deze actie of er was een fout tijdens het opslaan: ".mysql_errno().": ".mysql_error()."<br/>");
			//var_export($r);						
		}
	}
	
	function addsubjectsourcelink() {
		if (!$this->_vb->_lid->hasPermission('P_LOGGED_IN')) {
			$this->notify("U heeft geen rechten om bron relaties te leggen");
			return;
		}
		$sub = $_POST['subjid'];
		$source = $_POST['sourceid'];
		$reason = $_POST['reason'];
		if ($this->_vb->createSourceSubjectLink($sub, $source, $reason))
		{
			$this->_action ="source";
			$this->_objid = $source;
			$this->view();
		}
		else 
			$this->notify("Fout tijdens opslaan van nieuwe bron-thema relatie");
	}
	
	function addsourcesourcelink() {
		if (!$this->_vb->_lid->hasPermission('P_LOGGED_IN')) {
			$this->notify("U heeft geen rechten om bron relaties te leggen");
			return;
		}
		$source1 = $_POST['source1'];
		$source2 = $_POST['source2'];
		$reason = $_POST['reason'];
		if ($this->_vb->createSourceSourceLink($source1, $source2, $reason))
		{
			$this->_action ="source";
			$this->_objid = $source1;
			$this->view();
		}
		else 
			$this->notify("Fout tijdens opslaan van nieuwe bron-bron relatie");
	}
	
	/** Verwijderd een object */
	function removeObject()
	{
		$res = false;
		$r;
		$class = strtolower($_GET['class']);
		//rechten?
		$this->notify("Verwijderen van ".$class.":".$this->_objid);
		$r = $this->_vb->getObjectById($class,$this->_objid);
		if (!$r)
			$this->notify("Te verwijderen object niet gevonden!");
		if (!$this->_vb->magBewerken($r))
			return $this->notify("U mag dit ding niet bewerken"); //save the accolades.. :)
		//verwijderen, class specifiek
		if($class == 'vbsubject')
		{
			$res = $this->_vb->removeSubject($r, $this);
			$this->_objid = $r->parent; //navigate to its parent
			$this->_action = "subject";	
		}
		elseif(is_a(new $class,'vbsource')) //hooray, is a new $class :)
		{
			$res = $this->_vb->removeSource($r, $this);
			//TODO: fetch current subject id from navigation context
			$this->_objid = 0; 
			$this->_action = "subject";
		}
		elseif($class == 'vbsubjectsource' || $class == 'vbsourcesource')
		{
			$res = $this->_vb->_db->query($r->getDeleteQuery());
			$this->_action = "source";
			$this->_objid = ($class == "vbsubjectsource"?$_GET['sourceid']:$_GET['source1']);
			var_dump($this->_objid);
		}		
		else 
			$this->notify("Invalid class provided in vbcontent.removeObject");	
		if ($res !=false)
		{
			$this->notify("Object verwijderd");
			//succesfully created, rederict action (as defined earlier)
			$this->view();
		}
		else
		{ 
			$this->notify("Fout tijdens verwijderen: ".mysql_errno().": ".mysql_error()."<br/>Object:<br/>");
			var_export($r);						
		}
	}
	
	/** onderwerp type converteren, van en naar blad ofknoop. */
	function convertSubject()
	{
		$this->_vb->convertSubject($this->_objid, $this); //rechten worden daar gecontroleerd
		$this->_action = "subject";
		$this->view();
	}
	
	/** pritns a message on the top of the current page, to indicatie status issues etc.  */
	function notify($message)
	{
		//TODO: layout
		echo $message."<br/>";
	}
	
	/** /TODO: vieze copy paste van class.forum, aangezien de methode niet statisch is */
	function formatDatum($datetime){
		$nu=time();
		$moment=strtotime($datetime);
		$verschil=$nu-$moment;
		if($verschil<=60){
			$return='<em>'.$verschil.' ';
			if($verschil==1) {$return.='seconde';}else{$return.='seconden';}
			$return.='</em> geleden';
		}elseif($verschil<=60*60){
			$return='<em>'.floor($verschil/60);
			if(floor($verschil/60)==1){	$return.=' minuut'; }else{$return.=' minuten'; }
			$return.='</em> geleden';
		}elseif($verschil<=(60*60*4)){
			$return='<em>'.floor($verschil/(60*60)).' uur</em> geleden';
		}elseif(date('Y-m-d')==date('Y-m-d', $moment)){
			$return='vandaag om '.date("G:i", $moment);
		}elseif(date('Y-m-d', $moment)==date('Y-m-d', strtotime('1 day ago'))){
			$return='gisteren om '.date("G:i", $moment);
		}else{
			$return='op '. date("G:i j-n-Y", $moment);
		}
		return $return;
	}

	/** maak een nieuwe template aan, met gelijk wat standaard variabelen */
	private function newTemplate()
	{
		$res =new Smarty_csr();
		$res->caching=false;
		//set rights, which will be used inside the templates
		$res->assign('allowedit',$this->_vb->isModerator());
		$res->assign('allowadd',$this->_vb->isLid()); //TODO: might be incostend with magToevoegen(class)
		//TODO: notify area instead of printing directly?
		return $res;
	}
}
?>