<?php
/**
 * Plugin imagereference
 *
 * Syntax: <imgref linkname> - creates a figure link to an image
 *         <imgcaption linkname <orientation> | Image caption> Image/Table</imgcaption>
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Martin Heinemann <martinheinemann@tudor.lu>
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */

if(!defined('DOKU_INC')) die();

if(!defined('DOKU_LF')) define('DOKU_LF', "\n");
if(!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_imagereference_imgcaption extends DokuWiki_Syntax_Plugin {

    /* @var array $_captionparam */
    var $_captionparam = array();

    /**
     * @return string Syntax type
     */
    function getType() {
        return 'formatting';
    }

    /**
     * @return string Paragraph type
     */
    function getPType() {
        return 'normal';
    }

    /**
     * @return int Sort order
     */
    function getSort() {
        return 196;
    }

    /**
     * Specify modes allowed in the imgcaption/tabcaption
     * Using getAllowedTypes() includes too much modes.
     *
     * @param string $mode Parser mode
     * @return bool true if $mode is accepted
     */
    function accepts($mode) {
        $allowedsinglemodes = array(
            'media', //allowed content
            'internallink', 'externallink', 'linebreak', //clickable img allowed
            'emaillink', 'windowssharelink', 'filelink'
        );
        if(in_array($mode, $allowedsinglemodes)) return true;

        return parent::accepts($mode);
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<imgcaption.*?>(?=.*?</imgcaption>)', $mode, 'plugin_imagereference_imgcaption');

    }

    function postConnect() {
        $this->Lexer->addExitPattern('</imgcaption>', 'plugin_imagereference_imgcaption');
    }

    /**
     * Handle matches of the imgcaption/tabcaption syntax
     *
     * @param string          $match The match of the syntax
     * @param int             $state The state of the handler
     * @param int             $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    function handle($match, $state, $pos, &$handler) {

        switch($state) {
            case DOKU_LEXER_ENTER :
                $rawparam = trim(substr($match, 1, -1));
                $param    = $this->_parseParam($rawparam);

                //store parameters for closing tag
                $this->_captionparam = $param;

                return array('caption_open', $param);

            case DOKU_LEXER_UNMATCHED :
                // drop unmatched text inside imgcaption/tabcaption tag
                return array('data', '');

            // when normal text it's usefull, then use next lines instead
            //$handler->_addCall('cdata', array($match), $pos);
            //return false;

            case DOKU_LEXER_EXIT :
                //load parameters
                $param = $this->_captionparam;
                return array('caption_close', $param);
        }

        return array();
    }

    /**
     * Render xhtml output, latex output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml, latex and metadata)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $indata    The data from the handler function
     * @return bool If rendering was successful.
     */
    function render($mode, &$renderer, $indata) {
        global $ID;
        list($case, $data) = $indata;

        switch($mode) {
            case 'xhtml' :
                /** @var Doku_Renderer_xhtml $renderer */
                switch($case) {
                    case 'caption_open' :
                        $renderer->doc .= $this->_capstart($data);
                        return true;

                    // $data is empty string
                    case 'data' :
                        $renderer->doc .= $data;
                        return true;

                    case 'caption_close' :
                        //determine referencenumber
                        $caprefs           = p_get_metadata($ID, 'captionreferences '.$data['type']);
                        $data['refnumber'] = array_search($data['caprefname'], $caprefs);

                        $renderer->doc .= $this->_capend($data);
                        return true;
                }
                break;

            case 'metadata' :
                /** @var Doku_Renderer_metadata $renderer */
                switch($case) {
                    case 'caption_open' :
                        // store the image refences as metadata to expose them to the imgref/tabref and undercaption renderer
                        //create array and add index zero entry, so stored caprefnames start counting on one.
                        $type = $data['type'];
                        if(!isset($renderer->meta['captionreferences'][$type])) {
                            $renderer->meta['captionreferences'][$type][] = '';
                        }
                        $renderer->meta['captionreferences'][$type][] = $data['caprefname'];

                        //abstract
                        if($renderer->capture && $data['caption']) $renderer->doc .= '<';
                        return true;

                    case 'caption_close' :
                        //abstract
                        if($renderer->capture && $data['caption']) $renderer->doc .= hsc($data['caption']).'>';
                        return true;
                }
                break;

            case 'latex' :
                if($data['type'] == 'img') {
                    $floattype = 'figure';
                } else {
                    $floattype = 'table';
                }
                switch($case) {
                    case 'caption_open' :
                        $orientation = "\\centering";
                        if(strpos($data['classes'], 'left') !== false) {
                            $orientation = "\\left";
                        } elseif(strpos($data['classes'], 'right') !== false) {
                            $orientation = "\\right";
                        }
                        $renderer->doc .= "\\begin{".$floattype."}[H!]{".$orientation;
                        return true;

                    case 'data' :
                        $renderer->doc .= trim($data);
                        return true;

                    case 'caption_close' :
                        $renderer->doc .= "\\caption{".$data['caption']."}\\label{".$data['caprefname']."}\\end{".$floattype."}";
                        return true;
                }
                break;
        }
        return false;
    }

    /**
     * Parse parameters part of <imgcaption imgref class1 class2|Caption of image>
     *
     * @param string $str space separated parameters e.g."imgref class1 class2|Caption of image"
     * @return array(string imgrefname, string classes, string caption)
     */
    function _parseParam($str) {
        if($str == null || count($str) < 1) {
            return array();
        }

        // get caption, second part
        $parsed  = explode("|", $str, 2);
        $caption = '';
        if(isset($parsed[1])) $caption = trim($parsed[1]);

        // get the img ref name. Its the first word
        $parsed     = explode(" ", $parsed[0], 3);
        $captiontype = substr($parsed[0], 0, 3);
        $caprefname = $parsed[1];

        $tokens  = preg_split('/\s+/', $parsed[2], 9); // limit is defensive
        $classes = '';
        foreach($tokens as $token) {
            // restrict token (class names) characters to prevent any malicious data
            if(preg_match('/[^A-Za-z0-9_-]/', $token)) continue;
            $token = trim($token);
            if($token == '') continue;
            $classes .= ' '.$token;
        }

        return array(
            'caprefname'  => $caprefname,
            'classes'     => $classes,
            'caption'     => $caption,
            'type' => $captiontype
        );
    }

    /**
     * Create html of opening of caption wrapper
     *
     * @param array $data(caprefname, classes, ..)
     * @return string html start of caption wrapper
     */
    function _capstart($data) {

        $layout = '<span class="imgcaption'.($data['type'] =='img' ? ' img':'');
        if($data['classes'] != "") {
            $layout .= $data['classes'];
        }
        $layout .= '">';

        return $layout;
    }

    /**
     * Create html of closing of caption wrapper
     *
     * @param array $data(caprefname, refnumber, caption, ..) Caption data
     * @return string html caption wrapper
     */
    function _capend($data) {
        return '<span class="undercaption">'
                    .$this->getLang($data['type'].'short').' '.$data['refnumber'].($data['caption'] ? ': ' : '')
                    .'<a name="'.$data['type'].'_'.cleanID($data['caprefname']).'">'.hsc($data['caption']).'</a>
                    <a href=" "><span></span></a>
                </span></span>';
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :