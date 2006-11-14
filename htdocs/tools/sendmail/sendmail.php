<?php

# S.U.E. M.E. v1.01
# Stuur Uw Email Maar Even

# 2004 Oogopslag Internet
# BNBForm compatible PHP replacement
# Hans van Kranenburg

# C.S.R. Delft 2-1-2005

main();
exit();

function main() {

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	require_once("maildata.php");

	# Ophalen welk form het is
	$form_id = (isset($_POST['form_id'])) ? $_POST['form_id'] : 'f3u7z0r';
	if ($form_id == '' or !isset($form[$form_id])) die ($sendmail['noform_error']);

	# Referer check
	if (strpos($_SERVER["HTTP_REFERER"], $form[$form_id]['referer']) === false)
		die ($sendmail['referer_error']);

	# Wie heeft het verzonden?
	$submit_by = (isset($_POST['submit_by'])) ? $_POST['submit_by'] : $sendmail['default_submit_by'];

	# Controleer of maildata goed is ingesteld
	checkMailData($form[$form_id], 'submit_to');
	checkMailData($form[$form_id], 'subject');
	checkMailData($form[$form_id], 'data_order');
	checkMailData($form[$form_id], 'ok_url');

	# Controleer of er required velden zijn
	if ($form[$form_id]['required'] != '') {
		$required = explode(',', $form[$form_id]['required']);
		foreach ($required as $foo) checkFormData($foo);
	}

	# Klus email in elkaar.
	$message = "On " . date("r") . "\n";
	$message .= "The following information was submitted:\n";
	$message .= "From Host: " . $_SERVER['REMOTE_ADDR'] . "\n";

	$data_order = explode(',', $form[$form_id]['data_order']);
	foreach ($data_order as $foo) $message .= htmlspecialchars("{$foo} = {$_POST[$foo]}\n");

	# Verstuur email
	mail ($form[$form_id]['submit_to'], $form[$form_id]['subject'], $message,	"From: {$submit_by}");

	# Verstuur automessage
	if (isset($form[$form_id]['automessage']) and $form[$form_id]['automessage'] != '')
		mail ($_POST['submit_by'], $form[$form_id]['subject'], $form[$form_id]['automessage'], "From: {$sendmail['default_submit_by']}");

	# redirecten naar volgende pagina
	header("Location: {$form[$form_id]['ok_url']}");
}

function checkMailData(&$form, $field) {
	if (!isset($form[$field]) or $form[$field] == '')
		die ("Error: Ongeldige maildata-waarde of geen waarde voor {$field}");
}

function checkFormData($field) {
	if (!isset($_POST[$field]) or $_POST[$field] == '')
		die ("Error: Ongeldige waarde of geen waarde voor Formulier->{$field}");
}

?>
