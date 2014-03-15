<?php
/**
 * Plugin : Move
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Helper part of the move plugin.
 */
class helper_plugin_move extends DokuWiki_Plugin {
    /**
     * Move a namespace according to the given options
     *
     * @author Bastian Wolf
     * @param array $opts      Options for moving the namespace
     * @param bool  $checkonly If only the checks if all pages can be moved shall be executed
     * @return bool if the move was executed
     */
    function move_namespace(&$opts, $checkonly = false) {
        global $ID;
        global $conf;

        $pagelist = array();
        $pathToSearch = utf8_encodeFN(str_replace(':', '/', $opts['ns']));
        $searchOpts = array('depth' => 0, 'skipacl' => true);
        search($pagelist, $conf['datadir'], 'search_allpages', $searchOpts, $pathToSearch);

        // FIXME: either use ajax for executing the queue and/or store the queue so it can be resumed when the execution
        // is aborted.
        foreach ($pagelist as $page) {
            $ID = $page['id'];
            $newID = $this->getNewID($ID, $opts['ns'], $opts['newns']);
            $pageOpts = $opts;
            $pageOpts['ns']   = getNS($ID);
            $pageOpts['name'] = noNS($ID);
            $pageOpts['newname'] = noNS($ID);
            $pageOpts['newns'] = getNS($newID);
            if (!$this->move_page($pageOpts, $checkonly)) return false;
        }
        return true;
    }

    /**
     * Start a namespace move by creating the list of all pages and media files that shall be moved
     *
     * @param array $opts The options for the namespace move
     * @return int The number of items to move
     */
    public function start_namespace_move(&$opts) {
        global $conf;

        // generate and save a list of all pages
        $pagelist = array();
        $pathToSearch = utf8_encodeFN(str_replace(':', '/', $opts['ns']));
        $searchOpts = array('depth' => 0, 'skipacl' => true);
        if (in_array($opts['contenttomove'], array('pages', 'both'))) {
            search($pagelist, $conf['datadir'], 'search_allpages', $searchOpts, $pathToSearch);
        }
        $pages = array();
        foreach ($pagelist as $page) {
            $pages[] = $page['id'];
        }
        unset($pagelist);

        $opts['num_pages'] = count($pages);

        $files = $this->get_namespace_meta_files();
        io_saveFile($files['pagelist'], implode("\n", $pages));
        unset($pages);

        // generate and save a list of all media files
        $medialist = array();
        if (in_array($opts['contenttomove'], array('media,', 'both'))) {
            search($medialist, $conf['mediadir'], 'search_media', $searchOpts, $pathToSearch);
        }

        $media_files = array();
        foreach ($medialist as $media) {
            $media_files[] = $media['id'];
        }
        unset ($medialist);

        $opts['num_media'] = count($media_files);

        io_saveFile($files['medialist'], implode("\n", $media_files));

        $opts['remaining'] = $opts['num_media'] + $opts['num_pages'];

        // save the options
        io_saveFile($files['opts'], serialize($opts));

        return $opts['num_pages'] + $opts['num_media'];
    }

    /**
     * Execute the next steps (moving up to 10 pages or media files) of the currently running namespace move
     *
     * @return bool|int False if an error occurred, otherwise the number of remaining moves
     */
    public function continue_namespace_move() {
        global $ID;
        $files = $this->get_namespace_meta_files();

        if (!@file_exists($files['opts'])) {
            msg('Error: there are no saved options', -1);
            return false;
        }

        $opts = unserialize(file_get_contents($files['opts']));

        if (@file_exists($files['pagelist'])) {
            $pagelist = fopen($files['pagelist'], 'a+');;

            for ($i = 0; $i < 10; ++$i) {
                $ID = $this->get_last_id($pagelist);
                if ($ID === false) {
                    break;
                }
                $newID = $this->getNewID($ID, $opts['ns'], $opts['newns']);
                $pageOpts = $opts;
                $pageOpts['ns']   = getNS($ID);
                $pageOpts['name'] = noNS($ID);
                $pageOpts['newname'] = noNS($ID);
                $pageOpts['newns'] = getNS($newID);
                if (!$this->move_page($pageOpts)) {
                    fclose($pagelist);
                    return false;
                }

                // update the list of pages and the options after every move
                ftruncate($pagelist, ftell($pagelist));
                $opts['remaining']--;
                io_saveFile($files['opts'], serialize($opts));
            }

            fclose($pagelist);
            if ($ID === false) unlink($files['pagelist']);
        } elseif (@file_exists($files['medialist'])) {
            $medialist = fopen($files['medialist'], 'a+');

            for ($i = 0; $i < 10; ++$i) {
                $ID = $this->get_last_id($medialist);
                if ($ID === false) {
                    break;
                }
                $newID = $this->getNewID($ID, $opts['ns'], $opts['newns']);
                $pageOpts = $opts;
                $pageOpts['ns']   = getNS($ID);
                $pageOpts['name'] = noNS($ID);
                $pageOpts['newname'] = noNS($ID);
                $pageOpts['newns'] = getNS($newID);
                if (!$this->move_media($pageOpts)) {
                    fclose($medialist);
                    return false;
                }

                // update the list of media files and the options after every move
                ftruncate($medialist, ftell($medialist));
                $opts['remaining']--;
                io_saveFile($files['opts'], serialize($opts));
            }

            fclose($medialist);
            if ($ID === false) {
                unlink($files['medialist']);
                unlink($files['opts']);
            }
        } else {
            unlink($files['opts']);
            return 0;
        }

        return $opts['remaining'];
    }

    /**
     * Skip the item that would be executed next in the current namespace move
     *
     * @return bool|int False if an error occurred, otherwise the number of remaining moves
     */
    public function skip_namespace_move_item() {
        global $ID;
        $files = $this->get_namespace_meta_files();

        if (!@file_exists($files['opts'])) {
            msg('Error: there are no saved options', -1);
            return false;
        }

        $opts = unserialize(file_get_contents($files['opts']));

        if (@file_exists($files['pagelist'])) {
            $pagelist = fopen($files['pagelist'], 'a+');

            $ID = $this->get_last_id($pagelist);
            // save the list of pages after every move
            if ($ID === false || ftell($pagelist) == 0) {
                fclose($pagelist);
                unlink($files['pagelist']);
            } else {
                ftruncate($pagelist, ftell($pagelist));;
                fclose($pagelist);
            }
        } elseif (@file_exists($files['medialist'])) {
            $medialist = fopen($files['medialist'], 'a+');

            $ID = $this->get_last_id($medialist);;
            // save the list of media files after every move
            if ($ID === false || ftell($medialist) == 0) {
                fclose($medialist);
                unlink($files['medialist']);
                unlink($files['opts']);
            } else {
                ftruncate($medialist, ftell($medialist));
            }
        } else {
            unlink($files['opts']);
        }
        if ($opts['remaining'] == 0) return 0;
        else {
            $opts['remaining']--;
            // save the options
            io_saveFile($files['opts'], serialize($opts));
            return $opts['remaining'];
        }
    }

    /**
     * Get last file id from the list that is stored in the file that is referenced by the handle
     * The handle is set to the newline before the file id
     *
     * @param resource $handle The file handle to read from
     * @return string|bool the last id from the list or false if there is none
     */
    private function get_last_id($handle) {
        // begin the seek at the end of the file
        fseek($handle, 0, SEEK_END);
        $id = '';

        // seek one backwards as long as it's possible
        while (fseek($handle, -1, SEEK_CUR) >= 0) {
            $c = fgetc($handle);
            fseek($handle, -1, SEEK_CUR); // reset the position to the character that was read

            if ($c == "\n") {
                break;
            }
            if ($c === false) return false; // EOF, i.e. the file is empty
            $id = $c.$id;
        }

        if ($id === '') return false; // nothing was read i.e. the file is empty
        else return $id;
    }

