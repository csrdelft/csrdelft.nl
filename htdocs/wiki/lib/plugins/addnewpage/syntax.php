<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Add-New-Page Plugin: a simple form for adding new pages.
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   iDO <ido@idotech.info>
 * @author   Sam Wilson <sam@samwilson.id.au>
 */
class syntax_plugin_addnewpage extends DokuWiki_Syntax_Plugin {

    /**
     * Syntax Type
     */
    public function getType() {
        return 'substition';
    }

    /**
     * Paragraph Type
     */
    public function getPType() {
        return 'block';
    }

    /**
     * @return int
     */
    public function getSort() {
        return 199;
    }

    /**
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{NEWPAGE[^\}]*\}\}', $mode, 'plugin_addnewpage');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * Handled syntax options:
     *   {{NEWPAGE}}
     *   {{NEWPAGE>your:namespace}}
     *   {{NEWPAGE#newtpl1,newtpl2}}
     *   {{NEWPAGE#newtpl1|Title1,newtpl2|Title1}}
     *   {{NEWPAGE>your:namespace#newtpl1|Title1,newtpl2|Title1}}
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $options = substr($match, 9, -2); // strip markup
        $options = explode('#', $options, 2);

        $namespace = trim(ltrim($options[0], '>'));
        $templates = explode(',', $options[1]);
        $templates = array_map('trim', $templates);
        return array(
            'namespace' => $namespace,
            'newpagetemplates' => $templates
        );
    }

    /**
     * Create the new-page form.
     *
     * @param   $mode     string        output format being rendered
     * @param   $renderer Doku_Renderer the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        global $lang;

        if($mode == 'xhtml') {
            $disablecache = null;
            $namespaceinput = $this->_htmlNamespaceInput($data['namespace'], $disablecache);
            if($namespaceinput === false) {
                if($this->getConf('addpage_hideACL')) {
                    $renderer->doc .= '';
                } else {
                    $renderer->doc .= $this->getLang('nooption');
                }
                return true;
            }
            if($disablecache) $renderer->info['cache'] = false;

            $newpagetemplateinput = $this->_htmlTemplateInput($data['newpagetemplates']);

            $form = '<div class="addnewpage">' . DOKU_LF
                . DOKU_TAB . '<form name="addnewpage" method="get" action="' . DOKU_BASE . DOKU_SCRIPT . '" accept-charset="' . $lang['encoding'] . '">' . DOKU_LF
                . DOKU_TAB . DOKU_TAB . $namespaceinput . DOKU_LF
                . DOKU_TAB . DOKU_TAB . '<input class="edit" type="text" name="title" size="20" maxlength="255" tabindex="2" />' . DOKU_LF
                . $newpagetemplateinput
                . DOKU_TAB . DOKU_TAB . '<input type="hidden" name="do" value="edit" />' . DOKU_LF
                . DOKU_TAB . DOKU_TAB . '<input type="hidden" name="id" />' . DOKU_LF
                . DOKU_TAB . DOKU_TAB . '<input class="button" type="submit" value="' . $this->getLang('okbutton') . '" tabindex="4" />' . DOKU_LF
                . DOKU_TAB . '</form>' . DOKU_LF
                . '</div>';
            $renderer->doc .= $form;

            return true;
        }
        return false;
    }

    /**
     * Parse namespace request
     *
     * @author Samuele Tognini <samuele@cli.di.unipi.it>
     * @author Michael Braun <michael-dev@fami-braun.de>
     */
    protected function _parse_ns($ns) {
        global $ID;
        if($ns == "@PAGE@") return $ID;
        if($ns == "@NS@") return getNS($ID);
        $ns = preg_replace("/^\.(:|$)/", dirname(str_replace(':', '/', $ID)) . "$1", $ns);
        $ns = str_replace("/", ":", $ns);
        $ns = cleanID($ns);
        return $ns;
    }

