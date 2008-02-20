<?php
	require_once("../inc.database.php");
	
	if (isset($_POST["categorielijst"])) {
		$worklist = stripslashes($_POST["categorielijst"]);
		
		$metbr = nl2br($worklist);
		$regels = explode('<br />', $metbr);
		
		foreach ($regels as $regel) {
			list($id, $p_id, $categorie) = explode(';', $regel);
			
			db_query("INSERT INTO biebcategorie SET id = $id, p_id = $p_id, categorie = '$categorie'");
		}
	} else {
?>
		<form action="?" method="post">
			<textarea cols="40" rows="15" name="categorielijst"></textarea><br>
			<input type="submit">
		</form>
		</td>
	</tr>
</table>
</body>
</html>
<?
	}
?>