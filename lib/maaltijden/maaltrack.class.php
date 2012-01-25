<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.maaltijdvoorkeurcontent.php
# -------------------------------------------------------------------
# MaalTrack bevat overkoepelende functies voor maaltijden
# - maaltijd aanmaken
# - maaltijd verwijderen
# - maaltijdenlijst opvragen
# - abonnementen aan/uitzetten
# -------------------------------------------------------------------


require_once 'maaltijden/maaltijd.class.php';
require_once 'maaltijden/corveelid.class.php';
require_once 'maaltijden/corveeinstellingen.class.php';

class MaalTrack {
	# MySQL connectie
	var $_db;
	private $corveepunten;


	# evt. foutboodschap
	var $_error = '';
	var $_proxyerror = '';

	function __construct() {
		$this->_db =MySql::instance();
		$this->corveepunten = Corveeinstellingen::get('puntentotaal');
	}

	function getError() { $error = $this->_error; $this->_error = ""; return $error; }
	function getProxyError() { $proxyerror = $this->_proxyerror; $this->_proxyerror = ""; return $proxyerror; }

	# datum - timestamp wanneer de maaltijd is
	# tekst - omschrijving/menu van de maaltijd
	# abosoort - enum-waarde van een abo dat geldt voor deze maaltijd
	# tp = tafelpraeses-uid
	# max - maximaal aantal inschrijvingen
	function addMaaltijd($datum, $tekst, $abosoort, $tp, $koks, $afwassers, $theedoeken, $max = MAX_MAALTIJD, $punten_kok, $punten_afwas, $punten_theedoek ) {
		$datum = (int)$datum;
		$max = (int)$max;
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);

		$koks=abs((int)$koks);
		$afwassers=abs((int)$afwassers);
		$theedoeken=abs((int)$theedoeken);
		$punten_kok=abs((int)$punten_kok);
		$punten_afwas=abs((int)$punten_afwas);
		$punten_theedoek=abs((int)$punten_theedoek);

		# bij fouten, niet doorgaan, false teruggeven.
		if(!$this->validateMaaltijd($datum, $tekst, $abosoort, $tp, $max)){
			return false;
		}

		# voeg de maaltijd toe en geef het maalid terug, of false als het  niet gelukt is.
		$maaltijd="
			INSERT INTO
				maaltijd
			(
				datum
				,tekst
				,abosoort
				,max
				,tp
				,koks
				,afwassers
				,theedoeken
				,punten_kok
				,punten_afwas
				,punten_theedoek
				,schoonmaken_frituur
				,schoonmaken_afzuigkap
				,schoonmaken_keuken
				,klussen_licht
				,klussen_zwaar
				,punten_schoonmaken_frituur
				,punten_schoonmaken_afzuigkap
				,punten_schoonmaken_keuken
				,punten_klussen_licht
				,punten_klussen_zwaar
			)VALUES(
				'".$datum."', '".$tekst."', '".$abosoort."', '".$max."',
				'".$tp."', '".$koks."', '".$afwassers."', '".$theedoeken."',
				'".$punten_kok."', '".$punten_afwas."', '".$punten_theedoek."',
				0,0,0,0,0,0,0,0,0,0
			);";

