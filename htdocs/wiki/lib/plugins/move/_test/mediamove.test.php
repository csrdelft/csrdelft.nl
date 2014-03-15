<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for the move plugin
 */
class plugin_move_mediamove_test extends DokuWikiTest {

    public function setUp() {
        $this->pluginsEnabled[] = 'move';
        parent::setUp();
    }

    public function test_movePageWithRelativeMedia() {
        global $ID;

        $ID = 'mediareltest:foo';
        saveWikiText($ID,
            '{{ myimage.png}} [[:start|{{ testimage.png?200x800 }}]] [[bar|{{testimage.gif?400x200}}]]
[[doku>wiki:dokuwiki|{{wiki:logo.png}}]] [[http://www.example.com|{{testimage.jpg}}]]
[[doku>wiki:foo|{{foo.gif?200x3000}}]]', 'Test setup');
        idx_addPage($ID);

        $opts = array();
        $opts['ns']   = getNS($ID);
        $opts['name'] = noNS($ID);
        $opts['newns'] = '';
        $opts['newname'] = 'foo';
        /** @var helper_plugin_move $move */
        $move = plugin_load('helper', 'move');
        $this->assertTrue($move->move_page($opts));

        $this->assertEquals('{{ mediareltest:myimage.png}} [[:start|{{ mediareltest:testimage.png?200x800 }}]] [[mediareltest:bar|{{mediareltest:testimage.gif?400x200}}]]
[[doku>wiki:dokuwiki|{{wiki:logo.png}}]] [[http://www.example.com|{{mediareltest:testimage.jpg}}]]
[[doku>wiki:foo|{{mediareltest:foo.gif?200x3000}}]]', rawWiki('foo'));
    }

    public function test_moveSingleMedia() {
        global $AUTH_ACL;

        $AUTH_ACL[] = "wiki:*\t@ALL\t16";
        $AUTH_ACL[] = "foobar:*\t@ALL\t8";

        saveWikiText('wiki:movetest', '{{wiki:dokuwiki-128.png?200}}', 'Test initialized');
        idx_addPage('wiki:movetest');

        $opts = array();
        $opts['ns'] = 'wiki';
        $opts['name'] = 'dokuwiki-128.png';
        $opts['newns'] = 'foobar';
        $opts['newname'] = 'logo.png';

        /** @var helper_plugin_move $move */
        $move = plugin_load('helper', 'move');
        $this->assertTrue($move->move_media($opts));

        $this->assertTrue(@file_exists(mediaFn('foobar:logo.png')));

        $this->assertEquals('{{foobar:logo.png?200}}', rawWiki('wiki:movetest'));
    }
}
