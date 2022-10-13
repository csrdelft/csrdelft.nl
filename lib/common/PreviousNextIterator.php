<?php

namespace CsrDelft\common;

use ArrayIterator;

/**
 * Previous Next Iterator adds two methods to the ArrayIterator
 *
 *  -> getPreviousElement() To get the previous element of the iterator
 *  -> getNextElement()     To get the next element of the iterator
 *
 * These methods will not affect the internal pointer
 */
class PreviousNextIterator extends ArrayIterator
{
    protected $indexKeys = array();
    protected $keyIndexs = array();
    protected $elements  = array();
    protected $dirty     = true;

    /**
     * Constructor
     *
     * @param array   $array Input Array
     * @param integer $flags Flags
     */
    public function __construct($array = array(), $flags = 0)
    {
        parent::__construct($array, $flags);

        $this->load();
    }

    /**
     * Helper class to self create from an ArrayIterator
     *
     * @param  ArrayIterator        $iterator ArrayIterator to fetch
     * @return PreviousNextIterator New self instance
     */
    public static function createFromIterator(ArrayIterator $iterator)
    {
        return new self($iterator->getArrayCopy());
    }

    /**
     * Get the previous element of the iterator
     *
     * @return mixed Previous element
     */
    public function getPreviousElement()
    {
        $index = $this->getIndexKey($this->key());

        if (--$index < 0) {
            return;
        }

        $key = $this->getKeyIndex($index);

        return $this->elements[$key];
    }

    /**
     * Get the next element of the iterator
     *
     * @return mixed Next element
     */
    public function getNextElement()
    {
        $index = $this->getIndexKey($this->key());

        if (++$index >= $this->count()) {
            return;
        }

        $key = $this->getKeyIndex($index);

        return $this->elements[$key];
    }

    /**
     * Loads up the keys
     *
     * $this->elements
     *     Contains the copy of the iterator array
     *     Eg: [ 'a' => $fooInstance1, 'b' => $fooInstance2 ...]
     *
     * $this->keyIndexs
     *     Contains the keys indexed numerically
     *     Eg: [ 0 => 'a', 1 => 'b' ...]
     *
     * $this->indexKeys
     *     Contains the indexes of the keys
     *     Eg: [ 'a' => 0, 'b' => 1 ...]
     */
    protected function load()
    {
        if (!$this->isDirty()) {
            return;
        }

        $this->elements  = $this->getArrayCopy();
        $this->keyIndexs = array_keys($this->elements);
        $this->indexKeys = array_flip($this->keyIndexs);
        $this->dirty     = false;

    }

    /**
     * Checks whether the loader is dirty
     *
     * @return boolean
     */
    protected function isDirty()
    {
        return $this->dirty;
    }

    /**
     * Get the Index of a given key
     *
     * @param  string  $key Key name
     * @return integer Key's index
     */
    protected function getIndexKey($key)
    {
        $this->load();

        return array_key_exists($key, $this->indexKeys)
            ? $this->indexKeys[$key]
            : null;
    }

    /**
     * Get the key of a given index
     *
     * @param  integer $index Key's index 
     * @return string  Key name
     */
    protected function getKeyIndex($index)
    {
        $this->load();

        return array_key_exists($index, $this->keyIndexs)
            ? $this->keyIndexs[$index]
            : null;
    }

    /**
     * Following methods overrides default methods which alters the iterator
     * in order to create a "Dirty state" which will force the reload
     *
     * You just need to write them all so as to get a complete working class
     */
    public function append($value)
    {
        $this->dirty = true;

        return parent::append($value);
    }
}