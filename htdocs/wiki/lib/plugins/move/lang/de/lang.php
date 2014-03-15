<?php
/**
 * german language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     picsar <>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Seite/Namespace verschieben/umbenennen...';
$lang['desc'] = 'Seite/Namespace verschieben/umbenennen... Plugin';

$lang['notexist']    = 'Die Seite %s existiert nicht.';
$lang['medianotexist']    = 'Die Mediendatei %s existiert nicht';
$lang['notwrite']	= 'Sie haben unzureichende Rechte um diese Seite zu ändern';
$lang['badns']		= 'Ungültige Zeichen in der Namensraum-Bezeichnung.';
$lang['badname']		= 'Ungültiges Zeichen im Seitennamen.';
$lang['nochange']	= 'Name und Namensraum der Seite sind unverändert.';
$lang['nomediachange']    = 'Name und Namensraum der Mediendatei sind unverändert.';
$lang['existing']	= 'Eine Seite mit der Bezeichnung %s existiert bereits in %s';
$lang['mediaexisting']    = 'Eine Mediendatei mit der Bezeichnung %s existiert bereits in %s';
$lang['root']		= '[Wurzel des Namensraumes / Root namespace]';
$lang['current']		= '(Aktueller)';
$lang['renamed']     = 'Seitename wurde von %s auf %s geändert';
$lang['moved']       = 'Seite von %s nach %s verschoben';
$lang['move_rename'] = 'Seite von %s nach %s verschoben und umbenannt';
$lang['delete']      = 'Gelöscht durch das move Plugin';
$lang['norights']    = 'Sie haben unzureichende Rechte um %s zu bearbeiten.';
$lang['nomediarights']    = 'Sie haben unzureichende Rechte, um die Mediendatei %s zu löschen.';
$lang['notargetperms'] = 'Sie haben nicht die Berechtigung, die Seite %s anzulegen.';
$lang['nomediatargetperms'] = 'Sie haben nicht die Berechtigung, die Mediendatei %s anzulegen.';
$lang['filelocked']  = 'Die Seite %s ist gesperrt - versuchen Sie es später noch einmal.';
$lang['linkchange']  = 'Links angepasst weil Seiten im Wiki verschoben wurden';

$lang['ns_move_in_progress'] = 'Das Verschieben von %s Seiten und %s Mediendateien vom Namensraum %s nach %s ist derzeit im Gange.';
$lang['ns_move_continue'] = 'Das Verschieben des Namensraumes fortsetzen';
$lang['ns_move_abort'] = 'Das Verschieben des Namensraumes abbrechen';
$lang['ns_move_continued'] = 'Das Verschieben des Namensraumes %s nach %s wurde fortgesetzt, %s Einträge müssen noch verschoben werden.';
$lang['ns_move_started'] = 'Das Verschieben des Namensraumes %s nach %s wurde begonnen, %s Seiten und %s Mediendateien werden verschoben werden.';
$lang['ns_move_error'] = 'Ein Fehler ist beim Verschieben des Namensraumes %s nach %s aufgetreten.';
$lang['ns_move_tryagain'] = 'Nochmal versuchen';
$lang['ns_move_skip'] = 'Den aktuellen Eintrag überspringen';
// Form labels
$lang['newname']		= 'Neuer Seitenname:';
$lang['newnsname']   = 'Neuer Name für Namensraum:';
$lang['targetns']	= 'Wählen Sie einen neuen Namensraum: ';
$lang['newtargetns'] = 'Erstellen Sie einen neuen Namensraum';
$lang['movepage']	= 'Seite verschieben';
$lang['movens']		= 'Namensraum verschieben';
$lang['submit']      = 'Übernehmen';
// JavaScript preview
$lang['js']['previewpage'] = 'OLDPAGE wird in NEWPAGE umbenannt';
$lang['js']['previewns']	= 'Alle Seiten und Namensräume im Namensraum OLDNS werden in den Namensraum NEWNS verschoben';
