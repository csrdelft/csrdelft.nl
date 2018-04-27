<?php

//holymoly, wat een kekcode is dit zeg.

use CsrDelft\MijnSqli;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\View;

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	redirect(CSR_ROOT);
}

class stats implements View {

	public function getModel() {
		return null;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return 'Statistieken';
	}

	public function view() {
		if (isset($_GET['uid'])) {
			$this->uidLog($_GET['uid']);
		} elseif (isset($_GET['ip'])) {
			$this->ipLog($_GET['ip']);
		} else {
			$this->hoofdLog();
		}
	}

	function hoofdLog() {
		$db = MijnSqli::instance();
		$sLogQuery = "
			SELECT
				log.uid AS uid,  moment,
				ip, locatie, url, referer, useragent
			FROM
				log
			WHERE 1 ";
		if (isset($_GET['sjaars'])) {
			$sLogQuery.="AND status='S_NOVIET' ";
		}
		$sLogQuery.="ORDER BY
				ID DESC
			LIMIT
				0, 30;";
		$rLog = $db->query($sLogQuery);
		echo 'Opties:<br>- stats.php?ip=192.168.1.33<br>- stats.php?uid=x101';
		echo '<table class="forumtable"><tr><td class="forumhoofd">tijd</td><td class="forumhoofd">Naam</td><td class="forumhoofd">hostnaam</td><td class="forumhoofd">url</td>';
		echo '<td class="forumhoofd">useragent</td><td class="forumhoofd">referer</td></tr>';
		while ($aLogRegel = $db->next($rLog)) {
			$profiel = ProfielModel::get($aLogRegel['uid']);
			echo '<tr><td class="forumtitel">' . date('D H:i', strtotime($aLogRegel['moment'])) . '</td>';
			echo '<td class="forumtitel" ><a href="?uid=' . htmlspecialchars($aLogRegel['uid']) . '">+</a> ' . $profiel->getLink('volledig') . '</td>';
			echo '<td class="forumtitel"><a href="?ip=' . htmlspecialchars($aLogRegel['ip']) . '">+</a>
				' . gethostbyaddr($aLogRegel['ip']) . ' <span class="dikgedrukt">(' . $aLogRegel['locatie'] . ')</span></td>';
			echo '<td class="forumtitel" ';
			if (preg_match('/toevoegen/', $aLogRegel['url'])) {
				echo 'style="background-color: yellow;"';
			} elseif (preg_match('/maak-stemming/', $aLogRegel['url'])) {
				echo 'style="background-color: #CC0000;"';
			} elseif (preg_match('/zoeken/', $aLogRegel['url'])) {
				echo 'style="background-color: #33FF99;"';
			}
			echo '><a href="' . CSR_ROOT . $aLogRegel['url'] . '" target="_blank">' . $aLogRegel['url'] . '</a></td>';
			echo '<td class="forumtitel">' . $aLogRegel['useragent'] . '</td>';
			if ($aLogRegel['referer'] == '') {
				echo '<td class="forumtitel">-</td>';
			} else {
				if (!preg_match('/http\:\/\/csrdelft.nl/', $aLogRegel['referer'])) {
					if (preg_match('/google/i', $aLogRegel['referer'])) {
						$iQpos = 2 + strpos($aLogRegel['referer'], 'q=');
						$iLengte = strpos($aLogRegel['referer'], '&') - $iQpos - 3;

						$fragment = urldecode(substr($aLogRegel['referer'], $iQpos, $iLengte));
						echo '<td class="forumtitel">google:<br /><a href="' . $aLogRegel['referer'] . '" target="_blank">' . $fragment . '</a><td>';
					} else {
						echo '<td class="forumtitel"><a href="' . $aLogRegel['referer'] . '" target="_blank">' . $aLogRegel['referer'] . '</a><td>';
					}
				} else {
					echo '<td class="forumtitel">intern</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}

	function uidLog($uid) {
		if (!AccountModel::isValidUid($uid)) {
			echo 'geen correct uid opgegeven.';
		}
		$db = MijnSqli::instance();
		$sLogQuery = "
			SELECT
				log.uid AS uid, moment,
				ip, locatie, url, referer, useragent
			FROM
				log
			WHERE
				log.uid='" . $uid . "'
			ORDER BY
				ID DESC
			LIMIT
				0, 30;";
		$rLog = $db->query($sLogQuery);
		$profiel = ProfielModel::get($uid);
		echo 'Laatste bezoeken van <strong>' . $profiel->getLink('volledig') . '</strong>';
		echo '<table class="forumtable"><tr><td class="forumhoofd">tijd</td><td class="forumhoofd">hostnaam</td><td class="forumhoofd">url</td>';
		echo '<td class="forumhoofd">useragent</td><td class="forumhoofd">referer</td></tr>';
		while ($aLogRegel = $db->next($rLog)) {
			echo '<tr><td class="forumtitel">' . date('D H:i', strtotime($aLogRegel['moment'])) . '</td>';
			echo '<td class="forumtitel"><a href="?ip=' . htmlspecialchars($aLogRegel['ip']) . '">+</a> ';
			echo gethostbyaddr($aLogRegel['ip']) . ' <strong>(' . $aLogRegel['locatie'] . ')</strong></td>';
			echo '<td class="forumtitel"><a href="' . CSR_ROOT . $aLogRegel['url'] . '" target="_blank">' . $aLogRegel['url'] . '</a></td>';
			echo '<td class="forumtitel">' . $aLogRegel['useragent'] . '</td>';
			if ($aLogRegel['referer'] == '') {
				echo '<td class="forumtitel">-</td>';
			} else {
				if (!preg_match('/http\:\/\/csrdelft.nl/', $aLogRegel['referer'])) {
					if (preg_match('/google/i', $aLogRegel['referer'])) {
						$iQpos = 2 + strpos($aLogRegel['referer'], 'q=');
						$iLengte = strpos($aLogRegel['referer'], '&') - $iQpos - 3;

						$fragment = urldecode(substr($aLogRegel['referer'], $iQpos, $iLengte));
						echo '<td class="forumtitel">google:<br /><a href="' . $aLogRegel['referer'] . '" target="_blank">' . $fragment . '</a><td>';
					} else {
						echo '<td class="forumtitel"><a href="' . $aLogRegel['referer'] . '" target="_blank">' . $aLogRegel['referer'] . '</a><td>';
					}
				} else {
					echo '<td class="forumtitel">intern</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}

	function ipLog($ip) {
		$db = MijnSqli::instance();
		$sLogQuery = "
			SELECT
				log.uid AS uid, moment,
				ip, locatie, url, referer, useragent
			FROM
				log
			WHERE
				log.ip='" . $db->escape($ip) . "'
			ORDER BY
				ID DESC
			LIMIT
				0, 30;";
		$rLog = $db->query($sLogQuery);
		echo 'Laatste bezoeken van het ip <strong>' . gethostbyaddr($_GET['ip']) . '</strong>';
		echo '<table class="forumtable"><tr><td class="forumhoofd">moment</td><td class="forumhoofd">naam</td><td class="forumhoofd">url</td>';
		echo '<td class="forumhoofd">useragent</td><td class="forumhoofd">referer</td></tr>';
		while ($aLogRegel = $db->next($rLog)) {
			$profiel = ProfielModel::get($aLogRegel['uid']);
			echo '<tr>';
			echo '<td class="forumtitel">' . date('D H:i', strtotime($aLogRegel['moment'])) . '</td>';
			echo '<td class="forumtitel"><a href="?uid=' . htmlspecialchars($aLogRegel['uid']) . '">+</a> ' . $profiel->getLink('volledig') . '</td>';
			echo '<td class="forumtitel"><a href="' . CSR_ROOT . $aLogRegel['url'] . '" target="_blank">' . $aLogRegel['url'] . '</a></td>';
			echo '<td class="forumtitel">' . $aLogRegel['useragent'] . '</td>';
			if ($aLogRegel['referer'] == '') {
				echo '<td class="forumtitel">-</td>';
			} else {
				if (!preg_match('/http\:\/\/csrdelft.nl/', $aLogRegel['referer'])) {
					if (preg_match('/google/i', $aLogRegel['referer'])) {
						$iQpos = 2 + strpos($aLogRegel['referer'], 'q=');
						$iLengte = strpos($aLogRegel['referer'], '&') - $iQpos - 3;

						$fragment = urldecode(substr($aLogRegel['referer'], $iQpos, $iLengte));
						echo '<td class="forumtitel">google:<br /><a href="' . $aLogRegel['referer'] . '" target="_blank">' . $fragment . '</a><td>';
					} else {
						echo '<td class="forumtitel"><a href="' . $aLogRegel['referer'] . '" target="_blank">' . $aLogRegel['referer'] . '</a><td>';
					}
				} else {
					echo '<td class="forumtitel">intern</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}

}

// einde stats klasse


$pagina = new CsrLayoutPage(new stats());
$pagina->view();
