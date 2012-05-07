<?php
/**
 * DokuWiki Plugin profiellink (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Gerrit Uitslag
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_profiellink extends DokuWiki_Syntax_Plugin {
    function getType() {
        return 'substition';
    }

    function getPType() {
        return 'normal';
    }

    function getSort() {
        return 150;
    }


    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[user>.+?\]\]',$mode,'plugin_profiellink');
    }

    function handle($match, $state, $pos, &$handler){
        $match = trim(substr($match,7,-2));


        list($uid,$title) = explode('|',$match,2);

        return compact('uid','title');
    }

    function render($mode, &$R, $data) {
        global $auth;
        global $conf;
        extract($data);

        if($mode != 'xhtml' || is_null($auth)){
            $R->cdata($title?$title:$uid);
            return true;
        }

        // fetch userinfo
        $uinfo = $auth->getUserData($uid);

        // nothing found? render as text
        if(!$uinfo){
            $R->cdata($title?$title:$uid);
            return true;
        }

        if(!$title){
            if($this->getConf('usefullname')){
                $title = $uinfo['name'];
            }else{
                $title = $uid;
            }
        }
        if(!$title) $title = $uid;


        $R->doc .= '<a href="'.$this->getConf('profileurl').$uid.'" class="profiellink_plugin">';
        $R->doc .= hsc($title);

        $R->doc .= '<span class="profiellink_popup" title="Bekijk Profiel">';
        $R->doc .= '<img src="'.$uinfo['pasfoto'].'" class="medialeft" width="48" height="64" alt="" />';
        $R->doc .= '<b>'.hsc($uinfo['name']).'</b><br />';
        if($uinfo['name'] != $uid) $R->doc .= '<i>'.hsc($uid).'</i><br />';
        $R->doc .= '</span>';

        $R->doc .= '</a>';

        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
