<?
	require_once('inc.common.php');
	
	/* where_clause voorbereiden */
	$where_clause = '';
	
	$titel 		= getGET('titel');
	$auteur 	= getGET('auteur');
	$categorie 	= (int) getGET('categorie');
	$taal 		= getGET('taal');
	$alleen_csr = getGET('alleen_csr');
	
	$limit_start = db_escape(getGET('start'));
	$limit_max = db_escape(getGET('max'));
	if ($limit_start == "") $limit_start = 0;
	if ($limit_max == "") $limit_max = 50;
	$limit = "LIMIT $limit_start, $limit_max";
	
	if ($titel != '') 			$where_clause .= " AND b.titel LIKE '%".db_escape($titel)."%'";
	if ($auteur != '') 			$where_clause .= " AND a.auteur LIKE '%".db_escape($auteur)."%'";
	if ($categorie != '0') 		$where_clause .= " AND c3.id = ".db_escape($categorie);
	if ($taal != '') 			$where_clause .= " AND b.taal = '".db_escape($taal)."'";
	if ($alleen_csr == 'true') 	$where_clause .= " AND NOT (b.code = '' OR b.code IS NULL)";
	
	/* order_clause opvragen */
	$sorteerOp = db_escape(getGET('sorteerOp'));
	
	if ($sorteerOp == '') {
		$sorteerOp = db_escape('a.auteur,b.titel');
	}
	
	# informatie ophalen
	$count = db_firstCell("
		SELECT 
			COUNT(DISTINCT b.id)
		FROM 
			biebboek b, biebauteur a, biebexemplaar e, biebcategorie c1, biebcategorie c2, biebcategorie c3
		WHERE
			a.id = b.auteur_id AND
			b.id = e.boek_id AND
			c3.id = b.categorie_id AND
			c1.id = c2.p_id AND
			c2.id = c3.p_id $where_clause
		ORDER BY
			$sorteerOp
	");
	
	# informatie ophalen
	$result = db_query("
		SELECT DISTINCT
			b.id, b.titel, b.taal, 
			a.auteur,
			c1.categorie,
			c2.categorie,
			c3.categorie
		FROM 
			biebboek b, biebauteur a, biebexemplaar e, biebcategorie c1, biebcategorie c2, biebcategorie c3
		WHERE
			a.id = b.auteur_id AND
			b.id = e.boek_id AND
			c3.id = b.categorie_id AND
			c1.id = c2.p_id AND
			c2.id = c3.p_id $where_clause
		ORDER BY
			$sorteerOp
		$limit
	");
	
	if ($limit_start > 0) {
		$newLeftStart = max(0, $limit_start - 50);
		$leftArrow = "<a href=\"javascript:verversCatalogusLimit($newLeftStart, 50);\"><img src=\"images/arrow_left.gif\" border=\"0\"></a>";
	} else {
		$leftArrow = "<img src=\"images/arrow_leftoff.gif\" border=\"0\">";
	}
	
	$resultaatMelding = ($limit_start + 1) . "-" . min($count, ($limit_max + $limit_start)) . "/" . $count;
	
	$newRightStart = min($count -1, $limit_start + 50);
	if ($count -1 > $newRightStart) {
		$rightArrow = "<a href=\"javascript:verversCatalogusLimit($newRightStart, 50);\"><img src=\"images/arrow_right.gif\" border=\"0\"></a>";
	} else {
		$rightArrow = "<img src=\"images/arrow_rightoff.gif\" border=\"0\">";
	}

	if (mysql_num_rows($result) > 0) {
?>
		<table border="0" cellpadding="1" cellspacing="0" width="100">
			<tr>
				<td valign="center" width="20"><?=$leftArrow?></td>
				<td align="center" valign="center" width="100"><i><b><?=$resultaatMelding?></b></i></td>
				<td valign="center"><?=$rightArrow?></td>
			</tr>
		</table>
		<br />
		<table border="0" cellpadding="1" cellspacing="0" id="boekenTabel">
			<tr class="tabelkop">
				<td><a href="javascript:sorteerOp('b.titel');">Titel</a>&nbsp;
				<td><a href="javascript:sorteerOp('a.auteur');">Auteur</a>&nbsp;
				<td><a href="javascript:sorteerOp('c1.categorie, c2.categorie, c3.categorie');">Categorie</a>&nbsp;
<?
		$iCounter = 0;
		while ($row = mysql_fetch_row($result)) {
			list (
				$b_id, $titel, $taal, 
				$auteur,
				$hoofdCategorie,
				$subCategorie,
				$categorie
			) = $row;
?>
			<tr id="row_<?=$iCounter?>">
				<td><a href="?boekID=<?=$b_id?>"><?=mb_htmlentities($titel)?></a>&nbsp;
				<td><?=mb_htmlentities($auteur)?>&nbsp;
				<td width="240"><?=mb_htmlentities($subCategorie)?> - <?=mb_htmlentities($categorie)?>&nbsp;
<?						$iCounter++;
		}
?>
		</table>
		<br />
		<table border="0" cellpadding="1" cellspacing="0" width="100">
			<tr>
				<td valign="center" width="20"><?=$leftArrow?></td>
				<td align="center" valign="center" width="100"><i><b><?=$resultaatMelding?></b></i></td>
				<td valign="center"><?=$rightArrow?></td>
			</tr>
		</table>
<?
	}
?>