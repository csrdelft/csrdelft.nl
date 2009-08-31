<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.motencontent.php
# -------------------------------------------------------------------
# Beeldt de moot/kringindeling af
# -------------------------------------------------------------------


class MotenContent extends SimpleHTML {

	### private ###

	var $_kringen;
	var $_bEmail=false;


	### public ###

	public function __construct(){
		$this->_kringen=Moot::getKringen();
		//kijken of er email-adressen getoond moeten worden
		if(isset($_GET['email'])){ $this->_bEmail=true; }
	}
	function getTitel(){
		return 'Moot- en kringindeling';
	}
	function printKring($moot, $kring){
		echo '<td id="kring'.$moot.'.'.$kring.'leden">';
			if(!isset($this->_kringen[$moot][$kring])){
				echo '&nbsp;';
			}else{
				$kringsaldo=0;
				foreach ($this->_kringen[$moot][$kring] as $kringlid) {
					$lid=LidCache::getLid($kringlid['uid']);

					if($kringlid['kringleider'] != 'n' or $kringlid['motebal']!=0) echo '<em>';
					echo $lid->getNaamLink('civitas', 'link');
					if ($kringlid['motebal']!='0') echo '&nbsp;O';
					if ($kringlid['status']=='S_KRINGEL') echo '&nbsp;~';
					echo "<br />\n";
					if ($kringlid['kringleider'] != 'n' OR $kringlid['motebal']!=0) echo "</em>";
				}
				if($this->_bEmail===true AND $kring!=0){
					echo '<p><strong>email-adressen:</strong><br/>';
					$first=true;
					foreach($this->_kringen[$moot][$kring] as $kringlid){
						if(!$first){ echo ', '; }else{ $first=false; }
						echo $kringlid['email'];
					}
					echo '</p>';
				}
			}
		echo '</td>';

		return $kringsaldo;
	}
	function view() {


		# we willen weten hoeveel moten en wat het max aantal kringen in een moot is...
		$maxmoten = Moot::getMaxMoten();
		$maxkringen = Moot::getMaxKringen();

		# menuutje
		echo '<ul class="horizontal nobullets">
			<li>
				<a href="/communicatie/ledenlijst/">Ledenlijst</a>
			</li>
			<li>
				<a href="/communicatie/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a>
			</li>
			<li>
				<a href="/communicatie/verticalen/">Kringen</a>
			</li>
		</ul>
		<hr />';
		echo '<h1>Oude Moot en Kringindeling</h1>';
		echo '<p>';
		if($this->_bEmail===true){
			echo '<a href="moten">Toon zonder email-adressen</a>';
		}else{
			echo '<a href="moten?email">Toon ook email-adressen</a>';
		}
		echo '</p><table style="width: 100%">';


		# we gaan de kringen in de moot onder elkaar zetten, een moot per kolom
		for ($regel=1; $regel<=$maxkringen; $regel++) {
			echo '<tr>';
			for ($moot=1; $moot<=$maxmoten; $moot++) {
				if (isset($this->_kringen[$moot][$regel])) echo '<th id="kring'.$moot.'.'.$regel.'">Kring '.$moot.'.'.$regel.'</th>';
				else echo '<td>&nbsp;</td>';
			}
			echo '</tr><tr>';
			for ($moot=1; $moot<=$maxmoten; $moot++) {
				$this->printKring($moot, $regel);
			}
		}

		# nu nog even de kringlozen
		$regel = 0;
		print ("<tr>\n");
		for ($moot=1; $moot<=$maxmoten; $moot++) {
			if (isset($this->_kringen[$moot][$regel])){
				echo '<th id="kring'.$moot.'.0">Kring '.$moot.'.0</th>';
			}else{
				echo '<td>&nbsp;</td>';
			}
		}

		echo '</tr><tr>';
		//en ook even de leden van de .0-kring printen
		for ($moot=1; $moot<=$maxmoten; $moot++) {
			$mootsaldo[$moot]+=$this->printKring($moot, $regel);
		}
		echo '</tr>';

		//kringen invoeren... moet nog even goed afgemaakt worden met kringselectie.
		//daarom nu uitgeschakeld
		if(false){
			echo '<tr>';
			for ($moot=1; $moot<=$maxmoten; $moot++){
				echo '<td >';
				echo '<form action="moten#form" method="post"><a name="form" ></a>
					<input type="hidden" name="moot" value="'.$moot.'" />';
				$tekstInvoer=true;
				if(	isset($_POST['kringNamen']) AND trim($_POST['kringNamen'])!='' AND
						isset($_POST['moot']) AND $_POST['moot']==$moot){
					$aKringleden=namen2uid($_POST['kringNamen'], $this->_lid);
					if(is_array($aKringleden) AND count($aKringleden)!=0){
						echo '<table border="0">';
						echo '<tr><td><strong>Naam</strong></td>';
//						echo '<td><strong>KL</strong></td>';
//						echo '<td><strong>MB</strong></td>';
						echo '</tr>';
						$iNamenTeller=0;
						//pr($aKringleden);
						foreach($aKringleden as $aKringlid){
							if(isset($aKringlid['uid'])){
								//naam is gevonden en uniek, dus direct goed.
								echo '<tr>';
								echo '<td><input type="hidden" name="naam['.$iNamenTeller.']" value="'.$aKringlid['uid'].'" />'.$aKringlid['naam'].'</td>';
//								echo '<td><input type="checkbox" value="false" name="leider['.$iNamenTeller.']" /></td>';
//								echo '<td><input type="checkbox" value="false" name="motebal['.$iNamenTeller.']" /></td>';
								echo '</tr>';
							}else{
								//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
								if(count($aKringlid['naamOpties'])>0){
									echo '<tr><td><select name="naam['.$iNamenTeller.']" class="tekst">';
									foreach($aKringlid['naamOpties'] as $aNaamOptie){
										echo '<option value="'.$aNaamOptie['uid'].'">'.$aNaamOptie['naam'].'</option>';
									}
									echo '</select></td>';
//									echo '<td><input type="checkbox" value="false" name="leider['.$iNamenTeller.']" /></td>';
//									echo '<td><input type="checkbox" value="false" name="motebal['.$iNamenTeller.']" /></td>';
									echo '</tr>';
								}//dingen die niets opleveren wordt niets voor weergegeven.
							}
							$iNamenTeller++;
						}
						echo '</table>';
						$tekstInvoer=false;
					}
				}
				if($tekstInvoer){
					echo 'Geef hier namen of lidnummers op voor deze kring, gescheiden door komma\'s<br />
						<textarea name="kringNamen" class="tekst" cols="20" rows="8"></textarea><br />';
				}
				echo '<input type="submit" name="submit" value="verzenden" />';
				echo '</form></td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		?>
		<script type="text/javascript">
			if(document.location.hash.substring(1,6)=='kring'){
				kring=document.location.hash.substring(1);
				document.getElementById(kring).style.backgroundColor='lightblue';
				//document.getElementById(kring+'leden').style.backgroundColor='lightblue';
			}
		</script>
		<?php
	}
}

?>
