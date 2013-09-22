<?php
/*
 * This plugin extends DokuWiki's list markup syntax to allow definition lists
 * and list items with multiple paragraphs. The complete syntax is as follows:
 *
 *
 *   - ordered list item            [<ol><li>]  <!-- as standard syntax -->
 *   * unordered list item          [<ul><li>]  <!-- as standard syntax -->
 *   ? definition list term         [<dl><dt>]
 *   : definition list definition   [<dl><dd>]
 *
 *   -- ordered list item w/ multiple paragraphs
 *   ** unordered list item w/ multiple paragraphs
 *   :: definition list definition w/multiple paragraphs
 *   .. new paragraph in --, **, or ::
 *
 *
 * Lists can be nested within lists, just as in the standard DokuWiki syntax.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ben Slusky <sluskyb@paranoiacs.org>
 *
 */

if (!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_yalist extends DokuWiki_Syntax_Plugin {
    var $stack = array();

    function getInfo() {
        return array(
            'author' => 'Ben Slusky',
            'email'  => 'sluskyb@paranoiacs.org',
            'date'   => '2007-11-02',
            'name'   => 'Simple universal list plugin',
            'desc'   => 'Extend DokuWiki list syntax to allow definition list and multiple paragraphs in a list entry',
            'url'    => 'http://wiki.splitbrain.org/plugin:yalist',
        );
    }

    function getType() {
        return 'container';
    }

    function getSort() {
        return 9;  // just before listblock (10)
    }

    function getPType() {
        return 'block';
    }

    function getAllowedTypes() {
        return array('substition', 'protected', 'disabled', 'formatting');
    }

    function connectTo($mode) {
       $this->Lexer->addEntryPattern('\n {2,}(?:--?|\*\*?|\?|::?)', $mode, 'plugin_yalist');
       $this->Lexer->addEntryPattern('\n\t{1,}(?:--?|\*\*?|\?|::?)', $mode, 'plugin_yalist');

       $this->Lexer->addPattern('\n {2,}(?:--?|\*\*?|\?|::?|\.\.)', 'plugin_yalist');
       $this->Lexer->addPattern('\n\t{1,}(?:--?|\*\*?|\?|::?|\.\.)', 'plugin_yalist');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('\n', 'plugin_yalist');
    }

    function handle($match, $state, $pos, &$handler) {
        $output = array();
        $level = 0;

        switch ($state) {
        case DOKU_LEXER_ENTER:
            $frame = $this->_interpret_match($match);
            $level = $frame['level'] = 1;

            array_push($output,
                       "${frame['list']}_open",
                       "${frame['item']}_open",
                       "${frame['item']}_content_open");
            if ($frame['paras'])
                array_push($output, 'p_open');

            array_push($this->stack, $frame);
            break;

        case DOKU_LEXER_EXIT:
            $close_content = true;

            while ($frame = array_pop($this->stack)) {
                // for the first frame we pop off the stack, we'll need to
                // close the content tag; for the rest it will have been
                // closed already
                if ($close_content) {
                    if ($frame['paras'])
                        array_push($output, 'p_close');
                    array_push($output, "${frame['item']}_content_close");
                    $close_content = false;
                }
                array_push($output,
                           "${frame['item']}_close",
                           "${frame['list']}_close");
            }

            break;

        case DOKU_LEXER_MATCHED:
            $last_frame = end($this->stack);

            if (substr($match, -2) == '..') {
                // new paragraphs cannot be deeper than the current depth,
                // but they may be shallower
                $para_depth = count(explode('  ', str_replace("\t", '  ', $match)));
                $close_content = true;

                while ($para_depth < $last_frame['depth'] &&
                       count($this->stack) > 1)
                {
                    if ($close_content) {
                        if ($last_frame['paras'])
                            array_push($output, 'p_close');
                        array_push($output, "${last_frame['item']}_content_close");
                        $close_content = false;
                    }

                    array_push($output,
                               "${last_frame['item']}_close",
                               "${last_frame['list']}_close");

                    array_pop($this->stack);
                    $last_frame = end($this->stack);
                }

                if ($last_frame['paras']) {
                    if ($close_content)
                        // depth did not change
                        array_push($output, 'p_close', 'p_open');
                    else
                        array_push($output,
                                   "${last_frame['item']}_content_open",
                                   'p_open');
                } else {
                    // let's just pretend we didn't match...
                    $state = DOKU_LEXER_UNMATCHED;
                    $output = $match;
                }

                break;
            }

            $curr_frame = $this->_interpret_match($match);

            if ($curr_frame['depth'] > $last_frame['depth']) {
                // going one level deeper
                $level = $last_frame['level'] + 1;

                if ($last_frame['paras'])
                    array_push($output, 'p_close');
                array_push($output,
                           "${last_frame['item']}_content_close",
                           "${curr_frame['list']}_open");
            } else {
                // same depth, or getting shallower

                $close_content = true;

                // keep popping frames off the stack until we find a frame
                // that's at least as deep as this one, or until only the
                // bottom frame (i.e. the initial list markup) remains
                while ($curr_frame['depth'] < $last_frame['depth'] &&
                       count($this->stack) > 1)
                {
                    // again, we need to close the content tag only for
                    // the first frame popped off the stack
                    if ($close_content) {
                        if ($last_frame['paras'])
                            array_push($output, 'p_close');
                        array_push($output, "${last_frame['item']}_content_close");
                        $close_content = false;
                    }

                    array_push($output,
                               "${last_frame['item']}_close",
                               "${last_frame['list']}_close");

                    array_pop($this->stack);
                    $last_frame = end($this->stack);
                }

                // pull the last frame off the stack;
                // it will be replaced by the current frame
                array_pop($this->stack);

                $level = $last_frame['level'];

                if ($close_content) {
                    if ($last_frame['paras'])
                        array_push($output, 'p_close');
                    array_push($output, "${last_frame['item']}_content_close");
                    $close_content = false;
                }

                array_push($output, "${last_frame['item']}_close");

                if ($curr_frame['list'] != $last_frame['list']) {
                    // change list types
                    array_push($output,
                               "${last_frame['list']}_close",
                               "${curr_frame['list']}_open");
                }
            }

            // and finally, open tags for the new list item
            array_push($output,
                       "${curr_frame['item']}_open",
                       "${curr_frame['item']}_content_open");
            if ($curr_frame['paras'])
                array_push($output, 'p_open');

            $curr_frame['level'] = $level;
            array_push($this->stack, $curr_frame);
            break;

        case DOKU_LEXER_UNMATCHED:
            $output = $match;
            break;
        }

        return array('state' => $state, 'output' => $output, 'level' => $level);
    }

    function _interpret_match($match) {
        $tag_table = array(
            '*' => 'u_li',
            '-' => 'o_li',
            '?' => 'dt',
            ':' => 'dd',
        );

        $tag = $tag_table[substr($match, -1)];

        return array(
            'depth' => count(explode('  ', str_replace("\t", '  ', $match))),
            'list' => substr($tag, 0, 1) . 'l',
            'item' => substr($tag, -2),
            'paras' => (substr($match, -1) == substr($match, -2, 1)),
        );
    }

    function render($mode, &$renderer, $data) {
        if ($mode != 'xhtml' && $mode != 'latex')
            return false;

        if ($data['state'] == DOKU_LEXER_UNMATCHED) {
            $renderer->doc .= $renderer->_xmlEntities($data['output']);
            return true;
        }

        foreach ($data['output'] as $i) {
            $markup = '';

            if ($mode == 'xhtml') {
                switch ($i) {
                case 'ol_open':             $markup = "<ol>\n"; break;
                case 'ol_close':            $markup = "</ol>\n"; break;
                case 'ul_open':             $markup = "<ul>\n"; break;
                case 'ul_close':            $markup = "</ul>\n"; break;
                case 'dl_open':             $markup = "<dl>\n"; break;
                case 'dl_close':            $markup = "</dl>\n"; break;

                case 'li_open':
                    $markup = "<li class=\"level${data['level']}\">";
                    break;
                case 'li_content_open':
                    $markup = "<div class=\"li\">\n";
                    break;
                case 'li_content_close':
                    $markup = "\n</div>";
                    break;
                case 'li_close':
                    $markup = "</li>\n";
                    break;

                case 'dt_open':
                    $markup = "<dt class=\"level${data['level']}\">";
                    break;
                case 'dt_content_open':
                    $markup = "<span class=\"dt\">";
                    break;
                case 'dt_content_close':
                    $markup = "</span>";
                    break;
                case 'dt_close':
                    $markup = "</dt>\n";
                    break;

                case 'dd_open':
                    $markup = "<dd class=\"level${data['level']}\">";
                    break;
                case 'dd_content_open':
                    $markup = "<div class=\"dd\">\n";
                    break;
                case 'dd_content_close':
                    $markup = "\n</div>";
                    break;
                case 'dd_close':
                    $markup = "</dd>\n";
                    break;

                case 'p_open':              $markup = "<p>\n"; break;
                case 'p_close':             $markup = "\n</p>"; break;
                }
            } else {  // $mode == 'latex'
                switch ($i) {
                case 'ol_open':
                    $markup = "\\begin{enumerate}\n";
                    break;
                case 'ol_close':
                    $markup = "\\end{enumerate}\n";
                    break;
                case 'ul_open':
                    $markup = "\\begin{itemize}\n";
                    break;
                case 'ul_close':
                    $markup = "\\end{itemize}\n";
                    break;
                case 'dl_open':
                    $markup = "\\begin{description}\n";
                    break;
                case 'dl_close':
                    $markup = "\\end{description}\n";
                    break;

                case 'li_open':             $markup = "\item "; break;
                case 'li_content_open':     break;
                case 'li_content_close':    break;
                case 'li_close':            $markup = "\n"; break;

                case 'dt_open':             $markup = "\item["; break;
                case 'dt_content_open':     break;
                case 'dt_content_close':    break;
                case 'dt_close':            $markup = "] "; break;

                case 'dd_open':             break;
                case 'dd_content_open':     break;
                case 'dd_content_close':    break;
                case 'dd_close':            $markup = "\n"; break;

                case 'p_open':              $markup = "\n"; break;
                case 'p_close':             $markup = "\n"; break;
                }
            }

            $renderer->doc .= $markup;
        }

        if ($data['state'] == DOKU_LEXER_EXIT)
            $renderer->doc .= "\n";

        return true;
    }
}
