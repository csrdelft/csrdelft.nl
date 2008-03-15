<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrgozerbot.php
# -------------------------------------------------------------------
# Wrapper voor GozerbotUDP
# -------------------------------------------------------------------

require_once('gozerbot/class.GozerbotUDP.php');

class CsrGozerbot extends GozerbotUDP{
	
	public function CsrGozerbot(){
		$aSettings=parse_ini_file(ETC_PATH.'/gozerbot.ini');
		
		if ($aSettings['cryptkey']=='') {$aSettings['cryptkey']=null;}
		
		$this->setHost($aSettings['host']);
		$this->setPort($aSettings['port']);
		$this->setPassword($aSettings['password']);
		$this->setTarget($aSettings['target']);
		$this->setCryptKey($aSettings['cryptkey']);
	}
}


?>