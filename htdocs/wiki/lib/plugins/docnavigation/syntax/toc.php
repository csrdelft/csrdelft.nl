<?php
/**
 * DokuWiki Plugin docnav (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();


class syntax_plugin_docnav_toc extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getSort() {
        return 150;
    }


    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<doctoc>',$mode,'plugin_docnav_toc');
    }

    public function handle($match, $state, $pos, &$handler){
        $data = array();
        global $ID, $conf;

        $pages = array();
        $ns = str_replace(':', '/', getNS($ID));
        search($pages,$conf['datadir'],'search_list',array(),$ns);

        /*
         * pages inclusief pages in namespaces
         */
        //$pages2 = array();
        //$dir = $conf['datadir'].($ns ? '/'.$ns : '');

        // returns the list of pages in the given namespace and it's subspaces
        //$items = array();
        //search($pages2, $dir, 'search_allpages', array());

        //list of namespaces (in id entry)
        //search($pages3, $conf['datadir'], 'search_namespaces', array());
        //dbg($pages2);
        //dbg($pages3);/*

        dbg($pages);
        $metadata = array();
        $firstpid = $pages[0]['id']; //default first page of namespace
        foreach($pages as $page) {

            $pmeta = p_get_metadata($page['id'], 'docnav');
            if($pmeta === null) continue;

            //page without previous page is the first of sequence
            if(!$pmeta['previous']) $firstpid = $page['id'];
            foreach($pmeta as &$url) {
                //$url = array(pageid, title)
                if($url[0]) {
                    $url[0] = $this->getFullPageid($url[0], 2);
                }
            }
            $metadata[$page['id']] = new ListNode($page['id'], $pmeta);
        }

        dbg($metadata);

        $sorted = new DoublyLinkedList();
        $sorted->insertFirst($metadata[$firstpid]);

        $current = $sorted->getLast();
        $nextid = $current->getNextId();
        //dbg($nextid);
        while($nextid && isset($metadata[$nextid])) {
            $exist = $sorted->search($nextid);
            if($exist === null) {
                $previd = $metadata[$nextid]->getPrevId();
                if($previd == $current->id || !$node = $this->searchprevious($metadata, $current->id)) {
                    $sorted->insertLast($metadata[$nextid]);
                } else {
                    $sorted->insertLast($node);
                }
                $current = $sorted->getLast();
            } else {
                //ready?
                break;
            }
        }
       //dbg($sorted);

        return array($metadata,$sorted);
    }

    public function render($mode, &$renderer, $data) {

        if($mode != 'xhtml') return false;

        /**
         * @var array            $metadata
         * @var DoublyLinkedList $sorted
         */
        list($metadata, $sorted) = $data;

        $sorted->displayForward();

        return true;
    }

    public function getFullPageid($pageid) {
        global $ID;
        resolve_pageid(getNS($ID), $pageid, $exists);   //TODO relative to page
        list($page, $hash) = explode('#', $pageid, 2);
        return $page;
    }

    public function searchprevious(&$metadata, $previd) {
        foreach($metadata as $node) {
            if($node->getValue('previous') == $previd) {
                return $node;
            }
        }
        return false;
    }

}

/**
 * doublelist.class.php
 * http://www.codediesel.com/algorithms/doubly-linked-list-in-php/
 */

class ListNode {

    public $data;
    public $next;
    public $previous;
    public $id;

    function __construct($id, $data) {
        $this->id = $id;
        $this->data = $data;
    }

    public function getId() {
        return $this->id;
    }
    public function getNextId() { return $this->data['next'][0]; }
    public function getPrevId() { return $this->data['previous'][0]; }

}


class DoublyLinkedList {
    /** @var ListNode $_firstNode  */
    private $_firstNode;
    /** @var ListNode $_lastNode */
    private $_lastNode;
    /** @var int $_count */
    private $_count;

    function __construct() {
        $this->_firstNode = NULL;
        $this->_lastNode = NULL;
        $this->_count = 0;
    }

    public function isEmpty() {
        return ($this->_firstNode == NULL);
    }

    public function insertFirst($node) {
        $newLink = $node;

        if($this->isEmpty()) {
            $this->_lastNode = $newLink;
        } else {
            $this->_firstNode->previous = $newLink;
        }

        $newLink->next = $this->_firstNode;
        $this->_firstNode = $newLink;
        $this->_count++;
    }


    public function insertLast($node) {
        $newLink = $node;

        if($this->isEmpty()) {
            $this->_firstNode = $newLink;
        } else {
            $this->_lastNode->next = $newLink;
        }

        $newLink->previous = $this->_lastNode;
        $this->_lastNode = $newLink;
        $this->_count++;
    }


    public function insertAfter($key, $node) {

        $current = $this->search($key);
        if($current === null) return false;

        $newLink = $node;

        if($current == $this->_lastNode) {
            $newLink->next = NULL;
            $this->_lastNode = $newLink;
        } else {
            $newLink->next = $current->next;
            $current->next->previous = $newLink;
        }

        $newLink->previous = $current;
        $current->next = $newLink;
        $this->_count++;

        return true;
    }


    public function deleteFirstNode() {

        $temp = $this->_firstNode;

        if($this->_firstNode->next == NULL) {
            $this->_lastNode = NULL;
        } else {
            $this->_firstNode->next->previous = NULL;
        }

        $this->_firstNode = $this->_firstNode->next;
        $this->_count--;
        return $temp;
    }


    public function deleteLastNode() {

        $temp = $this->_lastNode;

        if($this->_firstNode->next == NULL) {
            $this->_firstNode = NULL;
        } else {
            $this->_lastNode->previous->next = NULL;
        }

        $this->_lastNode = $this->_lastNode->previous;
        $this->_count--;
        return $temp;
    }


    public function deleteNode($key) {

        $current = $this->search($key);
        if($current === null) return null;

        if($current == $this->_firstNode) {
            $this->_firstNode = $current->next;
        } else {
            $current->previous->next = $current->next;
        }

        if($current == $this->_lastNode) {
            $this->_lastNode = $current->previous;
        } else {
            $current->next->previous = $current->previous;
        }

        $this->_count--;
        return $current;
    }

    public function getFirst() {
        return $this->_firstNode;
    }

    public function getLast() {
        return $this->_lastNode;
    }

    public function search($id) {
        $current = $this->_firstNode;

        while($current->getId() != $id) {
            $current = $current->next;
            if($current == NULL)
                return null;
        }
        return $current;
    }

    public function displayForward() {

        $current = $this->_firstNode;

        while($current != NULL) {
            echo $current->getId() . " \n";
            $current = $current->next;
        }
    }

    public function displayBackward() {

        $current = $this->_lastNode;

        while($current != NULL) {
            echo $current->getId() . " \n";
            $current = $current->previous;
        }
    }

    public function totalNodes() {
        return $this->_count;
    }

}


/**
 * Page node of ToC
 */
/*
class Node {
    private $data;
    public
        $next = null,
        $prev = null;


    public function __construct($data) {
        $this->data = $data; //contains id
    }

    /**
     * Get value $key from inner data
     *
     * @param $key
     * @return mixed
     */      /*
    public function get($key) {
        return $this->data[$key]; //TODO key not exist - default value/return false
    }

    /**
     * Check if the node for page $id already exists
     * @param $id
     * @return bool
     */       /*
    public function exist($id) {
        if($id == $this->data['id']) {
            return true;
        } else {
            if($this->next !== null) {
                return $this->next->exist($id);
            } else {
                return false;
            }
        }
    }
}

/**
 * Page tree of ToC
 */      /*
class Tree {
    /** @var Node $first */  /*
    private $first;
    private $pages; //array(array('prev'=>'', 'next'=>'', 'toc'=>''))
    //private $sorted;

    public function populate() {
        $this->first = new Node($this->getFirstpage());

        $current = $this->first;
        while(true) {
            $nextid = $current->get('next');

            if($nextid) {
                if($this->exist($nextid)) {

                } else {
                    $current->next = new Node($this->pages[$nextid]);
                    $current = $current->next;
                }
            } else {
                break;
            }
        }
    }

    /**
     * Search node without predecessor, otherwise random node.
     */                 /*
    public function getFirstpage() {
        //First page has no predecessor
        foreach($this->pages as $page) {
            if(empty($page['previous'])) {
                return $page;
            }
        }
        //return random first TODO empty pageMetadata
        return $this->pages[0];
    }

    /**
     * Do a node exist for page $id
     *
     * @param string $id pageid
     */         /*
    public function exist($id) {
        $this->first->exist($id);
    }
}

/*
class nodeElement {
    public
        $data,
        $prev=false,
        $next=false;

    public function __construct($data) {
        $this->data=$data;
    }

    public function show() {
        if ($this->prev) $this->prev->show();
        echo $this->data,'<br />';
        if ($this->next) $this->next->show();
    }
}

class nodeTree {
    public
        $first=false;

    public function add($data) {
        if ($this->first) {
            $current=$this->first;
            while (true) {
                if (strnatcasecmp($current->data,$data)<0) {
                    if ($current->next) {
                        $current=$current->next;
                    } else {
                        $current->next=new nodeElement($data);
                        break;
                    }
                } else {
                    if ($current->prev) {
                        $current=$current->prev;
                    } else {
                        $current->prev=new nodeElement($data);
                        break;
                    }
                }
            }
        } else {
            $this->first=new nodeElement($data);
        }
    }

    public function show() {
        if ($this->first) {
            $this->first->show();
        }
    }

}  */

/*
class Node {
    var $left;
    var $right;
    var $data;

    function BinaryTree() {
        $this->left  = null;
        $this->right = null;
    }

    function search($key) {
        if($this->data == $key)
            return true;
        if($this->left != null && $this->data < $key)
            return $this->left->search($key);
        if($this->right != null && $this->data > $key)
            return $this->right->search($key);
        return false;
    }

    function addItem($key) {
        if($this->data == $key) {
            return false; //already got it
        }
        if($this->left != null && $key < $this->data)
            return $this->left->addItem($key);
        if($this->right != null && $key > $this->data)
            return $this->right->addItem($key);
        if($this->left == null && $key < $this->data) {
            $this->left       = new Node();
            $this->left->data = $item;
            return true;
        }
        if($right == null && $key > $this->data) {
            $this->right       = new Node();
            $this->right->data = $item;
            return true;
        }
    }
}

class BinaryTree {
    var $root;

    function BinaryTree() {
        $this->root = null;
    }

    function search($key) {
        if($this->root == null)
            return false;
        return $this->root->search($key);
    }

    function addItem($item) {
        if($this->root == null) {
            $this->root       = new Node();
            $this->root->data = $item;
            return true;
        }
        return $this->root->addItem($item);
    }
}
*/


// vim:ts=4:sw=4:et:
