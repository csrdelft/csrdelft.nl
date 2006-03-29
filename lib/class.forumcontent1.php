<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.forumcontent.php
# -------------------------------------------------------------------
# Historie:
#	03-03-2006 Jieter
# . basis functionaliteit is af.
# 28-01-2006 Jieter
# . gemaakt
#
require_once('bbcode/include.bbcode.php');

class ForumContent extends SimpleHTML {
	var $_db;
	var $_lid;
	var $_sError=false;
	var $_parseStart;
	
	function ForumContent(&$lid, &$db){
		$this->_lid =& $lid;
		$this->_db =& $db;
		//starttijd in parseStart rossen
		$this->addTijdMeting('start');
	}
	function addTijdMeting($naam){
		list($usec, $sec) = explode(" ",microtime()); 
   	$this->_parseStart[$naam]=((float)$usec + (float)$sec);
	}
	function viewTopic($iTopicID){
		$this->addTijdMeting('begin_viewTopic()');
		$iTopicID=(int)$iTopicID;
		require_once(SMARTY_DIR.'Smarty.class.php');
$this->addTijdMeting('begin_Smarty_class');
		$template=new Smarty();
		$template->caching=true;
		$template->compile_check = false;
		
		$template->template_dir = LIB_PATH.'/templates/source/';
		$template->compile_dir = DATA_PATH.'/template_cache/compiled/';
		$template->cache_dir = DATA_PATH.'/template_cache/cache/';
		$template->config_dir = LIB_PATH.'/templates/configs/';
		
$this->addTijdMeting('voor_isCached');
		if(!$template->is_cached('forum/viewtopic.tpl', $iTopicID)){
			echo 'niet uit cache...';
			require_once('class.forum.php');
			$this->addTijdMeting('voor_makenVan_Topic_class');
			$topic=new Topic($this->_lid, $this->_db);
$this->addTijdMeting('voor_loadTopic');
			if($topic->loadTopic($iTopicID)){
				$template->assign('topicID', $iTopicID);
				$template->assign_by_ref('topic', $topic); 
			}else{
				$bUseTemplate=false;
			}
		}else{
			echo 'wel uit cache';
		}
		if(isset($bUseTemplate)){
			echo 'Er is een fout opgetreden: <strong>'.$topic->getError().'</strong>';
		}else{	
$this->addTijdMeting('voor_$templaat->display');
			$template->display('forum/viewtopic.tpl', $iTopicID);
$this->addTijdMeting('na_$templaat->display');
		}
		
	}

	function setError($sError){
		$this->_sError=$sError;
	}
	function view(){
		$iTopicID=(int)$_GET['topic'];
		$this->viewTopic($iTopicID);
		echo $this->getProfile();
	}
	function getProfile(){
		$this->addTijdMeting('einde');
		list($usec, $sec) = explode(" ",microtime()); 
		$eindTijd=((float)$usec + (float)$sec);
		$startTijd=$this->_parseStart['start'];
		$totaal=$eindTijd-$startTijd;
		$vorige=$startTijd;
		unset($this->_parseStart['start']);
		$sOutput='';
		foreach($this->_parseStart as $label => $huidige){
			$sOutput.='tot <i>'.$label.'</i> duurde het <b>'.round($huidige-$vorige, 4).'</b>s <br />'; 
			$vorige=$huidige;
		}
		return $sOutput.'Totale parsetijd: '.round($totaal, 4).'s <br />';
	}
}
?>
