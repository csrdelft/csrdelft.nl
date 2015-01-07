<?php
/**
 * DokuWiki Plugin docnav (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Syntax for including a table of content of bundle of pages linked by docnavigation
 */
class syntax_plugin_docnavigation_toc extends DokuWiki_Syntax_Plugin {

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     *
     * @return string
     */
    public function getType() {
        return 'substition';
    }

    /**
     * Paragraph Type
     *
     * Defines how this syntax is handled regarding paragraphs. This is important
     * for correct XHTML nesting. Should return one of the following:
     *
     * 'normal' - The plugin can be used inside paragraphs
     * 'block'  - Open paragraphs need to be closed before plugin output
     * 'stack'  - Special case. Plugin wraps other paragraphs.
     *
     * @see Doku_Handler_Block
     *
     * @return string
     */
    public function getPType() {
        return 'block';
    }

    /**
     * Sort for applying this mode
     *
     * @return int
     */
    public function getSort() {
        return 150;
    }

    /**
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<doctoc\b.*?>', $mode, 'plugin_docnavigation_toc');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;

        $optstrs = substr($match, 7, -1); // remove "<doctoc"  and ">"
        $optstrs = explode(',', $optstrs);
        $options = array();
        foreach($optstrs as $optstr) {
            list($key, $value) = explode('=', $optstr, 2);
            $options[trim($key)] = trim($value);
        }

        //option: start
        if(isset($options['start'])) {
            $options['start'] = $this->getFullPageid($options['start']);
            $options['previous'] = $ID; //workaround for Include plugin: gets only correct ID in handler
        } else {
            $options['start'] = $ID;
            $options['previous'] = null;
        }

        //option: includeheadings
        if(isset($options['includeheadings'])) {
            $levels = explode('-', $options['includeheadings']);
            if(empty($levels[0])) {
                $levels[0] = 2;
            }
            $levels[0] = (int)$levels[0];
            if(empty($levels[1])) {
                $levels[1] = $levels[0];
            }
            $levels[1] = (int)$levels[1];


            //order from low to high
            if($levels[0] > $levels[1]) {
                $level = $levels[1];
                $levels[1] = $levels[0];
                $levels[0] = $level;
            }
            $options['includeheadings'] = array($levels[0], $levels[1]);
        }

        //option: numbers (=use ordered list?)
        $options['numbers'] = !empty($options['numbers']);

        //option: useheading
        $useheading = useHeading('navigation');
        if(isset($options['useheading'])) {
            $useheading = !empty($options['useheading']);
        }
        $options['useheading'] = $useheading;

        return $options;
    }

    /**
     * Handles the actual output creation.
     *
     * @param string        $mode     output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array         $options  data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
    public function render($mode, Doku_Renderer $renderer, $options) {
        global $ID;
        global $ACT;

        if($mode != 'xhtml') return false;
        /** @var Doku_Renderer_xhtml $renderer */

        $renderer->info['cache'] = false;

        $list = array();
        $pageid       = $options['start'];
        $previouspage = $options['previous'];
        while($pageid !== null) {
            $item = array();
            $item['id'] = $pageid;
            $item['ns'] = getNS($item['id']);
            $item['type'] = isset($options['includeheadings']) ? 'pagewithheadings' : 'pageonly'; //page or heading
            $item['level'] = 1;
            $item['ordered'] = $options['numbers'];

            if($options['useheading']) {
                $item['title'] = p_get_first_heading($item['id'], METADATA_DONT_RENDER);
            } else {
                $item['title'] = null;
            }
            $item['perm'] = auth_quickaclcheck($item['id']);

            if($item['perm'] >= AUTH_READ) {
                $list[$pageid] = $item;

                if(isset($options['includeheadings'])) {
                    $toc = p_get_metadata($pageid, 'description tableofcontents', METADATA_RENDER_USING_CACHE);

                    if(is_array($toc)) foreach($toc as $tocitem) {
                        if($tocitem['level'] < $options['includeheadings'][0] || $tocitem['level'] > $options['includeheadings'][1]) {
                            continue;
                        }
                        $item = array();
                        $item['id'] = $pageid . '#' . $tocitem['hid'];
                        $item['ns'] = getNS($item['id']);
                        $item['type'] = 'heading';
                        $item['level'] = 2 + $tocitem['level'] - $options['includeheadings'][0];
                        $item['title'] = $tocitem['title'];

                        $list[$item['id']] = $item;
                    }
                }
            }

            if($ACT == 'preview' && $pageid === $ID) {
                // the RENDERER_CONTENT_POSTPROCESS event is triggered just after rendering the instruction,
                // so syntax instance will exists
                /** @var syntax_plugin_docnavigation_pagenav $pagenav */
                $pagenav = plugin_load('syntax', 'docnavigation_pagenav');
                if($pagenav) {
                    $pagedata = $pagenav->data[$pageid];
                } else {
                    $pagedata = array();
                }
            } else {
                $pagedata = p_get_metadata($pageid, 'docnavigation');
            }

            //check referer
            if(empty($pagedata['previous'][0]) || $pagedata['previous'][0] != $previouspage) {

                // is not first page or non-existing page (so without syntax)?
                if($previouspage !== null && page_exists($pageid)) {
                    msg(sprintf($this->getLang('dontlinkback'), $pageid, $previouspage), -1);
                }
            }

            $previouspage = $pageid;
            if(empty($pagedata['next'][0])) {
                $pageid = null;
            } elseif(isset($list[$pagedata['next'][0]])) {
                msg(sprintf($this->getLang('recursionprevented'), $pageid, $pagedata['next'][0]), -1);
                $pageid = null;
            } else {
                $pageid = $pagedata['next'][0];
            }
        }

        $renderer->doc .= html_buildlist($list, 'pagnavtoc', array($this, 'list_item_navtoc'));

        return true;
    }

    /**
     * Index item formatter
     *
     * User function for html_buildlist()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param array $item
     * @return string
     */
    public function list_item_navtoc($item) {
        // default is noNSorNS($id), but we want noNS($id) when useheading is off FS#2605
        if($item['title'] === null) {
            $name = noNS($item['id']);
        } else {
            $name = $item['title'];
        }

        $ret = '';
        $link = html_wikilink(':' . $item['id'], $name);
        if($item['type'] == 'pagewithheadings') {
            $ret .= '<strong>';
            $ret .= $link;
            $ret .= '</strong>';
        } else {
            $ret .= $link;
        }
        return $ret;
    }

    /**
     * Callback for html_buildlist
     *
     * @param array $item
     * @return string html
     */
    function html_list_toc($item) {
        if(isset($item['hid'])) {
            $link = '#' . $item['hid'];
        } else {
            $link = $item['link'];
        }

        return '<a href="' . $link . '">' . hsc($item['title']) . '</a>';
    }

    /**
     * Resolves given id against current page to full pageid, removes hash
     *
     * @param string $pageid
     * @return mixed
     */
    public function getFullPageid($pageid) {
        global $ID;
        resolve_pageid(getNS($ID), $pageid, $exists);
        list($page, /* $hash */) = explode('#', $pageid, 2);
        return $page;
    }

}

// vim:ts=4:sw=4:et:
