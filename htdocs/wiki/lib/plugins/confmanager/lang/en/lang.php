<?php
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Configuration file Manager'; 
 
// custom language strings for the plugin

$lang['welcome']     = 'Welcome to the configuration file Manager.<br />'
                     . ' It allows you to edit the .conf files from the '
                     . '<a href="http://www.dokuwiki.org/config#summary_of_the_configuration_files" '
                     . 'class="interwiki iw_doku">dokuwiki configuration</a>.';

$lang['welcomehead'] = 'Configuration file Manager';

$lang['edithead']    = 'Edit a config file';
$lang['editcnf']     = 'edit config file';

$lang['cnf_acronyms']  = 'acronyms';
$lang['cnf_entities']  = 'entities';
$lang['cnf_interwiki'] = 'interwiki links';
$lang['cnf_mime']      = 'mime types';
$lang['cnf_smileys']   = 'smileys';

$lang['head_acronyms']  = 'Abbreviations and Acronyms';
$lang['head_entities']  = 'Entities';
$lang['head_interwiki'] = 'InterWiki Links';
$lang['head_mime']      = 'MIME configuration';
$lang['head_smileys']   = 'Smileys';

$lang['edit_desc'] = 'You can edit the current settings by simply changing there value in the listing below.'
                   . '<br /> To delete a config line just clear the value.  If you want to <a href="#__add">add a value</a> use the form below';

$lang['text_acronyms']  = 'Here you can edit all the acronym hints. For more details see <a class="interwiki iw_doku" href="http://www.dokuwiki.org/abbreviations">abbreviations and acronyms</a>';
$lang['text_entities']  = 'Here you can edit all entities replacements. For more details see <a class="interwiki iw_doku" href="http://www.dokuwiki.org/entities">entities</a>';
$lang['text_interwiki'] = 'Here you can edit all availible interwiki links. For more details see <a class="interwiki iw_doku" href="http://www.dokuwiki.org/interwiki">interwiki</a>';
$lang['text_mime']      = 'Here you can edit all the allowed mime types. For more details see <a class="interwiki iw_doku" href="http://www.dokuwiki.org/mime">mime</a>';
$lang['text_smileys']   = 'Here you can edit all availible smileys. For more details see <a class="interwiki iw_doku" href="http://www.dokuwiki.org/smileys">smileys</a>';

$lang['submitchanges']  = 'Submit changes';
$lang['reset']          = 'Reset';

$lang['addvarhead'] = 'Add a line';
$lang['additem'] = 'Add';
$lang['addvartext'] = 'Here you may add a new item pair.';

$lang['editdesc'] = 'Select another file to edit. (Unsaved changes will be lost)';
