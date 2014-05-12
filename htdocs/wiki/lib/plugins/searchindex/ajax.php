<?php
/**
 * AJAX call handler for searchindex plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && $HTTP_RAW_POST_DATA){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/indexer.php');
//close sesseion
session_write_close();

header('Content-Type: text/plain; charset=utf-8');

//we only work for admins!
if (auth_quickaclcheck($conf['start']) < AUTH_ADMIN){
    die('access denied');
}

//call the requested function
$call = 'ajax_'.$_POST['call'];
if (function_exists($call)) {
    $call();
} else {
    print "The called function '".htmlspecialchars($call)."' does not exist!";
}

/**
 * Searches for pages
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_pagelist(){
    global $conf;
    $data = array();
    search($data,$conf['datadir'], 'search_allpages', array());

    foreach($data as $val) {
        print $val['id']."\n";
    }
}

/**
 * Clear all index files
 */
function ajax_clearindex() {
    global $conf;
    // keep running
    @ignore_user_abort(true);

    // acquire a lock
    $lock = $conf['lockdir'] . '/_indexer.lock';
    while (!@mkdir($lock)){
        if (time()-@filemtime($lock) > 60*5) {
            // looks like a stale lock - remove it
            @rmdir($lock);
        } else {
            print 'indexer is locked.';
            exit;
        }
    }

    io_saveFile($conf['indexdir'].'/page.idx','');
    io_saveFile($conf['indexdir'].'/title.idx','');
    io_saveFile($conf['indexdir'].'/pageword.idx','');
    io_saveFile($conf['indexdir'].'/metadata.idx','');
    $dir = @opendir($conf['indexdir']);
    if ($dir!==false) {
        while (($f = readdir($dir)) !== false) {
            if (substr($f,-4)=='.idx' &&
               (substr($f,0,1)=='i' || substr($f,0,1)=='w'
               || substr($f,-6)=='_w.idx' || substr($f,-6)=='_i.idx' || substr($f,-6)=='_p.idx'))
                @unlink($conf['indexdir']."/$f");
        }
    }
    @unlink($conf['indexdir'].'/lengths.idx');

    // we're finished
    @rmdir($lock);

    print 'true';
}

/**
 * Index the given page
 *
 * We're doing basicly the same as the real indexer but ignore the
 * last index time here
 */
function ajax_indexpage() {
    global $conf;
    $force = false;

    if (!$_POST['page']) {
        print 0;
        exit;
    }
    if (isset($_POST['force'])) {
        $force = $_POST['force'] == 'true';
    }

    // keep running
    @ignore_user_abort(true);

    // try to aquire a lock (newer releases do the locking in idx_addPage)
    if (INDEXER_VERSION < 4){
        $lock = $conf['lockdir'].'/_indexer.lock';
        while (!@mkdir($lock)){
            if (time()-@filemtime($lock) > 60*5) {
                // looks like a stale lock - remove it
                @rmdir($lock);
            } else {
                print 'indexer is locked.';
                exit;
            }
        }
    }

    // do the work
    $success = idx_addPage($_POST['page'], false, $force);

    // we're finished
    if (INDEXER_VERSION < 4){
        io_saveFile(metaFN($id,'.indexed'),'');
        @rmdir($lock);
    }

    print ($success !== false) ? 'true' : '';
}

