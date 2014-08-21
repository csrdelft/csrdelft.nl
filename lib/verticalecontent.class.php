<?php

class VerticalenContent extends TemplateView {

	public function __construct() {
		parent::__construct(null);
	}

	public function getTitel() {
		return 'Verticalen der Civitas';
	}

	public function viewEmails($vertkring) {
		try {
			$verticale = new Verticale(substr($vertkring, 0, 1));
		} catch (Exception $e) {
			echo 'Verticale bestaat niet';
			return false;
		}
		if ($verticale instanceof Verticale) {
			try {
				$kring = $verticale->getKring((int) substr($vertkring, 2, 1));
			} catch (Exception $e) {
				echo 'Kring bestaat niet';
				return false;
			}
			$leden = array();
			foreach ($kring as $kringlid) {
				$leden[] = $kringlid->getEmail();
			}
			echo implode(', ', $leden);
		}
	}

	public function view() {
		$verticalen = Verticale::getAll();

		echo '<ul class="horizontal nobullets">
			<li>
				<a href="/communicatie/ledenlijst/">Ledenlijst</a>
			</li>
			<li>
				<a href="/communicatie/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a>
			</li>
			<li class="active">
				<a href="/communicatie/verticalen/">Kringen</a>
			</li>
		</ul>
		<hr />';

		foreach ($verticalen as $verticale) {

			echo '<div class="verticale">';
			echo '<h1>Verticale ' . $verticale->getNaam() . '</h1>';
			foreach ($verticale->getKringen() as $kringnaam => $kring) {
				$kringstyle = 'kring';
				if ($kringnaam == 0) {
					$kringstyle = 'geenkring';
				}
				echo '<div class="' . $kringstyle . '" id="kring' . $verticale->getLetter() . '.' . $kringnaam . '">';
				echo '<div class="mailknopje" onclick="toggleEmails(\'' . $verticale->getLetter() . '.' . $kringnaam . '\')">@</div>';
				if ($kringnaam == 0) {
					echo '<h2>Geen kring</h2>';
				} else {
					echo '<h2>Kring ' . $kringnaam . '</h2>';
				}
				echo '<div id="leden' . $verticale->getLetter() . '.' . $kringnaam . '" class="kringleden">';
				foreach ($kring as $lid) {
					if ($lid->isKringleider())
						echo '<em>';
					echo $lid->getNaamLink('full', 'visitekaartje');
					if ($lid->getStatus() == 'S_KRINGEL')
						echo '&nbsp;~';
					if ($lid->isVerticaan())
						echo '&nbsp;L';
					if ($lid->isKringleider())
						echo '</em>';
					echo '<br />';
				}
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
		<script type="text/javascript">
			if (document.location.hash.substring(1, 6) == 'kring') {
				kring = document.location.hash.substring(1);
				document.getElementById(kring).style.backgroundColor = '#f1f1f1';
				document.getElementById(kring).style.borderBottom = '1px solid black';
			}
		</script>
		<?php

	}

}
?>
