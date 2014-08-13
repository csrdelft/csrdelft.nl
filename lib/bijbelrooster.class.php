<?php

class Bijbelrooster extends TemplateView {

	public function __construct() {
		parent::__construct(null, 'Bijbelrooster');
	}

	function ubbContent($aantal) {
		$aantal = (int) max($aantal, 2);
		$begin = Date('y:m:d', strtotime("-" . min(abs($aantal / 2), 2) . " days"));
		$return = '<div class="mededeling-grotebalk"><div class="titel"><a href="/actueel/bijbelrooster/">Bijbelleesrooster</a></div><p class="half">';
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM bijbelrooster WHERE dag >= "' . $begin . '" ORDER BY dag ASC LIMIT 0,' . $aantal;
		$res = $db->select($query);
		if ($res === false) {
			return;
		}
		$itemsEachRow = (int) ceil(mysqli_num_rows($res) / 2);
		$i = 0;
		while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			if ($i++ % $itemsEachRow == 0 && $i != 1)
				$return .= '</p><p class="half">';
			$class = '';
			if ($row['dag'] < date('Y-m-d')) {
				$class = 'lichtgrijs';
			}
			$return.= '<span class="' . $class . '">' . date('d-m-Y', strtotime($row['dag'])) . ':</span> ' . $this->getLink($row['stukje']) . "<br />";
		}
		return $return . '</p><div class="clear"></div></div>';
	}

	function getLink($stukje) {
		return CsrUbb::getBiblijaLink($stukje);
	}

	/**
	 * Haal het hele rooster op en laat het zien in drie kolommen.
	 */
	public function view() {
		$db = MijnSqli::instance();
		$query = "SELECT * FROM bijbelrooster ORDER BY dag ASC";
		$res = $db->select($query);

		$stukjes = array();
		$itemEachRow = 0;
		if ($res !== false) {
			$itemsEachRow = (int) ceil(mysqli_num_rows($res) / 3);
			$stukjes = $db->result2array($res);
		}

		$return = '
			<h1>Bijbelrooster</h1>
			<p>Hier vindt u het bijbelrooster der C.S.R.. Uw favoriete bijbelvertaling kunt u instellen bij uw <a href="/instellingen/">instellingen</a>.</p>';


		if (count($stukjes) == 0) {
			$return .= 'Geen rooster aanwezig';
		} else {
			$return .= '<p class="oneThirth">';
			foreach ($stukjes as $key => $stukje) {
				if ($key % $itemsEachRow == 0 && $key != 0) {
					$return .= '</p><p class="oneThirth">';
				}
				$class = '';
				if ($stukje['dag'] < date('Y-m-d')) {
					$class = 'lichtgrijs';
				}
				$return.= '<span class="' . $class . '">' . $stukje['dag'] . ':</span> ';
				$return.=$this->getLink($stukje['stukje']) . '<br />';
			}
			$return .= '</p>';
		}
		$return .= '<div class="clear"></div>';

		return $return;
	}

}