    /**
     * Abort the currently running namespace move
     */
    public function abort_namespace_move() {
        $files = $this->get_namespace_meta_files();
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * Get the options for the namespace move that is currently in progress if there is any
     *
     * @return bool|array False if there is no namespace move in progress, otherwise the array of options
     */
    public function get_namespace_move_opts() {
        $files = $this->get_namespace_meta_files();

        if (!@file_exists($files['opts'])) {
            return false;
        }

        $opts = unserialize(file_get_contents($files['opts']));

        return $opts;
    }

    /**
     * Get the filenames for the metadata of the move plugin
     *
     * @return array The file names for opts, pagelist and medialist
     */
    protected function get_namespace_meta_files() {
        global $conf;
        return array(
            'opts' => $conf['metadir'].'/__move_opts',
            'pagelist' => $conf['metadir'].'/__move_pagelist',
            'medialist' => $conf['metadir'].'/__move_medialist'
        );
    }

    /**
     * Get the id of a page after a namespace move
     *
     * @param string $oldid The old id of the page
     * @param string $oldns The old namespace. $oldid needs to be inside $oldns
     * @param string $newns The new namespace
     * @return string The new id
     */
    function getNewID($oldid, $oldns, $newns) {
        $newid = $oldid;
        if ($oldns != '') {
            $newid = substr($oldid, strlen($oldns)+1);
        }

        if ($newns != '') {
            $newid = $newns.':'.$newid;
        }

        return $newid;
    }


    /**
     * move page
     *
     * @author  Gary Owen <gary@isection.co.uk>, modified by Kay Roesler
     * @author  Michael Hamann <michael@content-space.de>
     *
     * @param array $opts
     * @param bool  $checkonly Only execute the checks if the page can be moved
     * @return bool If the move was executed
     */
    public function move_page(&$opts, $checkonly = false) {
        global $ID;

        // Check we have rights to move this document
        if ( !page_exists($ID)) {
            msg(sprintf($this->getLang('notexist'), $ID), -1);
            return false;
        }
        if ( auth_quickaclcheck($ID) < AUTH_EDIT ) {
            msg(sprintf($this->getLang('norights'), hsc($ID)), -1);
            return false;
        }

        // Check file is not locked
        // checklock checks if the page lock hasn't expired and the page hasn't been locked by another user
        // the file exists check checks if the page is reported unlocked if a lock exists which means that
        // the page is locked by the current user
        if (checklock($ID) !== false || @file_exists(wikiLockFN($ID))) {
            msg( sprintf($this->getLang('filelocked'), hsc($ID)), -1);
            return false;
        }

        // Assemble fill document name and path
        $opts['new_id'] = cleanID($opts['newns'].':'.$opts['newname']);
        $opts['new_path'] = wikiFN($opts['new_id']);

        // Has the document name and/or namespace changed?
        if ( $opts['newns'] == $opts['ns'] && $opts['newname'] == $opts['name'] ) {
            msg($this->getLang('nochange'), -1);
            return false;
        }
        // Check the page does not already exist
        if ( @file_exists($opts['new_path']) ) {
            msg(sprintf($this->getLang('existing'), $opts['newname'], ($opts['newns'] == '' ? $this->getLang('root') : $opts['newns'])), -1);
            return false;
        }

        // Check if the current user can create the new page
        if (auth_quickaclcheck($opts['new_id']) < AUTH_CREATE) {
            msg(sprintf($this->getLang('notargetperms'), $opts['new_id']), -1);
            return false;
        }

        if ($checkonly) return true;

        /**
         * End of init (checks)
         */

        $page_meta  = $this->getMoveMeta($ID);
        if (!$page_meta) $page_meta = array();
        if (!isset($page_meta['old_ids'])) $page_meta['old_ids'] = array();
        $page_meta['old_ids'][$ID] = time();

        // ft_backlinks() is not used here, as it does a hidden page and acl check but we really need all pages
        $affected_pages = idx_get_indexer()->lookupKey('relation_references', $ID);

        $data = array('opts' => &$opts, 'old_ids' => $page_meta['old_ids'], 'affected_pages' => &$affected_pages);
        // give plugins the option to add their own meta files to the list of files that need to be moved
        // to the oldfiles/newfiles array or to adjust their own metadata, database, ...
        // and to add other pages to the affected pages
        // note that old_ids is in the form 'id' => timestamp of move
        $event = new Doku_Event('PLUGIN_MOVE_PAGE_RENAME', $data);
        if ($event->advise_before()) {
            // Open the old document and change forward links
            lock($ID);
            $text = rawWiki($ID);

            $text   = $this->rewrite_content($text, $ID, array($ID => $opts['new_id']));
            $oldRev = getRevisions($ID, -1, 1, 1024); // from changelog

            // Move the Subscriptions & Indexes
            if (method_exists('Doku_Indexer', 'renamePage')) { // new feature since Spring 2013 release
                $Indexer = idx_get_indexer();
            } else {
                $Indexer = new helper_plugin_move_indexer(); // copy of the new code
            }
            if (($idx_msg = $Indexer->renamePage($ID, $opts['new_id'])) !== true
                || ($idx_msg = $Indexer->renameMetaValue('relation_references', $ID, $opts['new_id'])) !== true) {
                msg('Error while updating the search index '.$idx_msg, -1);
                return false;
            }
            if (!$this->movemeta($opts)) {
                msg('The meta files of page '.$ID.' couldn\'t be moved', -1);
                return false;
            }

            // Save the updated document in its new location
            if ($opts['ns'] == $opts['newns']) {
                $lang_key = 'renamed';
            }
            elseif ( $opts['name'] == $opts['newname'] ) {
                $lang_key = 'moved';
            }
            else {
                $lang_key = 'move_rename';
            }

            // Wait a second when the page has just been rewritten
            if ($oldRev == time()) sleep(1);

            $summary = sprintf($this->getLang($lang_key), $ID, $opts['new_id']);
            saveWikiText($opts['new_id'], $text, $summary);

            // Delete the orginal file
            if (@file_exists(wikiFN($opts['new_id']))) {
                saveWikiText($ID, '', $this->getLang('delete') );
            }

            // Move the old revisions
            if (!$this->moveattic($opts)) {
                // it's too late to stop the move, so just display a message.
                msg('The attic files of page '.$ID.' couldn\'t be moved. Please move them manually.', -1);
            }

            foreach ($affected_pages as $id) {
                if (!page_exists($id, '', false) || $id == $ID || $id == $opts['new_id']) continue;
                // we are only interested in persistent metadata, so no need to render anything.
                $meta = $this->getMoveMeta($id);
                if (!$meta) $meta = array('moves' => array());
                if (!isset($meta['moves'])) $meta['moves'] = array();
                $meta['moves'] = $this->resolve_moves($meta['moves'], $id);
                $meta['moves'][$ID] = $opts['new_id'];
                //if (empty($meta['moves'])) unset($meta['moves']);
                p_set_metadata($id, array('plugin_move' => $meta), false, true);
            }

            p_set_metadata($opts['new_id'], array('plugin_move' => $page_meta), false, true);

            unlock($ID);
        }

        $event->advise_after();
        return true;
    }

    /**
     * Move media file
     *
     * @author  Michael Hamann <michael@content-space.de>
     *
     * @param array $opts
     * @param bool  $checkonly Only execute the checks if the media file can be moved
     * @return bool If the move was executed
     */
    public function move_media(&$opts, $checkonly = false) {
        $opts['id'] = cleanID($opts['ns'].':'.$opts['name']);
        $opts['path'] = mediaFN($opts['id']);

        // Check we have rights to move this document
        if ( !file_exists(mediaFN($opts['id']))) {
            msg(sprintf($this->getLang('medianotexist'), hsc($opts['id'])), -1);
            return false;
        }

        if ( auth_quickaclcheck($opts['ns'].':*') < AUTH_DELETE ) {
            msg(sprintf($this->getLang('nomediarights'), hsc($opts['id'])), -1);
            return false;
        }

        // Assemble media name and path
        $opts['new_id'] = cleanID($opts['newns'].':'.$opts['newname']);
        $opts['new_path'] = mediaFN($opts['new_id']);

        // Has the document name and/or namespace changed?
        if ( $opts['newns'] == $opts['ns'] && $opts['newname'] == $opts['name'] ) {
            msg($this->getLang('nomediachange'), -1);
            return false;
        }
        // Check the page does not already exist
        if ( @file_exists($opts['new_path']) ) {
            msg(sprintf($this->getLang('mediaexisting'), $opts['newname'], ($opts['newns'] == '' ? $this->getLang('root') : $opts['newns'])), -1);
            return false;
        }

        // Check if the current user can create the new page
        if (auth_quickaclcheck($opts['new_ns'].':*') < AUTH_UPLOAD) {
            msg(sprintf($this->getLang('nomediatargetperms'), $opts['new_id']), -1);
            return false;
        }

        if ($checkonly) return true;

        /**
         * End of init (checks)
         */

        $affected_pages = idx_get_indexer()->lookupKey('relation_media', $opts['id']);

        $data = array('opts' => &$opts, 'affected_pages' => &$affected_pages);
        // give plugins the option to add their own meta files to the list of files that need to be moved
        // to the oldfiles/newfiles array or to adjust their own metadata, database, ...
        // and to add other pages to the affected pages
        $event = new Doku_Event('PLUGIN_MOVE_MEDIA_RENAME', $data);
        if ($event->advise_before()) {
            // Move the Subscriptions & Indexes
            if (method_exists('Doku_Indexer', 'renamePage')) { // new feature since Spring 2013 release
                $Indexer = idx_get_indexer();
            } else {
                $Indexer = new helper_plugin_move_indexer(); // copy of the new code
            }
            if (($idx_msg = $Indexer->renameMetaValue('relation_media', $opts['id'], $opts['new_id'])) !== true) {
                msg('Error while updating the search index '.$idx_msg, -1);
                return false;
            }
            if (!$this->movemediameta($opts)) {
                msg('The meta files of the media file '.$opts['id'].' couldn\'t be moved', -1);
                return false;
            }

            // prepare directory
            io_createNamespace($opts['new_id'], 'media');

            if (!io_rename($opts['path'], $opts['new_path'])) {
                msg('Moving the media file '.$opts['id'].' failed', -1);
                return false;
            }

            io_sweepNS($opts['id'], 'mediadir');

            // Move the old revisions
            if (!$this->movemediaattic($opts)) {
                // it's too late to stop the move, so just display a message.
                msg('The attic files of media file '.$opts['id'].' couldn\'t be moved. Please move them manually.', -1);
            }

            foreach ($affected_pages as $id) {
                if (!page_exists($id, '', false)) continue;
                $meta = $this->getMoveMeta($id);
                if (!$meta) $meta = array('media_moves' => array());
                if (!isset($meta['media_moves'])) $meta['media_moves'] = array();
                $meta['media_moves'] = $this->resolve_moves($meta['media_moves'], '__');
                $meta['media_moves'][$opts['id']] = $opts['new_id'];
                //if (empty($meta['moves'])) unset($meta['moves']);
                p_set_metadata($id, array('plugin_move' => $meta), false, true);
            }
        }

        $event->advise_after();
        return true;
    }

    /**
     * Move the old revisions of the media file that is specified in the options
     *
     * @param array $opts Move options (used here: name, newname, ns, newns)
     * @return bool If the attic files were moved successfully
     */
    public function movemediaattic($opts) {
        global $conf;

        $ext = mimetype($opts['name']);
        if ($ext[0] !== false) {
            $name = substr($opts['name'],0, -1*strlen($ext[0])-1);
        } else {
            $name = $opts['name'];
        }
        $newext = mimetype($opts['newname']);
        if ($ext[0] !== false) {
            $newname = substr($opts['newname'],0, -1*strlen($ext[0])-1);
        } else {
            $newname = $opts['newname'];
        }
        $regex = '\.\d+\.'.preg_quote((string)$ext[0], '/');

        return $this->move_files($conf['mediaolddir'], array(
            'ns' => $opts['ns'],
            'newns' => $opts['newns'],
            'name' => $name,
            'newname' => $newname
        ), $regex);
    }

    /**
     * Move the meta files of the page that is specified in the options.
     *
     * @param array $opts Move options (used here: name, newname, ns, newns)
     * @return bool If the meta files were moved successfully
     */
    public function movemediameta($opts) {
        global $conf;

        $regex = '\.[^.]+';
        return $this->move_files($conf['mediametadir'], $opts, $regex);
    }

    /**
     * Move the old revisions of the page that is specified in the options.
     *
     * @param array $opts Move options (used here: name, newname, ns, newns)
     * @return bool If the attic files were moved successfully
     */
    public function moveattic($opts) {
        global $conf;

        $regex = '\.\d+\.txt(?:\.gz|\.bz2)?';
        return $this->move_files($conf['olddir'], $opts, $regex);
    }

    /**
     * Move the meta files of the page that is specified in the options.
     *
     * @param array $opts Move options (used here: name, newname, ns, newns)
     * @return bool If the meta files were moved successfully
     */
    public function movemeta($opts) {
        global $conf;

        $regex = '\.[^.]+';
        return $this->move_files($conf['metadir'], $opts, $regex);
    }

    /**
     * Internal function for moving and renaming meta/attic files between namespaces
     *
     * @param string $dir   The root path of the files (e.g. $conf['metadir'] or $conf['olddir']
     * @param array  $opts  Move options (used here: ns, newns, name, newname)
     * @param string $extregex Regular expression for matching the extension of the file that shall be moved
     * @return bool If the files were moved successfully
     */
    private function move_files($dir, $opts, $extregex) {
        $old_path = $dir;
        if ($opts['ns'] != '') $old_path .= '/'.utf8_encodeFN(str_replace(':', '/', $opts['ns']));
        $new_path = $dir;
        if ($opts['newns'] != '') $new_path .= '/'.utf8_encodeFN(str_replace(':', '/', $opts['newns']));
        $regex = '/^'.preg_quote(utf8_encodeFN($opts['name'])).'('.$extregex.')$/u';

        if (!is_dir($old_path)) return true; // no media files found

        $dh = @opendir($old_path);
        if($dh) {
            while(($file = readdir($dh)) !== false) {
                if (substr($file, 0, 1) == '.') continue;
                $match = array();
                if (is_file($old_path.'/'.$file) && preg_match($regex, $file, $match)) {
                    if (!is_dir($new_path)) {
                        if (!io_mkdir_p($new_path)) {
                            msg('Creating directory '.hsc($new_path).' failed.', -1);
                            return false;
                        }
                    }
                    if (!io_rename($old_path.'/'.$file, $new_path.'/'.utf8_encodeFN($opts['newname'].$match[1]))) {
                        msg('Moving '.hsc($old_path.'/'.$file).' to '.hsc($new_path.'/'.utf8_encodeFN($opts['newname'].$match[1])).' failed.', -1);
                        return false;
                    }
                }
            }
            closedir($dh);
        } else {
            msg('Directory '.hsc($old_path).' couldn\'t be opened.', -1);
            return false;
        }
        return true;
    }

    /**
     * Rewrite the text of a page according to the recorded moves, the rewritten text is saved
     *
     * @param string      $id   The id of the page that shall be rewritten
     * @param string|null $text Old content of the page. When null is given the content is loaded from disk.
     * @return string The rewritten content
     */
    public function execute_rewrites($id, $text = null) {
        $meta = $this->getMoveMeta($id);
        if($meta && (isset($meta['moves']) || isset($meta['media_moves']))) {
            if(is_null($text)) $text = rawWiki($id);
            $moves = isset($meta['moves']) ? $meta['moves'] : array();
            $media_moves = isset($meta['media_moves']) ? $meta['media_moves'] : array();

            $old_text = $text;
            $text = $this->rewrite_content($text, $id, $moves, $media_moves);
            $changed = ($old_text != $text);
            $file = wikiFN($id, '', false);
            if(is_writable($file) || !$changed) {
                if ($changed) {
                    // Wait a second if page has just been saved
                    $oldRev = getRevisions($id, -1, 1, 1024); // from changelog
                    if ($oldRev == time()) sleep(1);
                    saveWikiText($id, $text, $this->getLang('linkchange'));
                }
                unset($meta['moves']);
                unset($meta['media_moves']);
                p_set_metadata($id, array('plugin_move' => $meta), false, true);
            } else { // FIXME: print error here or fail silently?
                msg('Error: Page '.hsc($id).' needs to be rewritten because of page renames but is not writable.', -1);
            }
        }

        return $text;
    }

    /**
     * Rewrite a text in order to fix the content after the given moves.
     *
     * @param string $text   The wiki text that shall be rewritten
     * @param string $id     The id of the wiki page, if the page itself was moved the old id
     * @param array $moves  Array of all page moves, the keys are the old ids, the values the new ids
     * @param array $media_moves Array of all media moves.
     * @return string        The rewritten wiki text
     */
    function rewrite_content($text, $id, $moves, $media_moves = array()) {
        $moves = $this->resolve_moves($moves, $id);
        $media_moves = $this->resolve_moves($media_moves, $id);

        $handlers = array();
        $data     = array('id' => $id, 'moves' => &$moves, 'media_moves' => &$media_moves, 'handlers' => &$handlers);

        /*
         * PLUGIN_MOVE_HANDLERS REGISTER event:
         *
         * Plugin handlers can be registered in the $handlers array, the key is the plugin name as it is given to the handler
         * The handler needs to be a valid callback, it will get the following parameters:
         * $match, $state, $pos, $pluginname, $handler. The first three parameters are equivalent to the parameters
         * of the handle()-function of syntax plugins, the $pluginname is just the plugin name again so handler functions
         * that handle multiple plugins can distinguish for which the match is. The last parameter is the handler object.
         * It has the following properties and functions that can be used:
         * - id, ns: id and namespace of the old page
         * - new_id, new_ns: new id and namespace (can be identical to id and ns)
         * - moves: array of moves, the same as $moves in the event
         * - media_moves: array of media moves, same as $media_moves in the event
         * - adaptRelativeId($id): adapts the relative $id according to the moves
         */
        trigger_event('PLUGIN_MOVE_HANDLERS_REGISTER', $data);

        $modes = p_get_parsermodes();

        // Create the parser
        $Parser = new Doku_Parser();

        // Add the Handler
        $Parser->Handler = new helper_plugin_move_handler($id, $moves, $media_moves, $handlers);

        //add modes to parser
        foreach($modes as $mode) {
            $Parser->addMode($mode['mode'], $mode['obj']);
        }

        return $Parser->parse($text);
    }

    /**
     * Resolves the provided moves, i.e. it calculates for each page the final page it was moved to.
     *
     * @param array $moves The moves
     * @param string $id
     * @return array The resolved moves
     */
    protected function resolve_moves($moves, $id) {
        // resolve moves of pages that were moved more than once
        $tmp_moves = array();
        foreach($moves as $old => $new) {
            if($old != $id && isset($moves[$new]) && $moves[$new] != $new) {
                // write to temp array in order to correctly handle rename circles
                $tmp_moves[$old] = $moves[$new];
            }
        }

        $changed = !empty($tmp_moves);

        // this correctly resolves rename circles by moving forward one step a time
        while($changed) {
            $changed = false;
            foreach($tmp_moves as $old => $new) {
                if($old != $new && isset($moves[$new]) && $moves[$new] != $new && $tmp_moves[$new] != $new) {
                    $tmp_moves[$old] = $moves[$new];
                    $changed         = true;
                }
            }
        }

        // manual merge, we can't use array_merge here as ids can be numeric
        foreach($tmp_moves as $old => $new) {
            if($old == $new) unset($moves[$old]);
            else $moves[$old] = $new;
        }
        return $moves;
    }

    /**
     * Get the HTML code of a namespace move button
     * @param string $action The desired action of the button (continue, tryagain, skip, abort)
     * @param string|null $id The id of the target page, null if $ID shall be used
     * @return bool|string The HTML of code of the form or false if an invalid action was supplied
     */
    public function getNSMoveButton($action, $id = NULL) {
        if ($id === NULL) {
            global $ID;
            $id = $ID;
        }

        $class = 'move__nsform';
        switch ($action) {
            case 'continue':
            case 'tryagain':
                $class .= ' move__nscontinue';
                break;
            case 'skip':
                $class .= ' move__nsskip';
                break;
        }

        $form = new Doku_Form(array('action' => wl($id), 'method' => 'post', 'class' => $class));
        $form->addHidden('page', $this->getPluginName());
        $form->addHidden('id', $id);
        switch ($action) {
            case 'continue':
            case 'tryagain':
                $form->addHidden('continue_namespace_move', true);
                if ($action == 'tryagain') {
                    $form->addElement(form_makeButton('submit', 'admin', $this->getLang('ns_move_tryagain')));
                } else {
                    $form->addElement(form_makeButton('submit', 'admin', $this->getLang('ns_move_continue')));
                }
                break;
            case 'skip':
                $form->addHidden('skip_continue_namespace_move', true);
                $form->addElement(form_makeButton('submit', 'admin', $this->getLang('ns_move_skip')));
                break;
            case 'abort':
                $form->addHidden('abort_namespace_move', true);
                $form->addElement(form_makeButton('submit', 'admin', $this->getLang('ns_move_abort')));
                break;
            default:
                return false;
        }
        return $form->getForm();
    }

    /**
     * This function loads and returns the persistent metadata for the move plugin. If there is metadata for the
     * pagemove plugin (not the old one but the version that immediately preceeded the move plugin) it will be migrated.
     *
     * @param string $id The id of the page the metadata shall be loaded for
     * @return array|null The metadata of the page
     */
    public function getMoveMeta($id) {
        $all_meta = p_get_metadata($id, '', METADATA_DONT_RENDER);
        // migrate old metadata from the pagemove plugin
        if (isset($all_meta['plugin_pagemove']) && !is_null($all_meta['plugin_pagemove'])) {
            if (isset($all_meta['plugin_move'])) {
                $all_meta['plugin_move'] = array_merge_recursive($all_meta['plugin_pagemove'], $all_meta['plugin_move']);
            } else {
                $all_meta['plugin_move'] = $all_meta['plugin_pagemove'];
            }
            p_set_metadata($id, array('plugin_move' => $all_meta['plugin_move'], 'plugin_pagemove' => null), false, true);
        }
        return isset($all_meta['plugin_move']) ? $all_meta['plugin_move'] : null;
    }
}

/**
 * Indexer class extended by move features, only needed and used in releases older than Spring 2013
 */
class helper_plugin_move_indexer extends Doku_Indexer {
    /**
     * Rename a page in the search index without changing the indexed content
     *
     * @param string $oldpage The old page name
     * @param string $newpage The new page name
     * @return string|bool If the page was successfully renamed, can be a message in the case of an error
     */
    public function renamePage($oldpage, $newpage) {
        if (!$this->lock()) return 'locked';

        $pages = $this->getPages();

        $id = array_search($oldpage, $pages);
        if ($id === false) {
            $this->unlock();
            return 'page is not in index';
        }

        $new_id = array_search($newpage, $pages);
        if ($new_id !== false) {
            $this->unlock();
            // make sure the page is not in the index anymore
            $this->deletePage($newpage);
            if (!$this->lock()) return 'locked';

            $pages[$new_id] = 'deleted:'.time().rand(0, 9999);
        }

        $pages[$id] = $newpage;

        // update index
        if (!$this->saveIndex('page', '', $pages)) {
            $this->unlock();
            return false;
        }

        $this->unlock();
        return true;
    }