    /**
     * Create the HTML Select element for namespace selection.
     *
     * @param string|false $dest_ns The destination namespace, or false if none provided.
     * @param bool $disablecache reference indicates if caching need to be disabled
     * @global string $ID The page ID
     * @return string Select element with appropriate NS selected.
     */
    protected function _htmlNamespaceInput($dest_ns, &$disablecache) {
        global $ID;
        $disablecache = false;

        // If a NS has been provided:
        // Whether to hide the NS selection (otherwise, show only subnamespaces).
        $hide = $this->getConf('addpage_hide');

        // Whether the user can create pages in the provided NS (or root, if no
        // destination NS has been set.
        $can_create = (auth_quickaclcheck($dest_ns . ":") >= AUTH_CREATE);

        //namespace given, but hidden
        if($hide && !empty($dest_ns)) {
            if($can_create) {
                return '<input type="hidden" name="np_cat" id="np_cat" value="' . $this->_parse_ns($dest_ns) . '"/>';
            } else {
                return false;
            }
        }

        //show select of given namespace
        $currentns = getNS($ID);

        $ret = '<select class="edit" id="np_cat" name="np_cat" tabindex="1">';

        // Whether the NS select element has any options
        $someopt = false;

        // Show root namespace if requested and allowed
        if($this->getConf('addpage_showroot') && $can_create) {
            if(empty($dest_ns)) {
                // If no namespace has been provided, add an option for the root NS.
                $option_text = ((@$this->getLang('namespaceRoot')) ? $this->getLang('namespaceRoot') : 'top');
                $ret .= '<option ' . (($currentns == '') ? 'selected ' : '') . 'value="">' . $option_text . '</option>';
                $someopt = true;
            } else {
                // If a namespace has been provided, add an option for it.
                $ret .= '<option ' . (($currentns == $dest_ns) ? 'selected ' : '') . 'value="' . $dest_ns . '">' . $dest_ns . '</option>';
                $someopt = true;
            }
        }

        $subnamespaces = $this->_getnslist($dest_ns);
        foreach($subnamespaces as $ns) {
            if(auth_quickaclcheck($ns . ":") < AUTH_CREATE) continue;
            $nsparts = explode(':', $ns);
            $nsparts = str_repeat('&nbsp;&nbsp;', substr_count($ns, ':')) . $nsparts[count($nsparts) - 1];
            $ret .= '<option ' . (($currentns == $ns) ? 'selected ' : '') . 'value="' . $ns . '">' . $nsparts . '</option>';
            $someopt = true;
            $disablecache = true;
        }
        $ret .= '</select>';

        if($someopt) {
            return $ret;
        } else {
            return false;
        }
    }

    /**
     * Get a list of namespaces below the given namespace.
     * Recursively fetches subnamespaces.
     *
     * @param string $topns The top namespace
     * @return array Multi-dimensional array of all namespaces below $tns
     */
    protected function _getnslist($topns = '') {
        global $conf;

        $topns = utf8_encodeFN(str_replace(':', '/', $topns));

        $excludes = $this->getConf('addpage_exclude');
        if($excludes == "") {
            $excludes = array();
        } else {
            $excludes = @explode(';', strtolower($excludes));
        }
        $searchdata = array();
        search($searchdata, $conf['datadir'], 'search_namespaces', array(), $topns);

        $namespaces = array();
        foreach($searchdata as $ns) {
            foreach($excludes as $exclude) {
                if(strpos($ns['id'], $exclude) === 0) {
                    continue 2;
                }
            }
            $namespaces[] = $ns['id'];
        }

        return $namespaces;
    }

    /**
     * Create html for selection of namespace templates
     *
     * @param array $newpagetemplates array of namespace templates
     * @return string html of select or hidden input
     */
    public function _htmlTemplateInput($newpagetemplates) {
        $cnt = count($newpagetemplates);
        if($cnt < 1 || $cnt == 1 && $newpagetemplates[0] == '') {
            $input = '';

        } else {
            if($cnt == 1) {
                list($template, ) = $this->_parseNStemplatepage($newpagetemplates[0]);
                $input = '<input type="hidden" name="newpagetemplate" value="' . $template . '" />';
            } else {
                $first = true;
                $input = '<select name="newpagetemplate" tabindex="3">';
                foreach($newpagetemplates as $template) {
                    $p = ($first ? ' selected="selected"' : '');
                    $first = false;

                    list($template, $name) = $this->_parseNStemplatepage($template);
                    $p .= ' value="'.formText($template).'"';
                    $input .= "<option $p>".formText($name)."</option>";
                }
                $input .= '</select>';
            }
            $input = DOKU_TAB . DOKU_TAB . $input . DOKU_LF;
        }
        return $input;
    }

    /**
     * Parses and resolves the namespace template page
     *
     * @param $nstemplate
     * @return array
     */
    protected function _parseNStemplatepage($nstemplate) {
        global $ID;

        @list($template, $name) = explode('|', $nstemplate, 2);

        $exist = null;
        resolve_pageid(getNS($ID), $template, $exist); //get absolute id

        if (is_null($name)) $name = $template;

        return array($template, $name);
    }

}