		if (!$this->_db->query($maaltijd)){
			$this->_error="Er is iets mis met de database/query";
			return false;
		}else{
			$maaltijd = new Maaltijd($this->_db->insert_id());
			# ook maar meteen even hertellen, dan zijn de mensen die daar blij van worden weer extra blij...
			$maaltijd->recount();
			return $maaltijd->getMaalId();
		}
	}

	# bestaande maaltijd bewerken. Niet veel verschil met addMaaltijd, behalve dat hier nog even
	# gekeken wordt of de maaltijd wel bestaat.
	function editMaaltijd($maalid, $datum, $tekst, $abosoort, $tp, $koks, $afwassers, $theedoeken, $max=MAX_MAALTIJD){
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}
		$datum = (int)$datum;
		$max = (int)$max;
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);

		$koks=abs((int)$koks);
		$afwassers=abs((int)$afwassers);
		$theedoeken=abs((int)$theedoeken);

		# bij fouten, niet doorgaan, false teruggeven.
		if(!$this->validateMaaltijd($datum, $tekst, $abosoort, $tp, $max)){
			return false;
		}
		$maaltijd="
			UPDATE
				maaltijd
			SET
				datum=".$datum.",
				tekst='".$tekst."',
				abosoort='".$abosoort."',
				tp='".$tp."',
				koks='".$koks."',
				afwassers='".$afwassers."',
				theedoeken='".$theedoeken."',
				max=".$max."
			WHERE
				id=".$maalid."
			LIMIT 1;";
		if(!$this->_db->query($maaltijd)){
			$this->_error="Er is iets mis met de database/query.";
			return false;
		}else{
			$maaltijd = new Maaltijd ($maalid);
			# ook maar meteen even hertellen, dan zijn de mensen die daar blij van worden weer extra blij...
			$maaltijd->recount();
			return $maalid;
		}
	}
	
	# deze methode valideert de gemeenschappelijke waarden van addMaaltijd en editMaaltijd.
	# controle op specifieke dingen voor editMaaltijd gebeurt nog in de methode zelf.
	function validateMaaltijd($datum, $tekst, $abosoort, $tp, $max){
		# controleer of de datum niet in het verleden ligt
		if ($datum < time()) {
			$this->_error = "Het tijdstip van de maaltijd moet in de toekomst liggen";
			return false;
		}

		# tekst max 200 karakters
		if (!is_utf8($tekst)) {
			$this->_error = "De omschrijving bevat ongeldige tekens.";
			return false;
		}


		# kijk of $tp voorkomt in de ledenlijst
		if($tp != "" AND !Lid::exists($_POST['tp'])){
			$this->_error = "De tafelpraeses moet voorkomen in de ledenlijst.";
			return false;
		}

		# kijk of $abosoort voorkomt in tabel maaltijdabosoort
		if (!array_key_exists($abosoort, $this->getAbos())) {
			$this->_error = "Er is geen bestaande abonnementsvorm opgegeven.";
			return false;
		}

		# controleer of het maximum aantal > 0 en <= MAX_MAALTIJD is
		if ($max <= 0 or $max > MAX_MAALTIJD) {
			$this->_error = "Het maximaal aantal eters moet tussen 1 en " . MAX_MAALTIJD . " zijn.";
			return false;
		}
		# kijk of een gekozen abo niet meteen meer inschrijvingen oplevert dan het maximum wat ingesteld wordt
		if ($abosoort != "" and $this->getAboCount($abosoort) > $max) {
			$this->_error = "Het gekozen abonnement levert meer inschrijvingen op dan het maximaal ingestelde aantal.";
			return false;
		}
		return true;
	}

	function removeCorveeMaaltijd($maalid) {
		return $this->removeMaaltijd($maalid);
	}
	
	function removeMaaltijd($maalid) {
		if (!is_numeric($maalid)) {
			$this->_error = "Gebruik een numerieke maaltijd-id waarde om te verwijderen";
			return false;
		}
		# kijk of de maaltijd wel bestaat.
		if(!$this->isMaaltijd($maalid)){
			$this->_error = "Deze maaltijd bestaat niet";
			return false;
		}

		# verwijder alle aan/afmeldingen voor deze maaltijd
		# ...van leden met de bijbehoorende gasten.
		$aanmeldingen="DELETE FROM maaltijdaanmelding WHERE maalid=".$maalid;

		# verwijder corvee-aanmeldingen
		$corveeaanmeldingen="DELETE FROM maaltijdcorvee WHERE maalid=".$maalid;

		# verwijder de maaltijd zelf
		$maaltijd="DELETE FROM maaltijd WHERE id=".$maalid;

		return $this->_db->query($aanmeldingen) AND $this->_db->query($corveeaanmeldingen) AND $this->_db->query($maaltijd);
	}
	
	# bij bestaande maaltijd de relevante corveevelden bewerken
	function editCorveeMaaltijd($maalid, $koks, $afwassers, $theedoeken, $punten_kok, $punten_afwas, $punten_theedoek){
		
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}

		$koks=abs((int)$koks);
		$afwassers=abs((int)$afwassers);
		$theedoeken=abs((int)$theedoeken);

		// controleer aantal aangemelde taken
		$maaltijd = $this->getMaaltijd($maalid);
		if($koks < $maaltijd['koks_aangemeld']){
			$this->_error="Het aantal koks kan niet lager zijn dan het aantal ingedeelde koks.";
			return false;
		}
		if($afwassers < $maaltijd['afwassers_aangemeld']){
			$this->_error="Het aantal afwassers kan niet lager zijn dan het aantal ingedeelde afwassers.";
			return false;
		}
		if($theedoeken < $maaltijd['theedoeken_aangemeld']){
			$this->_error="Het aantal theedoekwassers kan niet lager zijn dan het aantal ingedeelde theedoekwassers.";
			return false;
		}

		$punten_kok=abs((int)$punten_kok);
		$punten_afwas=abs((int)$punten_afwas);
		$punten_theedoek=abs((int)$punten_theedoek);

		$maaltijd="
			UPDATE
				maaltijd
			SET
				koks='".$koks."',
				afwassers='".$afwassers."',
				theedoeken='".$theedoeken."',
				punten_kok='".$punten_kok."',
				punten_afwas='".$punten_afwas."',
				punten_theedoek='".$punten_theedoek."'
			WHERE
				id=".$maalid."
			LIMIT 1;";
		if(!$this->_db->query($maaltijd)){
			$this->_error="Er is iets mis met de database/query.";
			return false;
		}else{
			$maaltijd = new Maaltijd ($maalid);
			return $maalid;
		}
	}
	
	# bij bestaande maaltijd de taken bewerken
	function editCorveeMaaltijdTaken($maalid, $kok = array(), $afwas = array(), $theedoek = array()){
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}
		
		//verwijder dubbele uids
		$kok = array_unique($kok);
		$afwas = array_unique($afwas);
		$theedoek = array_unique($theedoek);
		
		//bestaande aanmeldingen verwijderen
		$query_maaltijd="
			DELETE FROM
				maaltijdcorvee
			WHERE
				maalid=".$maalid."";
		if(!$this->_db->query($query_maaltijd)){
			$this->_error="Er is iets mis met de database/query.";
			return false;
		}
				
		//aanmeldingen toevoegen
		foreach($kok as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO maaltijdcorvee (maalid, uid, kok) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE kok = 1");
		}
		foreach($afwas as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, afwas) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE afwas = 1");
		}
		foreach($theedoek as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, theedoek) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE theedoek = 1");
		}
		
		$maaltijd = new Maaltijd ($maalid);
		return $maalid;
	}

	/* Functie die de corveepunten van een gewone maaltijd bijwerkt, gegeven een maaltijd en een toekenningsarray
	 * Argumenten: 
	 * het id van de maaltijd 
	 * een array van uid's, met voor elk met 'onbekend','ja' of 'nee' aangegeven of het corvee door die persoon uitgevoerd is.  
	 */
	function editCorveeMaaltijdPunten($maalid, $punten){
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}		
		
		//Haal op uit de maaltijdgegevens wie wat gedaan heeft
		//Dit blijft tijdens de hele functie constant
		$maaltijd = $this->getMaaltijd($maalid);
		
		$kok = array();
		if(array_key_exists('koks', $maaltijd['taken'])){ 
			$kok = array_unique($maaltijd['taken']['koks']);
		}
		
		$afwas = array();
		if(array_key_exists('afwassers', $maaltijd['taken'])){ 
			$afwas = array_unique($maaltijd['taken']['afwassers']);
		}
		$theedoek = array();
		if(array_key_exists('theedoeken', $maaltijd['taken'])){ 
			$theedoek = array_unique($maaltijd['taken']['theedoeken']);
		}		
		
		//Verwerken punten: vergelijk het formulier met de toekenning in de database		
		foreach($punten as $uid=>$form_toegekend){			
			//haal op of punten al toegekend waren
			$sToegekendQuery="
			SELECT 
				punten_toegekend
			FROM 
				maaltijdcorvee 
			WHERE 
				maalid=".$maalid." 
				AND uid=".$uid."
			;";
			$dbresult = $this->_db->query($sToegekendQuery);
			if(!$dbresult){
				$this->_error=$this->_db->debug("");
				return false;
			}	
			$dbarray = $this->_db->next($dbresult);
			$db_toegekend = $dbarray['punten_toegekend'];

			$corveelid = new CorveeLid(LidCache::getLid((string)$uid));						
			
			//Als iemand nog geen punten toegekend had, maar nu wel, ken ze dan toe
			if($db_toegekend!='ja' && $form_toegekend=='ja'){				
				$punten_erbij = (in_array($uid,$kok)?1:0) * $maaltijd['punten_kok'] + 
								 (in_array($uid,$afwas)?1:0) * $maaltijd['punten_afwas'] + 
								 (in_array($uid,$theedoek)?1:0) * $maaltijd['punten_theedoek'];
				$punten_nu = $corveelid->getCorveePunten();
				if(!$corveelid->setCorveePunten($punten_nu + $punten_erbij)){
					return false;
				}
				$corveelid->save();				 				
			}
			//van ja naar iets anders: punten intrekken
			if($db_toegekend=='ja' && $form_toegekend!='ja'){
				$punten_eraf = (in_array($uid,$kok)?1:0) * $maaltijd['punten_kok'] + 
								 (in_array($uid,$afwas)?1:0) * $maaltijd['punten_afwas'] + 
								 (in_array($uid,$theedoek)?1:0) * $maaltijd['punten_theedoek'];
				$punten_nu = $corveelid->getCorveePunten();
				if(!$corveelid->setCorveePunten($punten_nu - $punten_eraf)){
					return false;
				}
				$corveelid->save();				
			}

			//Als iemand niet gefaald had, maar nu wel, ken dan strafpunten toe
			if($db_toegekend!='nee' && $form_toegekend=='nee'){
				$strafpunten = (in_array($uid,$kok)?1:0) * $maaltijd['punten_kok'] + 
								 (in_array($uid,$afwas)?1:0) * $maaltijd['punten_afwas'] + 
								 (in_array($uid,$theedoek)?1:0) * $maaltijd['punten_theedoek'];
				$bonuspunten_nu = $corveelid->getBonusPunten();
				if(!$corveelid->setBonusPunten($bonuspunten_nu - $strafpunten)){
					return false;
				}
				$corveelid->save();
			}
			//van nee naar iets anders, dus strafpunten intrekken
			if($db_toegekend=='nee' && $form_toegekend!='nee'){
				$strafpunten = (in_array($uid,$kok)?1:0) * $maaltijd['punten_kok'] + 
								 (in_array($uid,$afwas)?1:0) * $maaltijd['punten_afwas'] + 
								 (in_array($uid,$theedoek)?1:0) * $maaltijd['punten_theedoek'];
				$bonuspunten_nu = $corveelid->getBonusPunten();
				if(!$corveelid->setBonusPunten($bonuspunten_nu + $strafpunten)){
					return false;
				}
				$corveelid->save();				
			}
			
		}
		
		//Punten_toegekend updaten
		foreach($punten as $uid=>$form_toegekend){
			if (!$uid) continue;			
			$db_toegekend = $form_toegekend;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, punten_toegekend) VALUES('".$maalid."', '".$uid."', '".$db_toegekend."') ON DUPLICATE KEY UPDATE punten_toegekend = '".$db_toegekend."'");
		}
		
		$maaltijd = new Maaltijd ($maalid);
		return $maalid;
	}
	
	function addSchoonmaakMaaltijd($datum, $tekst, $schoonmaken_frituur, $schoonmaken_afzuigkap, $schoonmaken_keuken, $klussen_licht, $klussen_zwaar, $punten_schoonmaken_frituur, $punten_schoonmaken_afzuigkap, $punten_schoonmaken_keuken, $punten_klussen_licht, $punten_klussen_zwaar) {
		$datum = (int)$datum;
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);

		$schoonmaken_frituur=abs((int)$schoonmaken_frituur);
		$schoonmaken_afzuigkap=abs((int)$schoonmaken_afzuigkap);
		$schoonmaken_keuken=abs((int)$schoonmaken_keuken);
		$klussen_licht=abs((int)$klussen_licht);
		$klussen_zwaar=abs((int)$klussen_zwaar);
		$punten_schoonmaken_frituur=abs((int)$punten_schoonmaken_frituur);
		$punten_schoonmaken_afzuigkap=abs((int)$punten_schoonmaken_afzuigkap);
		$punten_schoonmaken_keuken=abs((int)$punten_schoonmaken_keuken);
		$punten_klussen_licht=abs((int)$punten_klussen_licht);
		$punten_klussen_zwaar=abs((int)$punten_klussen_zwaar);
		
		# voeg de maaltijd toe en geef het maalid terug, of false als het  niet gelukt is.
		$maaltijd="
			INSERT INTO
				maaltijd
			(
				datum, 
				type,
				tekst, 
				koks,
				afwassers,
				theedoeken,
				punten_kok,
				punten_afwas,
				punten_theedoek,
				schoonmaken_frituur, 
				schoonmaken_afzuigkap, 
				schoonmaken_keuken,
				klussen_licht, 
				klussen_zwaar,
				punten_schoonmaken_frituur, 
				punten_schoonmaken_afzuigkap, 
				punten_schoonmaken_keuken,
				punten_klussen_licht, 
				punten_klussen_zwaar
			)VALUES(
				'".$datum."', 'corvee', '".$tekst."', 0, 0, 0, 0, 0, 0,
				'".$schoonmaken_frituur."', '".$schoonmaken_afzuigkap."', '".$schoonmaken_keuken."', '".$klussen_licht."', '".$klussen_zwaar."',
				'".$punten_schoonmaken_frituur."', '".$punten_schoonmaken_afzuigkap."', '".$punten_schoonmaken_keuken."', '".$punten_klussen_licht."', '".$punten_klussen_zwaar."'
			);";
		if (!$this->_db->query($maaltijd)){
			echo $this->_db->getDebug(true, false, false, false, false, false);
			$this->_error="Er is iets mis met de database/query";
			return false;
		}else{
			$maaltijd = new Maaltijd($this->_db->insert_id());
			# ook maar meteen even hertellen, dan zijn de mensen die daar blij van worden weer extra blij...
			$maaltijd->recount();
			return $maaltijd->getMaalId();
		}
	}
	
	function editSchoonmaakMaaltijd($maalid, $schoonmaken_frituur, $schoonmaken_afzuigkap, $schoonmaken_keuken, $klussen_licht, $klussen_zwaar, $punten_schoonmaken_frituur, $punten_schoonmaken_afzuigkap, $punten_schoonmaken_keuken, $punten_klussen_licht, $punten_klussen_zwaar){		
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}

		$schoonmaken_frituur=abs((int)$schoonmaken_frituur);
		$schoonmaken_afzuigkap=abs((int)$schoonmaken_afzuigkap);
		$schoonmaken_keuken=abs((int)$schoonmaken_keuken);
		$klussen_licht=abs((int)$klussen_licht);
		$klussen_zwaar=abs((int)$klussen_zwaar);

		// controleer aantal aangemelde taken
		$maaltijd = $this->getMaaltijd($maalid);
		if($schoonmaken_frituur < $maaltijd['frituur_aangemeld']){
			$this->_error="Het aantal frituurschoonmakers kan niet lager zijn dan het aantal ingedeelde schoonmakers.";
			return false;
		}
		if($schoonmaken_afzuigkap < $maaltijd['afzuigkap_aangemeld']){
			$this->_error="Het aantal afzuigkapschoonmakers kan niet lager zijn dan het aantal ingedeelde schoonmakers.";
			return false;
		}
		if($schoonmaken_keuken < $maaltijd['keuken_aangemeld']){
			$this->_error="Het aantal keukenschoonmakers kan niet lager zijn dan het aantal ingedeelde schoonmakers.";
			return false;
		}
		if($klussen_licht < $maaltijd['lichteklus_aangemeld']){
			$this->_error="Het aantal klussers (Licht) kan niet lager zijn dan het aantal ingedeelde klussers (Licht).";
			return false;
		}
		if($klussen_zwaar < $maaltijd['zwareklus_aangemeld']){
			$this->_error="Het aantal klussers (Zwaar) kan niet lager zijn dan het aantal ingedeelde klussers (Zwaar).";
			return false;
		}
		
		$punten_schoonmaken_frituur=abs((int)$punten_schoonmaken_frituur);
		$punten_schoonmaken_afzuigkap=abs((int)$punten_schoonmaken_afzuigkap);
		$punten_schoonmaken_keuken=abs((int)$punten_schoonmaken_keuken);
		$punten_klussen_licht=abs((int)$punten_klussen_licht);
		$punten_klussen_zwaar=abs((int)$punten_klussen_zwaar);
		
		$maaltijd="
			UPDATE
				maaltijd
			SET
				schoonmaken_frituur='".$schoonmaken_frituur."',
				schoonmaken_afzuigkap='".$schoonmaken_afzuigkap."',
				schoonmaken_keuken='".$schoonmaken_keuken."',
				klussen_licht='".$klussen_licht."',
				klussen_zwaar='".$klussen_zwaar."',
				punten_schoonmaken_frituur='".$punten_schoonmaken_frituur."',
				punten_schoonmaken_afzuigkap='".$punten_schoonmaken_afzuigkap."',
				punten_schoonmaken_keuken='".$punten_schoonmaken_keuken."',
				punten_klussen_licht='".$punten_klussen_licht."',
				punten_klussen_zwaar='".$punten_klussen_zwaar."'
			WHERE
				id=".$maalid."
			LIMIT 1;";
		if(!$this->_db->query($maaltijd)){
			$this->_error="Er is iets mis met de database/query.";
			return false;
		}else{
			$maaltijd = new Maaltijd ($maalid);
			return $maalid;
		}
	}

	# bij bestaande maaltijd de taken bewerken
	function editSchoonmaakMaaltijdTaken($maalid, $frituur, $afzuigkap, $keuken, $lichteklus, $zwareklus){
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}

		// check parameters
		if (!is_array($frituur))
			$frituur = array();
		if (!is_array($afzuigkap))
			$afzuigkap = array();
		if (!is_array($keuken))
			$keuken = array();
		if (!is_array($lichteklus))
			$lichteklus = array();
		if (!is_array($zwareklus))
			$zwareklus = array();
		
		//verwijder dubbele uids
		$frituur = array_unique($frituur);
		$afzuigkap = array_unique($afzuigkap);
		$keuken = array_unique($keuken);
		$lichteklus = array_unique($lichteklus);
		$zwareklus = array_unique($zwareklus);
		
		//bestaande aanmeldingen verwijderen
		$query_maaltijd="
			DELETE FROM
				maaltijdcorvee
			WHERE
				maalid=".$maalid."";
		if(!$this->_db->query($query_maaltijd)){
			$this->_error="Er is iets mis met de database/query.";
			return false;
		}
				
		//aanmeldingen toevoegen
		foreach($frituur as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO maaltijdcorvee (maalid, uid, schoonmaken_frituur) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE schoonmaken_frituur = 1");
		}
		foreach($afzuigkap as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, schoonmaken_afzuigkap) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE schoonmaken_afzuigkap = 1");
		}
		foreach($keuken as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, schoonmaken_keuken) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE schoonmaken_keuken = 1");
		}
		foreach($lichteklus as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, klussen_licht) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE klussen_licht = 1");
		}
		foreach($zwareklus as $uid) {
			if (!$uid) continue;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, klussen_zwaar) VALUES('".$maalid."', '".$uid."', 1) ON DUPLICATE KEY UPDATE klussen_zwaar = 1");
		}
		
		$maaltijd = new Maaltijd ($maalid);
		return $maalid;
	}

	/* Functie die de corveepunten van een schoonmaakmaaltijd (ook wel corveevrijdag) bijwerkt, 
	 * gegeven een maaltijd en een toekenningsarray
	 * 
	 * Argumenten: 
	 * het id van de maaltijd 
	 * een array van uid's, met voor elk met 'onbekend','ja' of 'nee' aangegeven of het corvee door die persoon uitgevoerd is.  
	 */
	function editSchoonmaakMaaltijdPunten($maalid, $punten){
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}
		
		
		//Haal op uit de maaltijdgegevens wie wat gedaan heeft
		//Dit blijft tijdens de hele functie constant
		$maaltijd = $this->getMaaltijd($maalid);
		
		$frituur = array();
		if(array_key_exists('schoonmaken_frituur', $maaltijd['taken'])){ 
			$frituur = array_unique($maaltijd['taken']['schoonmaken_frituur']);
		}
		$afzuigkap = array();
		if(array_key_exists('schoonmaken_afzuigkap', $maaltijd['taken'])){ 
			$afzuigkap = array_unique($maaltijd['taken']['schoonmaken_afzuigkap']);
		}
		$keuken = array();
		if(array_key_exists('schoonmaken_keuken', $maaltijd['taken'])){ 
			$keuken = array_unique($maaltijd['taken']['schoonmaken_keuken']);
		}
		$lichteklus = array();
		if(array_key_exists('klussen_licht', $maaltijd['taken'])){ 
			$lichteklus = array_unique($maaltijd['taken']['klussen_licht']);
		}
		$zwareklus = array();
		if(array_key_exists('klussen_zwaar', $maaltijd['taken'])){ 
			$zwareklus = array_unique($maaltijd['taken']['klussen_zwaar']);
		}
		
		//Verwerken punten: vergelijk het formulier met de toekenning in de database
		foreach($punten as $uid=>$form_toegekend){
			if (!$form_toegekend) continue;
			//haal op of punten al toegekend waren
			$sToegekendQuery="
			SELECT 
				punten_toegekend
			FROM 
				maaltijdcorvee 
			WHERE 
				maalid=".$maalid." 
				AND uid=".$uid."
			;";
			$dbresult = $this->_db->query($sToegekendQuery);
			$dbarray = $this->_db->next($dbresult);
			$db_toegekend = $dbarray['punten_toegekend'];
			
			$corveelid = new CorveeLid(LidCache::getLid($uid));	

			//Als iemand nog geen punten toegekend had, maar nu wel, ken ze dan toe
			if($db_toegekend!='ja' && $form_toegekend=='ja'){				
				$punten_erbij = (in_array($uid,$frituur)?1:0) * $maaltijd['punten_schoonmaken_frituur'] + 
								 (in_array($uid,$afzuigkap)?1:0) * $maaltijd['punten_schoonmaken_afzuigkap'] + 
								 (in_array($uid,$keuken)?1:0) * $maaltijd['punten_schoonmaken_keuken'] +
								 (in_array($uid,$lichteklus)?1:0) * $maaltijd['punten_klussen_licht'] + 
								 (in_array($uid,$zwareklus)?1:0) * $maaltijd['punten_klussen_zwaar'];
				$punten_nu = $corveelid->getCorveePunten();
				if(!$corveelid->setCorveePunten($punten_nu + $punten_erbij)){
					return false;
				}
				$corveelid->save();					
			}
			//van ja naar iets anders: punten intrekken
			if($db_toegekend=='ja' && $form_toegekend!='ja'){
				$punten_eraf = (in_array($uid,$frituur)?1:0) * $maaltijd['punten_schoonmaken_frituur'] + 
								 (in_array($uid,$afzuigkap)?1:0) * $maaltijd['punten_schoonmaken_afzuigkap'] + 
								 (in_array($uid,$keuken)?1:0) * $maaltijd['punten_schoonmaken_keuken'] +
								 (in_array($uid,$lichteklus)?1:0) * $maaltijd['punten_klussen_licht'] + 
								 (in_array($uid,$zwareklus)?1:0) * $maaltijd['punten_klussen_zwaar'];
				$punten_nu = $corveelid->getCorveePunten();
				if(!$corveelid->setCorveePunten($punten_nu - $punten_eraf)){
					return false;
				}
				$corveelid->save();									 
			}

			//Als iemand niet gefaald had, maar nu wel, ken dan strafpunten toe
			if($db_toegekend!='nee' && $form_toegekend=='nee'){
				$strafpunten = (in_array($uid,$frituur)?1:0) * $maaltijd['punten_schoonmaken_frituur'] + 
								 (in_array($uid,$afzuigkap)?1:0) * $maaltijd['punten_schoonmaken_afzuigkap'] + 
								 (in_array($uid,$keuken)?1:0) * $maaltijd['punten_schoonmaken_keuken'] +
								 (in_array($uid,$lichteklus)?1:0) * $maaltijd['punten_klussen_licht'] + 
								 (in_array($uid,$zwareklus)?1:0) * $maaltijd['punten_klussen_zwaar'];
				$bonuspunten_nu = $corveelid->getBonusPunten();
				if(!$corveelid->setBonusPunten($bonuspunten_nu - $strafpunten)){
					return false;
				}
				$corveelid->save();
			}
			//van nee naar iets anders, dus strafpunten intrekken
			if($db_toegekend=='nee' && $form_toegekend!='nee'){
				$strafpunten = (in_array($uid,$frituur)?1:0) * $maaltijd['punten_schoonmaken_frituur'] + 
								 (in_array($uid,$afzuigkap)?1:0) * $maaltijd['punten_schoonmaken_afzuigkap'] + 
								 (in_array($uid,$keuken)?1:0) * $maaltijd['punten_schoonmaken_keuken'] + 
								 (in_array($uid,$lichteklus)?1:0) * $maaltijd['punten_klussen_licht'] + 
								 (in_array($uid,$zwareklus)?1:0) * $maaltijd['punten_klussen_zwaar'];
				$bonuspunten_nu = $corveelid->getBonusPunten();
				if(!$corveelid->setBonusPunten($bonuspunten_nu + $strafpunten)){
					return false;
				}
				$corveelid->save();
			}
			
		}
					
		//Punten_toegekend updaten
		foreach($punten as $uid=>$form_toegekend){
			if (!$uid) continue;
			//$toekenning = array('onbekend','ja','nee');
			$db_toegekend = $form_toegekend;
			$this->_db->query("INSERT INTO	maaltijdcorvee (maalid, uid, punten_toegekend) VALUES('".$maalid."', '".$uid."', '".$db_toegekend."') ON DUPLICATE KEY UPDATE punten_toegekend = '".$db_toegekend."'");
		}
		
		$maaltijd = new Maaltijd ($maalid);
		return $maalid;
	}
	
	# haalt één enkele maaltijd op ter bewerking
	function getMaaltijd($maalid){
		$maalid=(int)$maalid;
		if($maalid==0){
			$this->_error="Geen geldig maaltijd-id";
			return false;
		}
		$sMaaltijdQuery="
			SELECT
				id, type, datum, gesloten, tekst, abosoort, max, aantal,
				tp, koks, afwassers, theedoeken, punten_kok, punten_afwas, punten_theedoek,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND kok = 1) AS koks_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND afwas = 1) AS afwassers_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND theedoek = 1) AS theedoeken_aangemeld,
				schoonmaken_frituur, schoonmaken_afzuigkap, schoonmaken_keuken, klussen_licht,	klussen_zwaar,
				punten_schoonmaken_frituur, punten_schoonmaken_afzuigkap, punten_schoonmaken_keuken,punten_klussen_licht,	punten_klussen_zwaar,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND schoonmaken_frituur = 1) AS frituur_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND schoonmaken_afzuigkap = 1) AS afzuigkap_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND schoonmaken_keuken = 1) AS keuken_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND klussen_licht = 1) AS lichteklus_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND klussen_zwaar = 1) AS zwareklus_aangemeld,
				(SELECT COUNT(*)>0 FROM maaltijdcorvee WHERE maalid = id AND punten_toegekend = 'ja') AS is_toegekend
			FROM
				maaltijd
			WHERE
				id=".$maalid."
			LIMIT 1;";
		$rMaaltijd=$this->_db->query($sMaaltijdQuery);
		$aMaal=$this->_db->next($rMaaltijd);
		
		//taken ophalen
		$maaltijd = new Maaltijd ($maalid);
		$aMaal['taken'] = $maaltijd->getTaken();

		return $aMaal;
	}
	//van opgegeven of huidige lid de corveepunten, -vrijstellingen en alle aanwezige corveetaken opzoeken en teruggeven in een array
	static public function getCorveeTaken($uid=null){
		if($uid===null){
			$uid=LoginLid::instance()->getUid();
		}
		$taken = array();
		$return['aantal']=null;
		$db=MySql::instance();
		//eerst uit 'lid' corveepunten/bonus/vrijstelling, als er taken zijn die ook verzamelen uit 'maaltijdcorvee' en 'maaltijd'
		$sTakenquery = "
			SELECT maalid, lid.uid, datum, tekst, type, punten_toegekend, type , corvee_punten+corvee_punten_bonus AS corvee_punten_totaal, corvee_punten_bonus, corvee_vrijstelling, corvee_voorkeuren,
				kok, afwas, theedoek, maaltijdcorvee.klussen_zwaar, maaltijdcorvee.klussen_licht, maaltijdcorvee.schoonmaken_keuken, maaltijdcorvee.schoonmaken_afzuigkap, maaltijdcorvee.schoonmaken_frituur,
				punten_kok, punten_afwas, punten_theedoek, punten_klussen_zwaar, punten_klussen_licht, punten_schoonmaken_keuken, punten_schoonmaken_afzuigkap, punten_schoonmaken_frituur
			FROM lid 
			LEFT JOIN maaltijdcorvee ON maaltijdcorvee.uid = lid.uid
			LEFT JOIN maaltijd ON maaltijdcorvee.maalid = maaltijd.id
			WHERE lid.uid='".$db->escape($uid)."';";
		$result=$db->query($sTakenquery);
		if (($result !== false) and $db->numRows($result) > 0) {
			while ($record = $db->next($result)) {
				//zoek ingeroosterde taak
				$takenlijst = array('kok', 'afwas', 'theedoek', 'klussen_zwaar', 'klussen_licht', 'schoonmaken_keuken', 'schoonmaken_afzuigkap', 'schoonmaken_frituur');
				foreach($takenlijst as $taak){
					if($record[$taak] == 1){
						$taakprops = array('maalid', 'datum', 'tekst', 'type', 'punten_toegekend', 'type');
						$ataak = array_get_keys($record, $taakprops);
						$ataak['taak'] = str_replace('_', ' ', $taak);
						$ataak['punten'] = $record['punten_'.$taak];
						$taken[] = $ataak;
					}
					if(!isset($return['lid'])){
						$lidprops = array('uid', 'corvee_punten_totaal', 'corvee_punten_bonus', 'corvee_vrijstelling');
						$return['lid'] = array_get_keys($record, $lidprops);
					}
				}
			}
			//slaat gegevens op
			$return['taken']=$taken;
			$return['aantal']=count($taken);
			return $return;
		}else{
			return $return;
		}
	}
	# haalt de lijst met leden op die voor een taak ingedeeld kunnen worden
	function getTaakLeden(){
		$zoekLeden = Zoeker::zoekLeden('', 'uid', 'alle', 'achternaam', array('S_LID', 'S_NOVIET', 'S_GASTLID'), array('uid', 'achternaam', 'voornaam', 'tussenvoegsel', 'corvee_punten'));
		
		//Todo: |csrnaam gebruiken
		$taakleden = array('' => '- Geen -');
		foreach ($zoekLeden as $lid) {
			$naam = $lid['achternaam'].', '.$lid['voornaam'];
			if($lid['tussenvoegsel'] != '')
				$naam .= ' '.$lid['tussenvoegsel'];
				
			$taakleden[$lid['uid']] = $naam.' ('.$lid['corvee_punten'].')';	
		}
		return $taakleden;
	}
	
	#haalt de lijst met leden op, en filtert deze op hun corveewensen en kwalikok zijn. Gesorteerd op prognosepunten en dan achternaam.
	function getTaakLedenGefilterd($taak, $dag='', $puntentekort=0){		
		// Zet het filter op 
		//(Kl Li, Kl Zw, Do Kok, Do Afw, Theedoek, Afzuigk, Frituur, Keuken, Puntentekort)
		
		// Op dag
		$dagfilter = bindec('111111110');
		if(in_array($taak,array('kwalikok', 'kok', 'afwas'))){
			switch($dag){
				case 'Thu':
					$dagfilter = bindec('001100000');
				break;
			}
		}
		// Op taak
		$taakfilter = bindec('111111110');
		switch($taak){
			case 'lichteklus':
				$taakfilter = bindec('100000000');
			break;
			case 'zwareklus':
				$taakfilter = bindec('010000000');
			break;
			case 'kwalikok':
			case 'kok':
				$taakfilter = bindec('001000000');
			break;
			case 'afwas':
				$taakfilter = bindec('000100000');
			break;
			case 'theedoek':
				$taakfilter = bindec('000010000');
			break;
			case 'frituur':
				$taakfilter = bindec('000000100');
			break;
			case 'afzuigkap':
				$taakfilter = bindec('000001000');
			break;
			case 'keuken':
				$taakfilter = bindec('000000010');
			break;
		}
		
		//combineren van dag en taak
		$filtervoorkeur = $taakfilter & $dagfilter;
		$filtervoorkeur += $puntentekort;
		
		//de ledenlijst ophalen
		$zoekLeden = Zoeker::zoekLeden('', 'uid', 'alle', 'achternaam', array('S_LID', 'S_NOVIET', 'S_GASTLID') , array('uid', 'achternaam', 'voornaam', 'tussenvoegsel', 'corvee_kwalikok', 'corvee_voorkeuren', 'corvee_vrijstelling', 'corvee_punten'));
		
		//Voorbewerken van de lijst: puntentekort berekenen, kwalikoks selecteren
		$zoekLeden_gefilterd = array();
		foreach ($zoekLeden as $lid){
			$heefttekort = ($this->corveepunten - ceil($this->corveepunten*.01*$lid['corvee_vrijstelling'])-$lid['corvee_punten']-$lid['corvee_punten_bonus']) > 0 ? 1 : 0;			
			$eigenvoorkeur= bindec($lid['corvee_voorkeuren'] . $heefttekort); //Totaal 8+1 = 9 bits lang
			
			if($taak == 'kwalikok' && $lid['corvee_kwalikok']==0){
				$eigenvoorkeur=bindec('000000000');
			}
			if($taak == 'kok' && $lid['corvee_kwalikok']==1){
				$eigenvoorkeur=bindec('000000000');
			}
			
			/* Combineer de eigen voorkeur met de gezochte taak*/
			$bitstring = ''.decbin($eigenvoorkeur & $filtervoorkeur);
			// Tel het aantal enen
			if ((count(explode('1',$bitstring))-1) >= 1){
				$zoekLeden_gefilterd[$lid['uid']]=$lid;
			}			
		}

		//lijst met prognosepunten
		$prognoselijst = $this->getPuntenlijst('corvee_prognose', 'desc');

		//lijst met de volgorde van prognoselijst, maar met de entries van zoekLeden_gefilterd en >99%vrijstellingen gefilterd
		$taakleden = array('' => '- Geen -');
		foreach($prognoselijst as $index=>$entry){
			//lid zit in gefilterde leden?
			if(isset($zoekLeden_gefilterd[$entry['uid']])){
				$lid = $zoekLeden_gefilterd[$entry['uid']];
				//lid heeft minder dan 100% vrijstelling?
				if($lid['corvee_vrijstelling']<100){
					$naam = $lid['achternaam'].', '.$lid['voornaam'];
					if($lid['tussenvoegsel'] != ''){
						$naam .= ' '.$lid['tussenvoegsel'];
					}
					$taakleden[$lid['uid']] = $naam.' ('.$entry['corvee_prognose'].')';	
				}
			}
		}
		return $taakleden;
	}

	/*
	 * 
	 */
	public function getAlleTaken($datum, $puntentoekenningsstatus='onbekend'){
		//alleen bij toekenstatus onbekend/ja/nee een filter toevoegen, anders alle taken zoeken
		$where = "";
		if ($puntentoekenningsstatus!==null AND in_array($puntentoekenningsstatus, array('onbekend', 'ja','nee'))){
			$where = "AND punten_toegekend = '".$puntentoekenningsstatus."'";
		}
		$db=MySql::instance();
		$sTakenQuery="
			SELECT maalid, uid, punten_toegekend, datum, tekst, type
			FROM maaltijdcorvee
			LEFT JOIN maaltijd ON (maaltijd.id=maaltijdcorvee.maalid)
			WHERE maaltijd.datum <= UNIX_TIMESTAMP('".$datum." 23:59:59') ".$where."
			ORDER BY datum, maalid;";

		$result=$db->query($sTakenQuery);
		if (($result !== false) and $db->numRows($result) > 0) {
			$corveedag['maalid']=null;
			$dagprops = array('maalid', 'datum', 'tekst', 'type');
			$corveeerprops = array('uid','punten_toegekend');

			while ($corveeer = $db->next($result)) {
				if($corveedag['maalid']===null){
					//eerste loop
					$corveedag = array_get_keys($corveeer, $dagprops);
				}
				if($corveeer['maalid']!=$corveedag['maalid']){
					//nieuwe corveedag: vorige opslaan en naar de nieuwe
					
					$corveedag['corveeers']=$corveeers;
					$corveeers=array();
					$corveedagen[$corveedag['maalid']] = $corveedag;
					$corveedag = array_get_keys($corveeer, $dagprops);
				}
				$corveeers[]= array_get_keys($corveeer, $corveeerprops);
			}
			//na laatste loop
			$corveedag['corveeers']=$corveeers;
			$corveedagen[$corveedag['maalid']] = $corveedag;
			return $corveedagen;
		}else{
			return array();
		}
	}

	# haalt één enkele maaltijd op ter bewerking
	function getPuntenlijst($sorteer = 'corvee_tekort', $sorteer_richting = 'asc'){
		$sorteer_toegestaan = array('achternaam', 'kok', 'afwas', 'theedoek', 'schoonmaken_frituur', 'schoonmaken_afzuigkap', 'schoonmaken_keuken', 'klussen_licht', 'klussen_zwaar', 'corvee_kwalikok', 'corvee_punten', 'corvee_punten_bonus', 'corvee_vrijstelling', 'corvee_prognose', 'corvee_tekort');
		$sorteer_volgorde_toegestaan = array('asc', 'desc');
		if (!in_array($sorteer, $sorteer_toegestaan) || !in_array($sorteer_richting, $sorteer_volgorde_toegestaan))
			print('Ongeldige sorteeroptie');

		$startdatumpuntentelling = Corveeinstellingen::get('startpuntentelling');
		$sLedenQuery="
			SELECT
				lid.uid
				, corvee_kwalikok, corvee_punten, corvee_punten_bonus, corvee_vrijstelling, corvee_voorkeuren
				, (".(int)$this->corveepunten."-CEIL(".(int)$this->corveepunten."*.01*corvee_vrijstelling)-corvee_punten_bonus-corvee_punten)
				  - IFNULL(SUM(
					punten_kok*IF(punten_toegekend = 'onbekend',kok,0)
					+ punten_afwas*IF(punten_toegekend = 'onbekend',afwas,0)
					+ punten_theedoek*IF(punten_toegekend = 'onbekend',theedoek,0)
					+ punten_schoonmaken_frituur*IF(punten_toegekend = 'onbekend',maaltijdcorvee.schoonmaken_frituur,0)
					+ punten_schoonmaken_afzuigkap*IF(punten_toegekend = 'onbekend',maaltijdcorvee.schoonmaken_afzuigkap,0)
					+ punten_schoonmaken_keuken*IF(punten_toegekend = 'onbekend',maaltijdcorvee.schoonmaken_keuken,0)
					+ punten_klussen_licht*IF(punten_toegekend = 'onbekend',maaltijdcorvee.klussen_licht,0)
					+ punten_klussen_zwaar*IF(punten_toegekend = 'onbekend',maaltijdcorvee.klussen_zwaar,0)
					), 0) AS corvee_prognose				
				, IFNULL(SUM(kok*punten_kok DIV punten_kok ),0) AS kok
				, IFNULL(SUM(afwas*punten_afwas DIV punten_afwas),0) AS afwas
				, IFNULL(SUM(punten_theedoek*theedoek DIV punten_theedoek),0) AS theedoek
				, IFNULL(SUM(punten_schoonmaken_frituur*maaltijdcorvee.schoonmaken_frituur DIV punten_schoonmaken_frituur),0) AS schoonmaken_frituur
				, IFNULL(SUM(punten_schoonmaken_afzuigkap*maaltijdcorvee.schoonmaken_afzuigkap DIV punten_schoonmaken_afzuigkap),0) AS schoonmaken_afzuigkap
				, IFNULL(SUM(punten_schoonmaken_keuken*maaltijdcorvee.schoonmaken_keuken DIV punten_schoonmaken_keuken),0) AS schoonmaken_keuken
				, IFNULL(SUM(punten_klussen_licht*maaltijdcorvee.klussen_licht DIV punten_klussen_licht),0) AS klussen_licht
				, IFNULL(SUM(punten_klussen_zwaar*maaltijdcorvee.klussen_zwaar DIV punten_klussen_zwaar),0) AS klussen_zwaar
				, (".(int)$this->corveepunten."-CEIL(".(int)$this->corveepunten."*.01*corvee_vrijstelling)-corvee_punten_bonus-corvee_punten) AS corvee_tekort
			FROM
				lid
			LEFT JOIN
				maaltijdcorvee
			ON
				maaltijdcorvee.uid = lid.uid
			LEFT JOIN
				maaltijd
			ON
				maaltijd.id = maaltijdcorvee.maalid
			AND
				maaltijd.datum > UNIX_TIMESTAMP('".$startdatumpuntentelling." 00:00:00')
			WHERE
				status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET'  
			GROUP BY
				lid.uid
			ORDER BY
				".$sorteer." ".strtoupper($sorteer_richting).", achternaam, uid ASC";
		$rLeden=$this->_db->query($sLedenQuery);
		$aLeden=$this->_db->result2array($rLeden);
		
		// rood naar geel naar groen
		foreach($aLeden as &$rLid) {
			$rLid['corvee_tekort_rgb'] = $this->rgbCalculate($rLid['corvee_tekort']);
			$rLid['corvee_prognose_rgb'] = $this->rgbCalculate($rLid['corvee_prognose']);
		}

		return $aLeden;
	}

	# rgb overgang berekenen
	function rgbCalculate($lPunten)		
	{
			$kleur_start = 8;
			$tekort_offset = 2;
			
			$rRaw = 2 * (($lPunten/($this->corveepunten+$tekort_offset)));	// waarde tussen 0 en 1
			$gRaw = 2 * (1-$lPunten/($this->corveepunten+$tekort_offset));	// waarde tussen 0 en 1
			
			if ($rRaw > 0 && $rRaw < 0.5) $rRaw = 0.5; // verschil tussen 0 en 1 tekort wat duidelijker maken
			if ($rRaw < 0) $rRaw = 0; if ($rRaw > 1) $rRaw = 1;
			if ($gRaw < 0) $gRaw = 0; if ($gRaw > 1) $gRaw = 1;
			
			$r = $kleur_start + round($rRaw * (15-1-$kleur_start));
			$g = $kleur_start + round($gRaw * (15-1-$kleur_start));
			$b = $kleur_start;
						
			return dechex($r).dechex($g).dechex($b);
	}

	# haalt alle leden met maaltijdabo's op met hun abo
	function getLedenAbo(){
		$sAboQuery="
			SELECT lid.uid AS uid, lid.achternaam, lid.lidjaar, lid.status, lid.verticale, lid.kring, verticale.naam AS verticalenaam,
				GROUP_CONCAT( 
					maaltijdabo.abosoort
					ORDER BY maaltijdabo.abosoort
					SEPARATOR ', ' 
				) AS abosoort
			FROM lid
			LEFT JOIN maaltijdabo ON ( lid.uid = maaltijdabo.uid )
			LEFT JOIN verticale ON ( lid.verticale = verticale.id )
			WHERE 
				lid.status IN('S_LID','S_GASTLID','S_NOVIET') 
				OR (lid.status NOT IN('S_LID','S_GASTLID','S_NOVIET') AND abosoort IS NOT NULL)
			GROUP BY lid.uid
			ORDER BY lid.achternaam, lid.voornaam ASC
		";
		$rLeden=$this->_db->query($sAboQuery);
		$aLeden=$this->_db->result2array($rLeden);
		foreach($aLeden as &$rLid) {
			$abos = array('donderdag'=>0,'verticale'=>0,'verticaleabonaam'=>'A_VERT'.$rLid['verticale']);
			if($rLid['abosoort']!==NULL){
				$lidabos = explode(', ', $rLid['abosoort']);
				foreach($lidabos as $lidabo){
					switch($lidabo){
						case 'A_DONDERDAG':
							$abos['donderdag'] = 1;
						break;
						default:
							if(substr($lidabo, 0, 6)=='A_VERT'){
								$abos['verticaleabonaam']=$lidabo;
								$abos['verticale'] = 1;
							}
					}
				}
			}
			$rLid['abos'] = $abos;
		}
		return $aLeden;
	}

	# haalt maaltijden uit de maaltijdentabel op, voor uitgebreidere info
	# voor in de kolommen op de maaltijdencontent pagina, zie getMaaltijden hieronder
	# als de gebruiker uit moot 1-4 is, hou daar dan rekening mee
	# deze functionaliteit kan uitgezet worden door $mootfilter = false te zetten als argument
	public static function getMaaltijdenRaw($van = 0, $tot = 0, $mootfilter = true, $corveefilter = true) {
		$lid=LoginLid::instance();
		$db=MySql::instance();
		# kijk in db en haal alle maaltijden op waarbij de begintijd
		# na $van is, en voor $tot

		# meestal zal voor $van time() gebruikt worden, als er niets is opgegeven
		# dan wordt ook de huidige tijd gebruikt
		if ($van == 0) $van = time();

		# als $tot niet is opgegeven, of 0 is, dan worden alle maaltijden vanaf
		# van teruggegeven, gesorteerd op tijd
		$tot = (int)$tot;
		$totsql = ($tot != 0) ? "datum < '".$tot."'" : "1";

		# mootfilter
		if(!$lid->hasPermission('P_MAAL_MOD')){
			if($mootfilter === true){
				$moot = $lid->getLid()->getVerticaleID();
			}
		}else{
			$mootfilter=false;
		}
		
		# corveefilter - filtert speciale "dummy" maaltijden bedoelt voor corvee/schoonmaak
		if ($corveefilter){
			$corveevelden = "";
			$corveefilter = "AND type = 'normaal'";
		} else {
			$corveevelden = ", schoonmaken_frituur, schoonmaken_afzuigkap, schoonmaken_keuken, klussen_licht, klussen_zwaar, punten_schoonmaken_frituur, punten_schoonmaken_afzuigkap, punten_schoonmaken_keuken, punten_klussen_licht, punten_klussen_zwaar,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND schoonmaken_frituur = 1) AS frituur_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND schoonmaken_afzuigkap = 1) AS afzuigkap_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND schoonmaken_keuken = 1) AS keuken_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND klussen_licht = 1) AS lichteklus_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND klussen_zwaar = 1) AS zwareklus_aangemeld
				";
			$corveefilter = "";
		}

		$maaltijden = array();
		$sMaaltijdQuery="
			SELECT
				id, datum, type, gesloten, tekst, abosoort, max, aantal, tp, koks, afwassers, theedoeken, punten_kok, punten_afwas, punten_theedoek, corvee_gemaild,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND kok = 1) AS koks_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND afwas = 1) AS afwassers_aangemeld,
				(SELECT COUNT(uid) FROM maaltijdcorvee WHERE maalid = id AND theedoek = 1) AS theedoeken_aangemeld,
				(SELECT COUNT(*)>0 FROM maaltijdcorvee WHERE maalid = id AND punten_toegekend = 'ja') AS is_toegekend
				".$corveevelden."
			FROM
				maaltijd
			WHERE
				datum > '".$van."' AND ".$totsql." ".$corveefilter."
			ORDER BY
				datum ASC;";
		$result=$db->select($sMaaltijdQuery);
		if (($result !== false) and $db->numRows($result) > 0) {
			while ($record = $db->next($result)) {
				if(!($mootfilter===true AND preg_match("/(VERT)[^{$moot}]{1}/", $record['abosoort']))){
					$maaltijden[] = $record;

				}
			}
		}
		# id, datum, gesloten, tekst, abosoort, max, aantal, tp
		return $maaltijden;
	}

	function getAboTekst($abosoort = '') {
		# abotekst: tekst van het abo dat van toepassing is of ''
		if ($abosoort == '') return '';
		$result = $this->_db->select("SELECT tekst FROM maaltijdabosoort WHERE abosoort = '{$abosoort}'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$record = $this->_db->next($result);
			return $record['tekst'];
		} else return '';
	}

	# haalt maaltijden op en voegt extra info toe voor op de maaltijdenpagina
	function getMaaltijden($van = 0, $tot = 0, $mootfilter = true, $corveefilter = true, $uid=null, $alsArray=true, $koppelTaken=false) {
		if($uid==null){
			$uid=LoginLid::instance()->getUid();
		}
		if($uid == 'x999'){ $mootfilter = false; }

		$maaltijdenRaw = $this->getMaaltijdenRaw($van,$tot,$mootfilter,$corveefilter);

		$maaltijden=array();
		
		if($alsArray){
			foreach($maaltijdenRaw as $maaltijd){
				$maaltijd['abotekst'] = $this->getAboTekst($maaltijd['abosoort']);
	
				if($maaltijd['gesloten']=='1'){
					//als de maaltijd al gesloten is, dan uit de maaltijdgesloten-tabel ophalen.
					$sAanmeldingen="SELECT uid, gasten, gasten_opmerking FROM maaltijdgesloten WHERE uid = '".$uid."' AND maalid = ".$maaltijd['id'].";";
					$rAanmeldingen = $this->_db->query($sAanmeldingen);
					if (($rAanmeldingen !== false) and $this->_db->numRows($rAanmeldingen) > 0) {
						$maaltijd['status']='AAN';
					}else{
						$maaltijd['status']='';
					}
					# Gasten ophalen
					$record = $this->_db->next($rAanmeldingen);
					$maaltijd['gasten'] = $record['gasten'];
					$maaltijd['opmerking'] = $record['gasten_opmerking'];
				}else{
					# status: AAN,AF ABO ''
					# 1a. is er een aan of afmelding voor deze maaltijd?
					$sAanmeldingen="
						SELECT
							status, gasten, gasten_opmerking
						FROM
							maaltijdaanmelding
						WHERE
							uid = '".$uid."' AND maalid = ".$maaltijd['id'].";";
					$rAanmeldingen = $this->_db->query($sAanmeldingen);
					if (($rAanmeldingen !== false) and $this->_db->numRows($rAanmeldingen) > 0) {
						$record = $this->_db->next($rAanmeldingen);
						$maaltijd['status'] = $record['status'];
						# Gasten ophalen
						$maaltijd['gasten'] = $record['gasten'];
						$maaltijd['opmerking'] = $record['gasten_opmerking'];
					} else {
						# 1b. zo nee, is er een abo actief?
						$sAbo="SELECT uid FROM maaltijdabo WHERE uid = '".$uid."' AND abosoort = '".$maaltijd['abosoort']."'";
						$rAbo = $this->_db->query($sAbo);
						if(($rAbo !== false) and $this->_db->numRows($rAbo) > 0) {
							$record = $this->_db->next($rAbo);
							$maaltijd['status'] = 'ABO';
						}else{
							# 1c. zo ook nee, dan status = ''
							$maaltijd['status'] = '';
						}
					}
				}
				
				# Corvee voor user?
				$sCorvee="
					SELECT
						kok, afwas, theedoek
					FROM
						maaltijdcorvee
					WHERE
						uid = '".$uid."' AND maalid = ".$maaltijd['id'].";";
				$rCorvee = $this->_db->query($sCorvee);
				if (($rCorvee !== false) and $this->_db->numRows($rCorvee) > 0) {
					$record = $this->_db->next($rCorvee);
					$maaltijd['kok'] = $record['kok'];
					$maaltijd['afwas'] = $record['afwas'];
					$maaltijd['theedoek'] = $record['theedoek'];
				}

				# Koppel corveetaken aan array?
				if ($koppelTaken)
				{
					$maaltijdObject = new Maaltijd($maaltijd['id']);
					$maaltijd['taken'] = $maaltijdObject->getTaken();
				}
	
				# 2. actie is afhankelijk van status en evt. gesloten zijn van de maaltijd
				# actie: AAN, AF, ''
				if(($maaltijd['status']=='AAN' OR $maaltijd['status']=='ABO') AND $maaltijd['gesloten']=='0' ){
					$maaltijd['actie'] = 'af';
				}elseif(($maaltijd['status']=='AF' OR $maaltijd['status']=='') AND $maaltijd['aantal'] != $maaltijd['max'] AND $maaltijd['gesloten'] == '0' ){
					$maaltijd['actie'] = 'aan';
				}else{
					$maaltijd['actie'] = '';
				}
				$maaltijden[]=$maaltijd;
			}
		}else{
			foreach($maaltijdenRaw as $maaltijd){ 
				$maaltijden[] = new Maaltijd($maaltijd['id']);
			}
		}
		return $maaltijden;

	}

	# kijkt of er een maaltijd bestaat met deze maalid
	# te gebruiken alvorens een object maaltijd aan te maken
	function isMaaltijd($maalid) {
		if (!is_numeric($maalid)) {
			$this->_error = "De opgegeven maaltijd bestaat niet.";
			return false;
		}
		$result = $this->_db->select("SELECT * from `maaltijd` WHERE `id` = '$maalid'");
		if (($result === false) or $this->_db->numRows($result) == 0) {
			$this->_error = "De opgegeven maaltijd bestaat niet.";
			return false;
		}
		return true;
	}

	# wrapper-functie voor aanmelden, die controleert of de maaltijd wel bestaat
	# en om te zorgen dat foutmeldingen goed terugkomen in de pagina
	function aanmelden($maalid, $uid = '') {
		# isMaaltijd zet zelf een error als het nodig is
		if (!$this->isMaaltijd($maalid)) return false;
		$maaltijd = new Maaltijd($maalid);
		if (!$maaltijd->aanmelden($uid)) {
			$this->_error = $maaltijd->getError();
			$this->_proxyerror = $maaltijd->getProxyError();
			return false;
		}
		return true;
	}

	# wrapper-functie voor afmelden, die controleert of de maaltijd wel bestaat
	# en om te zorgen dat foutmeldingen goed terugkomen in de pagina
	function afmelden($maalid, $uid = '') {
		# isMaaltijd zet zelf een error als het nodig is
		if (!$this->isMaaltijd($maalid)) return false;
		$maaltijd = new Maaltijd($maalid);
		if (!$maaltijd->afmelden($uid)) {
			$this->_error = $maaltijd->getError();
			$this->_proxyerror = $maaltijd->getProxyError();
			return false;
		}
		return true;
	}

	# wrapper-functie voor gasten aanmelden, die controleert of de maaltijd wel bestaat
	# en om te zorgen dat foutmeldingen goed terugkomen in de pagina
	function gastenAanmelden($maalid, $gasten, $opmerking) {
		# isMaaltijd zet zelf een error als het nodig is
		if (!$this->isMaaltijd($maalid)) return false;
		$maaltijd = new Maaltijd($maalid);
		if (!$maaltijd->gastAanmelden($gasten, $opmerking)) {
			$this->_error = $maaltijd->getError();
			$this->_proxyerror = $maaltijd->getProxyError();
			return false;
		}
		return true;
	}

	# abo aanzetten voor huidige gebruiker
	function addAbo($abosoort,$uid=null) {
		if($uid==null){
			$uid = LoginLid::instance()->getUid();
		}
		# Kijk of deze abosoort geldig is voor deze persoon, en of we m aan kunnen zetten
		$geenabo = $this->getNotAboSoort($mootfilter=true,$uid);
		if (!array_key_exists($abosoort, $geenabo)) {
			$this->_error = "Er is een ongeldige abonnementsvorm opgegeven, of dit abo is al ingeschakeld voor u.";
			return false;
		}

		# abo toevoegen
		$result = $this->_db->query("INSERT INTO `maaltijdabo` (`uid`,`abosoort`) VALUES ('{$uid}','{$abosoort}')");

		# kijken of er maaltijden zijn in de toekomst met dit abo die VOL zijn, en daar AFmeldingen voor maken
		# bij de andere maaltijden met dit abo een recount doen
		$van = time();
		$result = $this->_db->query("SELECT * FROM maaltijd WHERE abosoort = '{$abosoort}' AND gesloten = '0'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$maaltijd = new Maaltijd ($record['id']);
				if ($record['aantal'] == $record['max']) $maaltijd->afmelden();
				else $maaltijd->recount();
				unset($maaltijd);
			}
		}

		return true;
	}

	# abo uitzetten voor huidige gebruiker
	function delAbo($abosoort,$uid=null) {
		if($uid==null){
			$uid = LoginLid::instance()->getUid();
		}
		# kijk of $abosoort voorkomt in de abo's van deze persoon
		$abos = $this->getAbo($uid);
		if (!array_key_exists($abosoort, $abos)) {
			$this->_error = "Er is een ongeldige abonnementsvorm opgegeven, of dit abo is niet ingeschakeld voor u.";
			return false;
		}

		# abo verwijderen
		$result = $this->_db->query("DELETE FROM `maaltijdabo` WHERE `uid` = '{$uid}' AND `abosoort` = '{$abosoort}'");

		# bij de maaltijden met dit abo een recount doen
		$van = time();
		$result = $this->_db->query("SELECT * FROM maaltijd WHERE abosoort = '{$abosoort}' AND gesloten = '0'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$maaltijd = new Maaltijd ($record['id']);
				# als gebruiker geen expliciete AAN of AF heeft kan het aantal inschrijvingen
				# veranderen doordat zijn abo's veranderen
				if ($maaltijd->getStatus($uid) == 'AUTO') $maaltijd->recount();
				unset($maaltijd);
			}
		}
		return true;
	}

	# abo's opvragen voor huidige gebruiker
	public function getAbo($uid=null) {
		$abos = array();
		if($uid==null){
			$uid = LoginLid::instance()->getUid();
		}
		$qAbo="
			SELECT maaltijdabosoort.abosoort, maaltijdabosoort.tekst
			FROM maaltijdabo, maaltijdabosoort
			WHERE maaltijdabo.abosoort = maaltijdabosoort.abosoort
				AND maaltijdabo.uid = '".$uid."';";
		$rAbo=$this->_db->query($qAbo);
		if (($rAbo !== false) and $this->_db->numRows($rAbo) > 0) {
			while ($record = $this->_db->next($rAbo)){
				$abos[$record['abosoort']] = $record['tekst'];
			}
		}
		return $abos;
	}
	# alle abo's opvragen, ook het 'Geen' abo...
	# deze functie wordt gebruikt om het soort abo te kunnen kiezen bij maaltijdenbeheer
	function getAbos() {
		$abos = array();
		$result = $this->_db->select("
			SELECT
				maaltijdabosoort.abosoort AS abosoort,
				maaltijdabosoort.tekst AS tekst
			FROM
				maaltijdabosoort;");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$abos[$record['abosoort']] = $record['tekst'];
			}
		}
		return $abos;
	}
	# Controleer of het gegeven abonnement wel bestaat.
	function isValidAbo($abo){ return $abo != 'A_GEEN' and array_key_exists($abo, $this->getAbos()); }

	# alle abosoorten opvragen, als deze gebruiker uit moot 1-4 is, hou daar dan rekening mee
	# deze functionaliteit kan uitgezet worden door $mootfilter = false te zetten als argument
	# het 'Geen' abonnement wordt hier uitgefilterd
	function getAboSoort($mootfilter = true, $uid=null) {
		$abos = array();
		if($mootfilter === true){
			if($uid==null){
				$uid = LoginLid::instance()->getUid();
			}
			$lid=new Lid($uid);
			$moot = $lid->getVerticaleID();
		}
		$result = $this->_db->select("SELECT * FROM maaltijdabosoort WHERE NOT abosoort='A_GEEN'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				if ($mootfilter === true and preg_match("/VERT[^{$moot}]{1}/", $record['abosoort'])) continue;
				$abos[$record['abosoort']] = $record['tekst'];
			}
		}
		return $abos;
	}

	# alle abosoorten die de ingelogde gebruiker *niet* heeft aanstaan
	function getNotAboSoort($mootfilter = true, $uid=null) {
		if($uid==null){
			$uid = LoginLid::instance()->getUid();
		}
		$abos = $this->getAbo($uid);
		$abosoorten = $this->getAboSoort($mootfilter, $uid);
		return array_diff_key($abosoorten, $abos);
	}

	# array van uids/namen maken die (behalve zichzelf) door de ingelogde persoon zijn aangemeld voor deze maaltijd
	function getProxyAanmeldingen($uid, $maalid) {
		$wienogmeer = array();
		$result = $this->_db->select("
			SELECT uid
			FROM maaltijdaanmelding
			WHERE maalid = {$maalid} AND door = '{$uid}' AND uid <> door AND status = 'AAN'
		");
		if (($result !== false) and $this->_db->numRows($result) > 0)
			while ($record = $this->_db->next($result)){
				$wienogmeer[$record['uid']] = (string)LidCache::getLid($record['uid']);
			}
		return $wienogmeer;
	}

	function getAboCount ($abosoort) {
		$abosoort = $this->_db->escape($abosoort);
		$result = $this->_db->select("SELECT uid FROM maaltijdabo WHERE abosoort = '{$abosoort}'");
		if ($result !== false) return $this->_db->numRows($result);
		return 0;
	}
	
	# corvee automailer
	function corveeAutoMailer($debugMode = 0, $debugAddr = 'pubcie@csrdelft.nl', $monthMailing=false)
	{
		// Debug, als enabled dan worden alle emails naar het debugAddr gestuurd, en word maaltijd niet gemarkeerd als gemaild
		
		// get maaltijden waar datum < deze week/maand
		if($monthMailing){
			$van = 0;
			$tot = time()+86400*7*5;
		}else{
			$van = 0;
			$tot = time()+86400*7;
		}
		$maaltijden = $this->getMaaltijdenRaw($van, $tot, true, false);
		
		// mail headers
		setlocale(LC_ALL, 'nl_NL');
		$headers = "From: noreply@csrdelft.nl\r\n";
		$headers .= "Reply-To: corvee@csrdelft.nl\r\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= "X-Mailer: csrdelft.nl/PubCie"."\r\n";
							
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'MAC')
			$headers = str_replace("\r\n", "\r", $headers);
		else if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN')
			$headers = str_replace("\r\n", "\n", $headers);
		
		// start
		echo '<pre>';
		$output = "CORVEE-AUTOMAILER: ".date('r')."\n";
		if ($debugMode)
			$output .= "DEBUG MODE: Mails gaan naar ".$debugAddr.", maaltijden worden niet gemarkeerd als gemaild\n";
		$output .= "Start\n";
		if($monthMailing){
			$output .= "Maaltijden ophalen binnen komende 35 dagen..\n";
		}else{
			$output .= "Maaltijden ophalen binnen komende 7 dagen..\n";
		}
		foreach($maaltijden as $maaltijd)
		{
			$output .= "- Maaltijd van ".strftime('%d-%m-%Y', $maaltijd['datum'])." (ID ".$maaltijd['id']."): ";
			if(($monthMailing AND $maaltijd['corvee_gemaild']>0) OR (!$monthMailing AND $maaltijd['corvee_gemaild']==2)){
				$output .= "Reeds gemaild.";
			}else{
				$lMaaltijd = new Maaltijd($maaltijd['id']);

				// taken ophalen
				$taken = $lMaaltijd->getTaken();
				// tafelpraeses toevoegen
				if($tp = $lMaaltijd->getTP()){
					$taken['tp'][] = $tp;
				}
				$teller = array();
				$foutTeller = array();

				print_r($taken);

				$takenTemplates = array(
					'afwassers' => 'afwas',
					'koks' => 'koks',
					'theedoeken' => 'theedoeken',
					'schoonmaken_frituur' => 'frituur',
					'schoonmaken_afzuigkap' => 'afzuigkap',
					'schoonmaken_keuken' => 'keuken',
					'klussen_licht' => 'lichteklus',
					'klussen_zwaar' => 'zwareklus',
					'tp' => 'tafelp'
				);
				
				foreach($taken as $taak => $leden)
				{
					$templateid = (isset($takenTemplates[$taak]) ? $takenTemplates[$taak] : null);
					if (!$templateid) continue;

					// mailen
					if($templateid=='tafelp'){
						$onderwerp = 'C.S.R. Delft Tafelpraeses - ';
						$headers = str_replace("corvee@csrdelft.nl", "maalcie@csrdelft.nl", $headers);
					}else{
						$onderwerp = 'C.S.R. Delft Corvee - ';
						$headers = str_replace("maalcie@csrdelft.nl", "corvee@csrdelft.nl", $headers);
					}
					$onderwerp .= strftime('%d-%m-%Y', $maaltijd['datum']);

					foreach($leden as $uid) {
						$to = ($debugMode ? $debugAddr : $uid.'@csrdelft.nl');

						$lid = LidCache::getLid($uid);

						// persoonlijk mailen (muv Jan Lid (x101))
						if($uid !='x101'){ 
							if ($lid != null ) {

								//mededeling over mee-eten inelkaar zetten
								$meeeten = '';
								if(in_array($taak, array('afwassers', 'koks', 'theedoeken','tp'))){
									$meeeten='U komt bij de maaltijd : ';
									$status=$lMaaltijd->getStatus($uid);
									switch($status){
										case 'AAN':
											$meeeten.='eten';
										break;
										case 'AUTO':
											if($lMaaltijd->heeftAbo($uid)){
												$meeeten.='eten (abo)';
												$status='AAN';
												break;
											}
										case 'AF':
										default:
											$meeeten.='niet eten';
									}
								}

								require_once 'maaltijden/corveeinstellingen.class.php';
								$template = Corveeinstellingen::get($templateid);
								$lidnaam = $lid->getNaamLink('civitas');
								$datum = strftime('%d-%m-%Y (%A)', $maaltijd['datum']);

								$bericht = htmlspecialchars(str_replace(array('LIDNAAM', 'DATUM', 'MEEETEN'), array($lidnaam, $datum, $meeeten), $template));

								if (mail($to, $onderwerp, $bericht, $headers))
									$teller[] = $lid->getNaam().' ('.$uid.')';
								else
									$foutTeller[] = $lid->getNaam().' ('.$uid.')';
							} else {
								$foutTeller[] = 'Onbekend lid met id '.$uid;
							}
						}
					}
				}
				$output .= "Mensen gemaild: ".count($teller)." (".implode($teller, ", ").")";
				if (count($foutTeller))
					$output .= "\n  FOUT: Mensen NIET gemaild: ".count($foutTeller)." (".implode($foutTeller, ", ").")";
				
				// update corvee_gemaild
				if (!$debugMode) {
					$query = "UPDATE maaltijd SET corvee_gemaild = corvee_gemaild + 1 WHERE id=".$maaltijd['id']."";
					if(!$this->_db->query($query))
						$output .= "FOUT: Er is iets mis met de database/query.";
				}
			}
			$output .= "\n";
		}
		$output .= "Klaar!\n";
		
		echo $output;
		echo '</pre>';
		
		// rapport voor beheerder
		$to = ($debugMode ? $debugAddr : 'corvee@csrdelft.nl');
		$headers = str_replace("corvee@csrdelft.nl", "maalcie@csrdelft.nl", $headers); //kan corveekeizer direct naar maalcieP mailen
		mail($to, 'Corvee-Automailer: Rapport', $output, $headers);
	}

}

?>
