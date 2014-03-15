<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for the media usage index
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class plugin_move_mediaindex_test extends DokuWikiTest {

    public function setUp() {
        $this->pluginsEnabled[] = 'move';
        parent::setUp();
    }

    public function test_internalmedia() {
        saveWikiText('test:internalmedia_usage', '{{internalmedia.png}} {{..:internal media.png}}', 'Test initialization');
        idx_addPage('test:internalmedia_usage');

        $query = array('test:internalmedia.png', 'internal_media.png');
        $this->assertEquals( array(
            'test:internalmedia.png' => array('test:internalmedia_usage'),
            'internal_media.png' => array('test:internalmedia_usage')
        ), idx_get_indexer()->lookupKey('relation_media', $query));
    }

    public function test_media_in_links() {
        saveWikiText('test:medialinks', '[[doku>wiki:dokuwiki|{{wiki:logo.png}}]] [[http://www.example.com|{{example.png?200x800}}]]', 'Test init');
        idx_addPage('test:medialinks');

        $query = array('wiki:logo.png', 'test:example.png');
        $this->assertEquals(array(
            'wiki:logo.png' => array('test:medialinks'),
            'test:example.png' => array('test:medialinks')
        ), idx_get_indexer()->lookupKey('relation_media', $query));
    }

    public function test_media_in_footnotes() {
        saveWikiText('test:media_footnotes', '(({{footnote.png?20x50}} [[foonote|{{:footlink.png}}]]))', 'Test initialization');
        idx_addPage('test:media_footnotes');

        $query = array('test:footnote.png', 'footlink.png');
        $this->assertEquals(array(
            'test:footnote.png' => array('test:media_footnotes'),
            'footlink.png' => array('test:media_footnotes')
        ), idx_get_indexer()->lookupKey('relation_media', $query));
    }
}
