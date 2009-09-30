<?php
/*
 * Roodschopperklasse.
 *
 * Stuur mensen die rood staan een schopmailtje.
 *
 * Er wordt ubb over gedaan, maar de mail wordt plaintext verzonden, dus erg veel zal daar niet
 * van overblijven. Wellicht kan er later nog een html-optie ingeklust worden.
 */
require_once 'class.csrubb.php';

class Roodschopper{
	private $cie='soccie';
	private $saldogrens;
	private $bericht;
	
	private $uitsluiten=array();
	private $from;
	private $bcc;

	private $teschoppen=null;

	public function __construct($cie, $saldogrens, $onderwerp, $bericht){
		if(!in_array($cie, array('maalcie', 'soccie'))){
			throw new Exception('Ongeldige commissie');
		}
		$this->cie=$cie;
		//er wordt in roodschopper.php (int)-abs($saldogrens) gedaan, dus dat dit voorkomt
		//is onwaarschijnlijk.
		if($saldogrens>0){
			throw new Exception('Saldogrens moet beneden nul zijn');
		}
		$this->saldogrens=$saldogrens;
		$this->onderwerp=htmlspecialchars($onderwerp);
		$this->bericht=htmlspecialchars($bericht);
		
		$this->from=$this->cie.'@csrdelft.nl';
	}

	public static function getDefaults(){

		$bericht='Beste LID,
Uw saldo bij de ~ is SALDO, dat is negatief. Inleggen met je hoofd.

Bij voorbaat dank,
h.t. Fiscus.';
		$return=new Roodschopper('soccie', -5, 'U staat rood', $bericht);
		$return->setBcc(LoginLid::instance()->getLid()->getEmail());
		$return->setUitgesloten('x101');
		return $return;
	}
	public function getCommissie(){	return $this->cie; }
		
	public function getBcc(){			return $this->bcc; }
	public function setBcc($bcc){		$this->bcc=$bcc; }

	public function getFrom(){			return $this->from; }
	public function setFrom($from){		$this->from=$from; }
	
	public function getSaldogrens(){	return $this->saldogrens; }

	public function getUitgesloten(){	return implode(',', $this->uitsluiten); }
	public function setUitgesloten($uids){
		if(is_array($uids)){
			$this->uitsluiten=$uids;
		}elseif(Lid::isValidUid($uids)){
			$this->uitsluiten[]=$uids;
		}else{
			$this->uitsluiten=explode(',', $uids);
		}
	}
	public function getOnderwerp(){		return $this->onderwerp; }
	public function getBericht(){		return $this->bericht; }
	
	public function simulate(){
		$db=MySql::instance();
		$query="
			SELECT uid, ".$this->cie."Saldo AS saldo
			FROM lid
			WHERE ".$this->cie."Saldo<".$this->saldogrens."
			 AND (status='S_LID' OR status='S_NOVIET' OR status='S_GASTLID')
			ORDER BY achternaam, voornaam;";

		$data=$db->query2array($query);

		$bericht=CsrUBB::instance()->getHtml($this->bericht);
	
		foreach($data as $lidsaldo){
			//als het uid in $this->uitsluiten staat sturen we geen mails.
			if(in_array($lidsaldo['uid'], $this->uitsluiten)){
				continue;
			}
			$this->teschoppen[$lidsaldo['uid']]=array(
				'onderwerp'=>$this->replace($this->onderwerp, $lidsaldo['uid'], $lidsaldo['saldo']),
				'bericht'=>$this->replace($this->bericht, $lidsaldo['uid'], $lidsaldo['saldo']));
		}
		return count($this->teschoppen);
		
	}
	public function replace($invoer, $uid, $saldo){
		$lid=LidCache::getLid($uid);
		$saldo=number_format($saldo, 2, ',', '');
		return str_replace(array('LID', 'SALDO'), array($lid->getNaam(), $saldo), $invoer);
	}
	public function getLeden(){
		if($this->teschoppen===null){
			$this->simulate();
		}
		$leden=array();
		foreach($this->teschoppen as $uid => $bericht){
			$leden[]=LidCache::getLid($uid);
		}
		return $leden;
	}
	public function preview(){
		if($this->teschoppen===null){
			$this->simulate();
		}
		foreach($this->teschoppen as $uid => $bericht){
			echo '<strong>'.$bericht['onderwerp'].'</strong><br /'.nl2br($bericht['bericht']).'<hr />';
		}
	}
	public function doit(){
		if($this->teschoppen===null){
			$this->simulate();
		}
		//zorg dat het onderwerp netjes utf8 in base64 is. Als je dit niet doet krijgt het
		//spampunten van spamassasin (SUBJECT_NEEDS_ENCODING,SUBJ_ILLEGAL_CHARS)
		$onderwerp=' =?UTF-8?B?'. base64_encode($bericht['onderwerp']) ."?=\n";

		$headers="From: ".$this->getFrom()."\n";
		if($this->bcc!=''){
			$headers.="BCC: ".$this->getBcc()."\n";
		}
		//content-type en charset zetten zodat rare tekens in wazige griekse namen
		//en euro-tekens correct weergegeven worden in de mails.
		$headers.="Content-Type: text/plain; charset=UTF-8\r\n";
		$headers.='X-Mailer: csrdelft.nl/Jieter'."\n\r";
		foreach($this->teschoppen as $uid => $bericht){
			mail($uid.'@csrdelft.nl', $onderwerp, $bericht['bericht'], $headers);
		}
		exit;
	}
}
