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

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_imagereference_imgcaption extends DokuWiki_Syntax_Plugin {

    /* @var array $_captionparam */
    protected $_captionparam = array();

    /**
     * @return string Syntax type
     */
    public function getType() {
        return 'formatting';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'normal';
    }

    /**
     * @return int Sort order
     */
    public function getSort() {
        return 196;
    }

    /**
     * Specify modes allowed in the imgcaption/tabcaption
     * Using getAllowedTypes() includes too much modes.
     *
     * @param string $mode Parser mode
     * @return bool true if $mode is accepted
     */
    public function accepts($mode) {
        $allowedsinglemodes = array(
            'media', //allowed content
            'internallink', 'externallink', 'linebreak', //clickable img allowed
            'emaillink', 'windowssharelink', 'filelink',
            'plugin_graphviz', 'plugin_ditaa'    //plugins
        );
        if(in_array($mode, $allowedsinglemodes)) return true;

        return parent::accepts($mode);
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addEntryPattern('<imgcaption.*?>(?=.*?</imgcaption>)', $mode, 'plugin_imagereference_imgcaption');

    }

    public function postConnect() {
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
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        global $ACT;
        switch($state) {
            case DOKU_LEXER_ENTER :
                $rawparam = trim(substr($match, 1, -1));
                $param    = $this->_parseParam($rawparam);

                //store parameters for closing tag
                $this->_captionparam = $param;

                //local counter for preview
                if($ACT == 'preview') {
                    self::captionReferencesStorage($param['type'], $param);
                }

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
    public function render($mode, Doku_Renderer &$renderer, $indata) {
        global $ID, $ACT;
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
                        if($ACT == 'preview') {
                            $caprefs = self::getCaptionreferences($ID, $data['type']);
                        } else {
                            $caprefs = p_get_metadata($ID, 'captionreferences '.$data['type']);
                        }
                        $data['refnumber'] = array_search($data['caprefname'], $caprefs);

                        if(!$data['refnumber']) {
                            $data['refnumber'] = "##";
                        }

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
                        $renderer->doc .= "\\begin{".$floattype."}[!h]{".$orientation."}";
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
     * When a array of caption data is given, this is stored. Otherwise the array is returned
     *
     * @param string $type          'img' or 'tab'
     * @param array  $captiondata   array with data of the caption
     * @param string $id            page id
     * @return void|array
     */
    static private function captionReferencesStorage($type, $captiondata = null, $id = null) {
        global $ID;
        static $captionreferences = array();

        if($captiondata !== null) {
            //store reference names
            if(!isset($captionreferences[$ID][$type])) {
                $captionreferences[$ID][$type][] = '';
            }
            $captionreferences[$ID][$type][] = $captiondata['caprefname'];
            return null;

        } else {
            //return reference names
            if($id === null) {
                $id = $ID;
            }
            return $captionreferences[$id][$type];
        }
    }

    /**
     * Returns the captionreferences of page
     *
     * @param string $id   page id
     * @param string $type caption type 'img' or 'tab'
     * @return array of stored reference names
     */
    static public function getCaptionreferences($id, $type) {
        return self::captionReferencesStorage($type, null, $id);
    }

    /**
     * Parse parameters part of <imgcaption imgref class1 class2|Caption of image>
     *
     * @param string $str space separated parameters e.g."imgref class1 class2|Caption of image"
     * @return array(string imgrefname, string classes, string caption)
     */
    protected function _parseParam($str) {
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
     * @var string $captionStart opening tag of caption, image/table dependent
     * @var string $captionEnd closing tag of caption, image/table dependent
     */
    protected $captionStart = '<span id="%s" class="imgcaption%s">';
    protected $captionEnd   = '</span>';

    /**
     * Create html of opening of caption wrapper
     *
     * @param array $data(caprefname, classes, ..)
     * @return string html start of caption wrapper
     */
    protected function _capstart($data) {
      return sprintf(
          $this->captionStart,
          $data['type'].'_'.cleanID($data['caprefname']),
          (strpos($data['classes'], 'center') == false ? '':' center'),
          $data['classes']  //needed for tabcaption
      ).DOKU_LF;
    }

    /**
     * Create html of closing of caption wrapper
     *
     * @param array $data(caprefname, refnumber, caption, ..) Caption data
     * @return string html caption wrapper
     */
    protected function _capend($data) {
        return DOKU_LF
                .'<span class="undercaption">'.DOKU_LF
                    .DOKU_TAB.$this->getLang($data['type'].'short').' '.$data['refnumber'].($data['caption'] ? ': ' : '')
                    .' '.hsc($data['caption'])
                    .' <a href=" "><span></span></a>'.DOKU_LF
                .'</span>'.DOKU_LF
            . $this->captionEnd;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :