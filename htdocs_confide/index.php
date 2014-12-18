<?php
/*
 * Ding om dingen uit de socciestreeplijst-db te halen.
 */

define('ETC_PATH', '.');
session_start();

require_once 'common.functions.php';
require_once 'MijnSqli.class.php';
require_once 'streeplijstrapportage.php';

$db = MijnSqli::instance();

//lijstje met artikelen regelen.
$artikelenResult = $db->query("SELECT Naam as naam, Sneltoets as letter, Prijs as prijs FROM Artikelen;");
$artikelen = array();
while ($art = $db->next($artikelenResult)) {
	$artikelen[$art['letter']] = $art;
}

//lijst met omzetten voor de afgelopen weken regelen.
if (isset($_GET['start'])) {
	$start = getDateTime(strtotime(((int) $_GET['start']) . ' months ago'));
} else {
	$start = getDateTime(strtotime('3 months ago'));
}

$db->query("SET SESSION group_concat_max_len=6000");

$weekrapportQuery = "
	SELECT
		year(Tijdstip) as jaar, week(Tijdstip) as week,
		SUM(Bedrag) as omzet, count(*) as aantal_bestellingen, 
		GROUP_CONCAT(Artikelen SEPARATOR '') as bestelstring
	FROM Bestellingen
	WHERE Tijdstip>'" . $start . "' AND Bedrag!=0
	GROUP BY jaar, week
	ORDER BY jaar DESC, week DESC;";