    /**
     * Renames a meta value in the index. This doesn't change the meta value in the pages, it assumes that all pages
     * will be updated.
     *
     * @param string $key       The metadata key of which a value shall be changed
     * @param string $oldvalue  The old value that shall be renamed
     * @param string $newvalue  The new value to which the old value shall be renamed, can exist (then values will be merged)
     * @return bool|string      If renaming the value has been successful, false or error message on error.
     */
    public function renameMetaValue($key, $oldvalue, $newvalue) {
        if (!$this->lock()) return 'locked';

        // change the relation references index
        $metavalues = $this->getIndex($key, '_w');
        $oldid = array_search($oldvalue, $metavalues);
        if ($oldid !== false) {
            $newid = array_search($newvalue, $metavalues);
            if ($newid !== false) {
                // free memory
                unset ($metavalues);

                // okay, now we have two entries for the same value. we need to merge them.
                $indexline = $this->getIndexKey($key, '_i', $oldid);
                if ($indexline != '') {
                    $newindexline = $this->getIndexKey($key, '_i', $newid);
                    $pagekeys     = $this->getIndex($key, '_p');
                    $parts = explode(':', $indexline);
                    foreach ($parts as $part) {
                        list($id, $count) = explode('*', $part);
                        $newindexline =  $this->updateTuple($newindexline, $id, $count);

                        $keyline = explode(':', $pagekeys[$id]);
                        // remove old meta value
                        $keyline = array_diff($keyline, array($oldid));
                        // add new meta value when not already present
                        if (!in_array($newid, $keyline)) {
                            array_push($keyline, $newid);
                        }
                        $pagekeys[$id] = implode(':', $keyline);
                    }
                    $this->saveIndex($key, '_p', $pagekeys);
                    unset($pagekeys);
                    $this->saveIndexKey($key, '_i', $oldid, '');
                    $this->saveIndexKey($key, '_i', $newid, $newindexline);
                }
            } else {
                $metavalues[$oldid] = $newvalue;
                if (!$this->saveIndex($key, '_w', $metavalues)) {
                    $this->unlock();
                    return false;
                }
            }
        }

        $this->unlock();
        return true;
    }
}

/**
 * Handler class for move. It does the actual rewriting of the content.
 */
class helper_plugin_move_handler {
    public $calls = '';
    public $id;
    public $ns;
    public $new_id;
    public $new_ns;
    public $moves;
    public $media_moves;
    private $handlers;

