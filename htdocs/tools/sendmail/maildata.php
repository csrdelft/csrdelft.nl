<?php

##############

$sendmail['default_submit_by'] = "pubcie@csrdelft.nl";
$sendmail['noform_error'] = "Error: Geen of ongeldig formulier opgegeven";
$sendmail['referer_error'] = "Error: U submit niet vanaf de C.S.R. Delft website, of controleer of uw browser geen HTTP Referers zendt.";

############## Variabelen in het form in te stellen: #############
#form_id:     name of form, corresponding with data below => $form['form_id'] (required)
#submit_by:   email address of person completing form (optional)
##################################################################

############## Variabelen hieronder in te stellen ################
#referer:     de pagina waarop het form staat, dus waarvandaan gesubmit wordt
#submit_to:   email address of person to input receive data by mail
#             this can be a comma seperated list of e-mail addresses
#subject:			subject of the generated message
#required:    comma delimited list of required entry fields
#data_order:  comma delimited list indicating what fields to actually
#             print and in what order.
#ok_url:      URL to go to if successful
#not_ok_url   URL to go to if unsuccessful
#automessage: text to print for autoconfirmation e-mail
##################################################################

# Tip: maak een stuk tekst als bv automessage als volgt:
# $form['naam']['automessage'] = <<<DUZ
#
# Typ hiertussen de tekst en eindig op het BEGIN van een nieuwe regel met
#
# DUZ
# ;

$form['lidworden']['referer']      = 'http://csrdelft.nl/informatie/lidworden.php';
$form['lidworden']['submit_to']    = 'pubcie@csrdelft.nl,vice-praeses@csrdelft.nl,owee@csrdelft.nl';
$form['lidworden']['subject']      = 'Lid-Worden formulier website';
$form['lidworden']['required']     = 'naam';
$form['lidworden']['data_order']   = 'naam,straat,postcode,plaats,telefoon,submit_by,opmerking';
$form['lidworden']['ok_url']       = 'http://csrdelft.nl/informatie/lidworden.php?a=ok';
$form['lidworden']['not_ok_url']   = '';
$form['lidworden']['automessage']  = '';

/*
$form['nieuw']['referer']      = '';
$form['nieuw']['submit_to']    = '';
$form['nieuw']['subject']      = '';
$form['nieuw']['required']     = '';
$form['nieuw']['data_order']   = '';
$form['nieuw']['ok_url']       = '';
$form['nieuw']['not_ok_irl']   = '';
$form['nieuw']['automessage']  = '';
*/

?>
