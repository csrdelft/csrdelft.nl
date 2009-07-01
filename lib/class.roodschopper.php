<?php
/*
 * Roodschopperklasse.
 *
 * Stuur mensen die rood staan een schopmailtje.
 */
require_once 'class.csrubb.php';

class Roodschopper{
	private $cie='soccie';
	private $saldogrens;
	private $bericht;
	
	private $uitsluiten=array();
	private $from='pubcie@csrdelft.nl';
	private $bcc;

	private $teschoppen=null;

	public function __construct($cie, $saldogrens, $onderwerp, $bericht){
		if(!in_array($cie, array('maalcie', 'soccie'))){
			throw new Exception('Ongeldige commissie');
		}
		$this->cie=$cie;
		if($saldogrens>0){
			throw new Exception('Saldogrens moet beneden nul zijn');
		}
		$this->saldogrens=$saldogrens;
		$this->onderwerp=htmlspecialchars($onderwerp);
		$this->bericht=htmlspecialchars($bericht);
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
			FROM lid WHERE ".$this->cie."Saldo<".$this->saldogrens."
			 AND (status='S_LID' OR status='S_NOVIET' OR status='S_GASTLID');";

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
		
	public function doit(){
		if($this->teschoppen===null){
			$this->simulate();
		}
		$headers="From: ".$this->from."\n\r";
		if($this->bcc!=''){
			$headers.="Bcc: ".$this->bcc."\n\r";
		}
		foreach($this->teschoppen as $uid => $bericht){
			//mail($data['uid'].'@csrdelft.nl', $bericht['onderwerp'], $bericht['bericht'], $headers);
			echo '<h1>'.$bericht['onderwerp'].'</h1>'.$bericht['bericht'].'<hr />';
		}
	}
}