    /**
     * Construct the move handler.
     *
     * @param string $id       The id of the text that is passed to the handler
     * @param array $moves    Moves that shall be considered in the form $old => $new ($old can be $id)
     * @param array $media_moves Moves of media files that shall be considered in the form $old => $new
     * @param array $handlers Handlers for plugin content in the form $plugin_anme => $callback
     */
    public function __construct($id, $moves, $media_moves, $handlers) {
        $this->id = $id;
        $this->ns = getNS($id);
        $this->moves = $moves;
        $this->media_moves = $media_moves;
        $this->handlers = $handlers;
        if (isset($moves[$id])) {
            $this->new_id = $moves[$id];
            $this->new_ns = getNS($moves[$id]);
        } else {
            $this->new_id = $id;
            $this->new_ns = $this->ns;
        }
    }

    /**
     * Handle camelcase links
     *
     * @param string $match  The text match
     * @param string $state  The starte of the parser
     * @param int    $pos    The position in the input
     * @return bool If parsing should be continued
     */
    public function camelcaselink($match, $state, $pos) {
        if ($this->ns)
            $old = cleanID($this->ns.':'.$match);
        else
            $old = cleanID($match);
        if (isset($this->moves[$old]) || $this->id != $this->new_id) {
            if (isset($this->moves[$old])) {
                $new = $this->moves[$old];
            } else {
                $new = $old;
            }
            $new_ns = getNS($new);
            // preserve capitalization either in the link or in the title
            if (noNS($new) == noNS($old)) {
                // camelcase link still seems to work
                if ($new_ns == $this->new_ns) {
                    $this->calls .= $match;
                } else { // just the namespace was changed, the camelcase word is a valid id
                    $this->calls .= "[[$new_ns:$match]]";
                }
            } else {
                $this->calls .= "[[$new|$match]]";
            }
        } else {
            $this->calls .= $match;
        }
        return true;
    }