$weekResult = $db->query($weekrapportQuery);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>SocCie streepcomputerrapportagegenereertool</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="PubCie C.S.R. Delft - Jieter" />
		<meta name="robots" content="nfollow" />
		<link rel="stylesheet" href="csrdelft.css" type="text/css" />
		<link rel="stylesheet" href="normaal.css" type="text/css" />
		<link rel="stylesheet" href="bbcode.css" type="text/css" />
		<script type="text/javascript" src="<?= CSR_ROOT ?>/layout/js/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="<?= CSR_ROOT ?>"/layout/js/datatables/jquery.dataTables.min.js"></script>
		<script type="text/javascript">
			jQuery.fn.dataTableExt.oSort['currency-asc'] = function (a, b) {
				/* Remove any commas (assumes that if present all strings will have a fixed number of d.p) */
				var x = a == "-" ? 0 : a.replace(/,/g, "");
				var y = b == "-" ? 0 : b.replace(/,/g, "");

				/* Remove the currency sign */
				x = x.substring(1);
				y = y.substring(1);

				/* Parse and return */
				x = parseFloat(x);
				y = parseFloat(y);
				return x - y;
			};

			jQuery.fn.dataTableExt.oSort['currency-desc'] = function (a, b) {
				/* Remove any commas (assumes that if present all strings will have a fixed number of d.p) */
				var x = a == "-" ? 0 : a.replace(/,/g, "");
				var y = b == "-" ? 0 : b.replace(/,/g, "");

				/* Remove the currency sign */
				x = x.substring(1);
				y = y.substring(1);

				/* Parse and return */
				x = parseFloat(x);
				y = parseFloat(y);
				return y - x;
			};

			jQuery(document).ready(function ($) {
				$('.details').click(function (e) {

					$('#table-' + this.id).show();
					$('#' + this.id).remove();
				});
				$('.zebra tr:even').addClass('even');

				$('#bestellingen').dataTable({
					'aaSorting': [[0, 'desc']],
					'aoColumns': [
						null,
						null,
						{'sType': 'currency'},
						{'bSortable': false},
					],
					"bInfo": false,
					'bSearch': true,
					"bLengthChange": false,
					"oLanguage": {
						"sSearch": "Zoeken:"
					},
				});
				$('#weken').dataTable({
					'aaSorting': [[0, 'desc']],
					'aoColumns': [
						null,
						null,
						{'sType': 'currency'},
						{'bSortable': false},
						{'sType': 'currency'},
						{'sType': 'currency'},
						null
					],
					'bSearch': false,
					'bFilter': false,
					'bPaginate': true,
					"bLengthChange": false,
					'bInfo': false
				});
			});
		</script>
	</head>
	<body>

		<?php wait_for_login(); ?>

		<h1>Rapportage's streepcompu SocCie</h1>
		<a href="#weken">Weekoverzicht</a><br />
		<a href="#bestellingen">Laatste bestellingen</a><br />
		<br />


		<strong>Periode:</strong>
		<em>3 maanden terug (standaard)</em> |
		<a href="?start=6">half jaar</a> |
		<a href="?start=12">1 jaar</a> |
		<a href="?start=24">2 jaar</a>


		<h3>Weekoverzichten</h3>
		Let op: cijfers kloppen na prijswijzigingen niet voor de weken v&oacute;&oacute;r de wijziging. Weeknummer zijn <a href="http://en.wikipedia.org/wiki/ISO_week_date">ISO-weken</a>: van maandag tot zondag dus...<br /><br />
		<table class="weken zebra" id="weken">
			<thead>
				<tr>
					<th>Week</th><th>#bestellingen</th><th>som omzet+inleg <br />(streepcompu)</th><th>detail</th><th>inleg</th><th>Omzet</th>
					<th title="Verschil tussen omzet uit detaillijst en omzet uit streeplijstcompu. Zou 0 moeten zijn">controle</th>
				</tr>
			</thead>

			<?php
			while ($row = $db->next($weekResult)) {
				$week = $row['week'];
				echo '<tr>';
				echo '<td>' . $row['jaar'] . '-' . $week . '</td>';
				echo '<td>' . $row['aantal_bestellingen'] . '</td>';
				echo '<td>' . euro($row['omzet']) . '</td>';

				$bestellingen = parse_bestelstring($row['bestelstring']);

				$inleg = 0;
				$omzet = 0;
				$totaal = 0;

				echo '<td>';
				echo '<a class="btn details" id="details-' . $week . '">&raquo; Toon details</a>';
				echo '<table id="table-details-' . $week . '" class="artikelen verborgen"><tr><th>artikel</th><th>letter</th><th>#</th><th>omzet</th></tr>';
				foreach ($bestellingen as $letter => $aantal) {
					$artikelomzet = $artikelen[$letter]['prijs'] * $aantal;
					echo '<tr><td>' . $artikelen[$letter]['naam'] . '</td>';
					echo '<td>' . $letter . '</td>';
					echo '<td>' . $aantal . '</td>';
					echo '<td class="euro">' . euro($artikelomzet) . '</td>';
					echo '</tr>';
					if ($letter == 'h' OR $artikelomzet < 0) {
						$inleg+=$artikelomzet;
					} else {
						$omzet+=$artikelomzet;
					}
					$totaal+=$artikelomzet;
				}
				echo '</table>';

				echo '</td>';
				echo '<td>' . euro(0 - $inleg) . '</td>';
				echo '<td>' . euro($omzet) . '</td>';
				echo '<td>' . round($totaal - $row['omzet']) . '</td>';
				echo '</tr>';
			}
			?>
		</table>
		<h3>Laatste bestellingen</h3>
		Zoeken in de laatste bestellingen: <em>(Met een limiet van 3000 bestellingen)</em>
		<table class="bestellingen zebra" id="bestellingen">
			<thead>
				<tr>
					<th>Tijdstip</th><th>Lid</th><th>bedrag </th><th>Artikelen</th>
				</tr>
			</thead>
			<?php
			$weekResult = $db->query('
	SELECT Tijdstip, Lid, Bedrag, Artikelen
	FROM Bestellingen
	WHERE Tijdstip>\'' . $start . '\' AND bedrag!=0
	ORDER BY Tijdstip DESC LIMIT 3000;');

			while ($row = $db->next($weekResult)) {
				echo '<tr>';
				echo '<td>' . $row['Tijdstip'] . '</td>';
				echo '<td>' . $row['Lid'] . '</td>';
				echo '<td>' . euro($row['Bedrag']) . '</td>';
				echo '<td>' . $row['Artikelen'] . '</td>';
				echo '</tr>';
			}
			?>
		</table>


	</body>
</html>
