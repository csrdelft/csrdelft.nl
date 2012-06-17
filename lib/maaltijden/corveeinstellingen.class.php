<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/corveeinstellingen.class.php
# -------------------------------------------------------------------
# Deze klasse verwerkt instellingen voor corvee.
# -------------------------------------------------------------------

require_once 'formulier.class.php';

class Corveeinstellingen{
	private $error = '';
	private $instellingen;
	private $instellingForm = array();


	//laad instellingen in object Corveeinstellingen en maakt formulierobjecten
	public function __construct(){
		$db=MySql::instance();
		$query="
			SELECT instelling, type, tekst, datum, `int`
			FROM maaltijdcorveeinstellingen";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			while($instelling=$db->next($result)){
				$this->array2instelling($instelling);
			}
		}else{
			$this->error .= mysql_error();	
		}

		//formulier objecten maken
		$this->assignInstellingenForm();
	}

	/*
	 * geeft waarde van $instelling terug
	 * @paramter string $instelling naam v. instelling
	 * @return (tekst,datum of int) waarde zoals opgeslagen in Corveeinstellingen 
	 */
	private function getValue($instelling){
		return $this->instellingen[$instelling][2];
	}
	/*
	 * zoekt waarde van instelling en geeft die
	 * @return (tekst,datum of int) waarde zoals opgeslagen in db 
	 */
	public static function get($instelling){
		$db=MySql::instance();
		$query="
			SELECT instelling, type, tekst, datum, `int`
			FROM maaltijdcorveeinstellingen
			WHERE instelling='".$db->escape($instelling)."'
			LIMIT 1;";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			$ainstelling=$db->next($result);
			return $ainstelling[$ainstelling['type']];
		}else{
			throw new Exception('Instelling '.$instelling.' niet gevonden. Corveeinstelingen::get()'.mysql_error());
		}
	}
	/*
	 * slaat waarde op in dit object
	 * @parameters 	string $instelling naam instelling, 
	 * 				$value datum, int of text
	 * @return void 
	 */
	public function set($instelling, $value){
		$this->instellingen[$instelling][2]= $value;
	}
	/*
	 * slaat gegeven array op in dit object
	 * @parameters	array $instelling 
	 * @return void
	 */
	public function array2instelling($instelling){
		$this->instellingen[$instelling['instelling']]= array(
			$instelling['instelling'], 
			$instelling['type'], 
			$instelling[$instelling['type']]
		);
	}

	// @return error string
	public function getError(){
		return $this->error;
	}

	/*
	 * formulier objecten maken
	 * @return void
	 */
	public function assignInstellingenForm(){
		$instellingForm[] = new Comment('Corvee- en Maaltijdbeheerpagina - weergegeven periode');
		$instellingForm[] = new DatumField('periodebegin',$this->getValue('periodebegin') , 'Begin periode',2020);
		$instellingForm[] = new DatumField('periodeeind',$this->getValue('periodeeind') , 'Einde periode',2020);

		$instellingForm[] = new Comment('Corveerooster - weergegeven periode');
		$instellingForm[] = new DatumField('roosterbegin',$this->getValue('roosterbegin') , 'Begin (Alleen MaalCie)',2020);
		$instellingForm[] = new DatumField('roostereind',$this->getValue('roostereind') , 'Einde periode',2020);

		$instellingForm[] = new Comment('Corveepunten');
		$instellingForm[] = new IntField('puntentotaal',$this->getValue('puntentotaal') , 'Totaal per jaar', 30, 0); 
		$instellingForm[] = new IntField('puntenkwalikoken',$this->getValue('puntenkwalikoken') , 'Kwalikoken', 30, 0); 
		$instellingForm[] = new IntField('puntenkoken',$this->getValue('puntenkoken') , 'Koken', 30, 0); 
		$instellingForm[] = new IntField('puntenafwas',$this->getValue('puntenafwas') , 'Afwas', 30, 0); 
		$instellingForm[] = new IntField('puntentheedoek',$this->getValue('puntentheedoek') , 'Theedoeken', 30, 0); 
		$instellingForm[] = new IntField('puntenafzuigkap',$this->getValue('puntenafzuigkap') , 'Afzuigkapschoonmaken', 30, 0); 
		$instellingForm[] = new IntField('puntenfrituur',$this->getValue('puntenfrituur') , 'Frituurschoonmaken', 30, 0); 
		$instellingForm[] = new IntField('puntenkeuken',$this->getValue('puntenkeuken') , 'Keukenschoonmaken', 30, 0); 
		$instellingForm[] = new IntField('puntenlichteklus',$this->getValue('puntenlichteklus') , 'Klussen (Licht)', 30, 0); 
		$instellingForm[] = new IntField('puntenzwareklus',$this->getValue('puntenzwareklus') , 'Klussen (Zwaar)', 30, 0); 
		$instellingForm[] = new DatumField('startpuntentelling',$this->getValue('startpuntentelling') , 'Start puntentelling',2020);

		$instellingForm[] = new Comment('E-mails voor automailer');
		$instellingForm[] = new TextField('koks',$this->getValue('koks') , 'Kwali-/gewone koks 
		Toegestane variabelen: LIDNAAM, DATUM, MEEETEN,KWALIAFWASSER');
		$instellingForm[] = new TextField('afwas',$this->getValue('afwas') , 'Afwassers');
		$instellingForm[] = new TextField('theedoeken',$this->getValue('theedoeken') , 'Theedoekwassers');
		$instellingForm[] = new TextField('afzuigkap',$this->getValue('afzuigkap') , 'Afzuigkapschoonmakers');
		$instellingForm[] = new TextField('frituur',$this->getValue('frituur') , 'Frituurschoonmakers');
		$instellingForm[] = new TextField('keuken',$this->getValue('keuken') , 'Keukenschoonmakers');
		$instellingForm[] = new TextField('lichteklus',$this->getValue('lichteklus') , 'Klussers (Licht)');
		$instellingForm[] = new TextField('zwareklus',$this->getValue('zwareklus') , 'Klussers (Zwaar)');
		$instellingForm[] = new TextField('tafelp',$this->getValue('tafelp') , 'Tafelpraeses');
		
		$this->instellingForm=$instellingForm;
	}

	/*
	 * Geeft objecten van het formulier terug
	 * @return array met FormField objecten
	 */
	public function getFields(){ 
		return $this->instellingForm;
	}
	
	/*
	 * Controleren of de velden van formulier zijn gePOST
	 * @return bool succes/mislukt
	 */
	public function isPostedFields(){
		$posted=false;
		foreach($this->getFields() as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}
	/*
	 * Controleren of de velden van formulier correct zijn
	 * @return bool succes/mislukt
	 */
	public function validFields(){
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid=true;
		foreach($this->getFields() as $field){
			//we checken alleen de formfields, niet de comments enzo.
			if($field instanceof FormField AND !$field->valid()){
				$valid=false;
				$this->error .= 'Een veld heeft geen geldige input';
			}
		}
		return $valid;
	}
	/*
	 * Slaat de velden van formulier op
	 * @return bool succes/mislukt
	 */
	public function saveFields(){
		//object vullen
		foreach($this->getFields() as $field){
			if($field instanceof FormField){
				$this->set($field->getName(), $field->getValue());
			}
		}

		if($this->save()){
			return true;
		}

		return false;
	}

	/*
	 * Slaat dit object op in db
	 * @return bool succes/mislukt
	 */
	private function save(){
		$db=MySql::instance();

		$values = array();
		foreach($this->instellingen as $key => $instelling){
			$data=array('tekst'=>"", 'datum'=>"0000-00-00", 'int'=>0);
			$data[$instelling[1]] = $db->escape($instelling[2]);

			$values[] = "('".$instelling[0]."', '".$instelling[1]."', '".$data['tekst']."', '".$data['datum']."', '".$data['int']."')";
		}
		$qSave="
			REPLACE INTO maaltijdcorveeinstellingen (
				instelling, type, tekst, datum, `int`
			)VALUES
				".implode(', ', $values).";";

		if($db->query($qSave)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Corveeinstellingen::save()';
		return false;
	}
}

/*
 * Zorgt voor de reset van het corveejaar
 * 
 * Reset omvat: 
 *  DISABLED- Alle corveetaken t/m datum worden verwijderd.
 *  - Hertelling: NieuwPuntentotaal = Corveepunten + bonus + ceil(teBehalenCorveepunten * %Vrijstelling) - teBehalenCorveepunten.
 *  - Bonus op nul zetten.
 */
class CorveeResetter {
	private $datum = null;
	private $melding = '';

	public function setDatum($datum){	$this->datum = $datum;}
	public function getDatum(){			return $this->datum;}
	public function getMelding(){		return $this->melding;}

	public function verwijderCorveetaken(){
		if($this->datum!==null){
			/* $db=MySql::instance();
			$sTakenDeleteQuery = "
				DELETE 
				FROM `maaltijdcorvee` 
				WHERE maalid = 
				(
					SELECT id 
					FROM maaltijd
					WHERE maaltijdcorvee.maalid = maaltijd.id 
						AND datum < UNIX_TIMESTAMP('".$this->datum." 23:59:59') 
				);";
			if($db->query($sTakenDeleteQuery)){
				return true;
			}else{
				$this->melding .= 'Taken verwijderen mislukt. '.mysql_error();
				return false;
			}*/
			setMelding('Taken verwijderen is uitgeschakeld.', 0);
			return true;
		}else{
			$this->melding.='Geen datum. Geen taken verwijderd.';
			return false;
		}
	}

	public function resetCorveeJaar(){
		if($this->datum!==null){
			$db=MySql::instance();
			$totaalpunten = Corveeinstellingen::get('puntentotaal');
			$sCorveepuntenUpdateQuery = "
				UPDATE 
					lid l1, 
					(
						SELECT (corvee_punten_bonus+corvee_punten-".$totaalpunten."+CEIL(".$totaalpunten."*.01*corvee_vrijstelling)) AS corvee_punten_nieuw, uid
						FROM lid 
						WHERE status = 'S_LID' OR status = 'S_GASTLID'
					) AS l2
				SET  
					l1.corvee_punten =  l2.corvee_punten_nieuw,
					l1.corvee_punten_bonus = 0
				WHERE 
					l1.uid=l2.uid 
					AND (status = 'S_LID' OR status = 'S_GASTLID')";
			if($db->query($sCorveepuntenUpdateQuery)){
				return true;
			}else{
				$this->melding.='Corvee- en bonuspunten bijwerken mislukt. '.mysql_error();
				return false;
			}
		}else{
			$this->melding.='Geen datum. Reset is niet gestart. ';
			return false;
		}
	}
}

?>