    /**
     * Handle rewriting of internal links
     *
     * @param string $match  The text match
     * @param string $state  The starte of the parser
     * @param int    $pos    The position in the input
     * @return bool If parsing should be continued
     */
    public function internallink($match, $state, $pos) {
        // Strip the opening and closing markup
        $link = preg_replace(array('/^\[\[/','/\]\]$/u'),'',$match);

        // Split title from URL
        $link = explode('|',$link,2);
        if ( !isset($link[1]) ) {
            $link[1] = NULL;
        } else if ( preg_match('/^\{\{[^\}]+\}\}$/',$link[1]) ) {
            // If the title is an image, rewrite it
            $old_title = $link[1];
            $link[1] = $this->rewrite_media($link[1]);
            // do a simple replace of the first match so really only the id is changed and not e.g. the alignment
            $oldpos = strpos($match, $old_title);
            $oldlen = strlen($old_title);
            $match  = substr_replace($match, $link[1], $oldpos, $oldlen);
        }
        $link[0] = trim($link[0]);


        //decide which kind of link it is

        if ( preg_match('/^[a-zA-Z0-9\.]+>{1}.*$/u',$link[0]) ) {
            // Interwiki
            $this->calls .= $match;
        }elseif ( preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u',$link[0]) ) {
            // Windows Share
            $this->calls .= $match;
        }elseif ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$link[0]) ) {
            // external link (accepts all protocols)
            $this->calls .= $match;
        }elseif ( preg_match('<'.PREG_PATTERN_VALID_EMAIL.'>',$link[0]) ) {
            // E-Mail (pattern above is defined in inc/mail.php)
            $this->calls .= $match;
        }elseif ( preg_match('!^#.+!',$link[0]) ){
            // local link
            $this->calls .= $match;
        }else{
            $id = $link[0];

            $hash = '';
            $parts = explode('#', $id, 2);
            if (count($parts) === 2) {
                $id = $parts[0];
                $hash = $parts[1];
            }

            $params = '';
            $parts = explode('?', $id, 2);
            if (count($parts) === 2) {
                $id = $parts[0];
                $params = $parts[1];
            }


            $new_id = $this->adaptRelativeId($id);

            if ($id == $new_id) {
                $this->calls .= $match;
            } else {
                if ($params !== '') {
                    $new_id.= '?'.$params;
                }

                if ($hash !== '') {
                    $new_id .= '#'.$hash;
                }

                if ($link[1] != NULL) {
                    $new_id .= '|'.$link[1];
                }

                $this->calls .= '[['.$new_id.']]';
            }

        }

