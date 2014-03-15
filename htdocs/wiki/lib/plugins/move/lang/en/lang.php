<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gary Owen <>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Page/Namespace Move/Rename...';
$lang['desc'] = 'Page/Namespace Move/Rename Plugin';

$lang['notexist']    = 'The page %s does not exist';
$lang['medianotexist']    = 'The media file %s does not exist';
$lang['notwrite']    = 'You do not have sufficient permissions to modify this page';
$lang['badns']       = 'Invalid characters in namespace.';
$lang['badname']     = 'Invalid characters in pagename.';
$lang['nochange']    = 'Page name and namespace are unchanged.';
$lang['nomediachange']    = 'Media file name and namespace are unchanged.';
$lang['existing']    = 'A page called %s already exists in %s';
$lang['mediaexisting']    = 'A media file called %s already exists in %s';
$lang['root']        = '[Root namespace]';
$lang['current']     = '(Current)';
$lang['renamed']     = 'Page name changed from %s to %s';
$lang['moved']       = 'Page moved from %s to %s';
$lang['move_rename'] = 'Page moved and renamed from %s to %s';
$lang['delete']		= 'Deleted by move plugin';
$lang['norights']    = 'You have insufficient permissions to edit %s.';
$lang['nomediarights']    = 'You have insufficient permissions to delete %s.';
$lang['notargetperms'] = 'You don\'t have the permission to create the page %s.';
$lang['nomediatargetperms'] = 'You don\'t have the permission to create the media file %s.';
$lang['filelocked']  = 'The page %s is locked. Try again later.';
$lang['linkchange']  = 'Links adapted because of a move operation';

$lang['ns_move_in_progress'] = 'There is currently a namespace move of %s page and %s media files from namespace %s to namespace %s in progress.';
$lang['ns_move_continue'] = 'Continue the namespace move';
$lang['ns_move_abort'] = 'Abort the namespace move';
$lang['ns_move_continued'] = 'The namespace move from namespace %s to namespace %s was continued, %s items are still remaining.';
$lang['ns_move_started'] = 'A namespace move from namespace %s to namespace %s was started, %s pages and %s media files will be moved.';
$lang['ns_move_error'] = 'An error occurred while continueing the namespace move from %s to %s.';
$lang['ns_move_tryagain'] = 'Try again';
$lang['ns_move_skip'] = 'Skip the current item';
// Form labels
$lang['newname']     = 'New page name:';
$lang['newnsname']   = 'New namespace name:';
$lang['targetns']    = 'Select new namespace:';
$lang['newtargetns'] = 'Create a new namespace:';
$lang['movepage']	= 'Move page';
$lang['movens']		= 'Move namespace';
$lang['submit']      = 'Submit';
$lang['content_to_move'] = 'Content to move';
$lang['move_pages']  = 'Pages';
$lang['move_media']  = 'Media files';
$lang['move_media_and_pages'] = 'Pages and media files';
// JavaScript preview
$lang['js']['previewpage'] = 'OLDPAGE will be moved to NEWPAGE';
$lang['js']['previewns'] = 'All pages and namespaces in the namespace OLDNS will be moved in the namespace NEWNS';
