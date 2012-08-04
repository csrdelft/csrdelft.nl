<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
require_once(DOKU_INC.'inc/confutils.php');

class admin_plugin_confmanager extends DokuWiki_Admin_Plugin {

    var $cnffiles = array('acronyms','entities','interwiki','mime','smileys');

    function getMenuSort() {
        return 101;
    }

    function handle() {
        if (isset($_REQUEST['cnf']) && in_array($_REQUEST['cnf'],$this->cnffiles)) {
            if (isset($_REQUEST['local']))
                $this->_change($_REQUEST['cnf'],$_REQUEST['local']);
            if (isset($_REQUEST['newkey']) && isset($_REQUEST['newval']))
                $this->_add($_REQUEST['cnf'],$_REQUEST['newkey'],$_REQUEST['newval']);
        }
    }

    function _sortConf( $k1 , $k2 ) {
        return strlen( $k2 ) - strlen( $k1 );
    }

    function _sortHuman( $k1 , $k2 ) {
        $k1 = strtolower($k1);
        $k2 = strtolower($k2);
        return strnatcmp($k1,$k2);
    }

    /**
     * Change the whole array
     *
     * Writes back to the most local file
     */
    function _change($name,$conf) {
        global $config_cascade;

        $org  = $this->_readConf($name, true); // the defaults
        $file = end($config_cascade[$name]['local']);

        $cnftext = '';
        uksort( $conf , array( &$this , '_sortConf' ));
        foreach ($conf as $k => $v) {
            $k = str_replace(' ','',$k);
            $k = str_replace("\t",'',$k);
            $v = trim($v);
            if( $k === '' ) continue;
            if( $v === '' ) continue;

            if ( ! isset($org[$k]) ) { // add new key
                $cnftext.= sprintf("%-30s %s\n",$this->_escape($k),$this->_escape($v));
                continue;
            }
            if ( $org[$k] != $v ) { // overwrite a key
                $cnftext.= sprintf("%-30s %s\n",$this->_escape($k),$this->_escape($v));
                continue;
            }
        }
        if (empty($cnftext)){
            @unlink($file);
        }else{
            file_put_contents($file,$cnftext);
        }
    }

    /**
     * Add a new item pair
     */
    function _add($name,$key,$val) {
        $conf = $this->_readConf($name); // current config
        $conf[$key] = $val;
        $this->_change($name,$conf);
    }

    function html() {
        if (isset($_REQUEST['cnf']) && in_array($_REQUEST['cnf'],$this->cnffiles)) {
            $this->display($_REQUEST['cnf']);
        } else {
            $this->welcome();
        }
        $this->edit();
    }

    function display($name) {
        $data    = $this->_readConf($name);         // all values
        $default = $this->_readConf($name,true);    // default values
        uksort( $data , array( &$this , '_sortHuman' ) );


        ptln('<h1>'.$this->getLang('head_'.$name).'</h1>');
        ptln('<div class="level1">');
        ptln('<p>'.$this->getLang('text_'.$name).'</p>');
        ptln('<p>'.$this->getLang('edit_desc').'</p>');
        ptln('<form method="post" action="doku.php">');
        ptln('<input type="hidden" name="do" value="'.$_REQUEST['do'].'" />');
        ptln('<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />');
        ptln('<input type="hidden" name="id" value="'.$_REQUEST['id'].'" />');
        ptln('<input type="hidden" name="cnf" value="'.hsc($_REQUEST['cnf']).'" />');
        ptln('<table class="confmanager">');
        foreach( $data as $k => $v ) {
            ptln('<tr>');
            ptln('<td>');
            if (isset($default[$k])) ptln('<b>');
            ptln( hsc($k) );
            if (isset($default[$k])) ptln('</b>');
            ptln('</td>');
            ptln('<td>');
            ptln('<input type="text" name="local['.hsc($k).']" value="'.hsc($v).'" class="edit val" /></td>');
            ptln('</tr>');
        }
        ptln('</table>');
        ptln('<input class="button" type="submit" value="'.$this->getLang('submitchanges').'" />');
        ptln('<input class="button" type="reset" value="'.$this->getLang('reset').'" />');
        ptln('</form>');
        ptln('</div>');

        ptln('<h2><a name="__add">'.$this->getLang('addvarhead').'</a></h2>');
        ptln('<div class="level2">');
        ptln('<p>');
        ptln($this->getLang('addvartext'));
        ptln('<form method="post" action="doku.php">');
        ptln('<input type="hidden" name="do" value="'.$_REQUEST['do'].'" />');
        ptln('<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />');
        ptln('<input type="hidden" name="id" value="'.$_REQUEST['id'].'" />');
        ptln('<input type="hidden" name="cnf" value="'.hsc($_REQUEST['cnf']).'" />');
        ptln('<input type="text" name="newkey" class="edit key" /> ');
        ptln('<input type="text" name="newval" class="edit val" />');
        ptln('<input type="submit" value="'.$this->getLang('additem').'" class="button" />');
        ptln('</form>');
        ptln('</p>');
        ptln('</div>');
    }

    /**
     * Escape the hash sign for writing to config files
     */
    function _escape($s) {
        return str_replace('#','\#',$s);
    }

    /**
     * Read the wanted config, optionally skip the one we write to
     */
    function _readConf($name,$skiplocal=false) {
        global $config_cascade;
        $dfiles = (array) $config_cascade[$name]['default'];
        $lfiles = (array) $config_cascade[$name]['local'];
        if($skiplocal) array_pop($lfiles); // we write back to this file

        $result = array();
        foreach (array_merge($dfiles,$lfiles) as $file){
            $cnf = confToHash($file);
            $result = array_merge($result,$cnf);
        }

        return $result;
    }

    function welcome() {
        ptln('<h1>'.$this->getLang('welcomehead').'</h1>');
        ptln('<div class="level1">');
        ptln('<p>'.$this->getLang('welcome').'</p>');
        ptln('</div>');
    }

    function edit() {
        ptln('<h2>'.$this->getLang('edithead').'</h2>');
        ptln('<div class="level2">');
        ptln('<p>'.$this->getLang('editdesc').'</p>');
        ptln('<form method="post" action="doku.php">');
        ptln('<input type="hidden" name="do" value="'.$_REQUEST['do'].'" />');
        ptln('<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />');
        ptln('<input type="hidden" name="id" value="'.$_REQUEST['id'].'" />');
        ptln('<select name="cnf">');
        foreach ($this->cnffiles as $cnf) {
            ptln('<option value="'.$cnf.'">'.$this->getLang('cnf_'.$cnf).'</option>');
        }
        ptln('</select>');
        ptln('<input type="submit" value="'.$this->getLang('editcnf').'" class="button" />');
        ptln('</form>');
        ptln('</div>');
    }
}