        return true;

    }

    /**
     * Handle rewriting of media links
     *
     * @param string $match  The text match
     * @param string $state  The starte of the parser
     * @param int    $pos    The position in the input
     * @return bool If parsing should be continued
     */
    public function media($match, $state, $pos) {
        $this->calls .= $this->rewrite_media($match);
        return true;
    }

    /**
     * Rewrite a media syntax
     *
     * @param string $match The text match of the media syntax
     * @return string The rewritten syntax
     */
    protected function rewrite_media($match) {
        $p = Doku_Handler_Parse_Media($match);
        if ($p['type'] == 'internalmedia') { // else: external media
            $new_src = $this->adaptRelativeId($p['src'], true);
            if ($new_src !== $p['src']) {
                // do a simple replace of the first match so really only the id is changed and not e.g. the alignment
                $srcpos = strpos($match, $p['src']);
                $srclen = strlen($p['src']);
                return substr_replace($match, $new_src, $srcpos, $srclen);
            }
        }
        return $match;
    }

    /**
     * Handle rewriting of plugin syntax, calls the registered handlers
     *
     * @param string $match  The text match
     * @param string $state  The starte of the parser
     * @param int    $pos    The position in the input
     * @param string $pluginname The name of the plugin
     * @return bool If parsing should be continued
     */
    public function plugin($match, $state, $pos, $pluginname) {
        if (isset($this->handlers[$pluginname])) {
            $this->calls .= call_user_func($this->handlers[$pluginname], $match, $state, $pos, $pluginname, $this);
        } else {
            $this->calls .= $match;
        }
        return true;
    }

    /**
     * Catchall handler for the remaining syntax
     *
     * @param string $name Function name that was called
     * @param array  $params Original parameters
     * @return bool If parsing should be continue
     */
    public function __call($name, $params) {
        if (count($params) == 3) {
            $this->calls .= $params[0];
            return true;
        } else {
            trigger_error('Error, handler function '.hsc($name).' with '.count($params).' parameters called which isn\'t implemented', E_USER_ERROR);
            return false;
        }
    }

    public function _finalize() {
        // remove padding that is added by the parser in parse()
        $this->calls = substr($this->calls, 1, -1);
    }

    /**
     * Adapts a link respecting all moves and making it a relative link according to the new id
     *
     * @param string $id A relative id
     * @param bool $media If the id is a media id
     * @return string The relative id, adapted according to the new/old id and the moves
     */
    public function adaptRelativeId($id, $media = false) {
        global $conf;

        if ($id === '') {
            return $id;
        }

        $abs_id = str_replace('/', ':', $id);
        $abs_id = resolve_id($this->ns, $abs_id, false);
        if (substr($abs_id, -1) === ':')
            $abs_id .= $conf['start'];
        $clean_id = cleanID($abs_id);
        // FIXME this simply assumes that the link pointed to :$conf['start'], but it could also point to another page
        // resolve_pageid does a lot more here, but we can't really assume this as the original pages might have been
        // deleted already
        if (substr($clean_id, -1) === ':')
            $clean_id .= $conf['start'];

        if (($media ? isset($this->media_moves[$clean_id]) : isset($this->moves[$clean_id])) || $this->ns !== $this->new_ns) {
            if (!$media && isset($this->moves[$clean_id])) {
                $new = $this->moves[$clean_id];
            } elseif ($media && isset($this->media_moves[$clean_id])) {
                $new = $this->media_moves[$clean_id];
            } else {
                $new = $clean_id;

                // only the namespace was changed so if the link still resolves to the same absolute id, we can skip the rest
                $new_abs_id = str_replace('/', ':', $id);
                $new_abs_id = resolve_id($this->new_ns, $new_abs_id, false);
                if (substr($new_abs_id, -1) === ':')
                    $new_abs_id .= $conf['start'];
                if ($new_abs_id == $abs_id) return $id;
            }
            $new_link = $new;
            $new_ns = getNS($new);
            // try to keep original pagename
            if ($this->noNS($new) == $this->noNS($clean_id)) {
                if ($new_ns == $this->new_ns) {
                    $new_link = $this->noNS($id);
                    if ($new_link === false) $new_link = $this->noNS($new);
                    if ($id == ':')
                        $new_link = ':';
                    else if ($id == '/')
                        $new_link = '/';
                } else if ($new_ns != false) {
                    $new_link = $new_ns.':'.$this->noNS($id);
                } else {
                    $new_link = $this->noNS($id);
                    if ($new_link === false) $new_link = $new;
                }
            } else if ($new_ns == $this->new_ns) {
                $new_link = $this->noNS($new_link);
            } else if (strpos($new_ns, $this->ns.':') === 0) {
                $new_link = '.:'.substr($new_link, strlen($this->ns)+1);
            }

            if ($this->new_ns != '' && $new_ns == false) {
                $new_link = ':'.$new_link;
            }

            return $new_link;
        } else {
            return $id;
        }
    }

    /**
     * Remove the namespace from the given id like noNS(), but handles '/' as namespace separator
     * @param string $id the id
     * @return string the id without the namespace
     */
    private function noNS($id) {
        $pos = strrpos($id, ':');
        $spos = strrpos($id, '/');
        if ($pos === false) $pos = $spos;
        if ($spos === false) $spos = $pos;
        $pos = max($pos, $spos);
        if ($pos!==false) {
            return substr($id, $pos+1);
        } else {
            return $id;
        }
    }
}

