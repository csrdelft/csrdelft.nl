<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_fontcolor extends DokuWiki_Action_Plugin {

    /**
     * return some info
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function getInfo(){
        return array_merge(confToHash(dirname(__FILE__).'/README'), array('name' => 'Toolbar Component'));
    }

    /**
     * register the eventhandlers
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function register(&$controller){
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'handle_toolbar', array ());
    }

    function handle_toolbar(&$event, $param) {
        $event->data[] = array (
            'type' => 'picker',
            'title' => $this->getLang('picker'),
            'icon' => '../../plugins/fontcolor/images/toolbar/picker.png',
            'list' => array(
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('black'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/black.png',
                    'open'   => '<fc #000000>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('maroon'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/maroon.png',
                    'open'   => '<fc #800000>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('green'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/green.png',
                    'open'   => '<fc #008000>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('olive'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/olive.png',
                    'open'   => '<fc #808000>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('navy'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/navy.png',
                    'open'   => '<fc #000080>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('purple'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/purple.png',
                    'open'   => '<fc #800080>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('teal'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/teal.png',
                    'open'   => '<fc #008080>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('silver'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/silver.png',
                    'open'   => '<fc #C0C0C0>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('gray'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/gray.png',
                    'open'   => '<fc #808080>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('red'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/red.png',
                    'open'   => '<fc #FF0000>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('lime'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/lime.png',
                    'open'   => '<fc #00FF00>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('yellow'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/yellow.png',
                    'open'   => '<fc #FFFF00>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('blue'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/blue.png',
                    'open'   => '<fc #0000FF>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('fuchsia'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/fuchsia.png',
                    'open'   => '<fc #FF00FF>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('aqua'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/aqua.png',
                    'open'   => '<fc #00FFFF>',
                    'close'  => '</fc>',
                ),
                array(
                    'type'   => 'format',
                    'title'  => $this->getLang('white'),
                    'icon'   => '../../plugins/fontcolor/images/toolbar/white.png',
                    'open'   => '<fc #FFFFFF>',
                    'close'  => '</fc>',
                ),
            )
        );
    }
}

